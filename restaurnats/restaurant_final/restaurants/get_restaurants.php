<?php
$conn = new mysqli("localhost", "root", "", "mywebsite1");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Get search filters safely
$food     = isset($_GET['food'])     ? trim($_GET['food'])     : "";
$budget   = isset($_GET['budget'])   ? trim($_GET['budget'])   : "";
$location = isset($_GET['location']) ? trim($_GET['location']) : "";
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : "";

// Build query with prepared statement to prevent SQL injection
$conditions = [];
$params     = [];
$types      = "";

if ($search !== "") {
    $conditions[] = "name LIKE ?";
    $params[]     = "%" . $search . "%";
    $types       .= "s";
}
if ($food !== "") {
    $conditions[] = "food LIKE ?";
    $params[]     = "%" . $food . "%";
    $types       .= "s";
}
if ($budget !== "") {
    $conditions[] = "budget = ?";
    $params[]     = $budget;
    $types       .= "s";
}
if ($location !== "") {
    $conditions[] = "location LIKE ?";
    $params[]     = "%" . $location . "%";
    $types       .= "s";
}

$sql = "SELECT * FROM restaurants";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY rating DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header("Content-Type: application/json");
echo json_encode($data);

$stmt->close();
$conn->close();
?>
