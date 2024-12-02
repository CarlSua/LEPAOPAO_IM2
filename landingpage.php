<?php
// Start the session to access session variables
session_start();

// Include the connection file to use the PDO instance
include('connection.php');
$connection = new Connection();
$pdo = $connection->OpenConnection();
try {
    // Use the $pdo object from your connection setup
    $stmt = $pdo->prepare("SELECT product_name, price, quantity FROM products");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>

        function openCart() {
        window.location.href = 'mycart.php';
        }
        
        // JavaScript to handle quantity increment and decrement
        function incrementQuantity(inputId) {
            let input = document.getElementById(inputId);
            input.value = parseInt(input.value) + 1;
        }

        function decrementQuantity(inputId) {
            let input = document.getElementById(inputId);
            if (input.value > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }
        

        function addToCart(productName, price, quantity) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "addtocart.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    alert('Product added to cart!');
                    refreshProductCards(); // Refresh product cards after adding to cart
                }
            };

            // Send the POST data (product name, price, quantity)
            xhr.send("productName=" + encodeURIComponent(productName) + "&price=" + encodeURIComponent(price) + "&quantity=" + encodeURIComponent(quantity));
        }

        function refreshProductCards() {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "getProducts.php", true); // Ensure this points to your PHP file that returns product data in JSON format
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                const products = JSON.parse(xhr.responseText);
                const productContainer = document.querySelector('.container .row');
                productContainer.innerHTML = ''; // Clear current product cards

                products.forEach((product, index) => {
                    const productCard = `
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">${product.product_name}</h5>
                                    <p class="card-text">Price: $${product.price}</p>
                                    <p class="card-text">Available Quantity: ${product.quantity}</p>
                                    
                                    <div class="d-flex align-items-center">
                                        <div class="input-group me-2" style="width: 120px;">
                                            <button class="btn btn-outline-secondary" type="button" onclick="decrementQuantity('quantity-${index}')">-</button>
                                            <input type="text" id="quantity-${index}" class="form-control text-center" value="1" min="1" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="incrementQuantity('quantity-${index}')">+</button>
                                        </div>
                                        <button class="btn btn-primary" onclick="addToCart('${product.product_name}', '${product.price}', document.getElementById('quantity-${index}').value)">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    productContainer.innerHTML += productCard; // Append each new product card
                });
            }
        };
        xhr.send();
    }


    </script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <!-- Profile Section with Font Awesome Icon -->
            <li class="nav-item d-flex align-items-center">
                <i class="fas fa-user-circle text-white" style="font-size: 40px;"></i>
                <!-- Display the logged-in username -->
                <span class="ms-2 text-white">
                    <?php 
                    // Display username if it's set in the session, else show 'Guest'
                    echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; 
                    ?>
                </span>
            </li>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                    <button id="openCartBtn" class="btn btn-outline-light" type="button" onclick="openCart()">
                        <i class="fas fa-shopping-cart"></i> Cart
                    </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Product Cards -->
    <div class="container">
        <div class="row">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $index => $row): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row["product_name"]) ?></h5>
                                <p class="card-text">Price: <?= htmlspecialchars($row["price"]) ?></p>
                                <p class="card-text">Available Quantity: <?= htmlspecialchars($row["quantity"]) ?></p>
                                
                                <div class="d-flex align-items-center">
                                    <div class="input-group me-2" style="width: 120px;">
                                        <button class="btn btn-outline-secondary" type="button" onclick="decrementQuantity('quantity-<?= $index ?>')">-</button>
                                        <input type="text" id="quantity-<?= $index ?>" class="form-control text-center" value="1" min="1" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="incrementQuantity('quantity-<?= $index ?>')">+</button>
                                    </div>
                                    <button class="btn btn-primary" onclick="addToCart('<?= htmlspecialchars($row['product_name']) ?>', '<?= htmlspecialchars($row['price']) ?>', document.getElementById('quantity-<?= $index ?>').value)">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
