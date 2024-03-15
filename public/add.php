<?php
require_once '../bootstrap.php';

if (!empty($_SESSION['user']['id'])) {
    $currentUserId = $_SESSION['user']['id'];
    $currentUser = getUserDataById($con, $currentUserId);
    $currentUserName = $currentUser['name'];
}

$currentUserProjects = getCurrentUserData($con, $currentUserId, getQueryCurrentUserProjects());
$currentUserTasks = getCurrentUserData($con, $currentUserId, getQueryCurrentUserTasks());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $required = ['name', 'project'];
    $exists = ['project'];
    $format = ['date'];
    $range = ['date'];

    $newTask = filter_input_array(INPUT_POST,[
        'name' => FILTER_DEFAULT,
        'project' => FILTER_DEFAULT,
        'date' => FILTER_DEFAULT
    ]);

    foreach ($newTask as $key => $value) {
        if (in_array($key, $required, true) && isFilled($key) === false) {
            $errors[$key] = "Заполните поле $key";
        }
        if (in_array($key, $exists, true) && empty($errors[$key]) && isProjExistsById($con, $key, $currentUserId) === false) {
            $errors[$key] = "Такого проекта не существует";
        }
        if (in_array($key, $format, true) && empty($errors[$key]) && isDateFormatValid($key) === false) {
            $errors[$key] = "Введенная дата не соответствует формату ГГГГ-ММ-ДД";
        }
        if (in_array($key, $range, true) && empty($errors[$key]) && isDateRangeValid($key) === false) {
            $errors[$key] = "Дата выполнения должна быть больше или равна текущей";
        }
    }

    $errors = array_filter($errors);
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

        if(empty($newTask['date'])){
            $newTask['date'] = null;
        }

        $stmt = db_get_prepare_stmt(
            $con,
            getQueryAddTask(),[
                $newTask['name'],
                $fileUrl,
                $newTask['date'],
                $currentUserId,
                $newTask['project']
            ]);

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
