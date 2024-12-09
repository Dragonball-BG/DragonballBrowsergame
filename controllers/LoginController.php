<?php

require_once 'models/UserModel.php';

class LoginController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    /**
     * Show the login form.
     */
    public function showLoginForm() {
        if (Session::has('user_id')) {
            $this->redirect('index.php?route=character_picker');
        }
        include 'views/login/login.php';
    }

    /**
     * Handle the login logic.
     */
    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate inputs
        if (empty($username) || empty($password)) {
            $this->redirect('index.php?route=login&error=missing_fields');
        }

        // Authenticate user
        if (!$this->userModel->authenticate($username, $password)) {
            $userDetails = $this->userModel->getUserByUsername($username);

            if ($userDetails && $userDetails['is_locked']) {
                $this->redirect('index.php?route=login&error=account_locked');
            } else {
                $this->redirect('index.php?route=login&error=invalid_credentials');
            }
        }

        // Set session data for the authenticated user
        $userDetails = $this->userModel->getDetails();
        Session::set('user_id', $userDetails['id']);
        Session::set('username', $userDetails['username']);

        $route = Session::has('selected_character_id') ? 'dashboard' : 'character_picker';
        $this->redirect("index.php?route=$route");
    }

    /**
     * Log out the user and destroy the session.
     */
    public function logout() {
        Session::destroy();
        $this->redirect('index.php?route=login');
    }

    /**
     * Redirect to a specified route.
     *
     * @param string $route URL to redirect to.
     */
    private function redirect($route) {
        header("Location: $route");
        exit;
    }
}
