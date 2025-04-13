<?php
header('Content-Type: application/json');

// Database connection details
$db_host = 'localhost';
$db_name = 'pharmacy';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get search query
    $query = isset($_GET['query']) ? trim($_GET['query']) : '';

    if ($query === '') {
        echo json_encode(['error' => 'No search term provided.', 'results' => []]);
        exit;
    }

    // Fetch medicines with name (m_name) and price
    $stmt = $pdo->prepare("SELECT m_name AS name, price FROM medicine WHERE m_name LIKE :query LIMIT 20");
    $stmt->execute(['query' => '%' . $query . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['results' => $results]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'results' => []]);
}
?>
