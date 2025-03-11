<?php

require "email_verification.php";

function updateFilter($conn, $user, $secret_code, $post_body) {
    // Update user with new filter from a post request
    $new_filter = json_decode($user['filter'], true) ?? [];

    // Explicitly check and set each field in the filter
    // Room count
    if (isset($post_body['filter']['min_num_rooms']) && is_numeric($post_body['filter']['min_num_rooms'])) {
        $new_filter['min_num_rooms'] = (float)$post_body['filter']['min_num_rooms'];
    }

    // Size in sqm
    if (isset($post_body['filter']['min_size_sqm']) && is_numeric($post_body['filter']['min_size_sqm'])) {
        $new_filter['min_size_sqm'] = (float)$post_body['filter']['min_size_sqm'];
    }

    // Max rent
    if (isset($post_body['filter']['max_rent']) && is_numeric($post_body['filter']['max_rent'])) {
        $new_filter['max_rent'] = (float)$post_body['filter']['max_rent'];
    }

    // Require balcony checkbox
    $new_filter['require_balcony'] = isset($post_body['filter']['require_balcony']) && $post_body['filter']['require_balcony'] === 'on';

    // Require elevator checkbox
    $new_filter['require_elevator'] = isset($post_body['filter']['require_elevator']) && $post_body['filter']['require_elevator'] === 'on';

    // Require new production checkbox
    $new_filter['require_new_production'] = isset($post_body['filter']['require_new_production']) && $post_body['filter']['require_new_production'] === 'on';

    // Include youth checkbox
    $new_filter['include_youth'] = isset($post_body['filter']['include_youth']) && $post_body['filter']['include_youth'] === 'on';

    // Include regular checkbox
    $new_filter['include_regular'] = isset($post_body['filter']['include_regular']) && $post_body['filter']['include_regular'] === 'on';

    // Include student checkbox
    $new_filter['include_student'] = isset($post_body['filter']['include_student']) && $post_body['filter']['include_student'] === 'on';

    // Include senior checkbox
    $new_filter['include_senior'] = isset($post_body['filter']['include_senior']) && $post_body['filter']['include_senior'] === 'on';

    // Include short lease checkbox
    $new_filter['include_short_lease'] = isset($post_body['filter']['include_short_lease']) && $post_body['filter']['include_short_lease'] === 'on';

    // City areas
    if (isset($post_body['filter']['city_areas'])) {
        // Split by comma, trim each element, and filter out any empty strings
        $new_filter['city_areas'] = array_filter(array_map('trim', explode(',', $post_body['filter']['city_areas'])), function($value) {
            return !empty($value);  // Keep only non-empty values
        });
    }

    // Kommuner
    if (isset($post_body['filter']['kommuns'])) {
        // Split by comma, trim each element, and filter out any empty strings
        $new_filter['kommuns'] = array_filter(array_map('trim', explode(',', $post_body['filter']['kommuns'])), function($value) {
            return !empty($value);  // Keep only non-empty values
        });
    }

    // Frequency
    $frequency = isset($post_body['frequency']) ? $post_body['frequency'] : 'daily';
    
    
    $stmt = $conn->prepare("UPDATE bostad_tracker_subscribers SET filter = ?, frequency = ? WHERE secret_code = ?");
    $encoded_filter = json_encode($new_filter);
    $stmt->bind_param("sss", $encoded_filter, $frequency, $secret_code);

    if ($stmt->execute()) {
        header("Location: configure.php?id=" . $secret_code);
        exit();
    } else {
        echo "Error updating filter: " . $stmt->error;
    }
}


function getUser($conn, $secret_code) {
    // Get user with given secret code
    $stmt = $conn->prepare(
        "SELECT email, filter, frequency, verified FROM bostad_tracker_subscribers WHERE secret_code = ?"
    );
    $stmt->bind_param("s", $secret_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        die("User not found.");
    }
    
    // $current_filter = json_decode($user['filter'], true);
    return $user;
}


function setLatestNotified($conn, $subscriber_email) {
    // Mark user with email given as notified NOW
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
}


function subscribe($conn, $email, $app_url) {
    // Register a user to stockgolm bostad tracker
    $error = false;
    $secret_code = bin2hex(random_bytes(16)); // Generate a unique secret code
    $verification_code = bin2hex(random_bytes(16)); // Generate a unique verification code
    $default_filter = json_encode([
        "city_areas" => [],
        "kommuns" => [],
        "min_num_rooms" => 1,
        "min_size_sqm" => 20,
        "max_rent" => 10000,
        "require_balcony" => false,
        "require_elevator" => false,
        "require_new_production" => false,
        "include_youth" => true,
        "include_regular" => true,
        "include_student" => true,
        "include_senior" => true,
        "include_short_lease" => true
    ]);

    // Check if the email already exists in the database
    $stmt = $conn->prepare("SELECT email FROM bostad_tracker_subscribers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Epostadressen är redan registrerad.";

    } else {
        $stmt = $conn->prepare(
            "INSERT INTO bostad_tracker_subscribers (email, secret_code, verification_code, filter) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $email, $secret_code, $verification_code, $default_filter);

        if ($stmt->execute()) {
            $sent_email = sendVerificationEmail(
                app_url: $app_url,
                email_address: $email,
                secret_code: $secret_code,
                verification_code: $verification_code
            );

            if ($sent_email == true) {
                header(header: "Location: configure.php?id=$secret_code");
            } else {
                $error = error_get_last()['message'];
            }
        } else {
            $error = "Lyckades inte registrera din epost. Kontakta support@joakimloxdal.se om du vill ha hjälp.";
        }
    }

    return $error;
}


function unsubscribe($conn, $secret_code) {
    // Unsubscribe a user using their secret code
    $stmt = $conn->prepare("DELETE FROM bostad_tracker_subscribers WHERE secret_code = ?");
    $stmt->bind_param("s", $secret_code);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error unsubscribing";
    }
}
