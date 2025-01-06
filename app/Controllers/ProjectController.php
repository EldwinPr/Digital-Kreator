<?php

namespace App\Controllers;

use App\Models\ProjectModel;
use CodeIgniter\RESTful\ResourceController;

class ProjectController extends ResourceController
{
    protected $projectModel;
    
    public function __construct(){
        $this->projectModel = new ProjectModel();
    }

    public function create(){
        // Check if user is logged in and has the 'creator' role
        $userId = session()->get('user_id'); 
        $userRole = session()->get('user_role'); // Assuming you store role in session

        // If user is not a 'creator', return an error
        if ($userRole !== 'creator') {
            return $this->failUnauthorized('Only users with the creator role can create projects.');
        }

        // Prepare data from the request
        $data = [
            'creator_id' => $userId,  // Get the user_id from the session
            'client_id' => $this->request->getPost('client_id'),
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'status' => $this->request->getPost('status'),
            'deadline' => $this->request->getPost('deadline'),
        ];

        // Create the project
        $projectId = $this->projectModel->createProject($data);

        if ($projectId) {
            return $this->respondCreated([
                'message' => 'Project created successfully!',
                'data' => $data
            ]);
        }

        return $this->fail('Failed to create project.');
    }

    public function delete($id = null){
        $userId = session()->get('user_id'); 
        $userRole = session()->get('user_role');

        // Check Project exists
        $project = $this->projectModel->find($this->request->getPost('id'));
        if (!$project) {
            return $this->failNotFound('Project not found.');
        }

        // Check if the logged-in user is the creator or an admin
        $creatorId = $project['creator_id'] ?? null;
        if ($creatorId != $userId && $userRole !== 'admin') {
            return $this->failUnauthorized('You are not authorized to delete this project.');
        }

        // Delete the project
        if ($this->projectModel->delete($this->request->getPost('id'))) {
            return $this->respondDeleted([
                'message' => 'Project deleted successfully!'
            ]);
        }

        return $this->fail('Failed to delete project.');
    }

    public function update($id = null) {
        // Check user authentication
        $userId = session()->get('user_id'); 
        $userRole = session()->get('user_role');
    
        if (!$userId) {
            return $this->failUnauthorized('You must be logged in.');
        }
    
        // Get project ID from URL or POST
        $projectId = $id ?? $this->request->getPost('id');
        if (!$projectId) {
            return $this->fail('Project ID is required.');
        }
    
        // Check Project exists
        $project = $this->projectModel->find($projectId);
        if (!$project) {
            return $this->failNotFound('Project not found.');
        }
    
        // Check if the logged-in user is the creator or an admin
        $creatorId = $project['creator_id'] ?? null;
        if ($creatorId != $userId && $userRole !== 'admin') {
            return $this->failUnauthorized('You are not authorized to update this project.');
        }
    
        // Prepare data from the request, using existing values if not provided
        $data = [
            'title' => $this->request->getPost('title') ?? $project['title'],
            'description' => $this->request->getPost('description') ?? $project['description'],
            'category' => $this->request->getPost('category') ?? $project['category'],
            'status' => $this->request->getPost('status') ?? $project['status'],
            'deadline' => $this->request->getPost('deadline') ?? $project['deadline'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
    
        // Update the project
        if ($this->projectModel->updateProject($projectId, $data)) {
            return $this->respondUpdated([
                'message' => 'Project updated successfully!',
                'data' => $data
            ]);
        }
    
        return $this->fail('Failed to update project.');
    }

    public function findAllProjects(){
        $projects = $this->projectModel->findAllProjects();

        return $this->respond($projects);
    }

    public function findProjectById($id = null){
        $project = $this->projectModel->find($this->request->getPost('id'));
        if (!$project) {
            return $this->failNotFound('Project not found.');
        }

        return $this->respond($project);
    }

    public function findByUser(){
        $userId = session()->get('user_id'); 
        $userRole = session()->get('user_role');

        if ($userRole === 'client') {
            $projects = $this->projectModel->findByClient($userId);
        } elseif ($userRole === 'creator') {
            $projects = $this->projectModel->findByCreator($userId);
        } else {
            return $this->failUnauthorized('Invalid user role.');
        }

        if ($projects) {
            return $this->respond([
                'message' => 'Projects retrieved successfully!',
                'data' => $projects
            ]);
        }

        return $this->failNotFound('No projects found for the given user.');
    }   
    
}
