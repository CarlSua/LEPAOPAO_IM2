<?php
session_start();
include('connection.php');
$connection = new Connection();
$pdo = $connection->OpenConnection();

// Check if necessary POST data is set
if (isset($_POST['productName'], $_POST['price'], $_POST['quantity'])) {
    $productName = $_POST['productName'];
    $price = $_POST['price'];
    $quantity = (int)$_POST['quantity'];
    $total = $price * $quantity; // Calculate the total

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Check if there is enough quantity in stock
        $stmt = $pdo->prepare("SELECT quantity FROM products WHERE product_name = :productName");
        $stmt->execute(['productName' => $productName]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product && $product['quantity'] >= $quantity) {
            // Deduct the chosen quantity from the products table
            $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - :quantity WHERE product_name = :productName");
            $stmt->execute(['quantity' => $quantity, 'productName' => $productName]);

            // Insert or update the product in the cart table for the current user
            $username = $_SESSION['username'] ?? 'Guest';
            $stmt = $pdo->prepare("INSERT INTO cart (username, product, quantity, price, total)
                                VALUES (:username, :product, :quantity, :price, :total)
                                ON DUPLICATE KEY UPDATE 
                                    quantity = quantity + :quantity,
                                    total = total + :total");
            $stmt->execute([
                'username' => $username,
                'product' => $productName,
                'quantity' => $quantity,
                'price' => $price,
                'total' => $total
            ]);

            // Commit the transaction
            $pdo->commit();
            echo "Product added to cart.";
        } else {
            // Rollback transaction if not enough stock
            $pdo->rollBack();
            echo "Not enough stock available.";
        }
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
