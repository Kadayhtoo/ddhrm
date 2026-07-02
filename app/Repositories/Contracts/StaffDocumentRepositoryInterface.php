<?php 
namespace App\Repositories\Contracts;

interface StaffDocumentRepositoryInterface
{
    public function updateOrCreateDocument(int $staffId, string $type, string $path);
    public function getByStaff(int $staffId);
}