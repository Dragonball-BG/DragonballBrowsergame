<?php
require_once 'helpers/Database.php';

class User {
    private $db;
    private $userDetails;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Authenticate a user with username and password.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function authenticate($username, $password) {
        // Fetch user by username
        $stmt = $this->db->prepare("SELECT * FROM users WHERE login_name = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false; // User not found
        }

        $user = $result->fetch_assoc();

        // Check if the user is locked
        if ($user['is_locked']) {
            return false; // User is locked and cannot log in
        }

        // Verify the provided password against the stored hashed password
        if (password_verify($password, $user['password'])) {
            // Store authenticated user details
            $this->userDetails = [
                'id' => $user['user_id'],
                'username' => $user['login_name'],
                'email' => $user['email']
            ];
            return true;
        }

        return false; // Password is incorrect
    }

    /**
     * Get the authenticated user's details.
     *
     * @return array|null
     */
    public function getDetails() {
        return $this->userDetails;
    }

    /**
     * Fetch a user by their ID.
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserById($userId) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    /**
     * Fetch a user by their ID.
     *
     * @param int $username
     * @return array|null
     */
    public function getUserByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE login_name = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if a user is found
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc(); // Fetch the user data as an associative array
            $stmt->close();
            return $user;
        } else {
            $stmt->close();
            return null; // No user found
        }
    }
}
?>
