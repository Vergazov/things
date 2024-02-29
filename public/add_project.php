<?php
session_start();

require_once '../functions/db.php';
require_once '../functions/template.php';
require_once '../functions/validators.php';
require_once '../db/db.php';

$titleName = 'Дела в порядке';
$currentUserName = '';
$currentUserId = '';
$currentUserEmail = '';

if (!empty($_SESSION['user']['id'])) {
    $currentUserId = $_SESSION['user']['id'];
    $currentUser = getUserDataById($con, $currentUserId);
    $currentUserEmail = $currentUser['email'];
    $currentUserName = $currentUser['name'];
}

$currentUserProjects = '';
$currentUserTasks = '';
$errors = [];

$currentUserProjects = getCurrentUserData($con, $currentUserId, getQueryCurrentUserProjects());
$currentUserTasks = getCurrentUserData($con, $currentUserId, getQueryCurrentUserTasks());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $required = ['project_name'];
    $exists = ['project_name'];

    $newProject = filter_input_array(INPUT_POST, ['project_name' => FILTER_DEFAULT]);

    foreach ($newProject as $key => $value) {
        if (in_array($key, $required, true) && !validateFilled($key)) {
            $errors[$key] = "Заполните поле $key";
        }

        if (in_array($key, $exists, true) && empty($errors[$key]) && !isProjExistsByName($con, $key,$currentUserId)) {
            $errors[$key] = "Такой проект уже существует";
        }
    }

    $errors = array_filter($errors);

    if (empty($errors)) {
        $stmt = db_get_prepare_stmt($con, getQueryAddProject(), [$newProject['project_name'], $currentUserId]);
        $res = mysqli_stmt_execute($stmt);
        if ($res) {
            header("Location:" . getAbsolutePath('index.php'));
        }
    }
}

if (empty($_SESSION['user']['id'])) {
    $content = include_template('guest.php');

    $layOut = include_template('layout.php', [
        'content' => $content,
        'titleName' => $titleName,
    ]);
} else {

    $content = include_template('add_project.php', [
        'currentUserProjects' => $currentUserProjects,
        'tasksForCount' => $currentUserTasks,
        'currentUserTasks' => $currentUserTasks,
        'errors' => $errors,
    ]);
    $layOut = include_template('layout.php', [
        'titleName' => $titleName,
        'user' => $currentUserName,
        'content' => $content,
    ]);
}
print($layOut);
