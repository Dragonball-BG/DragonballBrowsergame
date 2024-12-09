<?php
require_once 'models/BaseModel.php';

class QuestModel extends BaseModel {
    /**
     * Get character quest details.
     *
     * @param int $characterId
     * @return array|null
     */
    public function getCharacterQuests($characterId) {
        $query = "
            SELECT cq.character_quest_id, sq.quest_id, sq.name AS quest_name, sq.description, cq.start_time, 
                   sq.exp_reward, sq.fame_reward, sq.zeni_reward, sq.is_time_based, sq.time_limit_seconds,
                   TIMESTAMPDIFF(SECOND, cq.start_time, NOW()) AS elapsed_time
            FROM character_quests cq
            JOIN side_quests sq ON cq.quest_id = sq.quest_id
            WHERE cq.character_id = ? AND cq.status = 'ongoing'";
        return $this->fetchAll($query, [$characterId]);
    }

    /**
     * Get quest completion requirements.
     *
     * @param int $questId
     * @return array|null
     */
    public function getQuestCompletionRequirements($questId) {
        $query = "SELECT completion_requirements FROM side_quests WHERE quest_id = ?";
        $result = $this->fetchOne($query, [$questId]);
        return $result ? json_decode($result['completion_requirements'], true) : null;
    }

    /**
     * Mark a quest as completed.
     *
     * @param int $characterQuestId
     * @return bool
     */
    public function completeQuest($characterQuestId) {
        $query = "
            UPDATE character_quests 
            SET status = 'completed', completed_time = NOW() 
            WHERE character_quest_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('i', $characterQuestId);
        return $stmt->execute();
    }

    /**
     * Mark a quest as failed.
     *
     * @param int $characterQuestId
     * @param int $characterId
     * @return bool
     */
    public function failQuest($characterQuestId, $characterId) {
        $query = "
            UPDATE character_quests 
            SET status = 'failed', completed_time = NOW() 
            WHERE character_quest_id = ? AND character_id = ? AND status = 'ongoing'";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ii', $characterQuestId, $characterId);
        return $stmt->execute();
    }

    /**
     * Get quest rewards.
     *
     * @param int $questId
     * @return array|null
     */
    public function getQuestRewards($questId) {
        $query = "
            SELECT exp_reward, fame_reward, zeni_reward, health_reward, mana_reward, ki_reward 
            FROM side_quests WHERE quest_id = ?";
        return $this->fetchOne($query, [$questId]);
    }

    /**
     * Apply rewards to the character.
     *
     * @param int $characterId
     * @param array $rewards
     * @return bool
     */
    public function applyRewards($characterId, $rewards) {
        $query = "
            UPDATE characters 
            SET exp = exp + ?, fame = fame + ?, zeni = zeni + ?, 
                max_health = max_health + ?, max_mana = max_mana + ?, ki = ki + ?
            WHERE character_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            'iiiiiii',
            $rewards['exp_reward'],
            $rewards['fame_reward'],
            $rewards['zeni_reward'],
            $rewards['health_reward'],
            $rewards['mana_reward'],
            $rewards['ki_reward'],
            $characterId
        );
        return $stmt->execute();
    }
}
