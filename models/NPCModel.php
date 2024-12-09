<?php
require_once 'models/BaseModel.php';

class NPCModel extends BaseModel {
    /**
     * Get all NPCs by location ID.
     *
     * @param int $locationId
     * @return array List of NPCs at the location.
     */
    public function getNPCsByLocation($locationId) {
        $query = "
            SELECT npc_id, name, description, image, is_fightable
            FROM npcs
            WHERE location_id = ?";
        return $this->fetchAll($query, [$locationId]);
    }

    /**
     * Get NPC and relationship details for interaction.
     *
     * @param int $npcId
     * @param int $characterId
     * @return array|null NPC details or null if not found.
     */
    public function getNPCDetailsForInteraction($npcId, $characterId) {
        $query = "
            SELECT 
                n.name, 
                n.description, 
                n.image, 
                n.is_hostile, 
                n.hostile_condition,
                IFNULL(cnr.relationship_points, 0) AS relationship_points, 
                c.fame, 
                c.level,
                CASE 
                    WHEN n.item_required IS NOT NULL AND n.item_required != '' THEN 
                        (SELECT COUNT(*) FROM character_inventory ci WHERE ci.character_id = ? AND FIND_IN_SET(ci.item_id, n.item_required)) 
                    ELSE 0 
                END AS has_required_item,
                n.item_required, 
                n.quest_required, 
                CASE 
                    WHEN n.quest_required IS NOT NULL THEN 
                        (SELECT COUNT(*) FROM character_quests cq WHERE cq.character_id = ? AND cq.quest_id = n.quest_required AND cq.status = 'completed') 
                    ELSE 0 
                END AS has_completed_quest
            FROM npcs n
            LEFT JOIN character_npc_relationships cnr 
                ON n.npc_id = cnr.npc_id AND cnr.character_id = ?
            JOIN characters c 
                ON c.character_id = ?
            WHERE n.npc_id = ?";
        return $this->fetchOne($query, [$characterId, $characterId, $characterId, $characterId, $npcId]);
    }

    /**
     * Get dynamic dialogue for an NPC based on relationship points.
     *
     * @param int $npcId
     * @param string $relationshipLevel
     * @return string Dialogue or default message if none found.
     */
    public function getNPCDialogue($npcId, $relationshipLevel) {
        $query = "
            SELECT dialogue
            FROM npc_dialogues
            WHERE npc_id = ? AND relationship_level = ?";
        $dialogues = $this->fetchAll($query, [$npcId, $relationshipLevel]);

        if (empty($dialogues)) {
            return "Der NPC hat momentan nichts zu sagen.";
        }

        $randomDialogue = $dialogues[array_rand($dialogues)];
        return $randomDialogue['dialogue'];
    }

    /**
     * Fetch available quests for an NPC.
     *
     * @param int $npcId
     * @param int $characterId
     * @return array List of available quests.
     */
    public function getAvailableQuests($npcId, $characterId) {
        $query = "
            SELECT sq.quest_id, sq.name AS quest_name, sq.description AS quest_description, 
                   sq.exp_reward, sq.fame_reward, sq.zeni_reward, sq.is_repeatable
            FROM side_quests sq
            LEFT JOIN npc_quests nq ON nq.quest_id = sq.quest_id
            LEFT JOIN character_quests cq 
                ON cq.quest_id = sq.quest_id 
                AND cq.character_id = ? 
                AND cq.status IN ('ongoing')
            LEFT JOIN npcs n ON nq.npc_id = n.npc_id
            WHERE nq.npc_id = ?
            AND (cq.status IS NULL OR (sq.is_repeatable = 1 AND cq.status != 'ongoing'))
            AND (SELECT relationship_points 
                 FROM character_npc_relationships 
                 WHERE npc_id = ? AND character_id = ?) >= sq.relationship_required
            AND (SELECT fame 
                 FROM characters 
                 WHERE character_id = ?) >= sq.fame_required
            AND n.location_id = (SELECT location_id FROM characters WHERE character_id = ?)";
        return $this->fetchAll($query, [$characterId, $npcId, $npcId, $characterId, $characterId, $characterId]);
    }

    /**
     * Fetch the last relationship gain timestamp.
     */
    public function getLastRelationshipGain($characterId, $npcId) {
        $query = "
            SELECT last_relationship_gain
            FROM character_npc_relationships
            WHERE character_id = ? AND npc_id = ?";
        $result = $this->fetchOne($query, [$characterId, $npcId]);
        return $result['last_relationship_gain'] ?? null;
    }

    /**
     * Update or insert relationship points.
     */
    public function updateRelationshipPoints($characterId, $npcId, $points) {
        $query = "
            INSERT INTO character_npc_relationships (character_id, npc_id, relationship_points, last_interaction, last_relationship_gain)
            VALUES (?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                relationship_points = relationship_points + VALUES(relationship_points),
                last_interaction = NOW(),
                last_relationship_gain = NOW()";
        $this->fetchAll($query, [$characterId, $npcId, $points]);
    }

    /**
     * Add a fight record to the database.
     */
    public function startFightRecord($characterId, $npcId) {
        $query = "
            INSERT INTO fights (character_id, npc_id, start_time, status)
            VALUES (?, ?, NOW(), 'ongoing')";
        $this->fetchAll($query, [$characterId, $npcId]);
    }

}
