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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" />
    

   
</head>

<body>
    <div>
        <nav>
        <div class="logo" data-aos="fade-down" data-aos-duration="1000">
            RailwayYatri
        </div>
        <div class="links">
            <a href="./index.html" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="100">Home</a>
            <a href="booktickets.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="200">Book Tickets</a>
            <a href="findtrains.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="300">Find Trains</a>
            <a href="pnrstatus.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="400">PNR Status</a>
            <a href="feedback.php" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="500">Feedback</a>
            <a href="contactus.html" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="600">Contact Us</a>
        </div>
        <div class="buttons" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="700">
            <!-- Auth buttons -->
            <div id="authButtons">
                <button onclick="login()" id="loginBtn">Login</button>
                <button onclick="signup()" id="signupBtn">Sign up</button>
            </div>
            <!-- User profile -->
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
    
</body>

</html>