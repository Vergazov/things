<?php
session_start();

require_once '../functions/db.php';
require_once '../functions/template.php';
require_once '../functions/validators.php';
require_once '../db/db.php';

$titleName = 'Дела в порядке';
$currentUserId = '';
$currentUserName = '';
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

    $required = ['name', 'project'];

    $rules = [
        'name' => function ($value) {
            return validateFilled($value);
        },
        // TODO: Сделать читабельнее
        'project' => function ($value) use ($con, $currentUserId) {
            if (validateFilled($value)) {
                return validateFilled($value);
            }
            if (isProjExists($con, $value, $currentUserId)) {
                return isProjExists($con, $value, $currentUserId);
            }
        },
        // TODO: Сделать читабельнее
        'date' => function ($value) {
            if (validateDateFormat($value)) {
                return validateDateFormat($value);
            }
            if (validateDateRange($value)) {
                return validateDateRange($value);
            }
        },
    ];

    $task = filter_input_array(INPUT_POST,
        ['name' => FILTER_DEFAULT, 'project' => FILTER_DEFAULT, 'date' => FILTER_DEFAULT]);

    foreach ($task as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($key);
        }
        // TODO Думаю можно сделать без этого куска кода. Подумать можно ли его запихнуть в функцию валидации validateFilled()
        if (in_array($key, $required, true) && empty(trim($value))) {
            $errors[$key] = "Поле $key должно быть заполнено";
        }
    }

    $errors = array_filter($errors);

    $taskId = '';
    $file_path = '';
    $file_name = '';
    $fileUrl = '';

    if (!empty($_FILES['file']['name'])) {
        $file_name = $_FILES['file']['name'];
        $file_path = __DIR__ . '/uploads/';
        $fileUrl = 'uploads/' . $file_name;
    }
    move_uploaded_file($_FILES['file']['tmp_name'], $file_path . $file_name);

    if (empty($errors)) {
        $stmt = db_get_prepare_stmt($con, getQueryAddTask(),
            [$task['name'], $fileUrl, $task['date'], $currentUserId, $task['project']]);
        $res = mysqli_stmt_execute($stmt);
        if ($res) {
            $taskId = mysqli_insert_id($con);
            if ($fileUrl === '') {
                header("Location:" . getAbsolutePath('index.php'));
            } else {
                header("Location:" . getAbsolutePath('index.php') . "?fileUrl=" . $fileUrl . "&taskId=" . $taskId);
            }
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

    $content = include_template('add.php', [
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
