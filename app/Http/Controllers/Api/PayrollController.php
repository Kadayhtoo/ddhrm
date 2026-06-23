<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PayrollResource;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PayrollController extends Controller
{
    public function __construct(
        protected PayrollService $payrollService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        return PayrollResource::collection(
            $this->payrollService->list($this->filters($request), $perPage)
        );
    }

    public function stats(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->payrollService->stats($request->user()),
        ]);
    }

    public function show(int $id): PayrollResource
    {
        return new PayrollResource($this->payrollService->show($id));
    }

    // public function override(Request $request, int $id): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'base_salary' => 'nullable|numeric',
    //         'gross_salary' => 'nullable|numeric',
    //         'total_deductions' => 'nullable|numeric',
    //         'net_salary' => 'nullable|numeric',
    //         'late_penalty' => 'nullable|numeric',
    //         'unpaid_leave_deduction' => 'nullable|numeric',
    //         'paid_leave_deduction' => 'nullable|numeric',
    //         'note' => 'nullable|string',
    //     ]);

    //     $user = $request->user();

    //     if (! $user->hasRoleSlug('ceo') && ! $user->hasPermission('payroll.manage')) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $payroll = $this->payrollService->show($id);

    //     $payroll->update(array_filter($validated, fn($v) => $v !== null));

    //     return response()->json([
    //         'data' => new PayrollResource($payroll->fresh()),
    //         'message' => 'Payroll updated (override) successfully.',
    //     ]);
    // }
    public function override(Request $request, int $id): JsonResponse
{
    $validated = $request->validate([
        'base_salary' => 'nullable|numeric',
        'gross_salary' => 'nullable|numeric',
        'total_deductions' => 'nullable|numeric',
        'net_salary' => 'nullable|numeric',
        'late_penalty' => 'nullable|numeric',
        'unpaid_leave_deduction' => 'nullable|numeric',
        'paid_leave_deduction' => 'nullable|numeric',
        'note' => 'nullable|string',
    ]);

    $payroll = $this->payrollService->show($id);

    $payroll->fill(array_filter($validated, fn($v) => $v !== null));

    $payroll->net_salary = $payroll->base_salary 
                         - $payroll->late_penalty 
                         - $payroll->unpaid_leave_deduction 
                         - $payroll->paid_leave_deduction;

    $payroll->save();

    return response()->json([
        'data' => new PayrollResource($payroll->fresh()),
        'message' => 'Payroll updated and recalculated successfully.',
    ]);
}

    // public function payslip(Request $request, int $id)
    // {
    //     $payroll = $this->payrollService->show($id);

    //     $data = ['payroll' => $payroll];

    //     if (! class_exists('\PDF')) {
    //         return response()->json(['data' => $data, 'warning' => 'PDF generator not installed.'], 200);
    //     }

    //     $pdf = \PDF::loadView('payslip', $data);

    //     $filename = sprintf('payslip_%d_%s.pdf', $payroll->id, now()->format('Ymd'));

    //     return $pdf->download($filename);
    // }
    public function payslip(Request $request, int $id)
    {
        $payroll = $this->payrollService->show($id);
        $company = \App\Models\AboutUs::first(); 

        $data = [
            'payroll' => $payroll,
            'company' => $company
        ];

        if (!class_exists('\PDF')) {
            return response()->json(['data' => $data, 'warning' => 'PDF generator not installed.'], 200);
        }

        $pdf = \PDF::loadView('payslip', $data);
        $filename = sprintf('payslip_%d_%s.pdf', $payroll->id, now()->format('Ymd'));

        return $pdf->download($filename);
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'period_type' => 'required|in:daily,monthly',
            'date' => 'required_if:period_type,daily|date',
            'year' => 'required_if:period_type,monthly|integer|min:2020|max:2099',
            'month' => 'required_if:period_type,monthly|integer|min:1|max:12',
        ]);

        $user = User::query()->findOrFail($validated['user_id']);

        if (! $user->salary || (float) $user->salary <= 0) {
            throw ValidationException::withMessages([
                'salary' => ['User has no salary set. Please set a salary first.'],
            ]);
        }

        if ($validated['period_type'] === 'monthly') {
            $payroll = $this->payrollService->calculateMonthly(
                $user->id,
                (int) $validated['year'],
                (int) $validated['month']
            );
        } else {
            $payroll = $this->payrollService->calculateDaily(
                $user->id,
                $validated['date']
            );
        }

        return (new PayrollResource($payroll))
            ->response()
            ->setStatusCode(201);
    }

    public function markPaid(int $id): JsonResponse
    {
        $payroll = $this->payrollService->markAsPaid($id);

        return response()->json([
            'data' => new PayrollResource($payroll),
            'message' => 'Payroll marked as paid.',
        ]);
    }

    public function employees(): JsonResponse
    {
        $users = User::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'department_id', 'salary'])
            ->with('department:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'department_id' => $user->department_id,
                'department' => $user->department ? ['id' => $user->department->id, 'name' => $user->department->name] : null,
                'salary' => $user->salary,
                'has_salary' => ! is_null($user->salary) && (float) $user->salary > 0,
            ]);

        return response()->json($users);
    }

    public function settings(Request $request): JsonResponse
    {
        if ($request->isMethod('get')) {
            return response()->json([
                'data' => [
                    'daily_work_minutes' => $this->payrollService->dailyWorkMinutes(),
                    'late_penalty_rate' => $this->payrollService->latePenaltyRate(),
                    'paid_leave_deduction_rate' => $this->payrollService->paidLeaveDeductionRate(),
                    'days_per_month' => $this->payrollService->daysPerMonth(),
                ],
            ]);
        }

        $validated = $request->validate([
            'daily_work_minutes' => 'integer|min:60|max:720',
            'late_penalty_rate' => 'numeric|min:0|max:100000',
            'paid_leave_deduction_rate' => 'numeric|min:0|max:1',
            'days_per_month' => 'integer|min:20|max:31',
        ]);

        if (isset($validated['daily_work_minutes'])) {
            \App\Models\Setting::setValue('payroll.daily_work_minutes', $validated['daily_work_minutes']);
        }
        if (isset($validated['late_penalty_rate'])) {
            \App\Models\Setting::setValue('payroll.late_penalty_rate', $validated['late_penalty_rate']);
        }
        if (isset($validated['paid_leave_deduction_rate'])) {
            \App\Models\Setting::setValue('payroll.paid_leave_deduction_rate', $validated['paid_leave_deduction_rate']);
        }
        if (isset($validated['days_per_month'])) {
            \App\Models\Setting::setValue('payroll.days_per_month', $validated['days_per_month']);
        }

        return response()->json([
            'message' => 'Payroll settings updated.',
        ]);
    }

    protected function filters(Request $request): array
    {
        return [
            'user_id' => $request->query('user_id'),
            'period_type' => $request->query('period_type'),
            'status' => $request->query('status'),
            'year' => $request->query('year'),
            'month' => $request->query('month'),
        ];
    }

    public function sendEmail($id)
    {
        $payroll = Payroll::with(['user'])->findOrFail($id);
        $company = \App\Models\AboutUs::first(); 

        if (!$payroll->user || !$payroll->user->email) {
            return response()->json(['message' => 'Staff has no email!'], 400);
        }

        Mail::to($payroll->user->email)->send(new \App\Mail\PayslipMail($payroll, $company));

        return response()->json(['message' => 'Payslip sent successfully!']);
    }
}
