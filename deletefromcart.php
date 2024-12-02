<?php
session_start();
include('connection.php');
$connection = new Connection();
$pdo = $connection->OpenConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cartItemId'])) {
    $cartItemId = $_POST['cartItemId'];

    try {
        // Prepare and execute delete query based on 'cartid'
        $stmt = $pdo->prepare("DELETE FROM cart WHERE cartid = :cartid");
        $stmt->bindParam(':cartid', $cartItemId, PDO::PARAM_INT);
        $stmt->execute();

        // Return a success message in JSON format
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        // Return an error message in JSON format
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>