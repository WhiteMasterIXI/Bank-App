<?php 
require_once __DIR__ . '/constants.php';


// PostgreSQL bağlantısı
$connection = pg_connect(
    "host=" . DB_HOST . 
    " port=" . DB_PORT .
    " dbname=" . DB_NAME . 
    " user=" . DB_USER . 
    " password=" . DB_PASS
);

?>