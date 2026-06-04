<?php

namespace App\Repositories\Eloquent;

use App\Models\AboutUs;
use App\Repositories\Contracts\AboutUsRepositoryInterface;

class AboutUsRepository implements AboutUsRepositoryInterface
{
    public function getFirst(): ?AboutUs
    {
        return AboutUs::latest()->first();
    }

    public function findById(int $id): ?AboutUs
    {
        return AboutUs::find($id);
    }

    public function create(array $data): AboutUs
    {
        return AboutUs::create($data);
    }

    public function update(AboutUs $aboutUs, array $data): bool
    {
        return $aboutUs->update($data);
    }
}
