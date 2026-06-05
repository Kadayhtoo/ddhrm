<?php

namespace App\Repositories\Eloquent;

use App\Models\ContactPerson;
use App\Repositories\Contracts\ContactPersonRepositoryInterface;

class ContactPersonRepository implements ContactPersonRepositoryInterface
{
    public function getByClientId(int $clientId)
    {
        return ContactPerson::where('client_id', $clientId)->get();
    }

    public function create(array $data)
    {
        return ContactPerson::create($data);
    }

    public function update(int $id, array $data)
    {
        $contact = ContactPerson::findOrFail($id);
        $contact->update($data);
        return $contact;
    }

    public function delete(int $id)
    {
        $contact = ContactPerson::findOrFail($id);
        return $contact->delete();
    }
}