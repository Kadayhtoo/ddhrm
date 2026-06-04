<?php

namespace App\Repositories\Contracts;

use App\Models\AboutUs;

interface AboutUsRepositoryInterface
{
    public function getFirst(): ?AboutUs;

    public function findById(int $id): ?AboutUs;

    public function create(array $data): AboutUs;

    public function update(AboutUs $aboutUs, array $data): bool;
}
