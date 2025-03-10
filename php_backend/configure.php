<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require 'db.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$secret_code = $_GET['id'];

// Prepare the SQL query to get the user's details
$stmt = $conn->prepare("SELECT email, filter, frequency FROM bostad_tracker_subscribers WHERE secret_code = ?");
$stmt->bind_param("s", $secret_code);  // Bind the parameter
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Handle unsubscribing
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['unsubscribe'])) {
    // Prepare the delete query
    $stmt = $conn->prepare("DELETE FROM bostad_tracker_subscribers WHERE secret_code = ?");
    $stmt->bind_param("s", $secret_code);  // Bind the parameter

    if ($stmt->execute()) {
        // Redirect to a confirmation page or homepage after successful unsubscription
        header("Location: index.php");
        exit();
    } else {
        // Display error message if query fails
        echo "Error unsubscribing: " . $stmt->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['filter'])) {

    // Get the current filter from the database
    $new_filter = json_decode($user['filter'], true); // Get current filter values

    // Explicitly check and set each field in the filter

    // Room count
    if (isset($_POST['filter']['min_num_rooms']) && is_numeric($_POST['filter']['min_num_rooms'])) {
        $new_filter['min_num_rooms'] = (float)$_POST['filter']['min_num_rooms'];
    }

    // Size in sqm
    if (isset($_POST['filter']['min_size_sqm']) && is_numeric($_POST['filter']['min_size_sqm'])) {
        $new_filter['min_size_sqm'] = (float)$_POST['filter']['min_size_sqm'];
    }

    // Max rent
    if (isset($_POST['filter']['max_rent']) && is_numeric($_POST['filter']['max_rent'])) {
        $new_filter['max_rent'] = (float)$_POST['filter']['max_rent'];
    }

    // Require balcony checkbox
    $new_filter['require_balcony'] = isset($_POST['filter']['require_balcony']) && $_POST['filter']['require_balcony'] === 'on';

    // Require elevator checkbox
    $new_filter['require_elevator'] = isset($_POST['filter']['require_elevator']) && $_POST['filter']['require_elevator'] === 'on';

    // Require new production checkbox
    $new_filter['require_new_production'] = isset($_POST['filter']['require_new_production']) && $_POST['filter']['require_new_production'] === 'on';

    // Include youth checkbox
    $new_filter['include_youth'] = isset($_POST['filter']['include_youth']) && $_POST['filter']['include_youth'] === 'on';

    // Include regular checkbox
    $new_filter['include_regular'] = isset($_POST['filter']['include_regular']) && $_POST['filter']['include_regular'] === 'on';

    // Include student checkbox
    $new_filter['include_student'] = isset($_POST['filter']['include_student']) && $_POST['filter']['include_student'] === 'on';

    // Include senior checkbox
    $new_filter['include_senior'] = isset($_POST['filter']['include_senior']) && $_POST['filter']['include_senior'] === 'on';

    // Include short lease checkbox
    $new_filter['include_short_lease'] = isset($_POST['filter']['include_short_lease']) && $_POST['filter']['include_short_lease'] === 'on';

    // City areas
    if (isset($_POST['filter']['city_areas'])) {
        // Split by comma, trim each element, and filter out any empty strings
        $new_filter['city_areas'] = array_filter(array_map('trim', explode(',', $_POST['filter']['city_areas'])), function($value) {
            return !empty($value);  // Keep only non-empty values
        });
    }

    // Kommuner
    if (isset($_POST['filter']['kommuns'])) {
        // Split by comma, trim each element, and filter out any empty strings
        $new_filter['kommuns'] = array_filter(array_map('trim', explode(',', $_POST['filter']['kommuns'])), function($value) {
            return !empty($value);  // Keep only non-empty values
        });
    }

    // Frequency
    $frequency = isset($_POST['frequency']) ? $_POST['frequency'] : 'daily';

    // Prepare the update query
    $stmt = $conn->prepare("UPDATE bostad_tracker_subscribers SET filter = ?, frequency = ? WHERE secret_code = ?");
    
    // Encode filter data into JSON
    $encoded_filter = json_encode($new_filter);

    // Bind parameters
    $stmt->bind_param("sss", $encoded_filter, $frequency, $secret_code);  // Bind all parameters
    
    if ($stmt->execute()) {
        // Reload the page after successful update
        header("Location: configure.php?id=" . $secret_code);
        exit();
    } else {
        // Display error message if query fails
        echo "Error updating filter: " . $stmt->error;
    }
}

$current_filter = json_decode($user['filter'], true);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stockholm bostad tracker - Redigera filter</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 800px;
            margin: 10px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #4CAF50;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="number"],
        input[type="text"],
        input[type="email"],
        input[type="checkbox"],
        select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        input[type="checkbox"] {
            width: auto;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .info-text {
            font-size: 12px;
            color: #777;
            margin-top: -10px;
        }

        .unsubscribe-btn {
            background-color: #f44336;
            margin-top: 20px;
        }

        .unsubscribe-btn:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Stockholm bostad tracker - Redigera filter</h2>

    <center>
        <?= htmlspecialchars($user['email']); ?>
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

        <!-- Frequency selection -->
        <div class="form-group">
            <label>Uppdateringsfrekvens:</label>
            <select name="frequency">
                <option value="daily" <?= $user['frequency'] == 'daily' ? 'selected' : '' ?>>Dagligen</option>
                <option value="weekly" <?= $user['frequency'] == 'weekly' ? 'selected' : '' ?>>Veckovis</option>
            </select>
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
