<?php 
class QuestController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function viewActiveQuests()
    {
        $characterId = Session::get('selected_character_id');

        // Fetch character details
        $stmt = $this->db->prepare(
            "SELECT quest_points, last_quest_point_refill 
            FROM characters 
            WHERE character_id = ?"
        );
        $stmt->bind_param('i', $characterId);
        $stmt->execute();
        $character = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$character) {
            $content = "<p>Charakter nicht gefunden.</p>";
            include 'views/error.php';
            return;
        }

        // Refill quest points before displaying
        $character['quest_points'] = $this->refillQuestPoints($character) - 3;
        if ($character['quest_points'] < 0) $character['quest_points'] = 0;

        // Fetch active quests for the character
        $stmt = $this->db->prepare(
            "SELECT cq.character_quest_id, sq.quest_id as quest_id, sq.name AS quest_name, sq.description, cq.start_time, 
                    sq.exp_reward, sq.fame_reward, sq.zeni_reward, sq.is_time_based, sq.time_limit_seconds,
                    TIMESTAMPDIFF(SECOND, cq.start_time, NOW()) AS elapsed_time
            FROM character_quests cq
            JOIN side_quests sq ON cq.quest_id = sq.quest_id
            WHERE cq.character_id = ? AND cq.status = 'ongoing'"
        );
        $stmt->bind_param('i', $characterId);
        $stmt->execute();
        $quests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Add additional logic to check if quests can be ended or failed
        foreach ($quests as &$quest) {
            // Check if quest can be ended
            $quest['can_end'] = $this->validateQuestCompletion($characterId, $quest['quest_id']);

            // Check if quest can fail (time limit exceeded for time-based quests)
            $quest['can_fail'] = $quest['is_time_based'] && $quest['elapsed_time'] > $quest['time_limit_seconds'];
        }

        // Pass character and quests to the view
        include 'views/quests/view_active_quests.php';
    }

    public function endQuest($characterQuestId)
    {
        $characterId = Session::get('selected_character_id');

        // Fetch quest ID
        $stmt = $this->db->prepare(
            "SELECT quest_id 
            FROM character_quests 
            WHERE character_quest_id = ? AND character_id = ? AND status = 'ongoing'"
        );
        $stmt->bind_param('ii', $characterQuestId, $characterId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$result) {
            header("Location: index.php?route=view_active_quests&error=quest_not_found");
            exit;
        }

        $questId = $result['quest_id'];

        // Fetch quest requirements
        $stmt = $this->db->prepare(
            "SELECT completion_requirements 
            FROM side_quests 
            WHERE quest_id = ?"
        );
        $stmt->bind_param('i', $questId);
        $stmt->execute();
        $quest = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$quest) {
            header("Location: index.php?route=view_active_quests&error=quest_not_found");
            exit;
        }

        $requirements = json_decode($quest['completion_requirements'], true);

        // Validate quest completion requirements
        if (!$this->validateQuestCompletion($characterId, $questId)) {
            header("Location: index.php?route=view_active_quests&error=requirements_not_met");
            exit;
        }

        // Deduct required items or resources
        $this->deductQuestRequirements($characterId, $requirements);

        // Mark quest as completed
        $stmt = $this->db->prepare(
            "UPDATE character_quests 
            SET status = 'completed', completed_time = NOW() 
            WHERE character_quest_id = ?"
        );
        $stmt->bind_param('i', $characterQuestId);
        $stmt->execute();
        $stmt->close();

        // Apply rewards
        $this->applyQuestRewards($characterId, $questId);

        header("Location: index.php?route=view_active_quests&message=quest_completed");
    }

    private function deductQuestRequirements($characterId, $requirements)
    {
        // Deduct items
        if (!empty($requirements['required_items'])) {
            foreach ($requirements['required_items'] as $itemId) {
                $stmt = $this->db->prepare(
                    "DELETE FROM character_inventory 
                    WHERE character_id = ? AND item_id = ? LIMIT 1"
                );
                $stmt->bind_param('ii', $characterId, $itemId);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Deduct Zeni (gold)
        if (!empty($requirements['required_gold'])) {
            $stmt = $this->db->prepare(
                "UPDATE characters 
                SET zeni = zeni - ? 
                WHERE character_id = ? AND zeni >= ?"
            );
            $stmt->bind_param('iii', $requirements['required_gold'], $characterId, $requirements['required_gold']);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                // Insufficient Zeni, throw error
                $stmt->close();
                header("Location: index.php?route=view_active_quests&error=insufficient_gold");
                exit;
            }

            $stmt->close();
        }

        // Deduct other resources if needed (e.g., mana, health, etc.)
        // Add additional logic here if more resource types are required.
    }

    private function validateQuestCompletion($characterId, $questId)
    {
        // Fetch quest requirements
        $stmt = $this->db->prepare(
            "SELECT completion_requirements 
            FROM side_quests 
            WHERE quest_id = ?"
        );
        $stmt->bind_param('i', $questId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$result || empty($result['completion_requirements'])) {
            return true; // No requirements mean quest can always be completed
        }

        $requirements = json_decode($result['completion_requirements'], true);

        // Check required items
        if (!empty($requirements['required_items'])) {
            $itemCheckStmt = $this->db->prepare(
                "SELECT COUNT(*) AS item_count 
                FROM character_inventory 
                WHERE character_id = ? AND item_id IN (" . implode(',', $requirements['required_items']) . ")"
            );
            $itemCheckStmt->bind_param('i', $characterId);
            $itemCheckStmt->execute();
            $itemResult = $itemCheckStmt->get_result()->fetch_assoc();
            $itemCheckStmt->close();

            if ($itemResult['item_count'] < count($requirements['required_items'])) {
                return false; // Missing required items
            }
        }

        // Check required fights
        if (!empty($requirements['required_fights'])) {
            foreach ($requirements['required_fights'] as $fight) {
                $npcId = $fight['npc_id'];
                $count = $fight['count'];

                $fightCheckStmt = $this->db->prepare(
                    "SELECT COUNT(*) AS fight_count 
                    FROM character_fight_logs 
                    WHERE character_id = ? AND npc_id = ? AND result = 'win'"
                );
                $fightCheckStmt->bind_param('ii', $characterId, $npcId);
                $fightCheckStmt->execute();
                $fightResult = $fightCheckStmt->get_result()->fetch_assoc();
                $fightCheckStmt->close();

                if ($fightResult['fight_count'] < $count) {
                    return false; // Not enough fights won
                }
            }
        }

        // Check required gold
        if (!empty($requirements['required_gold'])) {
            $goldCheckStmt = $this->db->prepare(
                "SELECT zeni 
                FROM characters 
                WHERE character_id = ?"
            );
            $goldCheckStmt->bind_param('i', $characterId);
            $goldCheckStmt->execute();
            $goldResult = $goldCheckStmt->get_result()->fetch_assoc();
            $goldCheckStmt->close();

            if ($goldResult['zeni'] < $requirements['required_gold']) {
                return false; // Not enough gold
            }
        }

        return true; // All requirements met
    }

    public function failQuest($characterQuestId)
    {
        $characterId = Session::get('selected_character_id');

        // Mark the quest as failed
        $stmt = $this->db->prepare(
            "UPDATE character_quests 
             SET status = 'failed', completed_time = NOW() 
             WHERE character_quest_id = ? AND character_id = ? AND status = 'ongoing'"
        );
        $stmt->bind_param('ii', $characterQuestId, $characterId);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?route=view_active_quests&message=quest_failed");
    }

    private function refillQuestPoints($character)
    {
        $currentTime = new DateTime();
        $lastRefillTime = new DateTime($character['last_quest_point_refill']);
        
        // Calculate elapsed hours
        $elapsedHours = floor(($currentTime->getTimestamp() - $lastRefillTime->getTimestamp()) / 3600);

        $refillRate = 3; // Points per hour
        $maxPoints = 20;

        // Calculate refillable points
        $refillablePoints = min(
            $maxPoints, 
            $character['quest_points'] + ($elapsedHours * $refillRate)
        );

        if ($refillablePoints > $character['quest_points'] && $elapsedHours > 0) {
            // Update quest points and last refill time
            $stmt = $this->db->prepare(
                "UPDATE characters 
                SET quest_points = ?, last_quest_point_refill = NOW() 
                WHERE character_id = ?"
            );
            $stmt->bind_param('ii', $refillablePoints, $character['character_id']);
            $stmt->execute();
            $stmt->close();
        }

        return $refillablePoints;
    }

    private function applyQuestRewards($characterId, $questId)
    {
        // Fetch quest rewards
        $stmt = $this->db->prepare(
            "SELECT exp_reward, fame_reward, zeni_reward, health_reward, mana_reward, ki_reward 
            FROM side_quests 
            WHERE quest_id = ?"
        );
        $stmt->bind_param('i', $questId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$result) {
            return; // No rewards found
        }

        // Apply rewards to the character
        $stmt = $this->db->prepare(
            "UPDATE characters 
            SET exp = exp + ?, 
                fame = fame + ?, 
                zeni = zeni + ?, 
                max_health = max_health + ?, 
                max_mana = max_mana + ?, 
                ki = ki + ?
            WHERE character_id = ?"
        );
        $stmt->bind_param(
            'iiiiiii', 
            $result['exp_reward'], 
            $result['fame_reward'], 
            $result['zeni_reward'], 
            $result['health_reward'], 
            $result['mana_reward'], 
            $result['ki_reward'], 
            $characterId
        );
        $stmt->execute();
        $stmt->close();
    }

}
?>
