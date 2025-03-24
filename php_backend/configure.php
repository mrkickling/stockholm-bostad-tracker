<?php
require 'includes/db.php';
require 'includes/subscriber.php';
require 'includes/kommuner_och_stadsdelar.php';

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
$user_city_areas = explode(",", $user['city_areas'] ?? '');

// Handle when user makes change to their filter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    updateFilter($conn, $user, $secret_code, $_POST);
    $user = getUser($conn, $secret_code); // Refresh user data
}
?>

<!DOCTYPE html>
<html lang="sv">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Stockholm bostad tracker - Redigera filter</title>
        <link rel="stylesheet" href="assets/style.css">

        <!-- Add the CSS and JS for Choices.js -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

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
                <?php else: ?>
                    <span style="color: green;">Verifierad epost!</span>
                <?php endif; ?>
            </center>

            <form method="POST">
                <div class="form-group">
                    <label>Minst antal rum:</label>
                    <input type="number" name="min_num_rooms" value="<?= htmlspecialchars($user['min_num_rooms'] ?? 0) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Minst storlek (kvm):</label>
                    <input type="number" name="min_size_sqm" value="<?= htmlspecialchars($user['min_size_sqm'] ?? 0) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Max hyra:</label>
                    <input type="number" name="max_rent" value="<?= htmlspecialchars($user['max_rent'] ?? '') ?>" required>
                </div>

                <?php
                $checkbox_fields = [
                    'require_balcony' => 'Visa bara lägenheter med balkong',
                    'require_elevator' => 'Visa bara lägenheter med hiss',
                    'require_new_production' => 'Visa bara nyproduktion',
                    'include_youth' => 'Inkludera ungdomslägenhet',
                    'include_student' => 'Inkludera studentlägenhet',
                    'include_senior' => 'Inkludera seniorlägenhet',
                    'include_short_lease' => 'Inkludera korttid',
                    'include_regular' => 'Inkludera vanliga lägenheter'
                ];

                foreach ($checkbox_fields as $field => $label) {
                    echo "<div class='form-group'>
                            <label>$label:</label>
                            <input type='checkbox' name='$field' " . ($user[$field] ? 'checked' : '') . ">
                        </div>";
                }
                ?>

                <div class="form-group">
                    <label>Stadsdelar (lämna tomt om du inte vill filtrera på stadsdelar):</label>
                    <select name="city_areas[]" multiple id="city_areas">
                        <?php foreach ($stadsdelar as $stadsdel): ?>
                            <option value="<?= htmlspecialchars($stadsdel) ?>" <?= in_array($stadsdel, $user_city_areas) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($stadsdel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <script>
                    // Initialize Choices.js
                    new Choices('#city_areas', {
                        removeItemButton: true,
                        placeholderValue: 'Välj stadsdelar',
                        searchResultLimit: 5,
                        renderSelectedChoices: 'always'
                    });
                </script>

                <button type="submit">Spara</button>
            </form>

            <form method="POST" style="margin-top: 20px;">
                <button type="submit" name="unsubscribe" class="unsubscribe-btn">Avprenumerera</button>
            </form>
        </div>
    </body>

    <style>
        .choices__list--multiple .choices__item {
            background-color: rgb(50, 122, 163);
            font-size: 18px;
        }
    </style>

</html>
