<?php

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


function getNewMatchingApartments($conn, $subscriber): array {
    // Convert the comma-separated string into an array
    $cityAreasArray = array_filter(array_map('trim', explode(",", $subscriber['city_areas'])));

    // If no city areas are provided, handle NULL condition
    if (empty($cityAreasArray)) {
        $cityAreasArray = [null];
    }

    // Generate the correct number of placeholders (?, ?, ?)
    $placeholders = implode(',', array_fill(0, count($cityAreasArray), '?'));

    // Build the SQL query
    $query = "
        SELECT *
        FROM bostad_tracker_apartments a
        WHERE ( a.city_area IN ($placeholders) OR a.city_area IS NULL OR ? IS NULL )
        AND ( a.num_rooms IS NULL OR a.num_rooms >= ? )
        AND ( a.size_sqm IS NULL OR a.size_sqm >= ? )
        AND ( a.rent IS NULL OR a.rent <= ? )
        AND ( a.has_balcony OR ? = 0 )
        AND ( a.has_elevator OR ? = 0 )
        AND ( a.new_production OR ? = 0 )
        AND ( a.youth = 0 OR ? = 1 )
        AND ( a.student = 0 OR ? = 1 )
        AND ( a.senior = 0 OR ? = 1 )
        AND ( a.short_lease = 0 OR ? = 1 )
        AND ( a.regular = 0 OR ? = 1 )
        AND ( a.first_seen > ? )
    ";

    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Create types string for bind_param()
    $types = str_repeat('s', count($cityAreasArray)) . "siiiiiiiiiiis"; // 's' for each city area

    // Merge all values into one array
    $bindValues = array_merge($cityAreasArray, [$subscriber['city_areas']], [
        $subscriber['min_num_rooms'], 
        $subscriber['min_size_sqm'], 
        $subscriber['max_rent'], 
        $subscriber['require_balcony'], 
        $subscriber['require_elevator'], 
        $subscriber['require_new_production'], 
        $subscriber['include_youth'], 
        $subscriber['include_student'], 
        $subscriber['include_senior'], 
        $subscriber['include_short_lease'], 
        $subscriber['include_regular'],
        $subscriber['latest_notified']
    ]);

    // Bind parameters dynamically
    $stmt->bind_param($types, ...$bindValues);

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the matching apartments
    $apartments = [];
    while ($row = $result->fetch_assoc()) {
        $apartments[] = $row;
    }

    // Return results as JSON
    return $apartments;
}
