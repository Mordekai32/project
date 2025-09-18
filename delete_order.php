<?php
include('db.php');

if (isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    if ($stmt->execute()) {
        header("Location: orders.php?success=Order+deleted");
        exit;
    } else {
        echo "Error deleting order: " . $stmt->error;
    }
    $stmt->close();
} else {
    header("Location: orders.php");
    exit;
}
?>
