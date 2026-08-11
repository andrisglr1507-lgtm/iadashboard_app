<?php
// ajax_json_bootstrap.php - Pastikan response AJAX selalu JSON
if (defined('AJAX_JSON_BOOTSTRAP_LOADED')) {
    return;
}
define('AJAX_JSON_BOOTSTRAP_LOADED', true);

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

ob_start();

function ajax_json_response($data) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

set_exception_handler(function ($e) {
    ajax_json_response([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage()
    ]);
});

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        ajax_json_response([
            'success' => false,
            'message' => 'PHP Error: ' . $err['message'] . ' (' . basename($err['file']) . ':' . $err['line'] . ')'
        ]);
    }
});
