<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectModel extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'creator_id', 'client_id', 'title', 'description', 'category', 'status', 'deadline'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function createProject($data){
        if ($this->save($data)) {
            return $this->insertID; // Return the inserted ID if successful
        }
        return false; // Return false if insertion fails
    }

    public function updateProject($id, $data){
        if ($this->update($id, $data)) {
            return true;
        }
        return false;
    }

    public function deleteProject($id){
        if ($this->delete($id)) {
            return true;
        }
        return false;
    }

    public function findAllProjects(){
        return $this->findAll();
    }

    public function findById($id){
        return $this->find($id);
    }

    public function findByCreator($creatorId){
        return $this->where('creator_id', $creatorId)->findAll();
    }

    public function findByClient($clientId){
        return $this->where('client_id', $clientId)->findAll();
    }
}
