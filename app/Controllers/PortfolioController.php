<?php

namespace App\Controllers;

use App\Models\PortfolioModel;
use CodeIgniter\RESTful\ResourceController;

class PortfolioController extends ResourceController
{
    protected $portfolioModel;
    
    public function __construct()
    {
        $this->portfolioModel = new PortfolioModel();
    }

    public function create()
    {
        // Check if user is logged in and has the 'creator' role
        $userId = session()->get('user_id');
        $userRole = session()->get('user_role');

        if ($userRole !== 'creator') {
            return $this->failUnauthorized('Only creators can add portfolios.');
        }

	$filePath = null;

        // Handle file upload if exists
        if ($this->request->getFile('portfolio_file')) {
            $file = $this->request->getFile('portfolio_file');
            
            if (!$file->isValid()) {
                return $this->fail('Invalid file upload: ' . $file->getErrorString());
            }

            $uploadPath = WRITEPATH . 'uploads/portfolios';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $newFileName = $file->getRandomName();
            
            if ($file->move($uploadPath, $newFileName)) {
                $filePath = 'uploads/portfolios/' . $newFileName;
            } else {
                return $this->fail('Failed to upload file: ' . $file->getErrorString());
            }
        }

        // Prepare portfolio data
        $data = [
            'creator_id' => $userId,
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'category' => $this->request->getPost('category'),
            'file_path' => $filePath,
            'likes' => 0,
        ];

        // Create portfolio entry
        $portfolioId = $this->portfolioModel->createPortfolio($data);

        if ($portfolioId) {
            return $this->respondCreated([
                'message' => 'Portfolio created successfully!',
                'data' => $data
            ]);
        }

        return $this->fail('Failed to create portfolio.');
    }

    public function update($id = null)
    {
        // Check if user is logged in
        $userId = session()->get('user_id');
        $userRole = session()->get('user_role');
    
        if (!$userId) {
            return $this->failUnauthorized('You must be logged in.');
        }
    
        // Get ID from POST data if not in URL
        $portfolioId = $id ?? $this->request->getPost('id');
        if (!$portfolioId) {
            return $this->fail('Portfolio ID is required.');
        }
    
        // Check Portfolio exists
        $portfolio = $this->portfolioModel->find($portfolioId);
        if (!$portfolio) {
            return $this->failNotFound('Portfolio not found.');
        }
    
        // Check if user is authorized (creator of the portfolio or admin)
        if ($portfolio['creator_id'] != $userId && $userRole != 'admin') {
            return $this->failUnauthorized('You are not authorized to update this portfolio.');
        }
    
        // Handle file upload if new file is provided
        $file = $this->request->getFile('portfolio_file');
        $filePath = $portfolio['file_path']; // Access array key instead of property
    
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Delete old file if exists
            if (file_exists(WRITEPATH . $portfolio['file_path'])) {
                unlink(WRITEPATH . $portfolio['file_path']);
            }
    
            // Upload new file
            $newFileName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/portfolios', $newFileName);
            $filePath = 'uploads/portfolios/' . $newFileName;
        }
    
        // Prepare data from the request
        $data = [
            'title' => $this->request->getPost('title') ?? $portfolio['title'],
            'description' => $this->request->getPost('description') ?? $portfolio['description'],
            'category' => $this->request->getPost('category') ?? $portfolio['category'],
            'file_path' => $filePath
        ];
    
        // Update the portfolio
        if ($this->portfolioModel->updatePortfolio($portfolioId, $data)) {
            return $this->respondUpdated([
                'message' => 'Portfolio updated successfully!',
                'data' => $data
            ]);
        }
    
        return $this->fail('Failed to update portfolio.');
    }

    public function delete($id = null)
    {
        $userId = session()->get('user_id'); 
        $userRole = session()->get('user_role');
    
        // Debug untuk cek nilai ID yang diterima
        log_message('debug', 'URL ID: ' . $id);
        log_message('debug', 'POST ID: ' . $this->request->getPost('id'));
        
        // Get portfolio ID from URL param or POST data
        $portfolioId = $id ?? $this->request->getGet('id') ?? $this->request->getPost('id');
    
        if (!$portfolioId) {
            return $this->fail([
                'message' => 'Portfolio ID is required.',
                'url_id' => $id,
                'post_data' => $this->request->getPost(),
                'get_data' => $this->request->getGet()
            ]);
        }
    
        // Check Portfolio exists
        $portfolio = $this->portfolioModel->find($portfolioId);
        if (!$portfolio) {
            return $this->failNotFound('Portfolio not found.');
        }
    
        // Check if the logged-in user is the creator or an admin
        if ($portfolio['creator_id'] != $userId && $userRole !== 'admin') {
            return $this->failUnauthorized('You are not authorized to delete this portfolio.');
        }
    
        // Delete the file
        if (file_exists(WRITEPATH . $portfolio['file_path'])) {
            unlink(WRITEPATH . $portfolio['file_path']);
        }
    
        // Delete the portfolio
        if ($this->portfolioModel->deletePortfolio($portfolioId)) {
            return $this->respondDeleted([
                'message' => 'Portfolio deleted successfully!'
            ]);
        }
    
        return $this->fail('Failed to delete portfolio.');
    }

    public function findAll()
    {
        $portfolios = $this->portfolioModel->findAllPortfolios();
        return $this->respond($portfolios);
    }

    public function findById($id = null)
    {
        $portfolio = $this->portfolioModel->findById($id);
        if (!$portfolio) {
            return $this->failNotFound('Portfolio not found.');
        }

        return $this->respond($portfolio);
    }

    public function findByCreator($name = null)
    {
        // Get creator name from query string if not in URL
        $creatorName = $name ?? $this->request->getGet('name');
        
        if (!$creatorName) {
            return $this->fail('Creator name is required for search');
        }

        $portfolios = $this->portfolioModel->findByCreator($creatorName);
        
        return $this->respond([
            'message' => 'Portfolios retrieved successfully',
            'data' => $portfolios ? $portfolios : [],
            'search_term' => $creatorName
        ]);
    }

    public function findByCategory($category = null)
    {
        $category = $this->request->getGet('category') ?? $category;
        $portfolios = $this->portfolioModel->findByCategory($category);
        
        return $this->respond([
            'message' => 'Portfolios retrieved successfully',
            'data' => $portfolios ? $portfolios : [],
            'category_searched' => $category
        ]);
    }

    public function like($id = null)
    {
        // Check if user is logged in
        if (!session()->get('user_id')) {
            return $this->failUnauthorized('You must be logged in to like portfolios.');
        }

        // Get portfolio ID
        $portfolioId = $id ?? $this->request->getPost('id');
        
        if (!$portfolioId) {
            return $this->fail('Portfolio ID is required');
        }

        // Update the likes
        if ($this->portfolioModel->updateLikes($portfolioId)) {
            return $this->respondUpdated([
                'message' => 'Portfolio liked successfully!'
            ]);
        }

        return $this->fail('Failed to like portfolio.');
    }
}
