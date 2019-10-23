<?php
try {
    set_error_handler(
        function ($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        }
    );
    session_start();
    session_destroy();
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
