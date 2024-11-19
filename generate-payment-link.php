<?php
require 'init.php'; // Stripe initialization

// Fetch products and their prices from Stripe
$products = $stripe->products->all();
$prices = $stripe->prices->all();

$products_with_prices = [];
foreach ($products as $product) {
    // Only include products that have a one-time price
    $product_prices = array_filter($prices->data, function($price) use ($product) {
        return $price->product == $product->id && $price->recurring === null;  // Check for one-time price
    });

    if (!empty($product_prices)) {
        // Store the product with its prices for displaying in the form
        $products_with_prices[$product->id] = [
            'name' => $product->name,
            'prices' => $product_prices
        ];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_products = $_POST['products'] ?? [];

    if ($selected_products) {
        // Create line items for the selected products
        $line_items = [];
        foreach ($selected_products as $product_id) {
            $product = $products_with_prices[$product_id];
            foreach ($product['prices'] as $price) {
                $line_items[] = [
                    'price' => $price->id,
                    'quantity' => 1,  // You can adjust quantity as needed
                ];
            }
        }

        // Create a Payment Link with the selected line items
        $payment_link = $stripe->paymentLinks->create([
            'line_items' => $line_items,
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => ['url' => 'https://your-website.com/thank-you'],  // Customize the URL for after payment
            ],
        ]);

        // Redirect to the generated payment link URL
        header("Location: " . $payment_link->url);
        exit;
    } else {
        echo "<p style='color:red;'>Please select at least one product to continue.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Payment Link</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f9;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        .checkbox-group {
            margin: 10px 0;
        }
        .checkbox-group label {
            display: inline-block;
            margin-left: 10px;
        }
        .checkbox-group div {
            margin-bottom: 10px;
        }
        .checkbox-group input {
            margin-right: 10px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #28a745;
            color: white;
            font-size: 1rem;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h1>Generate Payment Link</h1>
    <form method="POST" action="">
        <label>Select Products:</label>
        <div class="checkbox-group">
            <?php foreach ($products_with_prices as $product_id => $product_data): ?>
                <div>
                    <input type="checkbox" id="product-<?php echo $product_id; ?>" name="products[]" value="<?php echo $product_id; ?>">
                    <label for="product-<?php echo $product_id; ?>"><?php echo htmlspecialchars($product_data['name']); ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit">Generate Payment Link</button>
    </form>
</body>
</html>
