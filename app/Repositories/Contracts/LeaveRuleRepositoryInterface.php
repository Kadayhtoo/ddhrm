<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LeaveRuleRepositoryInterface
{
    public function findById(int $id): mixed;
    
    public function all(): mixed;

    public function create(array $attributes): mixed;

    public function update(mixed $leaveRule, array $attributes): bool;

    public function delete(mixed $leaveRule): bool;
}