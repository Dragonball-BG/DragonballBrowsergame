<?php

class CharacterHelper
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Check and update the character's level based on experience points.
     *
     * @param int $characterId The ID of the character to check.
     */
    public function checkAndUpdateLevel($characterId) {
        // Fetch the character's current experience and level
        $stmt = $this->db->prepare(
            "SELECT exp, level, max_health, max_mana, ki, fame 
            FROM characters 
            WHERE character_id = ?"
        );
        $stmt->bind_param('i', $characterId);
        $stmt->execute();
        $character = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$character) {
            return; // Character not found
        }

        // Fetch the next level's requirements
        $stmt = $this->db->prepare(
            "SELECT exp_required, health_bonus, mana_bonus, ki_bonus, fame_bonus 
            FROM levels 
            WHERE level = ?"
        );
        $nextLevel = $character['level'] + 1;
        $stmt->bind_param('i', $nextLevel);
        $stmt->execute();
        $levelData = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$levelData || $character['exp'] < $levelData['exp_required']) {
            return; // Not enough experience or level data unavailable
        }

        // Update the character's level and stats
        $stmt = $this->db->prepare(
            "UPDATE characters 
            SET level = ?, 
                max_health = max_health + ?, 
                max_mana = max_mana + ?, 
                ki = ki + ?, 
                fame = fame + ? 
            WHERE character_id = ?"
        );
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
}

?>