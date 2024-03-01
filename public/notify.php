<?php
session_start();
require_once '../functions/db.php';
require_once '../functions/template.php';
require_once '../db/db.php';

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

require_once '../vendor/autoload.php';

$currentUserName = '';
$currentUserId = '';

if (!empty($_SESSION['user']['id'])) {
    $currentUserId = $_SESSION['user']['id'];
    $currentUser = getUserDataById($con, $currentUserId);
    $currentUserName = $currentUser['name'];
}
$dotenv = Dotenv\Dotenv::createImmutable('c:\xampp\htdocs\things');
$dotenv->load();

$mailTo = $_ENV['MAILTO'];
$mailFrom = $_ENV['MAILFROM'];
$mailFromPass = $_ENV['MAILFROMPASSWORD'];

$dsn = "smtp://$mailFrom:$mailFromPass@smtp.yandex.ru:465?encryption=SSL";

$notReadyTasks = getCurrentUserData($con, $currentUserId, getQueryGetNotReadyTasks());
$notReadyTasksNames = [];
foreach ($notReadyTasks as $task){
    $notReadyTasksNames[] = $task['name'];
}
$notReadyTasksNamesImploded = implode(', ',$notReadyTasksNames);

$tasksAmount = count($notReadyTasks);

$message = "Уважаемый, $currentUserName. На сегодня у вас " .
    get_noun_plural_form($tasksAmount, 'запланирована', 'запланировано', 'запланировано') . ' ' .
    $tasksAmount . ' ' . get_noun_plural_form($tasksAmount, 'задача', 'задачи', 'задач') . ': ' .
    $notReadyTasksNamesImploded;

$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);
$email = (new Email())
    ->to("$mailTo")
    ->from("$mailFrom")
    ->subject("Уведомление от сервиса «Дела в порядке»")
    ->text($message);
$res = $mailer->send($email);

if($res === null){
    header("Location:" . getAbsolutePath('index.php'));
}
