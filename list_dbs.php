<?php
$mysqli = @new mysqli('127.0.0.1', 'root', '');
if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error;
} else {
    echo "Connected successfully to MySQL\n";
    $result = $mysqli->query("SHOW DATABASES");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo $row['Database'] . "\n";
        }
    } else {
        echo "Error listing databases: " . $mysqli->error;
    }
}
