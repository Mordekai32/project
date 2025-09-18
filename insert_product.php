<?php
// add_product.php - Product Addition Page (Tailwind CSS Redesign)

// Include database connection
if (file_exists('db.php')) {
    include_once 'db.php';
} else {
    error_log("CRITICAL ERROR: db.php not found. Cannot establish database connection for products.");
    die("<h1>System Error</h1><p>A critical system component is missing. Please contact support.</p>");
}

session_start(); // Start session for messages

$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
// Clear messages after reading them
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Note: If product_id is AUTO_INCREMENT, you should not include it in the INSERT query
    // and definitely not bind it if you're not generating it client-side.
    // Based on your previous code where product_id was removed from bind_param,
    // I'm assuming it's AUTO_INCREMENT. I've removed it from the INSERT and validation here.

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $user_id = intval($_POST['user_id']);

    // Basic validation
    if (empty($name) || empty($price) || empty($user_id)) {
        $_SESSION['error_message'] = "Please fill in all required fields: Name, Price, and User ID.";
        header("Location: products.php");
        exit();
    }

    // Using prepared statements for security
    // Updated query for AUTO_INCREMENT product_id
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, user_id) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        error_log("Database Prepare Error (Product Insert): " . $conn->error);
        $_SESSION['error_message'] = "Database error: Could not prepare statement to add product.";
        header("Location: products.php");
        exit();
    }

    // Bind parameters: s for string (name), s for string (description), d for double (price), i for integer (user_id)
    $stmt->bind_param("ssdi", $name, $description, $price, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Product '{$name}' added successfully!";
    } else {
        error_log("Database Execute Error (Product Insert): " . $stmt->error . " (Data: " . json_encode($_POST) . ")");
        $_SESSION['error_message'] = "Error adding product: " . $stmt->error;
    }

    $stmt->close();
    header("Location: products.php"); // Redirect to prevent form re-submission
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Custom font application (best to configure via tailwind.config.js in a full setup) */
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <div class="max-w-md mx-auto mt-12 mb-12 p-6 md:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Add New Product</h1>
            <a href="dashboard_admin.php" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors duration-200 ease-in-out border border-gray-300 shadow-sm">
                <i class="fas fa-arrow-left mr-2 text-gray-500"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-lg flex items-center gap-3 mb-6 relative shadow-sm">
                <i class="fas fa-check-circle text-xl flex-shrink-0"></i>
                <div class="font-medium text-base leading-tight"><?php echo htmlspecialchars($success); ?></div>
                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-2xl text-green-600 hover:text-green-800 focus:outline-none" onclick="this.parentElement.style.display='none';" aria-label="Close alert">&times;</button>
            </div>
        <?php elseif ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-lg flex items-center gap-3 mb-6 relative shadow-sm">
                <i class="fas fa-times-circle text-xl flex-shrink-0"></i>
                <div class="font-medium text-base leading-tight"><?php echo htmlspecialchars($error); ?></div>
                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-2xl text-red-600 hover:text-red-800 focus:outline-none" onclick="this.parentElement.style.display='none';" aria-label="Close alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-xl p-6 md:p-8 border border-gray-100">
            <form method="POST" action="">
                <div class="mb-5">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                    <input type="text" id="name" name="name" placeholder="e.g., Wireless Mouse" required
                           class="block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                  transition-all duration-200 ease-in-out text-base">
                </div>
                <div class="mb-5">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                    <textarea id="description" name="description" rows="4" placeholder="A brief description of the product features."
                              class="block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400
                                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                     transition-all duration-200 ease-in-out text-base resize-y"></textarea>
                </div>
                <div class="mb-5">
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                    <input type="number" step="0.01" id="price" name="price" placeholder="e.g., 29.99" required
                           class="block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                  transition-all duration-200 ease-in-out text-base">
                </div>
                <div class="mb-6">
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User ID</label>
                    <input type="number" id="user_id" name="user_id" placeholder="ID of the user adding this product (e.g., 1)" required
                           class="block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                  transition-all duration-200 ease-in-out text-base">
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md">
                    <i class="fas fa-plus-circle mr-2"></i> Add Product
                </button>
            </form>
        </div>
    </div>
</body>
</html>