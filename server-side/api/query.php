<?php
try {
    set_error_handler(
        function ($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }
    );
    if (!isset($_POST['sql'])) {
        throw new Exception('SQL not specified');
    }
    session_start();
    $login_name = 'anonymous';
    $password = '';
    if (isset($_SESSION['loginName']) && isset($_SESSION['password'])) {
        $login_name = $_SESSION['loginName'];
        $password = $_SESSION['password'];
    }
    $config = json_decode(file_get_contents(__dir__ . '/../config.json'));
    $db = new \PDO(sprintf('mysql:host=%s;dbname=%s',
            $config->db->host, $config->db->db), $login_name, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $out = [];
    $stm = $db->query($_POST['sql']);
    if ($stm->columnCount()) {
        $out = $stm->fetchAll(\PDO::FETCH_ASSOC);
    }
    header('Content-Type: Application/json');
    echo json_encode($out);
} catch (\Exception $e) {
    restore_error_handler();
    header('HTTP/1.0 400');
    header('Content-Type: Application/json');
    error_log(sprintf("%s(%s): %s", $e->getFile() ,$e->getLine(), $e->getMessage()));
    echo json_encode(['error' => [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'message' => $e->getMessage()
    ]]);
}
