<?php 
require "init.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address_line1 = $_POST['address_line1'];
    $address_line2 = $_POST['address_line2'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $postal_code = $_POST['postal_code'];

    try {
        // Create customer in Stripe
        $customer = $stripe->customers->create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => [
                'line1' => $address_line1,
                'line2' => $address_line2,
                'city' => $city,
                'country' => $country,
                'postal_code' => $postal_code,
            ],
        ]);

        // Extract the customer data for displaying a cleaner message
        $customer_name = htmlspecialchars($customer->name);
        $customer_email = htmlspecialchars($customer->email);
        $customer_phone = htmlspecialchars($customer->phone);
        $customer_address = htmlspecialchars($customer->address->line1 . ' ' . $customer->address->line2 . ', ' . $customer->address->city . ', ' . $customer->address->country . ' ' . $customer->address->postal_code);

        // Success message
        $success_message = "
            <div style='background-color: #28a745; color: white; padding: 15px; border-radius: 5px;'>
                <h2>Customer Successfully Created!</h2>
                <p><strong>Name:</strong> $customer_name</p>
                <p><strong>Email:</strong> $customer_email</p>
                <p><strong>Phone:</strong> $customer_phone</p>
                <p><strong>Address:</strong> $customer_address</p>
            </div>
        ";

        echo $success_message;

    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Customer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f9f9f9;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        form button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #28a745;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
        }
        form button:hover {
            background-color: #218838;
        }
        h1 {
            text-align: center;
            margin-top: 20px;
            font-size: 2rem;
            color: #333;
        }
    </style>
</head>
<body>
    <h1>Create Customer</h1>
    <form method="POST" action="">
        <label for="name">Complete Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required>

        <label for="address_line1">Address Line 1:</label>
        <input type="text" id="address_line1" name="address_line1" required>

        <label for="address_line2">Address Line 2:</label>
        <input type="text" id="address_line2" name="address_line2">

        <label for="city">City:</label>
        <input type="text" id="city" name="city" required>

        <label for="country">Country:</label>
        <input type="text" id="country" name="country" required>

        <label for="postal_code">Postal Code:</label>
        <input type="text" id="postal_code" name="postal_code" required>

        <button type="submit">Create Customer</button>
    </form>
</body>
</html>
