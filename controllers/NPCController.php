<?php 
class NPCController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function viewNPCsByLocation($locationId) {
        $stmt = $this->db->prepare(
            "SELECT n.npc_id, n.name, n.description, n.image, n.is_fightable
            FROM npcs n
            WHERE n.location_id = ?"
        );
        $stmt->bind_param('i', $locationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $npcs = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        include 'views/npcs/view_npcs.php';
    }

    public function interactWithNPC($npcId)
    {
        $characterId = Session::get('selected_character_id');

        // Fetch NPC and relationship details
        $stmt = $this->db->prepare(
            "SELECT 
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
                    FROM 
                        npcs n
                    LEFT JOIN 
                        character_npc_relationships cnr 
                    ON 
                        n.npc_id = cnr.npc_id AND cnr.character_id = ?
                    JOIN 
                        characters c 
                    ON 
                        c.character_id = ?
                    WHERE 
                        n.npc_id = ?
                    "
        );
        $stmt->bind_param('iiiii', $characterId, $characterId, $characterId, $characterId, $npcId);
        $stmt->execute();
        $npc = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$npc) {
            $content = "<p>NPC nicht gefunden.</p>";
            include 'views/error.php';
            return;
        }

        // Determine hostility dynamically
        $isHostile = $this->determineHostility($npc);

        if ($isHostile) {
            $this->handleHostileInteraction($npc, $characterId, $npcId);
        } else {
            $this->updateRelationshipWithCooldown($characterId, $npcId, 5);
            $this->handleNonHostileInteraction($npc, $characterId, $npcId);
        }
    }

    private function determineHostility($npc)
    {
        if ($npc['hostile_condition']) {
            $hostileCondition = json_decode($npc['hostile_condition'], true);
            return !(
                $npc['relationship_points'] >= ($hostileCondition['relationship'] ?? 0) &&
                $npc['fame'] >= ($hostileCondition['fame'] ?? 0) &&
                $npc['level'] >= ($hostileCondition['level_required'] ?? 0) &&
                (!$npc['quest_required'] || $npc['has_completed_quest']) &&
                (!$npc['item_required'] || $npc['has_required_item'])
            );
        }
        return $npc['is_hostile'];
    }

    private function handleHostileInteraction($npc, $characterId, $npcId)
    {
        if (!empty($npc['item_required']) && $npc['has_required_item'] > 0) {
            // NPC demands the item
            $randomDialogue = "Ich sehe, dass du hast, was ich brauche. Gib es mir, oder bereite dich auf die Konsequenzen vor!";
            $giveItemLink = "index.php?route=give_item&npc_id={$npcId}&item_id={$npc['item_required']}";
            $refuseItemLink = "index.php?route=refuse_item&npc_id={$npcId}";

            include 'views/npcs/interact_with_npc_item_hostile.php';
        } else {
            // No item, direct hostility
            $randomDialogue = "Du wagst es, mir nahe zu kommen? Mach dich bereit zu kämpfen!";
            $this->startFight($characterId, $npcId);
            include 'views/npcs/interact_with_npc_hostile.php';
        }
    }

    private function handleNonHostileInteraction($npc, $characterId, $npcId)
    {
        if (!empty($npc['item_required']) && $npc['has_required_item'] > 0) {
            // NPC demands an item
            $randomDialogue = "Ich sehe, dass du etwas hast, das ich brauche. Gibst du es mir?";
            $giveItemLink = "index.php?route=give_item&npc_id={$npcId}&item_id={$npc['item_required']}";
            $refuseItemLink = "index.php?route=refuse_item_non_hostile&npc_id={$npcId}";

            include 'views/npcs/interact_with_npc_item_non_hostile.php';
        } else {
            // Normal interaction with quests
            $randomDialogue = $this->getDynamicDialogue($npcId, $npc['relationship_points']);
            $quests = $this->fetchAvailableQuests($npcId, $characterId);

            include 'views/npcs/interact_with_npc.php';
        }
    }

    private function fetchAvailableQuests($npcId, $characterId)
    {
        $stmt = $this->db->prepare(
            "SELECT sq.quest_id, sq.name AS quest_name, sq.description AS quest_description, 
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
            AND n.location_id = (SELECT location_id FROM characters WHERE character_id = ?)"
        );
        $stmt->bind_param('iiiiii', $characterId, $npcId, $npcId, $characterId, $characterId, $characterId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    private function getDynamicDialogue($npcId, $relationshipPoints)
    {
        // Determine the relationship level
        $relationshipLevel = 'misstrauisch';
        if ($relationshipPoints > 80) {
            $relationshipLevel = 'vertraut';
        } elseif ($relationshipPoints > 50) {
            $relationshipLevel = 'freundlich';
        } elseif ($relationshipPoints > 20) {
            $relationshipLevel = 'neutral';
        }

        // Fetch dialogues from the database for the specific NPC and relationship level
        $stmt = $this->db->prepare(
            "SELECT dialogue FROM npc_dialogues 
            WHERE npc_id = ? AND relationship_level = ?"
        );
        $stmt->bind_param('is', $npcId, $relationshipLevel);
        $stmt->execute();
        $result = $stmt->get_result();
        $dialogues = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // If no dialogues found, return a default message
        if (empty($dialogues)) {
            return "Der NPC hat momentan nichts zu sagen.";
        }

        // Randomly select a dialogue
        $randomDialogue = $dialogues[array_rand($dialogues)];
        return $randomDialogue['dialogue'];
    }

    public function startFight($characterId, $npcId) {
        // Logic to add the player to a fight with the NPC
        // Example: Add fight record in a `fights` table
    }

    public function updateRelationshipWithCooldown($characterId, $npcId, $points)
    {
        $cooldownPeriod = 7200; // Cooldown in seconds (5 minutes)

        // Check last relationship gain
        $stmt = $this->db->prepare(
            "SELECT last_relationship_gain 
            FROM character_npc_relationships 
            WHERE character_id = ? AND npc_id = ?"
        );
        $stmt->bind_param('ii', $characterId, $npcId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $currentTime = time();
        $lastGainTime = isset($result['last_relationship_gain']) ? strtotime($result['last_relationship_gain']) : 0;

        if ($currentTime - $lastGainTime < $cooldownPeriod) {
            // Relationship gain on cooldown
            return false;
        }

        // Update or insert relationship points
        $stmt = $this->db->prepare(
            "INSERT INTO character_npc_relationships (character_id, npc_id, relationship_points, last_interaction, last_relationship_gain)
            VALUES (?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
                relationship_points = relationship_points + VALUES(relationship_points),
                last_interaction = NOW(),
                last_relationship_gain = NOW()"
        );
        $stmt->bind_param('iii', $characterId, $npcId, $points);
        $stmt->execute();
        $stmt->close();

        return true;
    }

    public function startQuest($npcId, $questId)
    {
        $characterId = Session::get('selected_character_id');

        // Fetch character's current quest points and refill if needed
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
            header("Location: index.php?route=interact_with_npc&npc_id=$npcId&error=character_not_found");
            exit;
        }

        $questPoints = $this->refillQuestPoints($character);

        // Check if the character has enough quest points
        $questCost = 3; // Example cost for starting a quest
        if ($questPoints < $questCost) {
            header("Location: index.php?route=interact_with_npc&npc_id=$npcId&error=insufficient_quest_points");
            exit;
        }

        // Deduct quest points
        $stmt = $this->db->prepare(
            "UPDATE characters 
            SET quest_points = GREATEST(0, quest_points - ?) 
            WHERE character_id = ?"
        );
        $stmt->bind_param('ii', $questCost, $characterId);
        $stmt->execute();
        $stmt->close();

        // Start the quest
        $stmt = $this->db->prepare(
            "SELECT is_time_based, time_limit_seconds 
            FROM side_quests WHERE quest_id = ?"
        );
        $stmt->bind_param('i', $questId);
        $stmt->execute();
        $quest = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$quest) {
            header("Location: index.php?route=interact_with_npc&npc_id=$npcId&message=quest_not_found");
            exit;
        }

        $endTime = null;
        if ($quest['is_time_based']) {
            $endTime = new DateTime();
            $endTime->modify("+{$quest['time_limit_seconds']} seconds");
        }

        $stmt = $this->db->prepare(
            "INSERT INTO character_quests (character_id, quest_id, quest_type, start_time, end_time, status) 
            VALUES (?, ?, 'side', NOW(), ?, 'ongoing')"
        );
        $endTimeFormatted = $endTime ? $endTime->format('Y-m-d H:i:s') : null;
        $stmt->bind_param('iis', $characterId, $questId, $endTimeFormatted);
        $stmt->execute();
        $stmt->close();

        header("Location: index.php?route=interact_with_npc&npc_id=$npcId&message=quest_started");
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

}
?>
