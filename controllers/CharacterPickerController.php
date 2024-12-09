<?php

require_once 'models/CharacterModel.php';

class CharacterPickerController extends BaseController {
    private $character;

    public function __construct() {
        $this->character = new CharacterModel();
    }

    /**
     * Display the character picker.
     */
    public function index() {
        $userId = Session::get('user_id');
        $characters = $this->character->getAllCharactersByUser($userId);

        if (empty($characters)) {
            $this->redirect('index.php?route=create_character');
        }

        if (Session::has('selected_character_id')) {
            $this->redirect('index.php?route=news');
        }

        include 'views/character/character_picker.php';
    }

    /**
     * Handle character selection.
     */
    public function selectCharacter($characterId) {
        $userId = Session::get('user_id');
        $character = $this->character->getCharacterByIdAndUser($characterId, $userId);

        if ($character) {
            Session::set('selected_character_id', $character['character_id']);
            $this->redirect('index.php?route=news');
        } else {
            $this->redirect('index.php?route=character_picker&error=invalid_character');
        }
    }

    /**
     * Show the create character form.
     */
    public function createCharacter() {
        $userId = Session::get('user_id');

        if ($this->character->getCharacterCountByUser($userId) >= 5) {
            $this->redirect('index.php?route=character_picker&error=max_characters');
        }

        $races = $this->character->fetchAll("SELECT race_id, name, picture FROM races");
        $attitudes = $this->character->fetchAll("SELECT attitude_id, name FROM attitudes");

        include 'views/character/create_character.php';
    }

    /**
     * Handle storing a new character.
     */
    public function storeCharacter() {
        $userId = Session::get('user_id');
        $characterName = $_POST['character_name'] ?? '';
        $raceId = $_POST['race_id'] ?? null;
        $attitudeId = $_POST['attitude_id'] ?? null;

        // Validate inputs
        if (empty($characterName) || !$raceId || !$attitudeId) {
            $this->redirect('index.php?route=create_character&error=missing_fields');
        }

        if ($this->character->getCharacterCountByUser($userId) >= 5) {
            $this->redirect('index.php?route=character_picker&error=max_characters');
        }

        if ($this->character->isCharacterNameTaken($characterName)) {
            $this->redirect('index.php?route=create_character&error=name_taken');
        }

        $data = [
            'name' => $characterName,
            'race_id' => $raceId,
            'attitude_id' => $attitudeId,
            'user_id' => $userId,
        ];

        if ($this->character->insertCharacter($data)) {
            $this->redirect('index.php?route=character_picker');
        } else {
            $this->redirect('index.php?route=create_character&error=server_error');
        }
    }

    /**
     * Handle character deletion.
     */
    public function deleteCharacter() {
        $userId = Session::get('user_id');
        $characterId = $_POST['character_id'] ?? null;
        $password = $_POST['password'] ?? '';

        if (!$characterId || empty($password)) {
            $this->redirect('index.php?route=character_picker&error=missing_fields');
        }

        if (!$this->character->isUserPasswordValid($userId, $password)) {
            $this->redirect('index.php?route=character_picker&error=delete_error');
        }

        if ($this->character->deleteCharacterById($characterId, $userId)) {
            $this->redirect('index.php?route=character_picker&error=delete_success');
        } else {
            $this->redirect('index.php?route=character_picker&error=server_error');
        }
    }

    /**
     * Helper method to redirect to a URL.
     */
    private function redirect($url) {
        header("Location: $url");
        exit;
    }
}
