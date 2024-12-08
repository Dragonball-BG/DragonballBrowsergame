<?php
require_once 'helpers/Database.php';

class Character {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all characters for a specific user.
     *
     * @param int $userId
     * @return array
     */
    public function getCharactersByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM charakter WHERE chr_fk_user = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get the count of characters for a specific user.
     *
     * @param int $userId
     * @return int
     */
    public function getCharacterCountByUserId($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS count FROM charakter WHERE chr_fk_user = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int)$row['count'];
    }

    /**
     * Create a new character for a user.
     *
     * @param int $userId
     * @param string $characterName
     * @return bool
     */
    public function createCharacter($userId, $characterName) {
        $stmt = $this->db->prepare("
            INSERT INTO charakter (chr_name, chr_level, chr_exp, chr_lp, chr_maxlp, chr_kp, chr_maxkp, chr_fk_user)
            VALUES (?, 1, 0, 400, 400, 400, 400, ?)
        ");
        $stmt->bind_param("si", $characterName, $userId);
        return $stmt->execute();
    }
}
?>
