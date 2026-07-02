<?php 
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffDocumentRequest;
use App\Http\Requests\UpdateStaffDocumentRequest;
use App\Models\StaffDocument;
use App\Models\User;
use App\Services\StaffDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class StaffDocumentController extends Controller
{
    protected StaffDocumentService $documentService;

    public function __construct(StaffDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function index(User $user) {
        return response()->json(['data' => $user->documents]); 
    }

    /**
     * Handle bulk upload of staff documents.
     */
    public function store(StoreStaffDocumentRequest $request, int $staffId): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            
            $this->documentService->processBulkUpload($staffId, $validatedData['documents']);
            
            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function view(Request $request)
    {
        $path = $request->query('path');

        if (empty($path)) {
            return response()->json(['message' => 'File path is required'], 400);
        }

        $fullPath = null;

        if (Storage::disk('local')->exists($path)) {
            $fullPath = Storage::disk('local')->path($path);
        } else {
            $legacyPath = storage_path('app/private/' . ltrim($path, '/'));
            if (file_exists($legacyPath)) {
                $fullPath = $legacyPath;
            }
        }

        if (empty($fullPath) || !file_exists($fullPath)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $encryptedContent = file_get_contents($fullPath);

        if ($encryptedContent === false || trim($encryptedContent) === '') {
            return response()->json(['message' => 'The stored file is empty'], 422);
        }

        try {
            $decryptedContent = Crypt::decrypt($encryptedContent);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Unable to decrypt the stored document'], 500);
        }

        if (!is_string($decryptedContent) || $decryptedContent === '') {
            return response()->json(['message' => 'The document content is empty'], 422);
        }

        $mimeType = $this->detectMimeType($decryptedContent, $path);

        return response()->streamDownload(function () use ($decryptedContent) {
            echo $decryptedContent;
        }, basename($path), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
 
    private function detectMimeType(string $content, string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        if (isset($mimeTypes[$extension])) {
            return $mimeTypes[$extension];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = $finfo->buffer($content);

        return $detected ?: 'application/octet-stream';
    }

    public function update(UpdateStaffDocumentRequest $request, $staffId, $type)
    {
        $doc = StaffDocument::where('staff_id', $staffId)
            ->where('document_type', $type)
            ->firstOrFail();
        
        \Illuminate\Support\Facades\Storage::disk('local')->delete($doc->file_path);
        
        $file = $request->file('document');
        $content = file_get_contents($file->getRealPath());
        $encryptedContent = \Illuminate\Support\Facades\Crypt::encrypt($content);
        
        $newPath = 'staff_documents/' . uniqid() . '.enc';
        \Illuminate\Support\Facades\Storage::disk('local')->put($newPath, $encryptedContent);
        
        $doc->update(['file_path' => $newPath]);

        return response()->json(['message' => 'Document updated successfully']);
    }

    public function destroy($staffId, $type)
    {
        $doc = StaffDocument::where('staff_id', $staffId)->where('document_type', $type)->firstOrFail();
        Storage::disk('local')->delete($doc->file_path);
        $doc->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}