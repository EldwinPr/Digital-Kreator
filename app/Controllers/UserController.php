<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class UserController extends ResourceController
{
    protected $userModel;

    public function __construct(){
        $this->userModel = new UserModel();
    }

    public function register(){
        // Validation rules
        $rules = [
            'name' => 'required',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role' => 'required|in_list[creator,client]'
        ];

        // Run validation
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Prepare user data
        $userData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => $this->request->getPost('role')
        ];

        // Save new user
        $userId = $this->userModel->createUser($userData);

        // Return success response
        return $this->respondCreated([
            'message' => 'Registration successful',
            'userId' => $userId
        ]);
    }

    public function login(){
        // Get posted data
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        if (empty($email)) {
            return $this->failValidationError('Email is required');
        }
        if (empty($password)) {
            return $this->failValidationError('password is required');
        }

        // Log the input for debugging
        log_message('debug', 'Login attempt - Email: ' . $email);

        // Find user by email
        $user = $this->userModel->getUserByEmail(strtolower($email));


        // Debug user lookup
        if (!$user) {
            log_message('debug', 'No user found with email: ' . $email);
            return $this->failUnauthorized('No user found with this email');
        }

        // Debug password verification
        log_message('debug', 'Stored hash: ' . $user->password);
        log_message('debug', 'Submitted password: ' . $password);

        // Verify password
        if (!password_verify($password, $user->password)) {
            log_message('debug', 'Password verification failed');
            return $this->failUnauthorized('Invalid password');
        }

        session()->set('user_id', $user->id);
        session()->set('user_email', $user->email);
        session()->set('user_role', $user->role);

        // Successful login
        return $this->respond([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]);
    }

    public function logout(){
        // Destroy session or invalidate token
        session()->destroy();  // This will clear all session data
    
        return $this->respond([
            'message' => 'Logout successful'
        ]);
    }

    public function profile(){
        // Get user data from session
        $userId = session()->get('user_id');  // Retrieve user ID from session
    
        if (!$userId) {
            // If there's no user ID in the session, the user is not logged in
            return $this->failUnauthorized('You must be logged in to access this resource');
        }
    
        // Fetch user details from the database based on the session data
        $user = $this->userModel->asObject()->find($userId);
    
        if (!$user) {
            return $this->failNotFound('User not found');
        }
    
        // Return the user's profile data
        return $this->respond([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]);
    }
}