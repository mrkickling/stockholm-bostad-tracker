<?php
require 'db.php';

$api_key = getenv('API_KEY');
$provided_key = $_GET['api_key'] ?? '';

if ($provided_key !== $api_key) {
    http_response_code(403);
    die(json_encode(["error" => "Invalid API key"]));
}

$server_host = $_SERVER['HTTP_HOST'];

// Prepare the SQL query
$query = "SELECT email, frequency, secret_code, filter FROM bostad_tracker_subscribers";
$result = $conn->query($query);

if (!$result) {
    die(json_encode(["error" => "Database query failed"]));
}   

$subscribers = [];
while ($row = $result->fetch_assoc()) {
    // Decode the filter JSON into an associative array
    $row['filter'] = json_decode($row['filter'], true);  // Decode the filter column into an array

    // Add the URL for configuration
    $row['url'] = "http://$server_host/configure.php?id=" . $row['secret_code'];
    
    // Remove the secret code from the response
    unset($row['secret_code']);

    // Add the row to the subscribers array
    $subscribers[] = $row;
}

header("Content-Type: application/json");
echo json_encode($subscribers);
?>
