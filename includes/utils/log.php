<?php
function kame_erp_log_error($message) {
    $log_file = plugin_dir_path(__FILE__) . 'sync_errors.log';
    error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, $log_file);
}
