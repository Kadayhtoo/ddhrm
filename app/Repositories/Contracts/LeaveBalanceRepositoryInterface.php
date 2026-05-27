<?php

namespace App\Repositories;

interface LeaveBalanceRepositoryInterface
{
    public function getAllBalancesWithFilter(?string $search = null, int $perPage = 10);

    public function getBalanceByUserId(int $userId);

    public function firstOrCreateBalance(array $attributes, array $values);
}
