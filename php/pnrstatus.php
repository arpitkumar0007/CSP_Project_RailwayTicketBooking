<!-- this is for pnr status fetching -->
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
function connectDB() {
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
function censorDocumentNumber($number) {
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
                    $booking_details = mysqli_fetch_assoc($result);
                    // Censor document number
                    $booking_details['document_number'] = censorDocumentNumber($booking_details['document_number']);
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
    <title>PNR Status - RailwayYatri</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
</head>
<body>
       <div class="links">
            <div class="link" data-aos="fade-up" data-aos-duration="1200"><a href="">Home</a></div>
            <div class="link" data-aos="fade-up" data-aos-duration="1200" data-aos-delay="100"><a href="booktickets.php">Book Tickets</a></div>
            <div class="link" data-aos="fade-up" data-aos-duration="1200" data-aos-delay="200"><a href="findtrains.php">Find Trains</a></div>
            <div class="link" data-aos="fade-up" data-aos-duration="1200" data-aos-delay="300"><a href="pnrstatus.php">PNR Status</a></div>
            <div class="link" data-aos="fade-up" data-aos-duration="1200" data-aos-delay="400"><a href="feedback.php">Feedback</a></div>
            <div class="link" data-aos="fade-up" data-aos-duration="1200" data-aos-delay="500"><a href="contactus.html">Contact Us</a></div>
        </div>

    <div class="search-container <?php echo ($search_performed && $booking_details) ? 'hidden' : ''; ?>" data-aos="fade-up" data-aos-duration="1200">
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

    <?php if ($search_performed && $booking_details): ?>
    
        <div class="results-container">
        <div class="booking-card" data-aos="fade-up">
            <div class="booking-header">
                <div>
                    <h3>PNR: <?php echo htmlspecialchars($booking_details['pnr_number']); ?></h3>
                    <p>Train Number: <?php echo htmlspecialchars($booking_details['train_number']); ?></p>
                </div>
                <div>
                    <span class="status-badge">CONFIRMED</span>
                </div>
            </div>

            <div class="booking-details">
                <div class="detail-group">
                    <div class="detail-label">Passenger Name</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking_details['passenger_name']); ?></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Age/Gender</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking_details['age']) . '/' . htmlspecialchars($booking_details['gender']); ?></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Journey Date</div>
                    <div class="detail-value"><?php echo date('d M Y', strtotime($booking_details['journey_date'])); ?></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Booking Date</div>
                    <div class="detail-value"><?php echo date('d M Y', strtotime($booking_details['booking_date'])); ?></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Class</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking_details['class']); ?></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">From</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking_details['from_station']); ?></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">To</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking_details['to_station']); ?></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Coach/Seat</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking_details['allocated_seat']); ?></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Document Type</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking_details['document_type']); ?></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Document Number</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking_details['document_number']); ?></div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Fare</div>
                    <div class="detail-value">₹<?php echo htmlspecialchars($booking_details['fare']); ?></div>
                </div>
            </div>
        </div>
        <a href="pnrstatus.php" class="new-search-btn">Check Another PNR</a>
    </div>
    <?php elseif ($search_performed): ?>
        <div class="results-container">
            <div class="booking-card" data-aos="fade-up">
                <p style="text-align: center; color: #721c24;">No booking found for the provided PNR number.</p>
            </div>
            <a href="pnrstatus.php" class="new-search-btn">Try Another Search</a>
        </div>
    <?php endif; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>
</html>