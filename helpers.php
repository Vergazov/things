<?php
/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * is_date_valid('2019-01-01'); // true
 * is_date_valid('2016-02-29'); // true
 * is_date_valid('2019-04-31'); // false
 * is_date_valid('10.10.2010'); // false
 * is_date_valid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function is_date_valid(string $date): bool
{
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = [])
{
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            } else if (is_string($value)) {
                $type = 's';
            } else if (is_float($value)) {
                $type = 'd';
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
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
function get_noun_plural_form(int $number, string $one, string $two, string $many): string
{
    $number = (int)$number;
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

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function include_template(string $name, array $data = [])
{
    $name = 'templates/' . $name;
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
 * Мои функции
 */


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

function isTaskImportant($date): bool
{
    if (!validateDate1($date)) {
        return false;
    }
    $dateDiff = (strtotime($date) - time()) / 3600;
    if ($dateDiff <= 24) {
        return true;
    }
    return false;
}

// TODO исправить это

function validateDate1($date, $format = 'Y-m-d'): bool
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Запрос для получения списка всех проектов
function getQueryAllProjects(): string
{
    return 'SELECT * FROM things_are_fine.projects';
}

/**
 * @return string . Запрос для получения списка проектов у текущего пользователя
 */

function getQueryCurrentUserProjects(): string
{
    return 'SELECT projects.id, projects.name from things_are_fine.projects '
        . 'JOIN things_are_fine.users '
        . 'ON projects.user_id = users.id '
        . 'WHERE users.name = ?';
}

/**
 * @return string . Запрос для получения списка задач у текущего пользователя
 */

function getQueryCurrentUserTasks(): string
{
    return 'SELECT tasks.name, tasks.completion_date, projects.name project, tasks.status FROM things_are_fine.tasks '
        . 'JOIN things_are_fine.users '
        . 'ON  tasks.user_id = users.id '
        . 'JOIN things_are_fine.projects '
        . 'ON tasks.project_id = projects.id '
        . 'WHERE users.name = ?';
}

/**
 * @return string . Запрос для получения списка задач отфильтрованных по проекту
 */

function getQueryFilteredByProjTasks(): string
{
    return 'SELECT tasks.name, tasks.completion_date, projects.name project, tasks.status FROM things_are_fine.tasks '
        . 'JOIN things_are_fine.projects '
        . 'ON tasks.project_id = projects.id '
        . 'WHERE project_id = ?';
}

/**
 * Получить список данных для текущего пользователя, через подготовленное выражение.
 * @param $con . Ресурс соединения
 * @param $data . Данные для вставки в запрос
 * @param $sql . SQL запрос
 * @return array|string
 */

function getCurrentUserData($con, $data, $sql): array|string
{
    $stmt = db_get_prepare_stmt($con, $sql, [$data]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    return mysqli_error($con);
}

function getPostVal($name)
{
    return $_POST[$name] ?? "";
}

/**
 * Функции валидации
 */

/**
 * Проверка поля с датой на соответствие формату
 */
function validateDate($name, $format = 'Y-m-d'): bool|string|null
{
    $date = $_POST[$name];
    if ($date === '') {
        return null;
    }
    $curDate = date($format);
    if (strtotime($date) >= strtotime($curDate)) {
        $rightFormatDate = date_create_from_format($format, $date);
        if ($rightFormatDate->format($format) === $date) {
            return null;
        }
        return 'Введенная дата не соответствует формату ГГГ-ММ-ДД';
    }
    return 'Дата выполнения должна быть больше или равна текущей';
}

/**
 * Проверка поля на заполненность
 */

function validateFilled($name)
{
    if (empty($_POST[$name])) {
        return "Это поле должно быть заполнено";
    }
}


