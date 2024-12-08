<?php

require_once 'helpers/Session.php';
require_once 'helpers/Database.php';

class CharacterPickerController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

        // Ensure the user is logged in
        if (!Session::has('user_id')) {
            header('Location: index.php?route=login');
            exit;
        }
    }

    /**
     * Display the character picker.
     */
    public function index()
    {
        $userId = Session::get('user_id');

        // Fetch all characters for the logged-in user
        $stmt = $this->db->prepare(
            "SELECT c.character_id, c.name, c.level, c.ki, c.health, c.max_health, c.mana, c.max_mana, r.name AS race_name, r.picture AS race_picture
            FROM characters c
            JOIN races r ON c.race_id = r.race_id
            WHERE c.user_id = ?"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $characters = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Redirect to create character if no characters exist
        if (empty($characters)) {
            header("Location: index.php?route=create_character");
            exit;
        }

        if (Session::has('selected_character_id')) {
            header("Location: index.php?route=news");
            exit;
        }

        include 'views/character/character_picker.php';
    }

    /**
     * Handle character selection.
     */
    public function selectCharacter($characterId)
    {
        $userId = Session::get('user_id');

        // Validate that the character belongs to the logged-in user
        $stmt = $this->db->prepare(
            "SELECT c.character_id
            FROM characters c
            WHERE c.character_id = ? AND c.user_id = ?"
        );
        $stmt->bind_param('ii', $characterId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $character = $result->fetch_assoc();
        $stmt->close();

        if ($character) {
            Session::set('selected_character_id', $character['character_id']);
            header('Location: index.php?route=news');
        } else {
            header('Location: index.php?route=character_picker&error=invalid_character');
        }
        exit;
    }

    /**
     * Show the create character form.
     */
    public function createCharacter()
    {
        $userId = Session::get('user_id');

        // Check if the user already has 5 characters
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total
            FROM characters
            WHERE user_id = ?"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $characterCount = $result->fetch_assoc();
        $stmt->close();

        if ($characterCount['total'] >= 5) {
            header('Location: index.php?route=character_picker&error=max_characters');
            exit;
        }

        // Fetch races
        $racesStmt = $this->db->prepare("SELECT race_id, name, picture FROM races");
        $racesStmt->execute();
        $racesResult = $racesStmt->get_result();
        $races = $racesResult->fetch_all(MYSQLI_ASSOC);
        $racesStmt->close();

        // Fetch attitudes
        $attitudesStmt = $this->db->prepare("SELECT attitude_id, name FROM attitudes");
        $attitudesStmt->execute();
        $attitudesResult = $attitudesStmt->get_result();
        $attitudes = $attitudesResult->fetch_all(MYSQLI_ASSOC);
        $attitudesStmt->close();

        // Include the create character view
        include 'views/character/create_character.php';
    }

    /**
     * Handle storing a new character.
     */
    public function storeCharacter()
    {
        $userId = Session::get('user_id');
        $characterName = $_POST['character_name'] ?? '';
        $raceId = $_POST['race_id'] ?? null;
        $attitudeId = $_POST['attitude_id'] ?? null;

        // Validate inputs
        if (empty($characterName) || !$raceId || !$attitudeId) {
            header("Location: index.php?route=create_character&error=missing_fields");
            exit;
        }

        // Check if the user already has 5 characters
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total
            FROM characters
            WHERE user_id = ?"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $characterCount = $result->fetch_assoc();
        $stmt->close();

        if ($characterCount['total'] >= 5) {
            header('Location: index.php?route=character_picker&error=max_characters');
            exit;
        }
        
        // Check if the character name is already taken (global uniqueness check)
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total
            FROM characters
            WHERE name = ?"
        );
        $stmt->bind_param('s', $characterName);
        $stmt->execute();
        $result = $stmt->get_result();
        $nameCount = $result->fetch_assoc();
        $stmt->close();

        if ($nameCount['total'] > 0) {
            header("Location: index.php?route=create_character&error=name_taken");
            exit;
        }

        // Insert the new character into the database
        $stmt = $this->db->prepare(
            "INSERT INTO characters (name, race_id, attitude_id, user_id, level, health, max_health, mana, max_mana, zeni)
            VALUES (?, ?, ?, ?, 1, 400, 400, 400, 400, 0)"
        );
        $stmt->bind_param('siii', $characterName, $raceId, $attitudeId, $userId);

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: index.php?route=character_picker');
        } else {
            $stmt->close();
            header('Location: index.php?route=create_character&error=server_error');
        }
        exit;
    }

    /**
     * Handle character deletion.
     */
    public function deleteCharacter()
    {
        $userId = Session::get('user_id');
        $characterId = $_POST['character_id'] ?? null;
        $password = $_POST['password'] ?? '';

        // Validate inputs
        if (!$characterId || empty($password)) {
            header("Location: index.php?route=character_picker&error=missing_fields");
            exit;
        }

        // Verify the user's password
        $stmt = $this->db->prepare(
            "SELECT password
            FROM users
            WHERE user_id = ?"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($password, $user['password'])) {
            header("Location: index.php?route=character_picker&error=delete_error");
            exit;
        }

        // Check if the character belongs to the logged-in user
        $stmt = $this->db->prepare(
            "SELECT character_id
            FROM characters
            WHERE character_id = ? AND user_id = ?"
        );
        $stmt->bind_param('ii', $characterId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $character = $result->fetch_assoc();
        $stmt->close();

        if (!$character) {
            header("Location: index.php?route=character_picker&error=invalid_character");
            exit;
        }

        // Delete the character
        $stmt = $this->db->prepare(
            "DELETE FROM characters
            WHERE character_id = ? AND user_id = ?"
        );
        $stmt->bind_param('ii', $characterId, $userId);

        if ($stmt->execute()) {
            $stmt->close();
            header('Location: index.php?route=character_picker&error=delete_success');
        } else {
            $stmt->close();
            header('Location: index.php?route=character_picker&error=server_error');
        }
        exit;
    }

}
