<?php
include('connection.php');
$connection = new Connection();
$pdo = $connection->OpenConnection();

try {
    $stmt = $pdo->prepare("SELECT product_name, price, quantity FROM products");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return product data in JSON format
    echo json_encode($products);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
