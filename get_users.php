<?php
$mysqli = @new mysqli('127.0.0.1', 'root', '', 'digiskul');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$result = $mysqli->query("SELECT id, name, email, role, last_login FROM users");
if ($result) {
    echo "Users in system:\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: {$row['id']} | Name: {$row['name']} | Email: {$row['email']} | Role: {$row['role']} | Last Login: {$row['last_login']}\n";
    }
} else {
    echo "Error: " . $mysqli->error;
}
