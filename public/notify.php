<?php

require_once '../bootstrap.php';

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

$mailFrom = $config['mailer']['mail_from'];
$mailFromPass = $config['mailer']['mail_from_password'];
$dsn = "smtp://$mailFrom:$mailFromPass@smtp.yandex.ru:465?encryption=SSL";

$notReadyTasks = getCurrentUserData($con, 0, getQueryGetNotReadyTasks());

$tasksForUsers = [];
foreach ($notReadyTasks as $task) {
    $tasksForUsers[$task['user_id']][] = $task['name'];
}

$emails = [];
foreach ($tasksForUsers as $key => $Value) {
    $emails[$key] = getCurrentUserData($con, $key, getQueryGetEmailsForUsers());
}

$tasksForEmails = [];
foreach ($emails as $key => $value) {
    $tasksForEmails[$value[0]['email']]['countTasks'] = count($tasksForUsers[$key]);
    $tasksForEmails[$value[0]['email']]['tasks'] = implode(', ', $tasksForUsers[$key]);
    $tasksForEmails[$value[0]['email']]['userName'] = $value[0]['name'];
}

foreach ($tasksForEmails as $key => $value) {
    $message = "Уважаемый, " . $value['userName'] . " На сегодня у вас " .
        get_noun_plural_form($value['countTasks'], 'запланирована', 'запланировано', 'запланировано') . ' ' .
        $value['countTasks'] . ' ' . get_noun_plural_form($value['countTasks'], 'задача', 'задачи', 'задач') . ': ' .
        $value['tasks'];
    $transport = Transport::fromDsn($dsn);
    $mailer = new Mailer($transport);
    $email = (new Email())
        ->to("$key")
        ->from("$mailFrom")
        ->subject("Уведомление от сервиса «Дела в порядке»")
        ->text($message);
    $res = $mailer->send($email);

    if ($res === null) {
        header("Location:" . getAbsolutePath('index.php'));
    }
}


