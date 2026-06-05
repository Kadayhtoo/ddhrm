<?php

namespace App\Services;

use App\Models\AboutUs;
use App\Repositories\Contracts\AboutUsRepositoryInterface;

class AboutUsService
{
    protected AboutUsRepositoryInterface $aboutUsRepo;

    public function __construct(AboutUsRepositoryInterface $aboutUsRepo)
    {
        $this->aboutUsRepo = $aboutUsRepo;
    }

    public function getSettings(): ?AboutUs
    {
        return $this->aboutUsRepo->getFirst();
    }

    public function getById(int $id): ?AboutUs
    {
        return $this->aboutUsRepo->findById($id);
    }

    public function create(array $data): AboutUs
    {
        return $this->aboutUsRepo->create($data);
    }

    public function update(int $id, array $data): ?AboutUs
    {
        $aboutUs = $this->aboutUsRepo->findById($id);

        if (!$aboutUs) {
            return null;
        }

        $this->aboutUsRepo->update($aboutUs, $data);

        return $aboutUs->refresh();
    }
}
