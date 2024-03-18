<?php

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function include_template(string $name, array $data = []): string
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

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function get_noun_plural_form (int $number, string $one, string $two, string $many): string
{
    $number = (int) $number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}

/** Считает сколько задач подходит под категорию проекта
 * @param array $tasks Список задач
 * @param string $project . Проект
 * @return int Количество задач подходящих под переданный проект
 */
function countTasksForProject(array $tasks, string $project): int
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
 * @param string|null $date Дата выполнения задачи
 * @return bool Если задача срочная, возвращает true,
 * Если не срочная то возвращает false,
 * Если дата выполнения не была передана, то возвращает false
 */
function isTaskImportant(string|null $date): bool
{
    if($date === null){
        return false;
    }

    $dateDiff = (strtotime($date) - time()) / 3600;
    return $dateDiff <= 24;
}

/**
 * Функция для сохранения данных формы, в случае неудачной отправки запроса
 * @param string $name Ключ по которому в $_POST массиве будем искать данные из формы
 * @return mixed Данные из $_POST массива по переданному ключу
 */

function getPostVal(string $name): mixed
{
    return $_POST[$name] ?? "";
}

/**
 * Формирует абсолютный путь к файлу переданному функции
 * @param string $file Название запрашиваемой страницы
 * @return string
 */
function getAbsolutePath(string $file): string
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

/**
 * Формирует название прикрепленного файла.
 * @param string $fileUrl
 * @return string Возвращает название файла
 */
function getFileName(string $fileUrl): string
{
    $fileName = explode('/',$fileUrl);
    return $fileName[1];
}
