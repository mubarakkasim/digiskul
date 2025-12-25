<?php
$mysqli = @new mysqli('127.0.0.1', 'root', '', 'digiskul');
if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error;
} else {
    echo "Connected successfully to digiskul";
    $result = $mysqli->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "\nUser count: " . $row['count'];
    } else {
        echo "\nError querying users table: " . $mysqli->error;
    }
}
