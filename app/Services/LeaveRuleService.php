<?php

namespace App\Services;

use App\Models\LeaveRule;
use App\Repositories\Contracts\LeaveRuleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeaveRuleService
{
    public function __construct(
        protected LeaveRuleRepositoryInterface $leaveRules,
    ) {}

    public function paginate(int $perPage, ?string $search): LengthAwarePaginator
    {
        return $this->leaveRules->all($perPage, $search);
    }

    public function findById(int $id): ?LeaveRule
    {
        return $this->leaveRules->findById($id);
    }

    public function create(array $data): LeaveRule
    {
        return $this->leaveRules->create([
            'name' => $data['name'],
            'type' => $data['type'],
            'days' => $data['days'] !== '' && $data['days'] !== null ? (int) $data['days'] : null,
        ]);
    }

    public function update($leaveRule, array $data): LeaveRule
    {
        $this->leaveRules->update($leaveRule, [
            'name' => $data['name'],
            'type' => $data['type'],
            'days' => $data['days'] !== '' && $data['days'] !== null ? (int) $data['days'] : null,
        ]);

        return $this->leaveRules->findById($leaveRule->id) ?? $leaveRule;
    }

    public function delete($leaveRule): void
    {
        $this->leaveRules->delete($leaveRule);
    }
}
