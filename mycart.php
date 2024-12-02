<?php
// Start session and include database connection
session_start();
include('connection.php');
$connection = new Connection();
$pdo = $connection->OpenConnection();

try {
    // Prepare SQL query to fetch cart items for the logged-in user, including total
    $stmt = $pdo->prepare("SELECT cartid, product, quantity, price, total FROM cart WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate the grand total (sum of all item totals)
    $grandTotal = 0;
    foreach ($cartItems as $item) {
        $grandTotal += $item['total']; // Use 'total' from the database
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

if (isset($_GET['delete'])) {
    $cartid = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE cartid = ?");
        $stmt->execute([$cartid]);
        header("Location: mycart.php"); // Redirect back to the cart page after deletion
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Shopping Cart</a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-3">
        <h3>Your Shopping Cart</h3>

        <?php if (!empty($cartItems)): ?>
            <!-- Cart Table -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Price</th>
                        <th scope="col">Total</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td><?= htmlspecialchars($item['price']) ?></td>
                            <td><?= htmlspecialchars($item['total']) ?></td> <!-- Use total from the database -->
                            <td>
                                <!-- Delete Button -->
                                <a href="mycart.php?delete=<?= $item['cartid'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Total Price -->
            <div class="text-end">
                <h4>Total Price: <?= number_format($grandTotal, 2) ?></h4>
            </div>

        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>

        <!-- Buttons -->
        <div class="text-end mt-4">
            <a href="landingpage.php" class="btn btn-primary">Continue Shopping</a>
            <!-- Proceed to Checkout Button -->
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#checkoutModal">Proceed to Checkout</button>
        </div>

    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Checkout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Checkout Form -->
                    <form id="checkoutForm">
                        <!-- User Information Fields -->
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contact" name="contact" required>
                        </div>

                        <!-- Cart Items List in Modal -->
                        <h5>Items in Your Cart:</h5>
                        <ul class="list-group mb-3" id="cartItemsList">
                            <?php foreach ($cartItems as $item): ?>
                                <li class="list-group-item">
                                    <?= htmlspecialchars($item['product']) ?> - 
                                    Quantity: <?= htmlspecialchars($item['quantity']) ?> - 
                                    $<?= htmlspecialchars($item['quantity'] * $item['price']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <!-- Total Price Field (Readonly) -->
                        <div class="mb-3">
                            <label for="total" class="form-label">Total Price</label>
                            <input type="text" class="form-control" id="total" name="total" value="$<?= number_format($grandTotal, 2) ?>" readonly>
                        </div>

                        <button type="button" class="btn btn-primary" id="submitCheckoutBtn">Proceed with Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (for modal functionality) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery and AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
    $('#submitCheckoutBtn').on('click', function() {
        // Get form data
        var fullname = $('#fullname').val();
        var address = $('#address').val();
        var contact = $('#contact').val();
        var total = $('#total').val();
        var cartItems = [];

        // Gather cart items
        $('#cartItemsList li').each(function() {
            var product = $(this).text();
            cartItems.push(product);
        });

        // Send data to server (process_checkout.php)
        $.ajax({
            url: 'process_checkout.php',
            method: 'POST',
            data: {
                fullname: fullname,
                address: address,
                contact: contact,
                total: total,
                cartItems: cartItems
            },
            success: function(response) {
                if (response == 'success') {
                    alert('Checkout successful!');
                    // Clear the cart after checkout
                    window.location.reload(); // Reload the page to refresh cart
                } else {
                    alert('Error during checkout. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
});

    </script>
</body>
</html>