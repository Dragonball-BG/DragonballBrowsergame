<?php

require_once 'helpers/Database.php';
require_once 'helpers/Session.php';

class RegisterController
{
    private $db;
    private $requireBetakey = true; // Toggle betakey requirement here

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        if (Session::has('selected_character_id')) {
            header("Location: index.php?route=news");
            exit;
        }
        if (Session::has('user_id')) {
            header("Location: index.php?route=character_picker");
            exit;
        }
        include 'views/register/register.php';
    }

    /**
     * Store a new user in the database.
     */
    public function storeUser()
    {
        $loginName = $_POST['login_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $betakey = $_POST['betakey'] ?? '';

        // Validate inputs
        if (
            empty($loginName) ||
            empty($email) ||
            empty($password) ||
            empty($passwordConfirm) ||
            ($this->requireBetakey && empty($betakey))
        ) {
            header('Location: index.php?route=register&error=missing_fields');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: index.php?route=register&error=invalid_email');
            exit;
        }

        if ($password !== $passwordConfirm) {
            header('Location: index.php?route=register&error=password_mismatch');
            exit;
        }

        // Check betakey if required
        if ($this->requireBetakey) {
            $stmt = $this->db->prepare("SELECT * FROM betakey WHERE key_value = ? AND is_used = 0");
            $stmt->bind_param('s', $betakey);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $stmt->close();
                header('Location: index.php?route=register&error=invalid_betakey');
                exit;
            }

            $keyData = $result->fetch_assoc();
            $stmt->close();
        }

        // Check if the username or email already exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE login_name = ? OR email = ?");
        $stmt->bind_param('ss', $loginName, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt->close();
            header('Location: index.php?route=register&error=user_exists');
            exit;
        }
        $stmt->close();

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert the new user into the database
        $stmt = $this->db->prepare("INSERT INTO users (login_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $loginName, $email, $hashedPassword);
        if ($stmt->execute()) {
            $userId = $stmt->insert_id;

            // Mark the betakey as used if applicable
            if ($this->requireBetakey) {
                $stmt = $this->db->prepare("UPDATE betakey SET is_used = 1, used_by = ?, used_at = NOW() WHERE key_id = ?");
                $stmt->bind_param('ii', $userId, $keyData['key_id']);
                $stmt->execute();
                $stmt->close();
            }

            // Log the user in and redirect to character creation
            Session::set('user_id', $userId);
            header('Location: index.php?route=create_character');
            exit;
        } else {
            $stmt->close();
            header('Location: index.php?route=register&error=server_error');
            exit;
        }
    }
}
