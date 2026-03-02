<?php
$host = 'db';
$user = 'root';
$pass = 'root';
$db   = 'agromanager';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Errore connessione DB: ' . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');
?>
