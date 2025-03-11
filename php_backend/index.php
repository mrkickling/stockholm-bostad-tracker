<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db.php';

function sendVerificationEmail($email_address, $secret_code, $verification_code) {
    $env = parse_ini_file('.env');
    $app_url = $env['APP_URL'];
    
    $verification_url = "$app_url/configure.php?id=$secret_code&verification_code=$verification_code";
    $message = '
    <html>
    <head>
        <title>Stockholm bostad tracker - verifiera din epost</title>
    </head>
    <body>
        Hej '.$email_address.',<br><br>

        du har anmält din epostadress till <a href='.$app_url.'>Stockholm bostad tracker</a>.<br>
        Det innebär att du kommer få mejl när lägenheter som matchar ditt filter blir tillgängliga i Stockholms bostadskö.<br><br>

        <a href='.$verification_url.'>Tryck här för att verifiera din epostadress</a> eller kopiera denna url till din webbläsare:<br>
        '.$verification_url.'<br><br>

        Om du inte begärde detta e-postmeddelande så ska du inte klicka på länken, utan bara ignorera det här mejlet.<br><br>

        Vänliga hälsningar,<br>
        Stockholm bostad tracker<br>
    </body>
    </html>
    ';

    // To send HTML mail, the Content-type header must be set
    $headers = array(
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=utf-8',
        'From' => 'support@joakimloxdal.se',
    );
    
    mail(
        $email_address,
        "Stockholm Bostad Tracker - verifiera din epostadress",
        $message,
        $headers,
    );
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $secret_code = bin2hex(random_bytes(16)); // Generate a unique secret code
    $verification_code = bin2hex(random_bytes(16)); // Generate a unique verification code
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
        $error = "Kunde ej registrera din epost.";
    }

    if (!isset($error)) {
        // Prepare the SQL statement to insert the new subscription
        $stmt = $conn->prepare(
            "INSERT INTO bostad_tracker_subscribers (email, secret_code, verification_code, filter) VALUES (?, ?, ?, ?)"
        );

        // Bind parameters
        $stmt->bind_param("ssss", $email, $secret_code, $verification_code, $default_filter);
        // Execute the query
        if ($stmt->execute()) {
            // Redirect to the configuration page after successful subscription

            // Send verification email
            sendVerificationEmail($email, $secret_code, $verification_code);

            // Send user to their page
            header("Location: configure.php?id=$secret_code");
            exit();
        } else {
            echo $stmt->error;
            $error = "Lyckades inte registrera dig.";
        }
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
            margin: auto;
            margin-top: 20px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #0568a1;
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
            background-color:rgb(50, 122, 163);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0568a1;
        }

        .info {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .info h3 {
            color:rgb(42, 117, 160);
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

        .logo {
            width: 100%;
            max-width: 800px;
            margin: auto;
            text-align: center;
        }

        .logo img {
            max-width: 300px;
            margin-top: 20px;
        }

    </style>
</head>
<body>

<div class="logo">
    <img src="assets/logo.png" alt="">
</div>

<div class="container">
    <h2>Stockholm Bostad Tracker - Prenumerera på bostadskön</h2>

    <?php if (isset($error)) {echo $error;} ?>

    <div class="info">
        <h3 style='margin-top:0'>Om Stockholm Bostad Tracker</h3>
        <p>
            Håll koll på <a href='https://bostad.stockholm.se/'>bostadskön</a> utan att aktivt gå in varje dag.
            Du kommer få ett mejl varje dag det finns nya lägenheter som matchar ditt filter.
            Din epostadress kommer inte delas med någon annan. Du kan när som helst avregistrera dig.
        </p>

        <p>
            Projektet är öppet och fritt, se <a href="https://github.com/mrkickling/stockholm-bostad-tracker/">källkoden</a>.
            Detta är ett hobbyprojekt, så om det slutar funka så kan det vara för att jag råkat dra ut sladden.
            Mejla mig i så fall på <a href="mailto:loxdal@proton.me">loxdal@proton.me</a> så är jag tacksam.
        </p>
    </div>
    <br>
    <form method="POST">
        <input type="email" name="email" required placeholder="Ange din epostadress">
        <button type="submit">Prenumerera</button>
    </form>

</div>

</body>
</html>
