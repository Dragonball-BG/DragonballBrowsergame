<?php
require_once 'models/NPCModel.php';
require_once 'helpers/Session.php';

/**
 * Controller to handle NPC interactions and related actions.
 */
class NPCController extends BaseController {
    private $npcModel;

    /**
     * Constructor to initialize NPC model.
     */
    public function __construct() {
        $this->npcModel = new NPCModel();
    }

    /**
     * Displays a list of NPCs based on the given location ID.
     *
     * @param int $locationId ID of the location to fetch NPCs.
     */
    public function viewNPCsByLocation($locationId) {
        $npcs = $this->npcModel->getNPCsByLocation($locationId);
        include 'views/npcs/view_npcs.php';
    }

    /**
     * Handles interaction with an NPC by ID.
     *
     * @param int $npcId ID of the NPC to interact with.
     */
    public function interactWithNPC($npcId) {
        $characterId = Session::get('selected_character_id');

        // Fetch NPC details for interaction.
        $npc = $this->npcModel->getNPCDetailsForInteraction($npcId, $characterId);

        if (!$npc) {
            // NPC not found, display error view.
            $content = "<p>NPC nicht gefunden.</p>";
            include 'views/error.php';
            return;
        }

        // Determine if the NPC is hostile.
        $isHostile = $this->determineHostility($npc);

        // Handle interaction based on hostility.
        if ($isHostile) {
            $this->handleHostileInteraction($npc, $characterId, $npcId);
        } else {
            $this->updateRelationshipWithCooldown($characterId, $npcId, 5);
            $this->handleNonHostileInteraction($npc, $characterId, $npcId);
        }
    }

    /**
     * Determines if an NPC is hostile based on specific conditions.
     *
     * @param array $npc NPC data including conditions.
     * @return bool True if the NPC is hostile, otherwise false.
     */
    private function determineHostility($npc) {
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

    /**
     * Handles interaction with a hostile NPC.
     *
     * @param array $npc NPC data.
     * @param int $characterId ID of the interacting character.
     * @param int $npcId ID of the hostile NPC.
     */
    private function handleHostileInteraction($npc, $characterId, $npcId) {
        if (!empty($npc['item_required']) && $npc['has_required_item'] > 0) {
            // NPC demands an item.
            $randomDialogue = "Ich sehe, dass du hast, was ich brauche. Gib es mir, oder bereite dich auf die Konsequenzen vor!";
            $giveItemLink = "index.php?route=give_item&npc_id={$npcId}&item_id={$npc['item_required']}";
            $refuseItemLink = "index.php?route=refuse_item&npc_id={$npcId}";

            include 'views/npcs/interact_with_npc_item_hostile.php';
        } else {
            // NPC attacks the character.
            $randomDialogue = "Du wagst es, mir nahe zu kommen? Mach dich bereit zu kämpfen!";
            $this->startFight($characterId, $npcId);
            include 'views/npcs/interact_with_npc_hostile.php';
        }
    }

    /**
     * Handles interaction with a non-hostile NPC.
     *
     * @param array $npc NPC data.
     * @param int $characterId ID of the interacting character.
     * @param int $npcId ID of the non-hostile NPC.
     */
    private function handleNonHostileInteraction($npc, $characterId, $npcId) {
        if (!empty($npc['item_required']) && $npc['has_required_item'] > 0) {
            // NPC demands an item.
            $randomDialogue = "Ich sehe, dass du etwas hast, das ich brauche. Gibst du es mir?";
            $giveItemLink = "index.php?route=give_item&npc_id={$npcId}&item_id={$npc['item_required']}";
            $refuseItemLink = "index.php?route=refuse_item_non_hostile&npc_id={$npcId}";

            include 'views/npcs/interact_with_npc_item_non_hostile.php';
        } else {
            // Regular interaction with available quests.
            $relationshipLevel = $this->getRelationshipLevel($npc['relationship_points']);
            $randomDialogue = $this->npcModel->getNPCDialogue($npcId, $relationshipLevel);
            $quests = $this->npcModel->getAvailableQuests($npcId, $characterId);

            include 'views/npcs/interact_with_npc.php';
        }
    }

    /**
     * Updates the relationship points for an NPC with cooldown.
     *
     * @param int $characterId ID of the interacting character.
     * @param int $npcId ID of the NPC.
     * @param int $points Number of points to add.
     * @return bool True if update is successful, otherwise false.
     */
    private function updateRelationshipWithCooldown($characterId, $npcId, $points) {
        $cooldownPeriod = 7200; // Cooldown period in seconds (2 hours).

        $lastGainTime = $this->npcModel->getLastRelationshipGain($characterId, $npcId);
        $currentTime = time();

        if ($lastGainTime && ($currentTime - strtotime($lastGainTime)) < $cooldownPeriod) {
            // Relationship gain is on cooldown.
            return false;
        }

        // Update relationship points.
        $this->npcModel->updateRelationshipPoints($characterId, $npcId, $points);

        return true;
    }

    /**
     * Starts a fight with an NPC.
     *
     * @param int $characterId ID of the character.
     * @param int $npcId ID of the NPC.
     */
    private function startFight($characterId, $npcId) {
        $this->npcModel->startFightRecord($characterId, $npcId);
        header("Location: index.php?route=fight&npc_id={$npcId}");
        exit;
    }

    /**
     * Determines the relationship level based on points.
     *
     * @param int $relationshipPoints Current relationship points with the NPC.
     * @return string Relationship level.
     */
    private function getRelationshipLevel($relationshipPoints) {
        if ($relationshipPoints > 80) {
            return 'vertraut';
        } elseif ($relationshipPoints > 50) {
            return 'freundlich';
        } elseif ($relationshipPoints > 20) {
            return 'neutral';
        }
        return 'misstrauisch';
    }
}
