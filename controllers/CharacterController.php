<?php
require_once 'models/Character.php';

class CharacterController {

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

    public function switchCharacter($characterId)
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
            // Clear any session data related to the current character
            Session::remove('temporary_character_data');

            // Update the selected character in the session
            Session::set('selected_character_id', $character['character_id']);
            header('Location: index.php?route=news'); // Redirect to the desired route after switching
        } else {
            header('Location: index.php?route=character_picker&error=invalid_character'); // Redirect with an error if validation fails
        }
        exit;
    }


    /**
     * Fetch level data for the character.
     */
    public function getLevelData($level)
    {
        $stmt = $this->db->prepare(
            "SELECT exp_required, health_bonus, mana_bonus, ki_bonus
            FROM levels
            WHERE level = ?"
        );
        $stmt->bind_param('i', $level);
        $stmt->execute();
        $result = $stmt->get_result();
        $levelData = $result->fetch_assoc();
        $stmt->close();

        return $levelData;
    }

    /**
     * Get the logged-in character's details.
     */
    public function getLoggedInCharacterDetails()
    {
        $characterId = Session::get('selected_character_id');
        $stmt = $this->db->prepare(
            "SELECT c.name, c.ki, c.fame, c.zeni, c.quest_points, c.health, c.max_health, c.mana, c.max_mana, c.level, c.exp, r.name AS race_name, a.name AS attitude_name,
            (SELECT COUNT(*) + 1 FROM characters WHERE ki > c.ki) AS rank
            FROM characters c
            JOIN races r ON c.race_id = r.race_id
            JOIN attitudes a ON c.attitude_id = a.attitude_id
            WHERE c.character_id = ?"
        );
        $stmt->bind_param('i', $characterId);
        $stmt->execute();
        $result = $stmt->get_result();
        $characterDetails = $result->fetch_assoc();
        $stmt->close();

        $levelData = $this->getLevelData($characterDetails['level'] + 1);
        $characterDetails['exp_to_next_level'] = $levelData['exp_required'];

        return $characterDetails;
    }

    public function getCharacterLocationId() {
        $characterId = Session::get('selected_character_id');

        $stmt = $this->db->prepare(
            "SELECT location_id FROM characters WHERE character_id = ?"
        );
        $stmt->bind_param('i', $characterId);
        $stmt->execute();
        $result = $stmt->get_result();
        $location = $result->fetch_assoc();
        $stmt->close();

        return $location['location_id'] ?? null; // Return null if no location found
    }

    /**
     * Display character details in the left menu.
     */
    public function displayCharacterDetails()
    {
        $characterDetails = $this->getLoggedInCharacterDetails();
        if (!$characterDetails) {
            return;
        }

        $expPercent = ($characterDetails['exp'] / $characterDetails['exp_to_next_level']) * 100;
        $healthPercent = ($characterDetails['health'] / $characterDetails['max_health']) * 100;
        $manaPercent = ($characterDetails['mana'] / $characterDetails['max_mana']) * 100;

        include 'views/character/left_menu.php'; // This view will render the character details.
    }

    public function viewProfile()
    {
        $userId = Session::get('user_id'); // Assuming session variable for logged-in user

        if (!$userId) {
            header("Location: index.php?route=login&error=user_not_logged_in");
            exit;
        }

        // Fetch the active character for the user
        $stmt = $this->db->prepare(
            "SELECT character_id 
            FROM characters 
            WHERE user_id = ? AND locked = 0 
            LIMIT 1"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $activeCharacter = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Fetch all characters for the user
        $stmt = $this->db->prepare(
            "SELECT 
                c.character_id, 
                c.name, 
                c.level, 
                c.exp, 
                c.defense, 
                c.ki, 
                c.health, 
                c.max_health, 
                c.mana, 
                c.max_mana, 
                c.quest_points, 
                c.zeni, 
                c.alive, 
                c.race_id, 
                c.attitude_id, 
                c.location_id, 
                r.name AS race_name, 
                r.picture AS race_picture,
                a.name AS attitude_name, 
                l.name AS location_name
            FROM characters c
            LEFT JOIN races r ON c.race_id = r.race_id
            LEFT JOIN attitudes a ON c.attitude_id = a.attitude_id
            LEFT JOIN locations l ON c.location_id = l.location_id
            WHERE c.user_id = ?"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $characters = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        include 'views/character/profile.php';
    }


}
?>
