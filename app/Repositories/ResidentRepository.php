<?php


namespace App\Repositories;


use App\Models\User;
use App\Models\Resident;
use App\Interfaces\ResidentRepositoryInterface;

class ResidentRepository implements ResidentRepositoryInterface

{
    public function getAllResidents()
    {
        return Resident::all();
    }

    public function getResidentById(int $id)
    {
        return Resident::where('id', $id)->first();
    }

    public function createResident(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);

        $user->assignRole('resident');

        return $user->resident()->create($data);


    }

    public function updateResident(int $id, array $data)
    {
        $resident = $this->getResidentById($id);

        $resident->user->update([
            'name' => $data['name'],
            'password' => isset($data['password']) ? bcrypt($data['password']) : $resident->user->password,
        ]);

        return $resident->update($data);
    }

    public function deteleResident(int $id)
    {
        $resident = $this->getResidentById($id);

        $resident->user->delete();

        return $resident->delete();
    }
}
