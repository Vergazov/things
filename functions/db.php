<?php

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param mysqli $link Ресурс соединения
 * @param string $sql SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt(mysqli $link, string $sql, array $data = []): mysqli_stmt
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
            } else {
                if (is_string($value)) {
                    $type = 's';
                } else {
                    if (is_float($value)) {
                        $type = 'd';
                    }
                }
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
 * Выполняет подготовленное выражение.
 * @param mysqli $con Ресурс соединения
 * @param array|string $data Данные для вставки в запрос
 * @param string $sql SQL запрос
 * @return array|string
 */
function getCurrentUserData(mysqli $con, array|string $data, string $sql): array|string
{
    if (is_array($data)) {
        $stmt = db_get_prepare_stmt($con, $sql, $data);
    } else {
        $stmt = db_get_prepare_stmt($con, $sql, [$data]);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    return mysqli_error($con);
}

/**
 * @param array $config : Переменная, содержащая конфигурацию приложения
 * @return bool|string|mysqli|null
 * Если соединение успешно возвращает ресурс соединения с БД, иначе - сообщение об ошибке.
 */
function dbConnection(array $config): bool|string|mysqli|null
{
    try {
        $con = mysqli_connect(
            $config['db']['host'],
            $config['db']['user'],
            $config['db']['password'],
            $config['db']['database']
        );

        mysqli_set_charset($con, 'utf8');
        return $con;
    } catch (Exception $e) {
        return 'Ошибка при подключении к БД:' . $e->getMessage();
    }
}


/**
 * @return string Запрос для получения списка проектов у текущего пользователя
 */
function getQueryCurrentUserProjects(): string
{
    return 'SELECT projects.id, projects.name from things_are_fine.projects '
        . 'JOIN things_are_fine.users '
        . 'ON projects.user_id = users.id '
        . 'WHERE users.id = ?';
}

/**
 * @return string Запрос для получения списка задач у текущего пользователя
 */
function getQueryCurrentUserTasks(): string
{
    return 'SELECT tasks.id, tasks.name, tasks.completion_date,tasks.file, projects.name project, tasks.status FROM things_are_fine.tasks '
        . 'JOIN things_are_fine.users '
        . 'ON  tasks.user_id = users.id '
        . 'JOIN things_are_fine.projects '
        . 'ON tasks.project_id = projects.id '
        . 'WHERE users.id = ? '
        . 'ORDER BY tasks.creation_date DESC';
}

/**
 * @return string Запрос для получения списка задач, отфильтрованных по проекту
 */
function getQueryFilteredByProjTasks(): string
{
    return 'SELECT tasks.id, tasks.name, tasks.completion_date, projects.name project, tasks.status FROM things_are_fine.tasks '
        . 'JOIN things_are_fine.projects '
        . 'ON tasks.project_id = projects.id '
        . 'WHERE project_id = ? '
        . 'ORDER BY tasks.creation_date DESC';
}

/**
 * @return string Запрос для добавления новой задачи
 */
function getQueryAddTask(): string
{
    return 'INSERT INTO things_are_fine.tasks(creation_date,name,file,completion_date,user_id,project_id) '
        . 'VALUES (NOW(),?,?,?,?,?)';
}

/**
 * @return string Запрос для добавления нового проекта
 */
function getQueryAddProject(): string
{
    return 'INSERT INTO things_are_fine.projects(name,user_id) '
        . 'VALUES (?,?)';
}

/**
 * @return string Запрос для добавления нового пользователя
 */
function getQueryAddUser(): string
{
    return 'INSERT INTO things_are_fine.users(reg_date, email, name, password) '
        . 'VALUES (NOW(),?,?,?)';
}

/**
 * @return string Запрос для проверки существования проекта по его id
 */
function getQueryIsProjExists(): string
{
    return 'SELECT projects.name FROM things_are_fine.projects WHERE id = ? AND user_id = ?';
}

/**
 * @return string Запрос для проверки существования проекта по его id
 */
function getQueryIsProjExistsByName(): string
{
    return 'SELECT projects.name FROM things_are_fine.projects WHERE name = ? AND user_id = ?';
}

/**
 * @return string Запрос для проверки существования почты в базе
 */
function getQueryIsEmailExists(): string
{
    return 'SELECT users.email FROM things_are_fine.users WHERE email = ?';
}

/**
 * @return string Запрос всей информации о пользователе из базы по email
 */
function getQueryUserByEmail(): string
{
    return 'SELECT * FROM things_are_fine.users WHERE email = ?';
}

/**
 * @return string Запрос всей информации о пользователе из базы по id
 */
function getQueryUserById(): string
{
    return 'SELECT * FROM things_are_fine.users WHERE id = ?';
}

/**
 * @return string Возвращает запрос для FULLTEXT поиска по задачам у текущего пользователя
 */
function getQueryFtSearchCurrentUserTasks(): string
{
    return 'SELECT * FROM things_are_fine.tasks WHERE MATCH (name) AGAINST (?) AND user_id = ?';
}

/**
 * @return string Запрос на смену статуса задачи
 */
function getQueryInvertTaskStatus(): string
{
    return 'UPDATE things_are_fine.tasks SET status = ? WHERE tasks.id = ?';
}

/**
 * @return string Возвращает запрос для поиска задачи по id у текущего пользователя
 */
function getQuerySearchTaskById(): string
{
    return 'SELECT * FROM things_are_fine.tasks WHERE id = ? AND user_id = ?';
}

/**
 * @return string Возвращает запрос для поиска задач всех задач у текущего пользователя отфильтрованный по проектам
 */
function getQuerySearchAllTasksByProject(): string
{
    return 'SELECT * FROM things_are_fine.tasks WHERE user_id = ? AND project_id = ?';
}

/**
 * @return string Возвращает запрос для поиска задач на сегодня у текущего пользователя
 */
function getQuerySearchTodayTasks(): string
{
    return 'SELECT * FROM things_are_fine.tasks WHERE completion_date = CURDATE() AND user_id = ?';
}

/**
 * @return string Возвращает запрос для поиска задач на сегодня у текущего пользователя отфильтрованный по проектам
 */
function getQuerySearchTodayTasksByProject(): string
{
    return 'SELECT * FROM things_are_fine.tasks WHERE completion_date = CURDATE() AND user_id = ? AND project_id = ?';
}

/**
 * @return string Возвращает запрос для поиска задач на завтра у текущего пользователя
 */
function getQuerySearchTomorrowTasks(): string
{
    return 'SELECT * FROM things_are_fine.tasks WHERE completion_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND user_id = ?';
}

/**
 * @return string Возвращает запрос для поиска задач на завтра у текущего пользователя отфильтрованный по проектам
 */
function getQuerySearchTomorrowTasksByProject(): string
{
    return 'SELECT * FROM things_are_fine.tasks WHERE completion_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND user_id = ? AND project_id = ?';
}

/**
 * @return string Возвращает запрос для поиска просроченных задач у текущего пользователя
 */
function getQuerySearchOverdueTasks(): string
{
    return 'SELECT * FROM things_are_fine.tasks WHERE completion_date < CURDATE() AND user_id = ?';
}

/**
 * @return string Возвращает запрос для поиска просроченных задач у текущего пользователя отфильтрованный по проектам
 */
function getQuerySearchOverdueTasksByProject(): string
{
    return 'SELECT * FROM things_are_fine.tasks WHERE completion_date < CURDATE() AND user_id = ? AND project_id = ?';
}

/** Возвращает всю информацию об одном пользователе по Email
 * @param mysqli $con Ресурс подключения
 * @param string $email Email по которому ищем пользователя
 * @return array
 */
function getUserDataByEmail(mysqli $con, string $email): array
{
    $userData = getCurrentUserData($con, $email, getQueryUserByEmail());
    return $userData[0];
}

/** Возвращает всю информацию об одном пользователе по Id
 * @param mysqli $con Ресурс подключения
 * @param string $id id по которому ищем пользователя
 * @return array
 */
function getUserDataById(mysqli $con, string $id): array
{
    $userData = getCurrentUserData($con, $id, getQueryUserById());
    return $userData[0];

}

/**
 * Запрос на список невыполненных задач на сегодня у всех пользователей
 * @return string
 */
function getQueryGetNotReadyTasks(): string
{
    return 'SELECT * FROM things_are_fine.tasks WHERE status = ? AND completion_date = CURDATE()';
}

/**
 * Запрос на получение имени и почты конкретного пользователя по id
 * @return string
 */
function getQueryGetEmailsForUsers(): string
{
    return 'SELECT name ,email FROM things_are_fine.users WHERE id = ?';
}
