<?php
session_start();
include('connection.php');
$connection = new Connection();
$pdo = $connection->OpenConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data from AJAX request
    $fullname = $_POST['fullname'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $cartItems = $_POST['cartItems'];
    $total = $_POST['total'];

    try {
        // Insert into checkout table
        $stmt = $pdo->prepare("INSERT INTO checkout (fullname, address, contactnumber, products, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fullname, $address, $contact, implode(', ', $cartItems), $total]);

        // Get the last inserted id for the checkout record
        $checkoutId = $pdo->lastInsertId();

        // Delete all cart items for the logged-in user
        $stmt = $pdo->prepare("DELETE FROM cart WHERE username = ?");
        $stmt->execute([$_SESSION['username']]);

        echo 'success';
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
} else {
    echo 'Invalid request method';
}
?>
