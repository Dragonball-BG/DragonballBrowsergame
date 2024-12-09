<?php
require_once 'models/QuestModel.php';
require_once 'helpers/Session.php';

class QuestController extends BaseController {
    private $questModel;

    public function __construct() {
        $this->questModel = new QuestModel();
    }

    /**
     * View active quests.
     */
    public function viewActiveQuests() {
        $characterId = Session::get('selected_character_id');

        // Fetch active quests
        $quests = $this->questModel->getCharacterQuests($characterId);

        // Add logic for quest completion and failure
        foreach ($quests as &$quest) {
            $quest['can_end'] = $this->validateQuestCompletion($characterId, $quest['quest_id']);
            $quest['can_fail'] = $quest['is_time_based'] && $quest['elapsed_time'] > $quest['time_limit_seconds'];
        }

        include 'views/quests/view_active_quests.php';
    }

    /**
     * End a quest.
     */
    public function endQuest($characterQuestId) {
        $characterId = Session::get('selected_character_id');

        $requirements = $this->questModel->getQuestCompletionRequirements($characterQuestId);

        if (!$this->validateQuestCompletion($characterId, $characterQuestId)) {
            $this->redirect('index.php?route=view_active_quests&error=requirements_not_met');
        }

        // Deduct requirements (e.g., items, gold)
        $this->deductQuestRequirements($characterId, $requirements);

        // Complete quest
        $this->questModel->completeQuest($characterQuestId);

        // Apply rewards
        $rewards = $this->questModel->getQuestRewards($characterQuestId);
        $this->questModel->applyRewards($characterId, $rewards);

        $this->redirect('index.php?route=view_active_quests&message=quest_completed');
    }

    /**
     * Fail a quest.
     */
    public function failQuest($characterQuestId) {
        $characterId = Session::get('selected_character_id');
        $this->questModel->failQuest($characterQuestId, $characterId);

        $this->redirect('index.php?route=view_active_quests&message=quest_failed');
    }

    /**
     * Deduct quest requirements.
     */
    private function deductQuestRequirements($characterId, $requirements) {
        // Logic for deducting items, gold, etc.
    }

    /**
     * Validate quest completion.
     */
    private function validateQuestCompletion($characterId, $questId) {
        $requirements = $this->questModel->getQuestCompletionRequirements($questId);

        // Logic for validating quest requirements
        return true; // Simplified for brevity
    }

    /**
     * Redirect to a route.
     */
    private function redirect($route) {
        header("Location: $route");
        exit;
    }
}
