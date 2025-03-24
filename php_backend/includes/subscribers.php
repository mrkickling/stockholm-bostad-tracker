<?php
function getAllSubscribers($conn, $app_url) {
    // Get all subscribers
    $query = "SELECT email, frequency, secret_code, latest_notified 
              FROM bostad_tracker_subscribers";
    $result = $conn->query($query);

    if (!$result) {
        die(json_encode(["error" => "Database query failed"]));
    }   

    $subscribers = [];
    while ($row = $result->fetch_assoc()) {

        $subscriber = [];

        // Add the URL for configuration
        $subscriber['url'] = $app_url . "/configure.php?id=" . $row['secret_code'];
        $subscriber['email'] = $row['email'];
        $subscriber['frequency'] = $row['frequency'];
        $subscriber['latest_notified'] = $row['latest_notified'];

        // Add the row to the subscribers array
        $subscribers[] = $subscriber;
    }

    return json_encode($subscribers);
}

?>