<?php
require_once 'BaseModel.php';

class CharacterModel extends BaseModel {
    /**
     * Fetch character details by character ID and user ID.
     */
    public function getCharacterByIdAndUser($characterId, $userId) {
        $query = "SELECT character_id FROM characters WHERE character_id = ? AND user_id = ?";
        return $this->fetchOne($query, [$characterId, $userId]);
    }

    /**
     * Fetch character stats for combat.
     */
    public function getCharacterStats($characterId) {
        $query = "
            SELECT 
                health AS current_health, 
                ki AS current_ki, 
                0 AS current_focus, 
                0 AS current_fatigue, 
                speed AS current_speed, 
                max_health AS max_health, 
                mana AS current_mana,
                max_mana AS max_mana 
            FROM characters 
            WHERE character_id = ?";
        return $this->fetchOne($query, [$characterId]);
    }

    /**
     * Fetch character details, including rank, level, and associations.
     */
    public function getCharacterDetails($characterId) {
        $query = "
            SELECT c.name, c.ki, c.fame, c.zeni, c.quest_points, c.health, c.max_health, c.mana, c.max_mana, 
                   c.level, c.exp, r.name AS race_name, a.name AS attitude_name,
                   (SELECT COUNT(*) + 1 FROM characters WHERE ki > c.ki) AS rank
            FROM characters c
            JOIN races r ON c.race_id = r.race_id
            JOIN attitudes a ON c.attitude_id = a.attitude_id
            WHERE c.character_id = ?";
        return $this->fetchOne($query, [$characterId]);
    }

    /**
     * Fetch character location ID.
     */
    public function getCharacterLocationId($characterId) {
        $query = "SELECT location_id FROM characters WHERE character_id = ?";
        $result = $this->fetchOne($query, [$characterId]);
        return $result['location_id'] ?? null;
    }

    /**
     * Fetch level data by level.
     */
    public function getLevelData($level) {
        $query = "SELECT exp_required, health_bonus, mana_bonus, ki_bonus, fame_bonus FROM levels WHERE level = ?";
        return $this->fetchOne($query, [$level]);
    }

    /**
     * Check and update the character's level based on experience points.
     */
    public function checkAndUpdateLevel($characterId) {
        $character = $this->getCharacterDetails($characterId);

        if (!$character) {
            return; // Character not found
        }

        $nextLevel = $character['level'] + 1;
        $levelData = $this->getLevelData($nextLevel);

        if (!$levelData || $character['exp'] < $levelData['exp_required']) {
            return; // Not enough experience or level data unavailable
        }

        $query = "
            UPDATE characters 
            SET level = ?, 
                max_health = max_health + ?, 
                max_mana = max_mana + ?, 
                ki = ki + ?, 
                fame = fame + ? 
            WHERE character_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            'iiiiii', 
            $nextLevel, 
            $levelData['health_bonus'], 
            $levelData['mana_bonus'], 
            $levelData['ki_bonus'], 
            $levelData['fame_bonus'], 
            $characterId
        );
        $stmt->execute();
        $stmt->close();
    }

    public function getAllCharactersByUser($userId) {
        $query = "
            SELECT c.character_id, c.name, c.level, c.ki, c.health, c.max_health, c.mana, c.max_mana,
                   r.name AS race_name, r.picture AS race_picture
            FROM characters c
            JOIN races r ON c.race_id = r.race_id
            WHERE c.user_id = ?";
        return $this->fetchAll($query, [$userId]);
    }
    
    public function getCharacterCountByUser($userId) {
        $query = "SELECT COUNT(*) AS total FROM characters WHERE user_id = ?";
        $result = $this->fetchOne($query, [$userId]);
        return $result['total'] ?? 0;
    }
    
    public function isCharacterNameTaken($characterName) {
        $query = "SELECT COUNT(*) AS total FROM characters WHERE name = ?";
        $result = $this->fetchOne($query, [$characterName]);
        return $result['total'] > 0;
    }
    
    public function insertCharacter($data) {
        $query = "
            INSERT INTO characters (name, race_id, attitude_id, user_id, level, health, max_health, mana, max_mana, zeni)
            VALUES (?, ?, ?, ?, 1, 400, 400, 400, 400, 0)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('siii', $data['name'], $data['race_id'], $data['attitude_id'], $data['user_id']);
        return $stmt->execute();
    }
    
    public function deleteCharacterById($characterId, $userId) {
        $query = "DELETE FROM characters WHERE character_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $characterId, $userId);
        return $stmt->execute();
    }
    
    public function isUserPasswordValid($userId, $password) {
        $query = "SELECT password FROM users WHERE user_id = ?";
        $user = $this->fetchOne($query, [$userId]);
        return $user && password_verify($password, $user['password']);
    }
    
}
