<?php

namespace App\Repositories\Contracts;

interface ContactPersonRepositoryInterface
{
    public function getByClientId(int $clientId);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}