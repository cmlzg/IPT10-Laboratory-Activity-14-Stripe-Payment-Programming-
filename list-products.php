<?php
require "init.php";

$products = $stripe->products->all();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f9;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 16px;
            margin: 16px 0;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .product-card img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .product-card h2 {
            margin: 0;
            font-size: 1.5rem;
            color: #333;
        }
        .product-card p {
            margin: 4px 0;
            color: #666;
        }
        .price {
        font-size: 1.2rem;
        color: #28a745; /* Green color */
        font-weight: bold;
}
    </style>
</head>
<body>
    <h1>Product List</h1>
    <?php
    foreach ($products as $product) {
        $price = $stripe->prices->retrieve($product->default_price);
        $image = !empty($product->images) ? array_pop($product->images) : null;
    ?>
        <div class="product-card">
            <h2><?php echo htmlspecialchars($product->name); ?></h2>
            <?php if ($image): ?>
                <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($product->name); ?>">
            <?php else: ?>
                <p>No image available</p>
            <?php endif; ?>
            <p>Product ID: <?php echo htmlspecialchars($product->id); ?></p>
            <p class="price">
                <?php echo strtoupper($price->currency) . ' ' . number_format($price->unit_amount / 100, 2); ?>
            </p>
        </div>
    <?php
    }
    ?>
</body>
</html>
