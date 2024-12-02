<?php
session_start();
include("connection.php");

$connection = new Connection();
$con = $connection->OpenConnection(); // Initialize $con


if(isset($_POST['add_product']))
{
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity']; 
    $availability = $_POST['availability'];
    $date = $_POST['date'];


    //if user and email already exist
    $query = "INSERT INTO products(product_name,category,price,quantity,product_availability,date) 
    VALUES (:product_name, :category, :price, :quantity, :availability, :date)";
    $query_run = $con->prepare($query);

    $data = [
        ':product_name' => $product_name,
        ':category' => $category,
        ':price' => $price,
        ':quantity' => $quantity,
        ':availability' => $availability,
        ':date' => $date,
    ];
    $query_execute = $query_run->execute($data);

    if($query_execute)
    {
        $_SESSION['message'] = "Inserted Successfully";
        header('Location: index.php');
        die;
    }
        $_SESSION['message'] = "Not Inserted";
        header('Location: index.php');
        die;
    }


if (isset($_POST['update_product'])) { // Check if the form was submitted
    $id = $_POST['id']; // Assuming you have a hidden input for product ID
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity']; 
    $availability = $_POST['availability'];
    $date = $_POST['date'];

    // Prepare the update query
    $query = "UPDATE products SET 
                product_name = :product_name,
                category = :category,
                price = :price,
                quantity = :quantity,
                product_availability = :availability,
                date = :date
              WHERE id = :id"; // Make sure 'id' matches your primary key field name

    $query_run = $con->prepare($query);

    // Bind parameters to the query
    $data = [
        ':product_name' => $product_name,
        ':category' => $category,
        ':price' => $price,
        ':quantity' => $quantity,
        ':availability' => $availability,
        ':date' => $date,
        ':id' => $id, // Bind the product ID
    ];

    // Execute the query
    $query_execute = $query_run->execute($data);

    // Check if the update was successful
    if ($query_execute) {
        $_SESSION['message'] = "Updated Successfully";
        header('Location: index.php');
        exit; // Use exit instead of die
    } else {
        $_SESSION['message'] = "Update Failed";
        header('Location: index.php');
        exit; // Use exit instead of die
    }
}

if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id']; // Get the product ID from the form

    // Prepare the delete query
    $query = "DELETE FROM products WHERE id = :id"; // Make sure 'id' matches your primary key field name

    $query_run = $con->prepare($query);

    // Bind the product ID to the query
    $data = [
        ':id' => $product_id, // Bind the product ID
    ];

    // Execute the delete query
    $query_execute = $query_run->execute($data);

    // Check if the deletion was successful
    if ($query_execute) {
        $_SESSION['message'] = "Deleted Successfully";
        header('Location: index.php');
        exit; // Use exit instead of die
    } else {
        $_SESSION['message'] = "Deletion Failed";
        header('Location: index.php');
        exit; // Use exit instead of die
    }
}

?>