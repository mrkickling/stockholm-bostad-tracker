<?php
require 'includes/db.php';
require 'includes/subscriber.php';

// if (!isset($_GET['id'])) {
//     header('Location: index.php');
// }

$secret_code = $_GET['id'];

// Handle email verification
if (isset($_GET['verification_code'])) {
    $verification_code = $_GET['verification_code'];
    verifyEmail($conn, $secret_code, $verification_code);
}

// Handle unsubscribing
if (isset($_POST['unsubscribe'])) {
    unsubscribe($conn, $secret_code);
}

$user = getUser($conn, $secret_code);
$current_filter = json_decode($user['filter'], true);

// Handle when user makes change to their filter
if (isset($_POST['filter'])) {
    updateFilter($conn, $user, $secret_code, $_POST);
}


?>

<!DOCTYPE html>
<html lang="sv">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Stockholm bostad tracker - Redigera filter</title>
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>

        <a class="header-link" href="index.php">
            <img class="logo-img" src="assets/logo.png" alt="">
        </a>

<div class="container">
    <h2>Redigera filter</h2>

    <center>
        <?= htmlspecialchars($user['email']); ?>
        <?php if (!$user['verified']): ?>
            <span style="color: red;">- Overifierad epost - kolla din inkorg</span>
        <?php endif; ?>
    </center>

    <form method="POST">
        <div class="form-group">
            <label>Minst antal rum:</label>
            <input type="number" name="filter[min_num_rooms]" value="<?= htmlspecialchars($current_filter['min_num_rooms'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Minst storlek (kvm):</label>
            <input type="number" name="filter[min_size_sqm]" value="<?= htmlspecialchars($current_filter['min_size_sqm'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Max hyra:</label>
            <input type="number" name="filter[max_rent]" value="<?= htmlspecialchars($current_filter['max_rent'] ?? '') ?>" required>
        </div>

        <!-- Checkbox fields -->
        <div class="form-group">
            <label>Visa bara lägenheter med balkong:</label>
            <input type="checkbox" name="filter[require_balcony]" <?= isset($current_filter['require_balcony']) && $current_filter['require_balcony'] ? 'checked' : '' ?>>
        </div>

        <div class="form-group">
            <label>Visa bara lägenheter med hiss:</label>
            <input type="checkbox" name="filter[require_elevator]" <?= isset($current_filter['require_elevator']) && $current_filter['require_elevator'] ? 'checked' : '' ?>>
        </div>

        <div class="form-group">
            <label>Visa bara nyproduktion:</label>
            <input type="checkbox" name="filter[require_new_production]" <?= isset($current_filter['require_new_production']) && $current_filter['require_new_production'] ? 'checked' : '' ?>>
        </div>

        <div class="form-group">
            <label>Inkludera ungdomslägenhet:</label>
            <input type="checkbox" name="filter[include_youth]" <?= isset($current_filter['include_youth']) && $current_filter['include_youth'] ? 'checked' : '' ?>>
        </div>

        <div class="form-group">
            <label>Inkludera vanliga lägenheter:</label>
            <input type="checkbox" name="filter[include_regular]" <?= isset($current_filter['include_regular']) && $current_filter['include_regular'] ? 'checked' : '' ?>>
        </div>

        <div class="form-group">
            <label>Inkludera studentlägenhet:</label>
            <input type="checkbox" name="filter[include_student]" <?= isset($current_filter['include_student']) && $current_filter['include_student'] ? 'checked' : '' ?>>
        </div>

        <div class="form-group">
            <label>Inkludera seniorlägenhet:</label>
            <input type="checkbox" name="filter[include_senior]" <?= isset($current_filter['include_senior']) && $current_filter['include_senior'] ? 'checked' : '' ?>>
        </div>

        <div class="form-group">
            <label>Inkludera korttid:</label>
            <input type="checkbox" name="filter[include_short_lease]" <?= isset($current_filter['include_short_lease']) && $current_filter['include_short_lease'] ? 'checked' : '' ?>>
        </div>

        <div class="form-group">
            <label>Stadsdelar:</label>
            <input
                type="text"
                name="filter[city_areas]"
                value="<?= isset($current_filter['city_areas']) ? htmlspecialchars(implode(", ", (array) $current_filter['city_areas'])) : ''; ?>"
                placeholder="Komma-separerad lista"
            >
            <p class="info-text">Ange stadsdelar separerade med kommatecken (t.ex. Södermalm,Hägersten)</p>
        </div>

        <div class="form-group">
            <label>Kommuner:</label>
            <input
                type="text"
                name="filter[kommuns]"
                value="<?= isset($current_filter['kommuns']) ? htmlspecialchars(implode(", ", (array) $current_filter['kommuns'])) : ''; ?>"
                placeholder="Komma-separerad lista"
            >
            <p class="info-text">Ange kommuner separerade med kommatecken (t.ex. Stockholm,Huddinge)</p>
        </div>

        <button type="submit">Spara</button>
    </form>

    <!-- Unsubscribe button -->
    <form method="POST" style="margin-top: 20px;">
        <button type="submit" name="unsubscribe" class="unsubscribe-btn">Avprenumerera</button>
    </form>
</div>

</body>
</html>
