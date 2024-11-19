<?php
require "init.php"; // Stripe initialization

// Fetch customers and products from Stripe
$customers = $stripe->customers->all();
$products = $stripe->products->all();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $selected_products = $_POST['products'] ?? [];

    if ($customer_id && $selected_products) {
        // Create an invoice
        $invoice = $stripe->invoices->create([
            'customer' => $customer_id
        ]);

        // Add selected products to the invoice
        foreach ($selected_products as $product_id) {
            $product = $stripe->products->retrieve($product_id);

            // Ensure we're using a one-time price
            $prices = $stripe->prices->all(['product' => $product_id]);
            $one_time_price = null;

            foreach ($prices as $price) {
                if ($price->type === 'one_time') {
                    $one_time_price = $price->id;
                    break;
                }
            }

            if ($one_time_price) {
                $stripe->invoiceItems->create([
                    'customer' => $customer_id,
                    'price' => $one_time_price,
                    'invoice' => $invoice->id
                ]);
            } else {
                // Handle the case where no one-time price is found for the product
                echo "<p style='color:red;'>No one-time price found for the product '{$product->name}'.</p>";
                exit;
            }
        }

        // Finalize the invoice
        $stripe->invoices->finalizeInvoice($invoice->id);
        $invoice = $stripe->invoices->retrieve($invoice->id);

        // Display invoice links
        echo "<p>Invoice PDF: <a href='{$invoice->invoice_pdf}' target='_blank'>Download</a></p>";
        echo "<p>Hosted Invoice URL: <a href='{$invoice->hosted_invoice_url}' target='_blank'>Pay Invoice</a></p>";
        exit;
    } else {
        echo "<p style='color:red;'>Please select a customer and at least one product.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Builder</title>
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
        select {
            margin: 10px 0;
            padding: 8px;
            width: 100%;
            font-size: 1rem;
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
    <h1>Invoice Builder</h1>
    <form method="POST" action="">
        <label for="customer_id">Select Customer:</label>
        <select id="customer_id" name="customer_id" required>
            <option value="">-- Select Customer --</option>
            <?php foreach ($customers as $customer): ?>
                <option value="<?php echo htmlspecialchars($customer->id); ?>">
                    <?php echo htmlspecialchars($customer->name ?? $customer->email); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Select Products:</label>
        <div class="checkbox-group">
            <?php foreach ($products as $product): ?>
                <div>
                    <input type="checkbox" id="product-<?php echo $product->id; ?>" name="products[]" value="<?php echo $product->id; ?>">
                    <label for="product-<?php echo $product->id; ?>"><?php echo htmlspecialchars($product->name); ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit">Generate Invoice</button>
    </form>
</body>
</html>
