<?php
$mysqli = @new mysqli('127.0.0.1', 'root', '', 'digiskul');
if ($mysqli->connect_error) {
    echo "Connection failed: " . $mysqli->connect_error;
} else {
    echo "Connected successfully to digiskul\n";
    $result = $mysqli->query("SHOW TABLES");
    if ($result) {
        echo "Tables in digiskul:\n";
        while ($row = $result->fetch_array()) {
            echo "- " . $row[0] . "\n";
        }
    } else {
        echo "Error listing tables: " . $mysqli->error;
    }
}
