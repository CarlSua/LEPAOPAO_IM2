<?php
session_start();
include('connection.php');

$connection = new Connection();
$pdo = $connection->OpenConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Check if username already exists
    $checkQuery = "SELECT COUNT(*) FROM katawhan WHERE username = :username";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([':username' => $username]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        // Username already exists, redirect with error message
        $error = "Username already exists. Please choose a different one.";
        header("Location: login.php?error=" . urlencode($error));
        exit;
    } else {
        // Insert data into the 'katawhan' table
        $query = "INSERT INTO katawhan (first_name, last_name, address, birthdate, gender, role , username, password) 
        VALUES (:first_name, :last_name, :address, :birthdate, :gender, :role, :username, :password)";
        $stmt = $pdo->prepare($query);

        // Execute and check for successful registration
        if ($stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':address' => $address,
            ':birthdate' => $birthdate,
            ':gender' => $gender,
            ':role' => $role,
            ':username' => $username,
            ':password' => $password
        ])) {
            // Successful registration
            header("Location: login.php?success=1");
            exit;
        } else {
            // Registration failed
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
