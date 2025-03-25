<?php
require_once "includes/db.php";  // Database connection
require "includes/apartments.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_data = file_get_contents('php://input');
    $apartments = json_decode($input_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["error" => "Invalid JSON format"]);
        exit();
    }

    if (!is_array($apartments) || empty($apartments)) {
        echo json_encode(["error" => "No apartment data provided"]);
        exit();
    }

    // If a single apartment is sent, wrap it in an array
    if (isset($apartments['internal_id'])) {
        $apartments = [$apartments];
    }

    $responses = [];
    foreach ($apartments as $apartment) {
        $responses[] = insertApartment($conn, $apartment);
    }

    echo json_encode(["results" => $responses]);

} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $city_area = $_GET['city_area'] ?? '';
    $kommun = $_GET['kommun'] ?? '';
    $min_rent = $_GET['min_rent'] ?? 0;
    $max_rent = $_GET['max_rent'] ?? PHP_INT_MAX;
    $min_rooms = $_GET['min_rooms'] ?? 0;
    $max_rooms = $_GET['max_rooms'] ?? PHP_INT_MAX;

    $query = "
    SELECT * FROM bostad_tracker_apartments 
    WHERE 
        (rent BETWEEN ? AND ? OR rent IS NULL) 
        AND (num_rooms BETWEEN ? AND ? OR num_rooms IS NULL)
    ";

    if ($city_area) {
        $query .= " AND (city_area LIKE ? OR city_area IS NULL)";
    }

    if ($kommun) {
        $query .= " AND (kommun LIKE ? OR kommun IS NULL)";
    }

    $stmt = $conn->prepare($query);

    if ($city_area && $kommun) {
        $stmt->bind_param("iiiss", $min_rent, $max_rent, $min_rooms, $max_rooms, $city_area, $kommun);
    } elseif ($city_area) {
        $stmt->bind_param("iiis", $min_rent, $max_rent, $min_rooms, $max_rooms, $city_area);
    } elseif ($kommun) {
        $stmt->bind_param("iiis", $min_rent, $max_rent, $min_rooms, $max_rooms, $kommun);
    } else {
        $stmt->bind_param("iiii", $min_rent, $max_rent, $min_rooms, $max_rooms);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $apartments = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(["status" => "success", "apartments" => $apartments]);
}

?>
