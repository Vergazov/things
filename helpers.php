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
 * Выполняет подготовленное выражение. Будет работать если в подготовленном выражении есть только 1 плейсхолдер.
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
 * Отладочный вывод. Принимает один параметр который будет выведен на экран
 * @param $data . Параметр который будет выведен на экран
 */
function dd($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
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
 * @return string . Запрос для получения списка задач, отфильтрованных по проекту
 */
function getQueryFilteredByProjTasks(): string
{
    return 'SELECT tasks.name, tasks.completion_date, projects.name project, tasks.status FROM things_are_fine.tasks '
        . 'JOIN things_are_fine.projects '
        . 'ON tasks.project_id = projects.id '
        . 'WHERE project_id = ?';
}

/**
 * @return string . Запрос для проверки существования проекта по его id
 */
function getQueryIsProjExist(): string
{
    return 'SELECT projects.name FROM things_are_fine.projects WHERE id = ?';
}

/**
 * Проверка поля с датой на соответствие формату ГГГ-ММ-ДД
 * @param $name . Название ключа в $_POST массиве, по которому будем искать дату
 * @param string $format . Формат даты, с которым будем сверяться
 * @return bool|string|null
 * Возвращает null если формат даты верный, либо если дата не была указана при создании задачи
 * Если формат неверный, возвращает сообщение об ошибке
 */
function validateDateFormat($name, $format = 'Y-m-d'): bool|string|null
{
    $date = $_POST[$name];

    if ($date === '') {
        return null;
    }

    $rightFormatDate = DateTime::createFromFormat($format, $date);
    if ($rightFormatDate === false) {
        return 'Введенная дата не соответствует формату ГГГГ-ММ-ДД';
    }

    return null;
}

/**
 * Проверка даты на соответствие условия. Дата должна быть >= текущей
 * @param $name . Название ключа в $_POST массиве, по которому будем искать дату
 * @param string $format . Формат даты с которым будем работать
 * @return string|null Если дата не соответствует условию, выводит ошибку.
 */
function validateDateRange($name, $format = 'Y-m-d'): null|string
{
    $date = $_POST[$name];

    if ($date === '') {
        return null;
    }

    $curDate = date($format);
    if (strtotime($date) < strtotime($curDate)) {
        return 'Дата выполнения должна быть больше или равна текущей';
    }

    return null;
}

/**
 * Проверка поля на заполненность
 * @param $name . Название ключа в $_POST массиве, по которому будем искать проверяемое поле
 * @return string|null Если проверяемое в $_POST массиве поле, пустое, то возвращает текст ошибки. Иначе, возвращает null
 * При помощи ltrim() удаляем все пробелы вначале и в конце строки
 */
function validateFilled($name): string|null
{
    if (empty(trim($_POST[$name]))) {
        return "Это поле должно быть заполнено";
    }
    return null;
}

function isProjExist($con,$name): string|null
{
    $projectId = $_POST[$name];
    $project = getCurrentUserData($con,$projectId,getQueryIsProjExist());
    if(empty($project)){
        return 'Такого проекта не существует';
    }
    return null;
}


