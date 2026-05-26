<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LeaveRequestService
{
    public function __construct(
        protected LeaveRequestRepositoryInterface $repository
    ) {}

    public function getPaginatedRequests(User $user, int $perPage, ?string $search, ?string $scope = 'my_requests', ?string $dateFilter = null): LengthAwarePaginator
    {
        return $this->repository->paginateByRole($user, $perPage, $search, $scope, $dateFilter);
    }
    
    public function applyLeave(array $data, $attachment, User $actor): LeaveRequest
    {
        if (empty($data['user_id'])) {
            $data['user_id'] = $actor->id;
        }

        if (!empty($data['start_date'])) {
            $data['year'] = Carbon::parse($data['start_date'])->format('Y');
        } else {
            $data['year'] = Carbon::now()->format('Y'); 
        }
        
        if ($attachment) {
            $data['attachment'] = $attachment->store('leave_attachments', 'public');
        }

        $data['status'] = 'pending';
        $data['is_approve'] = 0;
        $data['is_approve_hr'] = 0;

        return $this->repository->create($data);
    }

    public function handleApproval(int $id, string $action, User $actor): LeaveRequest
    {
        return DB::transaction(function () use ($id, $action, $actor) 
        {
            
            $leaveRequest = $this->repository->findById($id);
            if (!$leaveRequest) {
                throw new \Exception('Leave request not found.');
            }

            if ($leaveRequest->status === 'approved' || $leaveRequest->status === 'rejected') {
                throw new \Exception('This request has already been processed.');
            }

            $isHRorAdmin = $actor->hasRoleSlug('hr') || $actor->hasRoleSlug('admin');
            $isAssignedApprover = ((int)$leaveRequest->approver_id === (int)$actor->id);

            if (!$isHRorAdmin && !$isAssignedApprover) {
                throw new \Exception('You do not have permission to review this leave request.');
            }

            if ($action === 'rejected') {
                $updateData = ['status' => 'rejected'];
                if ($isHRorAdmin) {
                    $updateData['is_approve_hr'] = 2;
                } else {
                    $updateData['is_approve'] = 2;
                }
                return $this->repository->update($leaveRequest, $updateData);
            }

            if ($action === 'approved') {
                $updateData = [];
                if ($isAssignedApprover && !$isHRorAdmin) {
                    $updateData['is_approve'] = 1;
                    $updateData['status'] = 'pending';
                    
                    return $this->repository->update($leaveRequest, $updateData);
                } 
                if ($isHRorAdmin) {
                    $updateData['is_approve_hr'] = 1;
                    if ($leaveRequest->is_approve === 0) {
                        $updateData['is_approve'] = 1; 
                    }
                    $updateData['status'] = 'approved';
                    $leaveBalance = $this->repository->findBalance($leaveRequest->user_id, $leaveRequest->leave_rule_id);
                    
                    if ($leaveBalance) {
                        $this->repository->updateBalance($leaveBalance, [
                            'used_days'      => $leaveBalance->used_days + $leaveRequest->total_days,
                            'remaining_days' => $leaveBalance->remaining_days - $leaveRequest->total_days,
                        ]);
                    } else {
                        $totalAllowedDays = $leaveRequest->leaveRule ? (float)$leaveRequest->leaveRule->days : 0.0;
                        
                        $this->repository->createBalance([
                            'user_id'            => $leaveRequest->user_id,
                            'leave_rule_id'      => $leaveRequest->leave_rule_id,
                            'total_allowed_days' => $totalAllowedDays, 
                            'used_days'          => $leaveRequest->total_days,
                            'remaining_days'     => $totalAllowedDays - $leaveRequest->total_days
                        ]);
                    }

                    return $this->repository->update($leaveRequest, $updateData);
                }
            }

            throw new \Exception('Invalid status action requested.');
        });
    }

    public function cancelLeaveRequest(int $id, User $actor): LeaveRequest
    {
        $leaveRequest = $this->repository->findById($id);

        if ($leaveRequest->user_id !== $actor->id) {
            throw new \Exception('You are not authorized to cancel this leave request.');
        }

        if ($leaveRequest->status !== 'pending') {
            throw new \Exception('You can only cancel pending leave requests.');
        }

        return $this->repository->cancel($id);
    }

    public function updateLeaveRequest(int $id, array $data, $newAttachment, User $actor): LeaveRequest
    {
        $leaveRequest = $this->repository->findById($id);

        if (!$leaveRequest) {
            throw new \Exception('Leave request not found.');
        }

        if ((int)$leaveRequest->user_id !== (int)$actor->id) {
            throw new \Exception('You are not authorized to modify this leave request.');
        }

        if ($leaveRequest->status !== 'pending' || $leaveRequest->is_approve !== 0 || $leaveRequest->is_approve_hr !== 0) {
            throw new \Exception('This request has already been processed or reviewed and cannot be modified.');
        }

        if ($newAttachment) {
            if ($leaveRequest->attachment) {
                Storage::disk('public')->delete($leaveRequest->attachment);
            }
            $data['attachment'] = $newAttachment->store('leave_attachments', 'public');
        }

        $data['status'] = 'pending';
        $data['is_approve'] = 0;
        $data['is_approve_hr'] = 0;

        return $this->repository->update($leaveRequest, $data);
    }
}