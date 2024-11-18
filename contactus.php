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
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
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
    <link rel="icon" href="logo.jpg" />
    <title>Contact Us</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="./css/contactus.css">
    <link rel="stylesheet" href="./css/common.css">
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>
</head>

<body>
    <nav>
        <div class="logo" data-aos="fade-down" data-aos-duration="1000">
        <a href="./index.html">RailYatri</a>
        </div>
        <div class="links">
            <a href="./index.html">Home</a>
            <a href="booktickets.php">Book Tickets</a>
            <a href="findtrains.php">Find Trains</a>
            <a href="pnrstatus.php">PNR Status</a>
            <a href="feedback.php">Feedback</a>
            <a href="packages.php">Travel Packages</a>
            <a href="contactus.php">Contact Us</a>
        </div>
        <div class="buttons" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="700">
            <div id="authButtons">
                <button id="loginBtn" onclick="window.location.href='./loginpage.html'">Register</button>
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
    <header class="header">
        <h1>RailYatri</h1>
        <p>Contact Us – We’re Just a Message Away</p>
    </header>
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

                <div class="contact-grid">
                    <div class="contact-form-container">
                        <form class="contact-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>

                        ?>">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" required>
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" required>
                            </div>

                            <div class="form-group">
                                <label>Subject</label>
                                <input type="text" name="subject" required>
                            </div>

                            <div class="form-group">
                                <label>Message</label>
                                <textarea name="message" required></textarea>
                            </div>

                            <button type="submit" class="submit-btn">Send Message</button>
                        </form>
                    </div>

                    <div class="contact-info">
                        <div class="info-card" data-aos="fade-up" data-aos-duration="1200">
                            <h3>Our Office</h3>
                            <p>Kanhar Hotsel</p>
                            <p>IIT Bhilai,Kutelabhata,Chhatisgarh</p>
                            <p>India</p>
                        </div>

                        <div class="info-card" data-aos="fade-up" data-aos-duration="1200">
                            <h3>Contact Information</h3>
                            <p>Phone: +91 7999399604</p>
                            <p>Email: railyatri203@gmail.com</p>
                            <p>Working Hours: 24/7</p>
                        </div>

                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="./script/login_script.js"></script>

    <script>
        AOS.init();
    </script>

    <style>
        .success-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }

        .success-message {
            font-size: 24px;
            color: #28a745;
            margin-bottom: 20px;
            animation: fadeIn 1s ease-in;
        }

        .checkmark {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-in-out;
        }

        .home-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .home-button:hover {
            background-color: #0056b3;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }
    </style>
</body>
<!-- Footer Section -->
<footer class="footer">
    <div class="footer-content">
        <!-- Main Footer -->
        <div class="footer-main">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="./logo.jpg" alt="RailYatri" class="footer-logo-img">
                    <p>Book your journey with comfort and ease</p>
                </div>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="booktickets.php">Book Tickets</a></li>
                    <li><a href="findtrains.php">Find Trains</a></li>
                    <li><a href="pnrstatus.php">PNR Status</a></li>
                    <li><a href="packages.html">Travel Packages</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Help & Support</h3>
            <ul>
                <li><a href="contactus.php">Contact Us</a></li>
                <li><a href="feedback.php">Feedback</a></li>
            </ul>
            </div>

            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul>
                    <li>Email: railyatri203@gmail.com</li>
                    <li>Phone: +91 7999399604</li>
                    <li>Address: Kanhar Hotsel, IIT Bhilai Kutelabhata Chhatisgarh</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
            <div class="copyright">
                <p>&copy; 2024 RailYatri. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</html>

<?php
$conn->close();
?>