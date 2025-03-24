<?php

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

    // Prepare the data for binding
    $old_times = "2025-03-24 19:14:57";

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
        $old_times
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
