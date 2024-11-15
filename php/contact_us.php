<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "railway_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$form_submitted = false;
$error_message = '';
$success_message = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // Store in database
    $sql = "INSERT INTO contact_messages (name, email, subject, message, submission_date) 
            VALUES ('$name', '$email', '$subject', '$message', NOW())";
    
    $db_success = $conn->query($sql);
    $web3forms_api_key = '4cb2dba0-585b-4837-917d-4d10d82bf8c9'; 
    
    $api_url = 'https://api.web3forms.com/submit';
    
    $email_data = array(
        'access_key' => $web3forms_api_key,
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'from_name' => 'RailwayYatri Contact Form',
    );

    $options = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => 'Content-Type: application/json',
            'content' => json_encode($email_data)
        )
    );

    $context = stream_context_create($options);
    $api_response = @file_get_contents($api_url, false, $context);
    $api_result = json_decode($api_response, true);
    
    if ($db_success && $api_result['success'] === true) {
        $form_submitted = true;
        $success_message = "Message sent successfully! We'll get back to you soon...";
    } else {
        $error_message = "Error: Unable to process your request. Please try again later.";
        if (!$db_success) {
            error_log("Database Error: " . $conn->error);
        }
        if (!$api_result['success']) {
            error_log("Email API Error: " . json_encode($api_result));
        }
    }
}
?>
