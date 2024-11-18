<?php
//starting a session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
function connectDB()
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "railway_db";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}

// Initialize variables
$conn = connectDB();
$error_message = '';
$booking_details = null;
$search_performed = false;

// Generate captcha if not exists
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
}

// Function to censor document number
function censorDocumentNumber($number)
{
    $length = strlen($number);
    $visible = 4; // Show last 4 characters
    $censored = str_repeat('*', $length - $visible) . substr($number, -$visible);
    return $censored;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_captcha = trim($_POST['captcha'] ?? '');
    $pnr_number = mysqli_real_escape_string($conn, trim($_POST['pnr_number'] ?? ''));

    // Validate captcha
    if (strtoupper($entered_captcha) === $_SESSION['captcha']) {
        if (strlen($pnr_number) === 10 && is_numeric($pnr_number)) {
            $query = "SELECT 
                pnr_number,
                train_number,
                passenger_name,
                age,
                gender,
                journey_date,
                class,
                from_station,
                to_station,
                allocated_seat,
                fare,
                document_type,
                document_number,
                booking_date
            FROM booking 
            WHERE pnr_number = ?";

            $stmt = mysqli_prepare($conn, $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $pnr_number);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) > 0) {
                    $booking_details = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $row['document_number'] = censorDocumentNumber($row['document_number']);
                        $booking_details[] = $row;
                    }
                } else {
                    $error_message = 'No booking found for the provided PNR number.';
                }
                mysqli_stmt_close($stmt);
            } else {
                $error_message = 'Database error occurred. Please try again.';
            }
        } else {
            $error_message = 'Invalid PNR number format. Please enter a 10-digit number.';
        }
        $search_performed = true;
    } else {
        $error_message = 'Invalid captcha! Please try again.';
    }

    $_SESSION['captcha'] = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="logo.jpg" />
    <title>PNR Status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="./css/pnrstatus.css">
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
           <div class="links">
            <a href="./index.html">Home</a>
            <a href="booktickets.php">Book Tickets</a>
            <a href="findtrains.php">Find Trains</a>
            <a href="pnrstatus.php">PNR Status</a>
            <a href="feedback.php">Feedback</a>
            <a href="packages.php">Travel Packages</a>
            <a href="contactus.php">Contact Us</a>
        </div>
        </div>
        <div class="buttons" data-aos="fade-down" data-aos-duration="1000" data-aos-delay="700">
            <!-- Auth buttons -->
            <div id="authButtons">
                <button id="loginBtn" onclick="window.location.href='./loginpage.html'">Register</button>
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
    <header class="header">
        <h1>RailYatri</h1>
        <p>Check Your PNR Status Instantly</p>
    </header>

    <div class="search-container <?php echo ($search_performed && $booking_details) ? 'hidden' : ''; ?>">
        <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Check PNR Status</h2>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form class="search-form" method="POST" novalidate>
            <div class="form-group">
                <label>Enter PNR Number</label>
                <input type="text" name="pnr_number" pattern="[0-9]{10}" maxlength="10" required
                    placeholder="Enter 10-digit PNR number"
                    value="<?php echo isset($_POST['pnr_number']) ? htmlspecialchars($_POST['pnr_number']) : ''; ?>">
            </div>
            <div class="form-group">
                <label>Enter Captcha</label>
                <div class="captcha-container">
                    <div class="captcha-code"><?php echo $_SESSION['captcha']; ?></div>
                    <input type="text" name="captcha" required placeholder="Enter captcha code">
                </div>
            </div>
            <button type="submit" class="search-btn">Check Status</button>
        </form>
    </div>

    <?php if ($search_performed && is_array($booking_details) && count($booking_details) > 0): ?>
        <div class="results-container">
            <?php foreach ($booking_details as $details): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div>
                            <h3>PNR: <?php echo htmlspecialchars($details['pnr_number']); ?></h3>
                            <p>Train Number: <?php echo htmlspecialchars($details['train_number']); ?></p>
                        </div>
                        <div>
                            <span class="status-badge">CONFIRMED</span>
                        </div>
                    </div>

                    <div class="booking-details">
                        <div class="detail-group">
                            <div class="detail-label">Passenger Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($details['passenger_name']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Age/Gender</div>
                            <div class="detail-value">
                                <?php echo htmlspecialchars($details['age']) . '/' . htmlspecialchars($details['gender']); ?>
                            </div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Journey Date</div>
                            <div class="detail-value"><?php echo date('d M Y', strtotime($details['journey_date'])); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Booking Date</div>
                            <div class="detail-value"><?php echo date('d M Y', strtotime($details['booking_date'])); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Class</div>
                            <div class="detail-value"><?php echo htmlspecialchars($details['class']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">From</div>
                            <div class="detail-value"><?php echo htmlspecialchars($details['from_station']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">To</div>
                            <div class="detail-value"><?php echo htmlspecialchars($details['to_station']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Coach/Seat</div>
                            <div class="detail-value"><?php echo htmlspecialchars($details['allocated_seat']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Document Type</div>
                            <div class="detail-value"><?php echo htmlspecialchars($details['document_type']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Document Number</div>
                            <div class="detail-value"><?php echo htmlspecialchars($details['document_number']); ?></div>
                        </div>
                        <div class="detail-group">
                            <div class="detail-label">Fare</div>
                            <div class="detail-value">₹<?php echo htmlspecialchars($details['fare']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <a href="pnrstatus.php" class="new-search-btn">Check Another PNR</a>
        </div>
    <?php elseif ($search_performed): ?>
        <div class="results-container">
            <div class="booking-card">
                <p style="text-align: center; color: #721c24;">No booking found for the provided PNR number.</p>
            </div>
            <a href="pnrstatus.php" class="new-search-btn">Try Another Search</a>
        </div>
    <?php endif; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="./script/login_script.js"></script>

    <script>
        AOS.init();
    </script>
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