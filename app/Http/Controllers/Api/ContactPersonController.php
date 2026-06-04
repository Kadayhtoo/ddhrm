<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactPersonResource;
use App\Services\ContactPersonService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContactPersonController extends Controller
{
    protected ContactPersonService $contactService;

    public function __construct(ContactPersonService $contactService)
    {
        $this->contactService = $contactService;
    }

    public function index($clientId)
    {
        $contacts = $this->contactService->getContactsByClient((int)$clientId);
        
        return ContactPersonResource::collection($contacts);
    }

    public function store(Request $request, $clientId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
        ]);

        $contact = $this->contactService->addContact((int)$clientId, $validated);

        return response()->json([
            'message' => 'Contact Person added successfully.',
            'data' => new ContactPersonResource($contact)
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
        ]);

        $contact = $this->contactService->updateContact((int)$id, $validated);

        return response()->json([
            'message' => 'Contact Person updated successfully.',
            'data' => new ContactPersonResource($contact)
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $this->contactService->deleteContact((int)$id);
        
        return response()->json([
            'message' => 'Contact Person deleted successfully.'
        ]);
    }
}