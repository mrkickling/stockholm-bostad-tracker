<?php

function sendVerificationEmail($app_url, $email_address, $secret_code, $verification_code) {
    // Send a verification email to a user that includes the verification link
    
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

    $headers = array(
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=utf-8',
        'From' => 'support@joakimloxdal.se',
    );
    
    return mail(
        $email_address,
        "Stockholm Bostad Tracker - verifiera epostadress",
        $message,
        $headers
    );
}


function verifyEmail($conn, $secret_code, $verification_code) {     
    // Verify email for a user if verification code is valid
    $query = "UPDATE bostad_tracker_subscribers SET verified = 1 WHERE verification_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $verification_code);
    if (!$stmt->execute()) {
        die(json_encode(["error" => "Database update failed"]));
    }
    header("Location: configure.php?id=$secret_code");
    $stmt->close();
    $conn->close();
}

?>