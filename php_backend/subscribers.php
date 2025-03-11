<?php
require 'db.php';

// Stuff below requires api key
$env = parse_ini_file('.env');
$app_url = $env['APP_URL'];
$api_key = $env['API_KEY'];
$provided_key = $_GET['api_key'] ?? '';
if ($provided_key !== $api_key) {
    http_response_code(403);
    die(json_encode(["error" => "Invalid API key"]));
}

// Update latest sync for subcriber
if (isset($_POST['subscriber_email'])) {
    // Confirm email sent
    $subscriber_email = $_POST['subscriber_email'];

    // Update the latest_notified field to current time
    $query = "UPDATE bostad_tracker_subscribers SET latest_notified = NOW() WHERE email = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        die(json_encode(["error" => "Database preparation failed"]));
    }
    
    $stmt->bind_param("s", $subscriber_email);
    $success = $stmt->execute();
    
    if (!$success) {
        die(json_encode(["error" => "Database update failed"]));
    }
    
    echo json_encode(["success" => true]);
    
    $stmt->close();
    $conn->close();
} else {
    // Get all subscribers
    $query = "SELECT email, frequency, secret_code, filter, latest_notified FROM bostad_tracker_subscribers WHERE verified = 1";
    $result = $conn->query($query);

    if (!$result) {
        die(json_encode(["error" => "Database query failed"]));
    }   

    $subscribers = [];
    while ($row = $result->fetch_assoc()) {
        // Decode the filter JSON into an associative array
        $row['filter'] = json_decode($row['filter'], true);  // Decode the filter column into an array

        // Add the URL for configuration
        $row['url'] = $app_url . "/configure.php?id=" . $row['secret_code'];

        // Remove the secret code from the response
        unset($row['secret_code']);

        // Add the row to the subscribers array
        $subscribers[] = $row;
    }

    header("Content-Type: application/json");
    echo json_encode($subscribers);
}

?>
