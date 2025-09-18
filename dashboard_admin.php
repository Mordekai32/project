<?php
include 'db.php';
session_start();

// Security check for admin access
// IMPORTANT: Ensure your login process sets $_SESSION['user_id'] upon successful login.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to your login page if not logged in
    exit();
}
// Optional: Add specific admin role check, e.g., if ($_SESSION['role'] !== 'admin') { ... }


// Count totals (error handling added for robustness)
$users = 0;
$products = 0;
$orders = 0;
$ads = 0;

if ($conn) { // Ensure connection is valid before querying
    $user_res = $conn->query("SELECT COUNT(*) AS total FROM users");
    if ($user_res) $users = $user_res->fetch_assoc()['total']; else error_log("Error counting users: " . $conn->error);

    $product_res = $conn->query("SELECT COUNT(*) AS total FROM products");
    if ($product_res) $products = $product_res->fetch_assoc()['total']; else error_log("Error counting products: " . $conn->error);

    $order_res = $conn->query("SELECT COUNT(*) AS total FROM orders");
    if ($order_res) $orders = $order_res->fetch_assoc()['total']; else error_log("Error counting orders: " . $conn->error);

    $ad_res = $conn->query("SELECT COUNT(*) AS total FROM advertisements");
    if ($ad_res) $ads = $ad_res->fetch_assoc()['total']; else error_log("Error counting advertisements: " . $conn->error);
    
    $conn->close(); // Close connection after all queries
} else {
    error_log("Database connection not available to fetch dashboard counts.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Google Font 'Inter' for a modern look -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Custom font application */
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Chart.js specific height (Tailwind doesn't have direct height for canvas) */
        .small-chart {
            height: 250px;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen bg-gray-50 text-gray-800 antialiased">

<!-- Navbar (Converted to Tailwind) -->
<nav class="bg-gray-800 shadow-md py-4">
    <div class="container mx-auto px-4 flex justify-between items-center">
        <a class="text-white text-xl font-bold flex items-center" href="#">
            <i class="fas fa-tachometer-alt mr-3 text-2xl"></i> Admin Dashboard
        </a>
        <button class="block lg:hidden text-gray-400 hover:text-white focus:outline-none focus:text-white"
                aria-label="Toggle navigation"
                onclick="document.getElementById('navbarNav').classList.toggle('hidden');">
            <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M4 5h16a1 1 0 010 2H4a1 1 0 110-2zm0 6h16a1 1 0 010 2H4a1 1 0 010-2zm0 6h16a1 1 0 010 2H4a1 1 0 010-2z" clip-rule="evenodd"></path>
            </svg>
        </button>
        <div class="hidden lg:flex flex-grow justify-end" id="navbarNav">
            <ul class="flex flex-col lg:flex-row lg:space-x-8 mt-4 lg:mt-0 items-center">
                <li><a class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium flex items-center transition-colors duration-200" href="dashboard_admin.php"><i class="fas fa-home mr-2"></i> Dashboard</a></li>
                <li><a class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium flex items-center transition-colors duration-200" href="products.php"><i class="fas fa-box-seam mr-2"></i> Products</a></li>
                <li><a class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium flex items-center transition-colors duration-200" href="orders.php"><i class="fas fa-shopping-cart mr-2"></i> Orders</a></li>
                <li><a class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium flex items-center transition-colors duration-200" href="ads.php"><i class="fas fa-bullhorn mr-2"></i> Advertisements</a></li>
                <li><a class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium flex items-center transition-colors duration-200" href="manage_user.php"><i class="fas fa-users mr-2"></i> Users</a></li>
                <li><a class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium flex items-center transition-colors duration-200" href="send_messages.php"><i class="fas fa-envelope mr-2"></i> Messages</a></li>
                <li><a class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium flex items-center transition-colors duration-200" href="settings.php"><i class="fas fa-cog mr-2"></i> Settings</a></li>
                <li><a class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium flex items-center transition-colors duration-200" href="notifications.php"><i class="fas fa-bell mr-2"></i> Notifications</a></li>
                <li><a class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium flex items-center transition-colors duration-200" href="insert_product.php"><i class="fas fa-plus-circle mr-2"></i> Add Product</a></li>
                <li class="ml-0 lg:ml-4 mt-4 lg:mt-0">
                    <a href="logout.php" class="inline-flex items-center justify-center px-4 py-2 border border-red-500 text-red-500 text-sm font-medium rounded-md hover:bg-red-500 hover:text-white transition-colors duration-200 ease-in-out">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Dashboard Content -->
<main class="flex-grow container mx-auto px-4 py-8">
    <h3 class="mb-6 text-center text-3xl font-bold text-gray-900">Dashboard Overview</h3>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-blue-500 text-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center text-center transform hover:scale-105 transition-transform duration-200 ease-in-out">
            <i class="fas fa-users text-4xl mb-3"></i>
            <h6 class="text-lg font-semibold">Total Users</h6>
            <p class="text-4xl font-bold"><?php echo htmlspecialchars($users); ?></p>
        </div>
        <div class="bg-green-500 text-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center text-center transform hover:scale-105 transition-transform duration-200 ease-in-out">
            <i class="fas fa-box-seam text-4xl mb-3"></i>
            <h6 class="text-lg font-semibold">Total Products</h6>
            <p class="text-4xl font-bold"><?php echo htmlspecialchars($products); ?></p>
        </div>
        <div class="bg-yellow-500 text-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center text-center transform hover:scale-105 transition-transform duration-200 ease-in-out">
            <i class="fas fa-shopping-cart text-4xl mb-3"></i>
            <h6 class="text-lg font-semibold">Total Orders</h6>
            <p class="text-4xl font-bold"><?php echo htmlspecialchars($orders); ?></p>
        </div>
        <div class="bg-red-500 text-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center text-center transform hover:scale-105 transition-transform duration-200 ease-in-out">
            <i class="fas fa-bullhorn text-4xl mb-3"></i>
            <h6 class="text-lg font-semibold">Total Ads</h6>
            <p class="text-4xl font-bold"><?php echo htmlspecialchars($ads); ?></p>
        </div>
    </div>

    <!-- Compact Graph -->
    <div class="mt-10">
        <h5 class="text-center text-2xl font-semibold text-gray-800 mb-4 flex items-center justify-center">
            <i class="fas fa-chart-line mr-3"></i> Orders & Products Overview
        </h5>
        <div class="bg-white rounded-lg shadow-xl p-6 border border-gray-200">
            <canvas id="dashboardChart" class="small-chart"></canvas>
        </div>
    </div>
</main>

<!-- Chart.js Script (no change needed) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('dashboardChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    {
                        label: 'Orders',
                        data: [12, 19, 15, 22, 30, 28],
                        backgroundColor: 'rgba(255, 193, 7, 0.2)', // Yellow (like Bootstrap warning)
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3 // Smooth lines
                    },
                    {
                        label: 'Products',
                        data: [10, 14, 18, 25, 27, 33],
                        backgroundColor: 'rgba(40, 167, 69, 0.2)', // Green (like Bootstrap success)
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3 // Smooth lines
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                family: 'Inter, sans-serif'
                            },
                            color: '#4a5568' // gray-700
                        }
                    },
                    tooltip: {
                        titleFont: { family: 'Inter, sans-serif' },
                        bodyFont: { family: 'Inter, sans-serif' }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }, // Remove x-axis grid lines
                        ticks: {
                            font: { family: 'Inter, sans-serif' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0' }, // Light grid lines for y-axis
                        ticks: {
                            font: { family: 'Inter, sans-serif' }
                        }
                    }
                }
            }
        });
    }
});
</script>

---

<footer class="bg-gray-900 text-white py-12 font-inter">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Brand Section -->
            <div class="col-span-1 md:col-span-2 lg:col-span-1 text-center md:text-left">
                <h6 class="text-xl font-bold flex items-center justify-center md:justify-start mb-3">
                    <i class="fas fa-rocket text-indigo-400 mr-3 text-2xl"></i> Mordekai Admin Panel
                </h6>
                <p class="text-gray-400 text-sm leading-relaxed max-w-xs mx-auto md:mx-0">
                    Powering your digital marketplace with advanced insights and seamless control.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="text-center md:text-left">
                <h6 class="text-lg font-semibold text-white mb-4">Quick Links</h6>
                <ul class="space-y-2 text-sm">
                    <li><a href="dashboard_admin.php" class="text-gray-400 hover:text-white transition-colors duration-200">Dashboard</a></li>
                    <li><a href="products.php" class="text-gray-400 hover:text-white transition-colors duration-200">Products</a></li>
                    <li><a href="orders.php" class="text-gray-400 hover:text-white transition-colors duration-200">Orders</a></li>
                    <li><a href="manage_users.php" class="text-gray-400 hover:text-white transition-colors duration-200">Users</a></li>
                </ul>
            </div>

            <!-- Management Links -->
            <div class="text-center md:text-left">
                <h6 class="text-lg font-semibold text-white mb-4">Management</h6>
                <ul class="space-y-2 text-sm">
                    <li><a href="advertisements.php" class="text-gray-400 hover:text-white transition-colors duration-200">Advertisements</a></li>
                    <li><a href="send_message.php" class="text-gray-400 hover:text-white transition-colors duration-200">Messages</a></li>
                    <li><a href="settings.php" class="text-gray-400 hover:text-white transition-colors duration-200">Settings</a></li>
                    <li><a href="notifications.php" class="text-gray-400 hover:text-white transition-colors duration-200">Notifications</a></li>
                </ul>
            </div>

            <!-- Contact & Legal (Example) -->
            <div class="text-center md:text-left">
                <h6 class="text-lg font-semibold text-white mb-4">Contact & Info</h6>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Privacy Policy</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Terms of Service</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-200">Support Center</a></li>
                    <li><a href="logout.php" class="text-gray-400 hover:text-white transition-colors duration-200">Logout</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-700 pt-8 mt-8 text-center text-sm text-gray-500">
            &copy; <?php echo date('Y'); ?> Mordekai. All rights reserved.
        </div>
    </div>
</footer>

</body>
</html>