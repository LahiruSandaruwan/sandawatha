<?php
namespace App\controllers;

use App\models\FavoriteModel;
use App\models\ProfileModel;
use Exception;

class FavoriteController extends BaseController {
    private $favoriteModel;
    private $profileModel;
    
    public function __construct() {
        parent::__construct();
        $this->favoriteModel = new FavoriteModel();
        $this->profileModel = new ProfileModel();
    }
    
    public function add() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $favoriteUserId = $_POST['profile_id'] ?? '';
        
        if (empty($favoriteUserId)) {
            $this->json(['success' => false, 'message' => 'Profile ID is required'], 400);
        }
        
        // Check if user is trying to favorite themselves
        if ($currentUser['id'] == $favoriteUserId) {
            $this->json(['success' => false, 'message' => 'Cannot favorite yourself'], 400);
        }
        
        try {
            $favoriteId = $this->favoriteModel->addFavorite($currentUser['id'], $favoriteUserId);
            
            // Log activity
            $this->logUserActivity($currentUser['id'], 'profile_favorited', "Added user ID: {$favoriteUserId} to favorites");
            
            $this->json([
                'success' => true,
                'message' => 'Profile added to favorites!',
                'favorite_id' => $favoriteId
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function remove() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $favoriteUserId = $_POST['profile_id'] ?? '';
        
        if (empty($favoriteUserId)) {
            $this->json(['success' => false, 'message' => 'Profile ID is required'], 400);
        }
        
        try {
            $success = $this->favoriteModel->removeFavorite($currentUser['id'], $favoriteUserId);
            
            if ($success) {
                // Log activity
                $this->logUserActivity($currentUser['id'], 'profile_unfavorited', "Removed user ID: {$favoriteUserId} from favorites");
                
                $this->json([
                    'success' => true,
                    'message' => 'Profile removed from favorites!'
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Profile was not in favorites'], 400);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    public function list() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->redirect('/login');
        }
        
        $page = $_GET['page'] ?? 1;
        
        $favorites = $this->favoriteModel->getUserFavorites($currentUser['id'], $page, 20);
        $favoriteCount = $this->favoriteModel->getFavoriteCount($currentUser['id']);
        $mutualFavorites = $this->favoriteModel->getMutualFavorites($currentUser['id']);
        $whoFavoritedMe = $this->favoriteModel->getWhoFavoritedUser($currentUser['id'], 1, 10);
        $stats = $this->favoriteModel->getFavoriteStats($currentUser['id']);
        
        $data = [
            'title' => 'My Favorites - Sandawatha.lk',
            'favorites' => $favorites,
            'favorite_count' => $favoriteCount,
            'mutual_favorites' => $mutualFavorites,
            'who_favorited_me' => $whoFavoritedMe,
            'stats' => $stats,
            'current_page' => $page,
            'csrf_token' => $this->generateCsrf(),
            'scripts' => ['favorites']
        ];
        
        $this->layout('main', 'favorites/list', $data);
    }
}
?>