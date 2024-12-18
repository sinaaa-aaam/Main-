<?php
session_start();
require_once '../db/config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

// If there's no ID passed, stop the process
if (!$id) {
    die("Error: Missing health data ID.");
}

// Fetch the specific health record
$query = $db->prepare("SELECT * FROM health_metrics WHERE id = :id AND user_id = :user_id");
$query->execute([':id' => $id, ':user_id' => $user_id]);
$health_data = $query->fetch(PDO::FETCH_ASSOC);

// If no record is found, show an error
if (!$health_data) {
    die("Error: Health data not found.");
}

// Process form submission to update health data
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $steps = $_POST['steps'];
    $sleep = $_POST['sleep'];
    $heart_rate = $_POST['heart_rate'];
    $calories = $_POST['calories'];

    // Validation: Ensure positive values for metrics
    if ($steps < 0 || $sleep < 0 || $heart_rate < 0 || $calories < 0) {
        die("Error: Health metrics must be positive numbers.");
    }

    try {
        // Update the health record in the database
        $query = $db->prepare("
            UPDATE health_metrics 
            SET steps = :steps, sleep = :sleep, heart_rate = :heart_rate, calories = :calories
            WHERE id = :id AND user_id = :user_id
        ");
        $query->execute([
            ':steps' => $steps,
            ':sleep' => $sleep,
            ':heart_rate' => $heart_rate,
            ':calories' => $calories,
            ':id' => $id,
            ':user_id' => $user_id,
        ]);

        // Redirect to the dashboard after successful update
        header("Location: ../public/user_dashboard2.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Health Data</title>
    <link rel="stylesheet" href="../styles/update_userdata.css">
</head>
<body>
    <div class="container">
        <h2>Edit Health Data</h2>
        <form method="POST" action="">
            <input type="number" name="steps" placeholder="Steps" value="<?php echo htmlspecialchars($health_data['steps']); ?>" required>
            <input type="number" name="sleep" placeholder="Sleep (hours)" value="<?php echo htmlspecialchars($health_data['sleep']); ?>" required>
            <input type="number" name="heart_rate" placeholder="Heart Rate (bpm)" value="<?php echo htmlspecialchars($health_data['heart_rate']); ?>" required>
            <input type="number" name="calories" placeholder="Calories Burned" value="<?php echo htmlspecialchars($health_data['calories']); ?>" required>
            <button type="submit">Update</button>
        </form>
        <a href="../public/user_dashboard2.php" class="back-btn">Back to Dashboard</a>
    </div>
</body>
</html>
