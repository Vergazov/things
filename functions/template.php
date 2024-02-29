<?php

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function include_template(string $name, array $data = [])
{
    $name = '../templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/** Считает сколько задач подходит под категорию проекта
 * @param $tasks . Список задач
 * @param $project . Проект
 * @return int Количество задач подходящих под переданный проект
 */
function countTasksForProject($tasks, $project): int
{
    $tasksNumber = 0;
    foreach ($tasks as $task) {
        if ($project === $task['project']) {
            $tasksNumber++;
        }
    }
    return $tasksNumber;
}

/**
 * Определяет является ли задача срочной.
 * Задача считается срочной если до даты выполнения остается 24 часа или меньше.
 * @param $date
 * @return bool Если срочная то true, если не срочная, то false
 */
function isTaskImportant($date): bool
{
    $dateDiff = (strtotime($date) - time()) / 3600;
    return $dateDiff <= 24;
}

/**
 * Функция для сохранения данных формы, в случае неудачной отправки запроса
 * @param $name . Ключ по которому в $_POST массиве будем искать данные из формы
 * @return mixed Данные из $_POST массива по переданному ключу
 */

function getPostVal($name)
{
    return $_POST[$name] ?? "";
}

/**
 * Формирует абсолютный путь к файлу переданному функции
 * @param $file
 * @return string
 */
function getAbsolutePath($file): string
{
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

    return "http://$host$uri/$file";
}

/**
 * Отладочный вывод. Принимает один параметр который будет выведен на экран
 * @param $data . Параметр который будет выведен на экран
 */
function dd($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

function getFileName($fileUrl): string
{
    $fileName = explode('/',$fileUrl);
    return $fileName[1];
}
