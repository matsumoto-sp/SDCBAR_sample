<?php
try {
    set_error_handler(
        function ($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }
    );
    $config = json_decode(file_get_contents(__dir__ . '/../config.json'));
    $db = new \PDO(sprintf('mysql:host=%s;dbname=%s',
            $config->db->host, $config->db->db),
            $_POST['loginName'], $_POST['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    [$role, $login_name] = $db->query('SELECT my_role(), my_login_name()')->fetch(\PDO::FETCH_NUM);
    session_start();
    $_SESSION['loginName'] = $_POST['loginName'];
    $_SESSION['password'] = $_POST['password'];
    header('Content-Type: Application/json');
    echo json_encode(['role' => $role, 'loginName' => $login_name]);
} catch (\Exception $e) {
    restore_error_handler();
    header('HTTP/1.0 400');
    header('Content-Type: Application/json');
    echo json_encode(['error' => [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'message' => $e->getMessage()
    ]]);
}
