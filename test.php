<?php
require_once "UserCRUD.php";
$dsn = "mysql:host=localhost;dbname=test_db;charset=utf8";
$user = "root";
$password = "adminadminadmin";
$userCRUD = new UserCRUD($dsn, $user, $password);
//$userCRUD->addUser("Egor","qwerty@mail.com",[1]);
print_r($userCRUD->getUsers());