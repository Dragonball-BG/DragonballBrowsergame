<?php
require_once 'helpers/Database.php';
require_once 'helpers/Session.php';

class NewsController
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
    public function showNews()
    {
        include 'views/news/news.php';
    }
}