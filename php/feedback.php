<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "railway_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = '';
$error_message = '';
$form_submitted = false;

// Fetch recent feedbacks
$recent_feedbacks_sql = "SELECT name, feedback_type, message, rating, DATE_FORMAT(submission_date, '%d %M %Y') as formatted_date 
                        FROM feedback 
                        ORDER BY submission_date DESC 
                        LIMIT 4";
$recent_feedbacks = $conn->query($recent_feedbacks_sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $feedback_type = $conn->real_escape_string($_POST['feedback_type']);
    $message = $conn->real_escape_string($_POST['message']);
    $rating = intval($_POST['rating']);

    $sql = "INSERT INTO feedback (name, email, feedback_type, message, rating, submission_date) 
            VALUES ('$name', '$email', '$feedback_type', '$message', $rating, NOW())";

    if ($conn->query($sql) === TRUE) {
        $success_message = "Thank you for your valuable feedback!";
        $form_submitted = true;
    } else {
        $error_message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - RailwayYatri</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
</head>
<body>
</body>
</html>