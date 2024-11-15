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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - RailwayYatri</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="./css/contactus.css">
    <link rel="stylesheet" href="./css/common.css">
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>
</head>

<body>
    <nav>
        <div class="logo" data-aos="fade-down" data-aos-duration="1000">
            RailwayYatri
        </div>
        <div class="links">
            <a href="./index.html" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="100">Home</a>
            <a href="booktickets.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="200">Book
                Tickets</a>
            <a href="findtrains.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="300">Find Trains</a>
            <a href="pnrstatus.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="400">PNR Status</a>
            <a href="feedback.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="500">Feedback</a>
            <a href="contactus.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="600">Contact Us</a>
        </div>
        <div class="buttons" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="700">
            <div id="authButtons">
                <button onclick="login()" id="loginBtn">Login</button>
                <button onclick="signup()" id="signupBtn">Sign up</button>
            </div>
            <div class="user-profile" id="userProfile">
                <img id="userPhoto" src="" alt="Profile">
                <span id="userName"></span>
                <div class="dropdown-menu">
                    <button onclick="signOut()">Sign Out</button>
                </div>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="contact-container" data-aos="fade-up" data-aos-duration="1200">
            <h2 class="section-title">Contact Us</h2>

            <?php if ($form_submitted): ?>
            <div class="success-container">
                <div class="success-message">
                    <?php echo $success_message; ?>
                </div>
                <div class="checkmark">
                    ✓
                </div>
                <p>
                    Thank you for contacting us. Our team will respond to your inquiry as soon as possible.
                </p>
                <a href="index.html" class="home-button">
                    Return to Home
                </a>
            </div>
            <?php else: ?>
            <?php if ($error_message): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>

            
            <?php endif; ?>
        </div>
    </div>

    