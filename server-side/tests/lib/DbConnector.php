<?php

class DbConnector extends \PDO
{
    public $login_name;
    
    public function __construct($dsn, $user, $password)
    {
        $this->login_name = $user;
        parent::__construct($dsn, $user, $password);
    }
}
