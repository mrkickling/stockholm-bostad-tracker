<?php
require 'includes/db.php';
require 'includes/subscriber.php';

$env = parse_ini_file(filename: '.env');
$app_url = $env['APP_URL'];

if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $error = subscribe($conn, $email, $app_url);
}

?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stockholm Bostad Tracker - Subscribe</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<img class="logo" src="assets/logo.png" alt="">

<div class="container">
    <h2>Stockholm Bostad Tracker - Prenumerera på bostadskön</h2>

    <?php if (isset($error)) {echo "<p style='color: red;'>$error</p>";} ?>

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
