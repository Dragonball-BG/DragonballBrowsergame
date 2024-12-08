<?php

require_once 'models/User.php';
require_once 'helpers/Session.php';

class LoginController
{
    private $user;

    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Session::has('user_id')) {
            header("Location: index.php?route=character_picker");
        }
        include 'views/login/login.php';
    }

    /**
     * Handle the login logic.
     */
    public function login()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate inputs
        if (empty($username) || empty($password)) {
            header("Location: index.php?route=login&error=missing_fields");
            exit;
        }

        // Authenticate user
        if (!$this->user->authenticate($username, $password)) {
            // Check if the user is locked
            $userDetails = $this->user->getUserByUsername($username);

            if ($userDetails && $userDetails['is_locked']) {
                header("Location: index.php?route=login&error=account_locked");
            } else {
                header("Location: index.php?route=login&error=invalid_credentials");
            }
            exit;
        }

        // Set user session data
        $userDetails = $this->user->getDetails();
        Session::set('user_id', $userDetails['id']);
        Session::set('username', $userDetails['username']);

        
        if (Session::has('selected_character_id')) {
            header("Location: index.php?route=dashboard");
        } else {
            header("Location: index.php?route=character_picker");
        }
        exit;
    }

    /**
     * Log out the user and destroy the session.
     */
    public function logout()
    {
        Session::destroy();
        header("Location: index.php?route=login");
        exit;
    }
}
