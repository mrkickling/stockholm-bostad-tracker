<?php
require "includes/db.php";
require "includes/apartments.php";
require "includes/subscriber.php";

header(header: 'Content-Type: application/json');

// Stuff below requires api key
$env = parse_ini_file(filename: '.env');
$app_url = $env['APP_URL'];
$api_key = $env['API_KEY'];

$provided_key = $_GET['api_key'] ?? '';
if ($provided_key !== $api_key) {
    http_response_code(response_code: 403);
    die(json_encode(value: ["error" => "Invalid API key"]));
}

if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $subscriber = getUserByEmail($conn, $email);
    $matching_new_apartments = getNewMatchingApartments($conn, $subscriber);
    if ($matching_new_apartments) {
        echo json_encode(value: [
            'status' => 'success',
            'message'=> 'Sending email with ' . count($matching_new_apartments) . ' apartments to ' . $email . '.'
        ]);
        sendNotificationEmail($subscriber, $matching_new_apartments);
    } else {
        echo json_encode(value: [
            'status' => 'success',
            'message'=> 'No new matching apartments for ' . $email . '.'
        ]);
    }
    updateLatestNotified($conn, $subscriber);
}

?>