<?php
require_once "includes/db.php";  // Database connection

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

    function insertApartment($conn, $apartment) {
        $stmt = $conn->prepare("
            SELECT internal_id FROM bostad_tracker_apartments 
            WHERE internal_id = ?
        ");
        $stmt->bind_param("s", $apartment['internal_id']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            return ["status" => "error", "message" => "Apartment already exists.", "internal_id" => $apartment['internal_id']];
        }

        $stmt = $conn->prepare("
            INSERT INTO bostad_tracker_apartments (
                internal_id, city_area, address, kommun, floor, num_rooms, size_sqm, rent, url,
                has_balcony, has_elevator, new_production, youth, student, senior,
                short_lease, regular, apartment_type, published_date, last_date,
                latitude, longitude
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            return ["status" => "error", "message" => "SQL prepare failed: " . $conn->error];
        }

        $stmt->bind_param(
            "ssssiiddsddddddddsssdd",
            $apartment['internal_id'],
            $apartment['city_area'],
            $apartment['address'],
            $apartment['kommun'],
            $apartment['floor'],
            $apartment['num_rooms'],
            $apartment['size_sqm'],
            $apartment['rent'],
            $apartment['url'],
            $apartment['has_balcony'],
            $apartment['has_elevator'],
            $apartment['new_production'],
            $apartment['youth'],
            $apartment['student'],
            $apartment['senior'],
            $apartment['short_lease'],
            $apartment['regular'],
            $apartment['apartment_type'],
            $apartment['published_date'],
            $apartment['last_date'],
            $apartment['latitude'],
            $apartment['longitude']
        );

        if (!$stmt->execute()) {
            return ["status" => "error", "message" => "Insertion failed: " . $stmt->error, "internal_id" => $apartment['internal_id']];
        }

        return ["status" => "success", "message" => "Apartment inserted successfully.", "internal_id" => $apartment['internal_id']];
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

} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $city_area = isset($_GET['city_area']) ? $_GET['city_area'] : '';
    $kommun = isset($_GET['kommun']) ? $_GET['kommun'] : '';
    $min_rent = isset($_GET['min_rent']) ? $_GET['min_rent'] : 0;
    $max_rent = isset($_GET['max_rent']) ? $_GET['max_rent'] : PHP_INT_MAX;
    $min_rooms = isset($_GET['min_rooms']) ? $_GET['min_rooms'] : 0;
    $max_rooms = isset($_GET['max_rooms']) ? $_GET['max_rooms'] : PHP_INT_MAX;

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

    echo json_encode(value: ["status" => "success", "apartments" => $apartments]);
}
?>
