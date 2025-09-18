<?php
// advertisements.php - Advertisement Management Page

// Include database connection
// Ensure db.php exists and connects properly
if (file_exists('db.php')) {
    include_once 'db.php';
} else {
    error_log("CRITICAL ERROR: db.php not found. Cannot establish database connection for advertisements.");
    die("<h1>System Error</h1><p>A critical system component is missing. Please contact support.</p>");
}

session_start(); // Start session to store temporary messages

// Initialize success and error messages
$success = $_SESSION['success_message'] ?? '';
$error = $_SESSION['error_message'] ?? '';
// Clear messages after reading them
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Handle Insert Ad
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ad_id = trim($_POST['ad_id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = intval($_POST['user_id']); // Ensure it's an integer

    // Basic validation
    if (empty($ad_id) || empty($title) || empty($content) || empty($user_id)) {
        $_SESSION['error_message'] = "❌ Please fill all fields. All fields are required.";
        header("Location: advertisements.php"); // Redirect to self to prevent form resubmission
        exit();
    }

    $created_at = date('Y-m-d H:i:s'); // Current timestamp

    // Using prepared statements for security
    $stmt = $conn->prepare("INSERT INTO advertisements (ad_id, title, content, created_at, user_id) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        error_log("Database Prepare Error (Ad Insert): " . $conn->error);
        $_SESSION['error_message'] = "❌ Database error: Could not prepare statement to add advertisement.";
        header("Location: advertisements.php");
        exit();
    }

    $stmt->bind_param("ssssi", $ad_id, $title, $content, $created_at, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "✅ Advertisement '{$title}' added successfully!";
    } else {
        // Check for duplicate ad_id error specifically (MySQL error code 1062 for duplicate entry)
        if ($stmt->errno === 1062) {
            $_SESSION['error_message'] = "❌ Error: An advertisement with this Ad ID already exists. Please use a unique ID.";
        } else {
            error_log("Database Execute Error (Ad Insert): " . $stmt->error . " (Data: " . json_encode($_POST) . ")");
            $_SESSION['error_message'] = "❌ Failed to add advertisement: " . $stmt->error;
        }
    }
    $stmt->close();
    header("Location: advertisements.php"); // Redirect to self to prevent form resubmission
    exit();
}

// Fetch all ads
$ads = [];
if ($conn) {
    $result = $conn->query("SELECT ad_id, title, content, created_at, user_id FROM advertisements ORDER BY created_at DESC");
    if ($result) {
        $ads = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        error_log("Database Query Error (Fetch Ads): " . $conn->error);
        $_SESSION['error_message'] = "❌ Could not retrieve advertisements from the database.";
        header("Location: ads.php"); // Redirect to show message
        exit();
    }
    $conn->close(); // Close database connection after fetching all data
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Advertisements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5; /* Light gray background, modern feel */
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #333;
        }
        .container {
            max-width: 960px; /* Wider container for better content display */
        }
        .card {
            border: none; /* Remove default card border */
            border-radius: 0.75rem; /* More rounded corners */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); /* Softer, more prominent shadow */
            overflow: hidden; /* Ensures shadows and borders apply correctly */
        }
        .card-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); /* Gradient header */
            color: white;
            padding: 1.5rem; /* More padding */
            border-bottom: none; /* Remove header border */
            font-weight: 600; /* Semi-bold */
        }
        .form-control {
            border-radius: 0.375rem; /* Slightly rounded inputs */
            padding: 0.75rem 1rem; /* More comfortable padding */
            border-color: #ced4da; /* Default Bootstrap border */
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 0.75rem 1.5rem; /* Larger button */
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
        }
        .ad-card .card-header {
            background-color: #e9ecef; /* Lighter header for individual ads */
            color: #495057;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        .ad-card .card-body {
            padding: 1.5rem;
        }
        .ad-card small.text-muted {
            font-size: 0.85rem;
        }
        /* Custom alert styling */
        .alert-custom {
            border-left: 5px solid;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .alert-success-custom { border-color: #28a745; background-color: #d4edda; color: #155724; }
        .alert-danger-custom { border-color: #dc3545; background-color: #f8d7da; color: #721c24; }
        .alert-custom .icon {
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        /* Button for back to dashboard */
        .btn-back-dashboard {
            background-color: #6c757d; /* Bootstrap secondary color */
            border-color: #6c757d;
            padding: 0.65rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }
        .btn-back-dashboard:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-2px); /* Slight lift */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-5 fw-bold text-primary">Advertisement Management</h1>
            <a href="dashboard_admin.php" class="btn btn-back-dashboard text-white">
                <i class="fas fa-tachometer-alt me-2"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success-custom alert-dismissible fade show mb-4 alert-custom" role="alert">
                <span class="icon"><i class="fas fa-check-circle"></i></span>
                <div><?= htmlspecialchars($success) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger-custom alert-dismissible fade show mb-4 alert-custom" role="alert">
                <span class="icon"><i class="fas fa-times-circle"></i></span>
                <div><?= htmlspecialchars($error) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0"><i class="fas fa-plus-circle me-2"></i> Post New Advertisement</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="ad_id" class="form-label">Advertisement ID:</label>
                        <input type="text" name="ad_id" id="ad_id" class="form-control" placeholder="Unique Ad ID (e.g., AD001)" required>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Ad Title:</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="Compelling headline for your ad" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Ad Content:</label>
                        <textarea name="content" id="content" class="form-control" rows="4" placeholder="Detailed description of your advertisement" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="user_id" class="form-label">User ID:</label>
                        <input type="number" name="user_id" id="user_id" class="form-control" placeholder="User ID associated with this ad" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane me-2"></i> Post Ad</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0"><i class="fas fa-bullhorn me-2"></i> All Advertisements</h2>
                <span class="badge bg-secondary rounded-pill"><?= count($ads) ?> Total Ads</span>
            </div>
            <div class="card-body">
                <?php if (count($ads) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($ads as $ad): ?>
                            <div class="card ad-card mb-3">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <?= htmlspecialchars($ad['title']) ?>
                                        <small class="text-muted ms-2"> (Ad ID: <?= htmlspecialchars($ad['ad_id']) ?>)</small>
                                    </h5>
                                    <small class="text-muted d-block">Posted by User ID: <?= htmlspecialchars($ad['user_id']) ?></small>
                                </div>
                                <div class="card-body">
                                    <p class="card-text mb-2"><?= nl2br(htmlspecialchars($ad['content'])) ?></p>
                                    <small class="text-muted"><i class="fas fa-clock me-1"></i> Posted on: <?= htmlspecialchars($ad['created_at']) ?></small>
                                </div>
                                </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                        <i class="fas fa-info-circle me-2"></i> No advertisements found. Start by posting a new one!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>