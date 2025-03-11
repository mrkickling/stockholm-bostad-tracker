<?php
require 'includes/db.php';
require 'includes/subscriber.php';
require 'includes/subscribers.php';

// Stuff below requires api key
$env = parse_ini_file(filename: '.env');
$app_url = $env['APP_URL'];
$api_key = $env['API_KEY'];

$provided_key = $_GET['api_key'] ?? '';
if ($provided_key !== $api_key) {
    http_response_code(response_code: 403);
    die(json_encode(value: ["error" => "Invalid API key"]));
}

// Update latest sync for subcriber
if (isset($_POST['subscriber_email'])) {
    $subscriber_email = $_POST['subscriber_email'];
    setLatestNotified($conn, $subscriber_email);

} else {
    header(header: "Content-Type: application/json");
    return getAllSubscribers($conn, $app_url);
}
