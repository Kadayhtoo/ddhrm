<?php 

namespace App\Services;

use App\Repositories\Contracts\StaffDocumentRepositoryInterface;

class StaffDocumentService
{
    protected $repository;

    public function __construct(StaffDocumentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function processBulkUpload($staffId, array $files)
    {
        foreach ($files as $type => $file) {
            $content = file_get_contents($file->getRealPath());
            $encryptedContent = \Illuminate\Support\Facades\Crypt::encrypt($content);
            
            $extension = $file->getClientOriginalExtension();
            
            $path = 'staff_documents/' . uniqid() . '.enc';
            \Illuminate\Support\Facades\Storage::disk('local')->put($path, $encryptedContent);
            
            $this->repository->updateOrCreateDocument($staffId, $type, $path, $extension);
        }
    }
}