<?php

class CharacterController extends BaseController {
    private $character;

    public function __construct() {
        $this->character = new CharacterModel();
    }

    public function switchCharacter($characterId) {
        $userId = Session::get('user_id');
        $character = $this->character->getCharacterByIdAndUser($characterId, $userId);

        if ($character) {
            Session::remove('temporary_character_data');
            Session::set('selected_character_id', $character['character_id']);
            $this->redirect('index.php?route=news');
        } else {
            $this->redirect('index.php?route=character_picker&error=invalid_character');
        }
    }

    public function getLoggedInCharacterDetails() {
        $characterId = Session::get('selected_character_id');
        $details = $this->character->getCharacterDetails($characterId);

        if (!$details) {
            return null;
        }

        $nextLevelData = $this->character->getLevelData($details['level'] + 1);
        $details['exp_to_next_level'] = $nextLevelData['exp_required'] ?? 0;

        return $details;
    }

    public function displayCharacterDetails() {
        $details = $this->getLoggedInCharacterDetails();

        if (!$details) {
            return;
        }

        $details['exp_percent'] = $this->calculatePercentage($details['exp'], $details['exp_to_next_level']);
        $details['health_percent'] = $this->calculatePercentage($details['health'], $details['max_health']);
        $details['mana_percent'] = $this->calculatePercentage($details['mana'], $details['max_mana']);

        include 'views/character/left_menu.php';
    }

    private function calculatePercentage($current, $max) {
        return ($max > 0) ? ($current / $max) * 100 : 0;
    }

    private function redirect($route) {
        header("Location: $route");
        exit;
    }
}
