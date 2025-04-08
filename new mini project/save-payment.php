<?php
// Database connection details
$db_host = 'localhost';
$db_name = 'pharmacy';
$db_user = 'root';
$db_pass = '';

// Set header to return JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

// Get amount and order_id from POST data
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

// Validate data
if ($amount <= 0 || $order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid amount or order ID']);
    exit;
}

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Insert payment record
    $stmt = $pdo->prepare("INSERT INTO payment (amount, order_id) VALUES (:amount, :order_id)");
    $stmt->execute([
        'amount' => $amount,
        'order_id' => $order_id
    ]);
    
    // Return success response
    echo json_encode(['success' => true, 'message' => 'Payment saved successfully']);
    
} catch (PDOException $e) {
    // Return error response
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>

