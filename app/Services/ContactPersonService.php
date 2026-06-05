<?php

namespace App\Services;

use App\Repositories\Contracts\ContactPersonRepositoryInterface;

class ContactPersonService
{
    protected ContactPersonRepositoryInterface $contactRepo;

    public function __construct(ContactPersonRepositoryInterface $contactRepo)
    {
        $this->contactRepo = $contactRepo;
    }

    public function getContactsByClient(int $clientId)
    {
        return $this->contactRepo->getByClientId($clientId);
    }

    public function addContact(int $clientId, array $data)
    {
        $data['client_id'] = $clientId;
        return $this->contactRepo->create($data);
    }

    public function updateContact(int $id, array $data)
    {
        return $this->contactRepo->update($id, $data);
    }

    public function deleteContact(int $id)
    {
        return $this->contactRepo->delete($id);
    }
}