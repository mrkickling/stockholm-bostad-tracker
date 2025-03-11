<?php

function getAllSubscribers($conn, $app_url) {
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

    return json_encode($subscribers);
    
}

?>