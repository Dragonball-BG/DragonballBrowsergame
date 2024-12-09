<?php

require_once 'models/UserModel.php';
require_once 'models/BetaKeyModel.php';
require_once 'helpers/Session.php';

class RegisterController extends BaseController {
    private $requireBetakey = true;
    private $userModel;
    private $betakeyModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->betakeyModel = new BetaKeyModel();
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm() {
        if (Session::has('selected_character_id')) {
            $this->redirect('index.php?route=news');
        }

        if (Session::has('user_id')) {
            $this->redirect('index.php?route=character_picker');
        }

        include 'views/register/register.php';
    }

    /**
     * Store a new user in the database.
     */
    public function storeUser() {
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
            $this->redirect('index.php?route=register&error=missing_fields');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('index.php?route=register&error=invalid_email');
        }

        if ($password !== $passwordConfirm) {
            $this->redirect('index.php?route=register&error=password_mismatch');
        }

        // Check betakey if required
        if ($this->requireBetakey) {
            $keyData = $this->betakeyModel->isValidBetakey($betakey);
            if (!$keyData) {
                $this->redirect('index.php?route=register&error=invalid_betakey');
            }
        }

        // Check if the username or email already exists
        if ($this->userModel->userExists($loginName, $email)) {
            $this->redirect('index.php?route=register&error=user_exists');
        }

        // Hash the password and create the user
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $userId = $this->userModel->createUser($loginName, $email, $hashedPassword);

        if (!$userId) {
            $this->redirect('index.php?route=register&error=server_error');
        }

        // Mark the betakey as used if applicable
        if ($this->requireBetakey) {
            $this->betakeyModel->markBetakeyAsUsed($keyData['key_id'], $userId);
        }

        // Log the user in and redirect to character creation
        Session::set('user_id', $userId);
        $this->redirect('index.php?route=create_character');
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
