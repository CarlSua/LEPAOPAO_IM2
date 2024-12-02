<?php
session_start(); // Start the session

// Include the connection file to use the PDO instance
include('connection.php');
$connection = new Connection();
$pdo = $connection->OpenConnection();

// Get the username from the session
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

    // Ensure the username is available
    if ($username) {
        // Prepare the SQL statement to retrieve cart items for the user
        $query = "SELECT product, price, quantity FROM cart WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username);

        try {
            $stmt->execute();
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Return the cart items as JSON
            echo json_encode($cartItems);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "User not logged in."]);
    }
    ?>
