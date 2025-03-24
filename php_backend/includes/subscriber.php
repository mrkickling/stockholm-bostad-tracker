<?php

require "email_verification.php";

function updateFilter($conn, $user, $secret_code, $post_body) {
    // Prepare the update query with all necessary fields
    $stmt = $conn->prepare(
        "UPDATE bostad_tracker_subscribers 
         SET 
            min_num_rooms = ?, 
            min_size_sqm = ?, 
            max_rent = ?, 
            require_balcony = ?, 
            require_elevator = ?, 
            require_new_production = ?, 
            include_youth = ?, 
            include_regular = ?, 
            include_student = ?, 
            include_senior = ?, 
            include_short_lease = ?, 
            city_areas = ?, 
            frequency = ?
         WHERE secret_code = ?"
    );

    // Prepare values from post_body and handle defaults where necessary
    $min_num_rooms = isset($post_body['min_num_rooms']) && is_numeric($post_body['min_num_rooms']) ? (float)$post_body['min_num_rooms'] : null;
    $min_size_sqm = isset($post_body['min_size_sqm']) && is_numeric($post_body['min_size_sqm']) ? (float)$post_body['min_size_sqm'] : null;
    $max_rent = isset($post_body['max_rent']) && is_numeric($post_body['max_rent']) ? (float)$post_body['max_rent'] : null;

    $require_balcony = isset($post_body['require_balcony']) && $post_body['require_balcony'] === 'on';
    $require_elevator = isset($post_body['require_elevator']) && $post_body['require_elevator'] === 'on';
    $require_new_production = isset($post_body['require_new_production']) && $post_body['require_new_production'] === 'on';

    $include_youth = isset($post_body['include_youth']) && $post_body['include_youth'] === 'on';
    $include_regular = isset($post_body['include_regular']) && $post_body['include_regular'] === 'on';
    $include_student = isset($post_body['include_student']) && $post_body['include_student'] === 'on';
    $include_senior = isset($post_body['include_senior']) && $post_body['include_senior'] === 'on';
    $include_short_lease = isset($post_body['include_short_lease']) && $post_body['include_short_lease'] === 'on';

    // Process city areas and kommuns (multi-select)
    $city_areas = isset($post_body['city_areas']) ? implode(',', array_map('trim', $post_body['city_areas'])) : null;

    // Default frequency if not provided
    $frequency = isset($post_body['frequency']) ? $post_body['frequency'] : 'daily';

    // Bind the parameters
    $stmt->bind_param(
        "dddddddddddsss",
        $min_num_rooms, 
        $min_size_sqm, 
        $max_rent, 
        $require_balcony, 
        $require_elevator, 
        $require_new_production, 
        $include_youth, 
        $include_regular, 
        $include_student, 
        $include_senior, 
        $include_short_lease, 
        $city_areas, 
        $frequency, 
        $secret_code
    );

    // Execute the update
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
        "SELECT * FROM bostad_tracker_subscribers WHERE secret_code = ?"
    );
    $stmt->bind_param("s", $secret_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        die("User not found.");
    }

    // You can process and use these individual filter columns as needed
    // Example: decoding comma-separated values or handling NULLs

    return $user;
}

function getUserByEmail($conn, $email) {
    // Get user with given secret code
    $stmt = $conn->prepare(
        "SELECT * FROM bostad_tracker_subscribers WHERE email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        die("User not found.");
    }

    return $user;
}

function subscribe($conn, $email, $app_url) {
    $error = false;
    $secret_code = bin2hex(random_bytes(16));
    $verification_code = bin2hex(random_bytes(16));

    // Standardvärden vid registrering
    $frequency = 'daily';
    $verified = 0;
    $default_city_areas = '';
    $default_kommuns = '';
    $default_min_num_rooms = 1;
    $default_min_size_sqm = 20;
    $default_max_rent = 10000;
    $default_require_balcony = false;
    $default_require_elevator = false;
    $default_require_new_production = false;
    $default_include_youth = true;
    $default_include_regular = true;
    $default_include_student = true;
    $default_include_senior = true;
    $default_include_short_lease = true;

    // Kolla om e-post redan finns
    $stmt = $conn->prepare("SELECT email FROM bostad_tracker_subscribers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Epostadressen är redan registrerad.";
    } else {
        // Infoga ny prenumerant
        $stmt = $conn->prepare("
            INSERT INTO bostad_tracker_subscribers (
                email, secret_code, verification_code, frequency, verified,
                city_areas, kommuns, min_num_rooms, min_size_sqm, max_rent,
                require_balcony, require_elevator, require_new_production,
                include_youth, include_regular, include_student, include_senior, include_short_lease
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssissiiiiiiiiiii",
            $email,
            $secret_code,
            $verification_code,
            $frequency,
            $verified,
            $default_city_areas,
            $default_kommuns,
            $default_min_num_rooms,
            $default_min_size_sqm,
            $default_max_rent,
            $default_require_balcony,
            $default_require_elevator,
            $default_require_new_production,
            $default_include_youth,
            $default_include_regular,
            $default_include_student,
            $default_include_senior,
            $default_include_short_lease
        );

        if ($stmt->execute()) {
            $sent_email = sendVerificationEmail(
                app_url: $app_url,
                email_address: $email,
                secret_code: $secret_code,
                verification_code: $verification_code
            );
            $sent_email = true;
            if ($sent_email) {
                header("Location: configure.php?id=$secret_code");
                exit;
            } else {
                $error = "Epost kunde inte skickas. Försök igen.";
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

function generateNotificationEmail($subscriber, $apartments) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Nya lägenheter</title>
        <style>
            * {
                box-sizing: border-box;
            }
            body {
                font-family: Arial, sans-serif;
            }
            .container {
                width: 600px;
                margin: auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            .header {
                background-color: #007bff;
                color: white;
                padding: 10px;
                text-align: center;
                font-size: 20px;
                font-weight: bold;
            }
            .apartment {
                border-bottom: 1px solid #ddd;
                padding: 10px 0;
            }
            .apartment:last-child {
                border-bottom: none;
            }
            .footer {
                margin-top: 20px;
                font-size: 12px;
                color: #777;
                text-align: center;
            }
            .tablet {
                background: lightgrey;
                border-radius: 10px;
                padding: 5px;
                font-size: 12px;
                display: inline;
            }
            h3 {
                font-size: 20px;
                margin: 0;
                padding: 0;
                margin-bottom: 5px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">Stockholm Bostad Tracker</div>
            <p>Hej <?= htmlspecialchars($subscriber['email']) ?>,</p>
            <p>Här är nya lägenheter som matchar ditt filter:</p>
            
            <?php foreach ($apartments as $apartment): ?>
                <div class="apartment">
                    <h3>
                        <?= htmlspecialchars(ucfirst($apartment['address'])) ?>
        
                        <?php if ($apartment['youth']): ?>
                            <div class="tablet">Ungdom</div>
                        <?php endif; ?>

                        <?php if ($apartment['senior']): ?>
                            <div class="tablet">Senior</div>
                        <?php endif; ?>

                        <?php if ($apartment['student']): ?>
                            <div class="tablet">Student</div>
                        <?php endif; ?>                
                    </h3>

                    <?= $apartment['size_sqm'] ?? '?' ?> kvm
                    -
                    <?= htmlspecialchars(ucfirst($apartment['city_area'])) ?>
                    -
                    <?= $apartment['rent'] ?? '?' ?> kr / må
                    <a href="<?= htmlspecialchars($apartment['url']) ?>">Läs mer</a>
                </div>
            <?php endforeach; ?>

            <div class="footer">
                <a href="<?= htmlspecialchars($subscriber['url']) ?>">Avprenumerera</a>
                -
                <a href="<?= htmlspecialchars($subscriber['url']) ?>">Redigera filter</a>
            </div>
        </div>
    </body>
    </html>
    <?php

    return ob_get_clean();

}

function sendNotificationEmail($subscriber, $apartments) {
    $email_content = generateNotificationEmail($subscriber, $apartments);
    $headers = array(
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=utf-8',
        'From' => 'support@joakimloxdal.se',
    );
    return mail(
        $subscriber['email'],
        "Stockholm Bostad Tracker - nya lägenheter",
        $email_content,
        $headers
    );
}
