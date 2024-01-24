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
 * Запрос для вставки информации в БД через подготовленное выражение
 */
function insertData()
{

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
 * Функция для сохранения данных формы, в случае неудачной отправки запроса
 * @param $name . Ключ по которому в $_POST массиве будем искать данные из формы
 * @return mixed Данные из $_POST массива по переданному ключу
 */

function getPostVal($name)
{
    return $_POST[$name] ?? "";
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
    return 'SELECT tasks.id, tasks.name, tasks.completion_date, projects.name project, tasks.status FROM things_are_fine.tasks '
        . 'JOIN things_are_fine.users '
        . 'ON  tasks.user_id = users.id '
        . 'JOIN things_are_fine.projects '
        . 'ON tasks.project_id = projects.id '
        . 'WHERE users.name = ? '
        . 'ORDER BY tasks.creation_date DESC';
}

/**
 * @return string . Запрос для получения списка задач, отфильтрованных по проекту
 */
function getQueryFilteredByProjTasks(): string
{
    return 'SELECT tasks.name, tasks.completion_date, projects.name project, tasks.status FROM things_are_fine.tasks '
        . 'JOIN things_are_fine.projects '
        . 'ON tasks.project_id = projects.id '
        . 'WHERE project_id = ? '
        . 'ORDER BY tasks.creation_date DESC';
}

/**
 * @return string . Запрос для добавления новой задачи
 */
function getQueryAddTask(): string
{
    return 'INSERT INTO things_are_fine.tasks(creation_date,name,file,completion_date,user_id,project_id) '
        . 'VALUES (NOW(),?,?,?,1,?)';
}

/**
 * @return string . Запрос для добавления нового пользователя
 */
function getQueryAddUser(): string
{
    return 'INSERT INTO things_are_fine.users(reg_date, email, name, password) '
        . 'VALUES (NOW(),?,?,?)';
}

/**
 * @return string . Запрос для проверки существования проекта по его id
 */
function getQueryIsProjExists(): string
{
    return 'SELECT projects.name FROM things_are_fine.projects WHERE id = ?';
}

/**
 * @return string . Запрос для проверки существования почты в базе
 */
function getQueryIsEmailExists(): string
{
    return 'SELECT users.email FROM things_are_fine.users WHERE email = ?';
}

/**
 * @return string . Запрос всей информации о пользователе иза базы
 */
function getQueryUser(): string
{
    return 'SELECT * FROM things_are_fine.users WHERE email = ?';
}

/**
 * Проверка поля с датой на соответствие формату ГГГГ-ММ-ДД
 * @param $name . Название ключа в $_POST массиве, по которому будем искать дату
 * @return bool|string|null
 * Возвращает null если формат даты верный, либо если дата не была указана при создании задачи
 * Если формат неверный, возвращает сообщение об ошибке
 */
function validateDateFormat($name): bool|string|null
{
    $format = 'Y-m-d';
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
 * @return string|null Если дата не соответствует условию, выводит ошибку.
 */
function validateDateRange($name): null|string
{
    $format = 'Y-m-d';
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
 * Проверяет чтобы поля были заполнены, так же удаляет лишние пробелы из начала и конца строки
 * @param $name . Название ключа в $_POST массиве, по которому будем искать проверяемое поле
 * @return string|null Если проверяемое в $_POST массиве поле, пустое, то возвращает текст ошибки. Иначе, возвращает null
 */
function validateFilled($name): string|null
{
    if (empty(trim($_POST[$name]))) {
        return "Это поле должно быть заполнено";
    }
    return null;
}

/**
 * Проверка существует ли проект. Осуществляется по id проекта в базе
 * @param $con . Ресурс соединения с БД
 * @param $name . Название ключа в $_POST массиве, по которому будем искать id проекта
 * @return string|null Возвращает либо ошибку о том что проекта нету либо null если проект существует
 */
function isProjExists($con, $name): string|null
{
    $projectId = $_POST[$name];
    $project = getCurrentUserData($con, $projectId, getQueryIsProjExists());
    if (empty($project)) {
        return 'Такого проекта не существует';
    }
    return null;
}

/**
 * Проверяет почту в базе на дубликат
 * @param $con
 * @param $name
 * @return string|null
 * Если находит в базе почту идентичную той что ввел пользователь, то возвращает текст ошибки.
 * В противном случае возвращает null
 */
function isEmailExists($con, $name): string|null
{
    $emailFromForm = $_POST[$name];
    $emailFromBase = getCurrentUserData($con, $emailFromForm, getQueryIsEmailExists());
    if (!empty($emailFromBase)) {
        return 'Указанный почтовый ящик уже занят';
    }
    return null;
}

/**
 * Проверяет при аутентификации, существует ли такая почта. Если да, то все ок, если нет, значит такого пользователя нет
 * @param $con
 * @param $name
 * @return string|null Если находит почту, возвращает null, если не находит, то сообщение об ошибке
 */
function isEmailExistsForAuth($con, $name): string|null
{
    $emailFromForm = $_POST[$name];
    $emailFromBase = getCurrentUserData($con, $emailFromForm, getQueryIsEmailExists());
    if (empty($emailFromBase)) {
        return 'Пользователя таким почтовым ящиком не существует';
    }
    return null;
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
 * Проверяет email на соответствие формату
 * @param $name
 * @return string|null
 * Возвращает сообщение об ошибке если валидация не пройдена, если пройдена возвращает null
 */
function validateEmailFormat($name): null|string
{
    $email = $_POST[$name];
    $validate = filter_var($email, FILTER_VALIDATE_EMAIL);
    if ($validate === false) {
        return 'Поле Email не соответствует формату';
    }
    return null;
}

function getUserData($con, $email)
{
    return getCurrentUserData($con, $email, getQueryUser());

}


