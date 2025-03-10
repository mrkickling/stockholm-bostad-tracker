<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $secret_code = bin2hex(random_bytes(16)); // Generate a unique secret code
    $default_filter = json_encode([
        "city_areas" => [],
        "kommuns" => ["Stockholm"],
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
        // Email already exists, handle error (e.g., show a message or redirect)
        echo "Det finns redan en prenumeration med denna epostadress.";
        exit();
    }

    // Prepare the SQL statement to insert the new subscription
    $stmt = $conn->prepare("INSERT INTO bostad_tracker_subscribers (email, secret_code, filter) VALUES (?, ?, ?)");

    // Bind parameters
    $stmt->bind_param("sss", $email, $secret_code, $default_filter);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect to the configuration page after successful subscription
        header("Location: configure.php?id=$secret_code");
        exit();
    } else {
        echo "Error subscribing!";
    }
}
?>


<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stockholm Bostad Tracker - Subscribe</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #4CAF50;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
            margin: 10px 0;
            color: #555;
        }

        input[type="email"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .info {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .info h3 {
            color: #4CAF50;
        }

        .info p {
            font-size: 14px;
            color: #333;
        }

        .disclaimer {
            font-size: 14px;
            color: #777;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Stockholm Bostad Tracker - Prenumerera</h2>
    <form method="POST">
        <input type="email" name="email" required placeholder="Ange din epostadress">
        <button type="submit">Prenumerera</button>
    </form>

    <div class="info">
        <h3>Om Stockholm Bostad Tracker</h3>
        <p>Håll koll på bostadskön utan att aktivt gå in varje dag. Skriv upp dig för att få epost med nya lägenheter som matchar ditt filter.</p>
        <p>Projektet är öppet och fritt, se <a href="https://github.com/mrkickling/stockholm-bostad-tracker/">källkoden</a>.</p>

        <div class="disclaimer">
            <h4>Disclaimer</h4>
            <p>Detta är ett hobbyprojekt, så om det slutar funka så kan det vara för att jag råkat dra ut sladden.</p>
            <p>Mejla mig i så fall på <a href="mailto:loxdal@proton.me">loxdal@proton.me</a> så är jag tacksam.</p>
        </div>
    </div>
</div>

</body>
</html>
