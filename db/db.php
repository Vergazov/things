<?php
require_once '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('c:\xampp\htdocs\things');
$dotenv->load();

try {
    $con = mysqli_connect($_ENV['HOSTNAME'], $_ENV['USERNAME'], $_ENV['PASSWORD'], $_ENV['DATABASE']);
    mysqli_set_charset($con, 'utf8');
} catch (Exception $e) {
    echo 'Ошибка при подключении к БД:' . $e->getMessage();
}


