<?php 
namespace App\Repositories\Eloquent;

use App\Models\StaffDocument;
use App\Repositories\Contracts\StaffDocumentRepositoryInterface;

class StaffDocumentRepository implements StaffDocumentRepositoryInterface
{
    public function updateOrCreateDocument(int $staffId, string $type, string $path)
    {
        return StaffDocument::updateOrCreate(
            ['staff_id' => $staffId, 'document_type' => $type],
            ['file_path' => $path]
        );
    }

    public function getByStaff(int $staffId)
    {
        return StaffDocument::where('staff_id', $staffId)->get();
    }
}