<?php
require_once 'BaseController.php';
require_once SITE_ROOT . '/app/models/ProfileModel.php';
require_once SITE_ROOT . '/app/models/CompatibilityScoreModel.php';
require_once SITE_ROOT . '/app/models/PremiumModel.php';

class AIController extends BaseController {
    private $profileModel;
    private $compatibilityModel;
    private $premiumModel;
    
    public function __construct() {
        $this->profileModel = new ProfileModel();
        $this->compatibilityModel = new CompatibilityScoreModel();
        $this->premiumModel = new PremiumModel();
    }
    
    public function matches() {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->redirect('/login');
        }
        
        // Check if user has premium access for AI matching
        if (!$this->premiumModel->canUseFeature($currentUser['id'], 'ai_matching')) {
            $this->redirectWithMessage('/premium', 'AI matching is a premium feature. Upgrade your membership to access it.', 'warning');
        }
        
        $currentProfile = $this->profileModel->findByUserId($currentUser['id']);
        if (!$currentProfile) {
            $this->redirectWithMessage('/profile/edit', 'Please complete your profile first to use AI matching.', 'warning');
        }
        
        // Get AI-generated matches
        $matches = $this->generateAIMatches($currentUser['id'], $currentProfile);
        
        // Calculate horoscope compatibility for matches
        foreach ($matches as &$match) {
            if ($currentProfile['horoscope_file'] && $match['horoscope_file']) {
                $match['horoscope_compatibility'] = $this->calculateHoroscopeCompatibility(
                    $currentProfile['horoscope_file'],
                    $match['horoscope_file']
                );
            }
        }
        
        $data = [
            'title' => 'AI Matches - Sandawatha.lk',
            'matches' => $matches,
            'current_profile' => $currentProfile,
            'csrf_token' => $this->generateCsrf(),
            'scripts' => ['ai-matches']
        ];
        
        $this->layout('main', 'ai/matches', $data);
    }
    
    public function calculateCompatibility() {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 400);
        }
        
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            $this->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }
        
        $profileId = $_POST['profile_id'] ?? '';
        
        if (empty($profileId)) {
            $this->json(['success' => false, 'message' => 'Profile ID is required'], 400);
        }
        
        $currentProfile = $this->profileModel->findByUserId($currentUser['id']);
        $targetProfile = $this->profileModel->find($profileId);
        
        if (!$currentProfile || !$targetProfile) {
            $this->json(['success' => false, 'message' => 'Profile not found'], 404);
        }
        
        try {
            $compatibility = $this->calculateProfileCompatibility($currentProfile, $targetProfile);
            
            // Store the compatibility score
            $this->compatibilityModel->saveCompatibilityScore(
                $currentUser['id'],
                $targetProfile['user_id'],
                $compatibility['score'],
                $compatibility['explanation'],
                $compatibility['factors']
            );
            
            $this->json([
                'success' => true,
                'score' => $compatibility['score'],
                'explanation' => $compatibility['explanation'],
                'factors' => $compatibility['factors'],
                'horoscope_match' => $compatibility['horoscope_match'] ?? null
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    private function generateAIMatches($userId, $currentProfile) {
        // Mock AI matching algorithm
        // In a real implementation, this would use machine learning models
        
        $filters = [
            'gender' => $currentProfile['gender'] === 'male' ? 'female' : 'male',
        ];
        
        // Get potential matches
        $potentialMatches = $this->profileModel->searchProfiles($filters, $userId, 1, 50);
        $scoredMatches = [];
        
        foreach ($potentialMatches as $profile) {
            $compatibility = $this->calculateProfileCompatibility($currentProfile, $profile);
            
            if ($compatibility['score'] >= COMPATIBILITY_THRESHOLD) {
                $profile['ai_score'] = $compatibility['score'];
                $profile['ai_explanation'] = $compatibility['explanation'];
                $profile['ai_factors'] = $compatibility['factors'];
                $scoredMatches[] = $profile;
            }
        }
        
        // Sort by compatibility score
        usort($scoredMatches, function($a, $b) {
            return $b['ai_score'] - $a['ai_score'];
        });
        
        return array_slice($scoredMatches, 0, 20); // Return top 20 matches
    }
    
    private function calculateProfileCompatibility($profile1, $profile2) {
        $factors = [];
        $totalScore = 0;
        $maxScore = 0;
        
        // Age compatibility (20% weight)
        $age1 = $this->calculateAge($profile1['date_of_birth']);
        $age2 = $this->calculateAge($profile2['date_of_birth']);
        $ageDiff = abs($age1 - $age2);
        
        if ($ageDiff <= 2) {
            $ageScore = 100;
        } elseif ($ageDiff <= 5) {
            $ageScore = 80;
        } elseif ($ageDiff <= 10) {
            $ageScore = 60;
        } else {
            $ageScore = 30;
        }
        
        $factors['age_compatibility'] = $ageScore;
        $totalScore += $ageScore * 0.2;
        $maxScore += 100 * 0.2;
        
        // Religion compatibility (25% weight)
        $religionScore = ($profile1['religion'] === $profile2['religion']) ? 100 : 40;
        $factors['religion_compatibility'] = $religionScore;
        $totalScore += $religionScore * 0.25;
        $maxScore += 100 * 0.25;
        
        // Location compatibility (15% weight)
        $locationScore = ($profile1['district'] === $profile2['district']) ? 100 : 60;
        $factors['location_compatibility'] = $locationScore;
        $totalScore += $locationScore * 0.15;
        $maxScore += 100 * 0.15;
        
        // Education compatibility (15% weight)
        $education1 = $this->getEducationLevel($profile1['education']);
        $education2 = $this->getEducationLevel($profile2['education']);
        $eduDiff = abs($education1 - $education2);
        
        if ($eduDiff <= 1) {
            $educationScore = 100;
        } elseif ($eduDiff <= 2) {
            $educationScore = 75;
        } else {
            $educationScore = 50;
        }
        
        $factors['education_compatibility'] = $educationScore;
        $totalScore += $educationScore * 0.15;
        $maxScore += 100 * 0.15;
        
        // Goals compatibility (15% weight)
        $goalScore = 70; // Base score
        
        if ($profile1['wants_migration'] && $profile2['wants_migration']) {
            $goalScore += 15;
        } elseif ($profile1['wants_migration'] !== $profile2['wants_migration']) {
            $goalScore -= 10;
        }
        
        if ($profile1['career_focused'] && $profile2['career_focused']) {
            $goalScore += 10;
        }
        
        if ($profile1['wants_early_marriage'] && $profile2['wants_early_marriage']) {
            $goalScore += 5;
        }
        
        $goalScore = min(100, max(0, $goalScore));
        $factors['goals_compatibility'] = $goalScore;
        $totalScore += $goalScore * 0.15;
        $maxScore += 100 * 0.15;
        
        // Income compatibility (10% weight)
        if ($profile1['income_lkr'] > 0 && $profile2['income_lkr'] > 0) {
            $incomeRatio = min($profile1['income_lkr'], $profile2['income_lkr']) / 
                          max($profile1['income_lkr'], $profile2['income_lkr']);
            $incomeScore = $incomeRatio * 100;
        } else {
            $incomeScore = 70; // Neutral score if income not provided
        }
        
        $factors['income_compatibility'] = round($incomeScore);
        $totalScore += $incomeScore * 0.1;
        $maxScore += 100 * 0.1;
        
        // Final compatibility score
        $finalScore = round(($totalScore / $maxScore) * 100);
        
        // Generate explanation
        $explanation = $this->generateCompatibilityExplanation($finalScore, $factors);
        
        return [
            'score' => $finalScore,
            'explanation' => $explanation,
            'factors' => $factors
        ];
    }
    
    private function generateCompatibilityExplanation($score, $factors) {
        $explanations = [];
        
        if ($factors['religion_compatibility'] >= 90) {
            $explanations[] = "You share the same religious beliefs";
        }
        
        if ($factors['location_compatibility'] >= 90) {
            $explanations[] = "You're from the same district";
        }
        
        if ($factors['age_compatibility'] >= 80) {
            $explanations[] = "Your ages are well-matched";
        }
        
        if ($factors['education_compatibility'] >= 75) {
            $explanations[] = "You have similar educational backgrounds";
        }
        
        if ($factors['goals_compatibility'] >= 80) {
            $explanations[] = "Your life goals align well";
        }
        
        if ($score >= 90) {
            $prefix = "Excellent match! ";
        } elseif ($score >= 80) {
            $prefix = "Great compatibility! ";
        } elseif ($score >= 70) {
            $prefix = "Good potential match. ";
        } else {
            $prefix = "Some compatibility factors. ";
        }
        
        if (empty($explanations)) {
            return $prefix . "You may have different backgrounds but could complement each other well.";
        }
        
        return $prefix . implode(', ', $explanations) . ".";
    }
    
    private function calculateHoroscopeCompatibility($horoscope1, $horoscope2) {
        // Mock horoscope compatibility calculation
        // In a real implementation, this would analyze horoscope data
        
        $compatibility = rand(60, 95);
        
        $reasons = [
            "Favorable planetary positions",
            "Compatible zodiac signs", 
            "Matching lunar phases",
            "Harmonious star alignments",
            "Complementary birth charts"
        ];
        
        return [
            'score' => $compatibility,
            'reason' => $reasons[array_rand($reasons)]
        ];
    }
    
    private function calculateAge($birthDate) {
        $birth = new DateTime($birthDate);
        $today = new DateTime();
        return $today->diff($birth)->y;
    }
    
    private function getEducationLevel($education) {
        $levels = [
            'O/L' => 1,
            'A/L' => 2,
            'Diploma' => 3,
            'Bachelor\'s Degree' => 4,
            'Master\'s Degree' => 5,
            'PhD' => 6,
            'Professional Qualification' => 4
        ];
        
        return $levels[$education] ?? 3;
    }
}
?>