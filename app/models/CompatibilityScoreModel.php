<?php
require_once 'BaseModel.php';

class CompatibilityScoreModel extends BaseModel {
    protected $table = 'compatibility_scores';
    
    public function saveCompatibilityScore($user1Id, $user2Id, $score, $explanation, $factors, $horoscopeScore = 0) {
        // Ensure consistent user ordering
        if ($user1Id > $user2Id) {
            $temp = $user1Id;
            $user1Id = $user2Id;
            $user2Id = $temp;
        }
        
        $existing = $this->getCompatibilityScore($user1Id, $user2Id);
        
        $data = [
            'user1_id' => $user1Id,
            'user2_id' => $user2Id,
            'compatibility_score' => $score,
            'explanation' => $explanation,
            'factors' => json_encode($factors),
            'horoscope_match_score' => $horoscopeScore,
            'calculated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->create($data);
        }
    }
    
    public function getCompatibilityScore($user1Id, $user2Id) {
        // Ensure consistent user ordering
        if ($user1Id > $user2Id) {
            $temp = $user1Id;
            $user1Id = $user2Id;
            $user2Id = $temp;
        }
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE user1_id = :user1_id AND user2_id = :user2_id";
        
        return $this->fetchOne($sql, [
            ':user1_id' => $user1Id,
            ':user2_id' => $user2Id
        ]);
    }
    
    public function getUserCompatibilityScores($userId, $limit = 20) {
        $sql = "SELECT cs.*, 
                       CASE 
                           WHEN cs.user1_id = :user_id THEN cs.user2_id 
                           ELSE cs.user1_id 
                       END as other_user_id,
                       up.first_name, up.last_name, up.profile_photo, up.district,
                       TIMESTAMPDIFF(YEAR, up.date_of_birth, CURDATE()) as age
                FROM {$this->table} cs
                LEFT JOIN user_profiles up ON (
                    CASE 
                        WHEN cs.user1_id = :user_id THEN cs.user2_id 
                        ELSE cs.user1_id 
                    END = up.user_id
                )
                LEFT JOIN users u ON up.user_id = u.id
                WHERE (cs.user1_id = :user_id OR cs.user2_id = :user_id)
                AND u.status = 'active'
                ORDER BY cs.compatibility_score DESC, cs.calculated_at DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':limit' => $limit
        ]);
    }
    
    public function getTopMatches($userId, $minScore = 70, $limit = 10) {
        $sql = "SELECT cs.*, 
                       CASE 
                           WHEN cs.user1_id = :user_id THEN cs.user2_id 
                           ELSE cs.user1_id 
                       END as other_user_id,
                       up.first_name, up.last_name, up.profile_photo, up.district, up.religion,
                       TIMESTAMPDIFF(YEAR, up.date_of_birth, CURDATE()) as age
                FROM {$this->table} cs
                LEFT JOIN user_profiles up ON (
                    CASE 
                        WHEN cs.user1_id = :user_id THEN cs.user2_id 
                        ELSE cs.user1_id 
                    END = up.user_id
                )
                LEFT JOIN users u ON up.user_id = u.id
                WHERE (cs.user1_id = :user_id OR cs.user2_id = :user_id)
                AND cs.compatibility_score >= :min_score
                AND u.status = 'active'
                ORDER BY cs.compatibility_score DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':min_score' => $minScore,
            ':limit' => $limit
        ]);
    }
    
    public function getCompatibilityStats($userId) {
        $sql = "SELECT 
                    COUNT(*) as total_calculated,
                    AVG(compatibility_score) as average_score,
                    MAX(compatibility_score) as highest_score,
                    COUNT(CASE WHEN compatibility_score >= 90 THEN 1 END) as excellent_matches,
                    COUNT(CASE WHEN compatibility_score >= 80 THEN 1 END) as great_matches,
                    COUNT(CASE WHEN compatibility_score >= 70 THEN 1 END) as good_matches
                FROM {$this->table}
                WHERE user1_id = :user_id OR user2_id = :user_id";
        
        return $this->fetchOne($sql, [':user_id' => $userId]);
    }
    
    public function getRecentCalculations($userId, $days = 7) {
        $sql = "SELECT cs.*, 
                       CASE 
                           WHEN cs.user1_id = :user_id THEN cs.user2_id 
                           ELSE cs.user1_id 
                       END as other_user_id,
                       up.first_name, up.last_name, up.profile_photo
                FROM {$this->table} cs
                LEFT JOIN user_profiles up ON (
                    CASE 
                        WHEN cs.user1_id = :user_id THEN cs.user2_id 
                        ELSE cs.user1_id 
                    END = up.user_id
                )
                WHERE (cs.user1_id = :user_id OR cs.user2_id = :user_id)
                AND cs.calculated_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                ORDER BY cs.calculated_at DESC";
        
        return $this->fetchAll($sql, [
            ':user_id' => $userId,
            ':days' => $days
        ]);
    }
    
    public function updateHoroscopeScore($user1Id, $user2Id, $horoscopeScore) {
        // Ensure consistent user ordering
        if ($user1Id > $user2Id) {
            $temp = $user1Id;
            $user1Id = $user2Id;
            $user2Id = $temp;
        }
        
        $sql = "UPDATE {$this->table} 
                SET horoscope_match_score = :horoscope_score,
                    calculated_at = NOW()
                WHERE user1_id = :user1_id AND user2_id = :user2_id";
        
        return $this->execute($sql, [
            ':horoscope_score' => $horoscopeScore,
            ':user1_id' => $user1Id,
            ':user2_id' => $user2Id
        ]);
    }
    
    public function getFactorsAnalysis($userId) {
        $sql = "SELECT factors FROM {$this->table}
                WHERE user1_id = :user_id OR user2_id = :user_id";
        
        $results = $this->fetchAll($sql, [':user_id' => $userId]);
        
        $allFactors = [
            'age_compatibility' => [],
            'religion_compatibility' => [],
            'location_compatibility' => [],
            'education_compatibility' => [],
            'goals_compatibility' => [],
            'income_compatibility' => []
        ];
        
        foreach ($results as $result) {
            $factors = json_decode($result['factors'], true);
            if ($factors) {
                foreach ($factors as $factor => $score) {
                    if (isset($allFactors[$factor])) {
                        $allFactors[$factor][] = $score;
                    }
                }
            }
        }
        
        $analysis = [];
        foreach ($allFactors as $factor => $scores) {
            if (!empty($scores)) {
                $analysis[$factor] = [
                    'average' => round(array_sum($scores) / count($scores), 1),
                    'count' => count($scores),
                    'max' => max($scores),
                    'min' => min($scores)
                ];
            }
        }
        
        return $analysis;
    }
    
    public function getMostCompatibleProfiles($limit = 10) {
        $sql = "SELECT 
                    user1_id, user2_id, compatibility_score,
                    up1.first_name as user1_name, up1.profile_photo as user1_photo,
                    up2.first_name as user2_name, up2.profile_photo as user2_photo
                FROM {$this->table} cs
                LEFT JOIN user_profiles up1 ON cs.user1_id = up1.user_id
                LEFT JOIN user_profiles up2 ON cs.user2_id = up2.user_id
                LEFT JOIN users u1 ON cs.user1_id = u1.id
                LEFT JOIN users u2 ON cs.user2_id = u2.id
                WHERE u1.status = 'active' AND u2.status = 'active'
                ORDER BY compatibility_score DESC
                LIMIT :limit";
        
        return $this->fetchAll($sql, [':limit' => $limit]);
    }
}
?>