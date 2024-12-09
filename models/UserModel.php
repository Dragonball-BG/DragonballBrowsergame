<?php
require_once 'models/BaseModel.php';

class UserModel extends BaseModel {
    private $userDetails;

    /**
     * Authenticate a user with username and password.
     *
     * @param string $username
     * @param string $password
     * @return bool True if authentication is successful, false otherwise.
     */
    public function authenticate($username, $password) {
        $user = $this->getUserByUsername($username);

        if (!$user || $user['is_locked']) {
            return false; // User not found or account is locked
        }

        if (password_verify($password, $user['password'])) {
            $this->userDetails = [
                'id' => $user['user_id'],
                'username' => $user['login_name'],
                'email' => $user['email']
            ];
            return true;
        }

        return false; // Invalid password
    }

    /**
     * Get the authenticated user's details.
     *
     * @return array|null Authenticated user's details or null if not authenticated.
     */
    public function getDetails() {
        return $this->userDetails;
    }

    /**
     * Fetch a user by their ID.
     *
     * @param int $userId
     * @return array|null User data or null if not found.
     */
    public function getUserById($userId) {
        return $this->fetchOne("SELECT * FROM users WHERE user_id = ?", [$userId]);
    }

    /**
     * Fetch a user by their username.
     *
     * @param string $username
     * @return array|null User data or null if not found.
     */
    public function getUserByUsername($username) {
        return $this->fetchOne("SELECT * FROM users WHERE login_name = ?", [$username]);
    }

    /**
     * Check if a user exists by login name or email.
     *
     * @param string $loginName
     * @param string $email
     * @return bool True if a user exists, false otherwise.
     */
    public function userExists($loginName, $email) {
        $query = "SELECT * FROM users WHERE login_name = ? OR email = ?";
        $result = $this->fetchOne($query, [$loginName, $email]);
        return $result !== null;
    }

    /**
     * Create a new user.
     *
     * @param string $loginName
     * @param string $email
     * @param string $hashedPassword
     * @return int|null ID of the created user, or null on failure.
     */
    public function createUser($loginName, $email, $hashedPassword) {
        $query = "INSERT INTO users (login_name, email, password) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('sss', $loginName, $email, $hashedPassword);

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            $stmt->close();
            return $userId;
        }

        $stmt->close();
        return null;
    }
}
