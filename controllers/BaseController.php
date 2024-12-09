<?php
require_once 'helpers/Session.php';

class BaseController {
    protected $db;
    protected $characterId;

    public function __construct() {
        if (!Session::has('selected_character_id')) {
            header("Location: index.php?route=login");
            exit;
        }

        $this->characterId = Session::get('selected_character_id');
        $this->db = Database::getInstance()->getConnection();
    }
}

?>