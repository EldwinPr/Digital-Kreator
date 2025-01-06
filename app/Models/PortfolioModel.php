<?php

namespace App\Models;

use CodeIgniter\Model;

class PortfolioModel extends Model
{
    protected $table = 'portfolios';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'creator_id',
        'title',
        'description',
        'category',
        'file_path',
        'likes'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Create a new portfolio entry
    public function createPortfolio($data)
    {
        if ($this->save($data)) {
            return $this->insertID;
        }
        return false;
    }

    // Update an existing portfolio
    public function updatePortfolio($id, $data)
    {
        if ($this->update($id, $data)) {
            return true;
        }
        return false;
    }

    // Delete a portfolio
    public function deletePortfolio($id)
    {
        if ($this->delete($id)) {
            return true;
        }
        return false;
    }

    // Find all portfolios
    public function findAllPortfolios()
    {
        return $this->findAll();
    }

    // Find portfolio by ID
    public function findById($id)
    {
        if (!$id) {
            return null;
        }
        $result = $this->find($id);
        if (!$result) {
            return null;
        }
        return $result;
    }

    // Find portfolios by creator
    public function findByCreator($creatorName)
    {
        return $this->db->table('portfolios p')
            ->select('p.*, u.name as creator_name')
            ->join('users u', 'u.id = p.creator_id')
            ->like('u.name', $creatorName)
            ->get()
            ->getResultArray();
    }

    // Find portfolios by category
    public function findByCategory($category)
    {
        // Debug query
        $result = $this->where('category', $category)->findAll();
        
        return $result;
    }

    // Update likes count
    public function updateLikes($id)
    {
        $portfolio = $this->find($id);
        if (!$portfolio) {
            return false;
        }

        $currentLikes = $portfolio['likes'] ?? 0;
        $data = ['likes' => $currentLikes + 1];
        
        return $this->update($id, $data);
    }
}