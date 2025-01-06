<?php namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'email', 'password', 'role'];

    public function getUsers(){
        return $this->findAll();
    }

    public function getUserByEmail($email){
        return $this->where('email', $email)->asObject()->first();
    }

    public function createUser($data){
        return $this->insert($data);
    }

    public function updateUser($id, $data){
        return $this->update($id, $data);
    }

    public function deleteUser($id){
        return $this->delete($id);
    }

    public function getUserByName($name){
        return $this->where('name', $name)->asObject()->first();
    }
}