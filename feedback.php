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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    <link rel="stylesheet" href="css/common.css" <!-- Page specific CSS -->
    <link rel="stylesheet" href="./css/feedback.css">
    <link rel="icon" href="logo.jpg" />
    <title>Feedback</title>
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>


</head>

<body>
    <div>
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
                <!-- Auth buttons -->
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
    </div>
    <header class="header">
        <h1>RailYatri</h1>
        <p>Help Us Improve with Your Feedback</p>
    </header>


    <div class="container">
        <?php if ($recent_feedbacks && $recent_feedbacks->num_rows > 0): ?>
            <div class="recent-feedbacks" data-aos="fade-up" data-aos-duration="1200" data-aos-delay="200">
                <h2 class="section-title">Recent Feedback</h2>
                <div class="feedback-grid">
                    <?php while ($feedback = $recent_feedbacks->fetch_assoc()): ?>
                        <div class="feedback-card" data-aos="fade-up" data-aos-duration="1200">
                            <div class="feedback-header">
                                <strong><?php echo htmlspecialchars($feedback['name']); ?></strong>
                                <span
                                    class="feedback-type"><?php echo ucfirst(htmlspecialchars($feedback['feedback_type'])); ?></span>
                            </div>
                            <div class="feedback-message">
                                <p><?php echo htmlspecialchars($feedback['message']); ?></p>
                            </div>
                            <div class="feedback-rating">
                                <span>
                                    <?php
                                    for ($i = 1; $i <= $feedback['rating']; $i++) {
                                        echo "⭐";
                                    }
                                    ?>
                                </span>
                                <span style="color: white; font-size: 0.9rem;">
                                    <?php echo $feedback['formatted_date']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($success_message && $form_submitted): ?>
            <div class="feedback-container" data-aos="fade-up" data-aos-duration="1200">
                <div class="message success"><?php echo $success_message; ?></div>
            </div>
        <?php else: ?>
            <div class="feedback-container" data-aos="fade-up" data-aos-duration="1200">
                <h2 class="section-title">We Value Your Feedback</h2>

                <?php if ($error_message): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form class="feedback-form" method="POST">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Feedback Type</label>
                        <select name="feedback_type" required>
                            <option value="">Select Feedback Type</option>
                            <option value="general">General Feedback</option>
                            <option value="service">Service Quality</option>
                            <option value="website">Website Experience</option>
                            <option value="booking">Booking Process</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="complaint">Complaint</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Your Message</label>
                        <textarea name="message" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Rating</label>
                        <div class="rating-group">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label>
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" required>
                                    <?php echo $i; ?> ⭐
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Submit Feedback</button>
                </form>
            </div>
        <?php endif; ?>


    </div>
    <script src="./script/login_script.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>
<!-- Footer Section -->
<footer class="footer">
    <div class="footer-content">
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

        <!-- Bottom Footer -->
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