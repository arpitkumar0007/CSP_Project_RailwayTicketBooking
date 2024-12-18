<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "railway_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create booking table if not exists
$create_table = "CREATE TABLE IF NOT EXISTS booking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pnr_number VARCHAR(10) NOT NULL,
    train_number VARCHAR(10) NOT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    gender VARCHAR(10) NOT NULL,
    age INT NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    document_number VARCHAR(50) NOT NULL,
    berth_preference VARCHAR(10) NOT NULL,
    allocated_seat VARCHAR(10) NOT NULL,
    booking_date DATE NOT NULL,
    journey_date DATE NOT NULL,
    from_station VARCHAR(100) NOT NULL,
    to_station VARCHAR(100) NOT NULL,
    class VARCHAR(20) NOT NULL,
    fare DECIMAL(10,2) NOT NULL,
    booking_status VARCHAR(20) DEFAULT 'CONFIRMED'
)";

if (!$conn->query($create_table)) {
    die("Error creating table: " . $conn->error);
}

// Fetch all unique stations
$station_query = "SELECT DISTINCT from_station FROM trains UNION SELECT DISTINCT to_station FROM trains ORDER BY from_station";
$station_result = $conn->query($station_query);
$stations = [];
if ($station_result) {
    while ($row = $station_result->fetch_assoc()) {
        $stations[] = $row['from_station'];
    }
    $station_result->free_result();
}

$search_results = [];
$search_performed = false;
$booking_success = false;
$generated_pnr = "";
$seat = "";

// Function to generate PNR
function generatePNR()
{
    return mt_rand(1000000000, 9999999999);
}

// Function to generate seat number
function generateSeatNumber($class, $berth_preference)
{
    // Initialize variables
    $coach_number = '';
    $berth_code = '';
    $max_seats = 0;

    switch ($class) {
        case '1A':
            // First AC: HA1, HA2, HA3
            $coach_number = 'HA' . mt_rand(1, 3);
            $max_seats = 18;
            break;
        case '2A':
            // Second AC: A1, A2, A3, A4, A5
            $coach_number = 'A' . mt_rand(1, 5);
            $max_seats = 46;
            break;
        case '3A':
            // Third AC: B1, B2, B3, B4, B5, B6, B7
            $coach_number = 'B' . mt_rand(1, 7);
            $max_seats = 64;
            break;
        case 'SL':
            // Sleeper: S1, S2, S3, ..., S10
            $coach_number = 'S' . mt_rand(1, 10);
            $max_seats = 72;
            break;
        default:
            $coach_number = 'S1';
            $max_seats = 72;
    }

    // Set berth code and number based on preference
    switch ($berth_preference) {
        case 'Lower':
            $berth_code = 'LB';
            $seat_number = generateNumberForBerthType($max_seats, 1);
            break;
        case 'Middle':
            $berth_code = 'MB';
            $seat_number = generateNumberForBerthType($max_seats, 2);
            break;
        case 'Upper':
            $berth_code = 'UB';
            $seat_number = generateNumberForBerthType($max_seats, 3);
            break;
        case 'Side Lower':
            $berth_code = 'SL';
            $seat_number = generateSideNumber($max_seats, true);
            break;
        case 'Side Upper':
            $berth_code = 'SU';
            $seat_number = generateSideNumber($max_seats, false);
            break;
        default:
            $berth_code = 'LB';
            $seat_number = generateNumberForBerthType($max_seats, 1);
    }

    return [
        'coach' => $coach_number,
        'berth_type' => $berth_code,
        'seat_number' => $seat_number,
        'full_seat' => $coach_number . '-' . $berth_code . $seat_number
    ];
}

function generateNumberForBerthType($max_seats, $remainder)
{
    $possible_numbers = range($remainder, $max_seats - 2, 3);
    return $possible_numbers[array_rand($possible_numbers)];
}

function generateSideNumber($max_seats, $is_lower)
{
    if ($is_lower) {
        $possible_numbers = range(7, $max_seats, 8);
    } else {
        $possible_numbers = range(8, $max_seats, 8);
    }
    return $possible_numbers[array_rand($possible_numbers)];
}

// Handle train search
if (isset($_POST['search_trains'])) {
    $from_station = $conn->real_escape_string($_POST['from_station'] ?? '');
    $to_station = $conn->real_escape_string($_POST['to_station'] ?? '');
    $journey_date = $conn->real_escape_string($_POST['journey_date'] ?? '');
    $class = $conn->real_escape_string($_POST['class'] ?? '');
    $day_of_week = date('l', strtotime($journey_date));

    $query = "SELECT t.*, 
    CASE 
        WHEN '$class' = '1A' THEN base_ac1_fare
        WHEN '$class' = '2A' THEN base_ac2_fare
        WHEN '$class' = '3A' THEN base_ac3_fare
        WHEN '$class' = 'SL' THEN base_sleeper_fare
        ELSE 0
    END as selected_fare,
    TIMEDIFF(arrival_time, departure_time) as duration_calc
    FROM trains t
    WHERE from_station LIKE '%$from_station%' 
    AND to_station LIKE '%$to_station%' 
    AND FIND_IN_SET('$day_of_week', runs_on) > 0 
    AND train_status != 'CANCELLED'";

    $result = $conn->query($query);
    if ($result) {
        $search_results = $result->fetch_all(MYSQLI_ASSOC);
        $result->free_result();
    }
    $search_performed = true;
}

// Handle booking submission
if (isset($_POST['book_ticket'])) {
    $pnr = generatePNR();
    $seat_details = generateSeatNumber($_POST['class'], $_POST['berth_preference']);

    // Map the class values if needed
    $class_mapping = [
        'ac1' => '1A',
        'ac2' => '2A',
        'ac3' => '3A',
        'sleeper' => 'SL'
    ];

    // Get the correct class value
    $class_value = isset($class_mapping[$_POST['class']]) ?
        $class_mapping[$_POST['class']] :
        $_POST['class'];

    $insert_query = "INSERT INTO booking (
        pnr_number, train_number, passenger_name, gender, age,
        document_type, document_number, berth_preference, allocated_seat,
        booking_date, journey_date, from_station, to_station, class, fare
    ) VALUES (
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        CURDATE(), ?, ?, ?, ?, ?
    )";

    try {
        $stmt = $conn->prepare($insert_query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Use the mapped class value
        if (
            !$stmt->bind_param(
                "ssssississsssd",
                $pnr,
                $_POST['train_number'],
                $_POST['passenger_name'],
                $_POST['gender'],
                $_POST['age'],
                $_POST['document_type'],
                $_POST['document_number'],
                $_POST['berth_preference'],
                $seat_details['full_seat'], 
                $_POST['journey_date'],
                $_POST['from_station'],
                $_POST['to_station'],
                $_POST['class'],
                $_POST['fare']
            )
        ) {
            throw new Exception("Binding parameters failed: " . $stmt->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $booking_success = true;
        $generated_pnr = $pnr;
        $generated_seat = $seat_details;

    } catch (Exception $e) {
        die("Booking failed: " . $e->getMessage());
    } finally {
        if (isset($stmt)) {
            $stmt->close();
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
    <title>Find Trains</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>
    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/findtrains.css">
</head>

<body>
    <!-- NavBar -->
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

                    <!-- <button onclick="signup()" id="signupBtn">Sign up</button> -->
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

    <!-- Main Container -->
    <div class="container">
        <header class="header">
            <h1>RailYatri</h1>
            <p>Find the Best Routes and Schedules</p>
        </header>

        <!-- Search Form -->
        <form class="search-form" method="POST">
            <div class="form-grid">
                <!-- From Station -->
                <div class="form-group">
                    <label for="from_station">From Station</label>
                    <i class="fas fa-map-marker-alt"></i>
                    <select name="from_station" id="from_station" class="form-control" required>
                        <option value="">Select departure station</option>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo htmlspecialchars($station); ?>" 
                                    <?php echo (isset($_POST['from_station']) && $_POST['from_station'] == $station) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($station); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- To Station -->
                <div class="form-group">
                    <label for="to_station">To Station</label>
                    <i class="fas fa-map-marker"></i>
                    <select name="to_station" id="to_station" class="form-control" required>
                        <option value="">Select arrival station</option>
                        <?php foreach ($stations as $station): ?>
                            <option value="<?php echo htmlspecialchars($station); ?>"
                                    <?php echo (isset($_POST['to_station']) && $_POST['to_station'] == $station) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($station); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Journey Date -->
                <div class="form-group">
                    <label for="journey_date">Journey Date</label>
                    <i class="fas fa-calendar"></i>
                    <input type="date" name="journey_date" id="journey_date" class="form-control" required>
                </div>

                <!-- Class -->
                <div class="form-group">
                    <label for="class">Class</label>
                    <i class="fas fa-train"></i>
                    <select name="class" id="class" class="form-control" required>
                        <option value="1A">First AC (1A)</option>
                        <option value="2A">Second AC (2A)</option>
                        <option value="3A">Third AC (3A)</option>
                        <option value="SL">Sleeper (SL)</option>
                    </select>
                </div>
            </div>

            <!-- Search Button -->
            <button type="submit" name="search_trains" class="btn">
                <i class="fas fa-search"></i>
                Search Trains
            </button>
        </form>

        <!-- Train Results -->
        <?php if ($search_performed): ?>
            <?php if (empty($search_results)): ?>
                <div class="train-card">
                    <div class="train-header">
                        <div class="train-name">No trains found</div>
                    </div>
                    <p>No trains available for the selected route and date. Please try different dates or stations.</p>
                </div>
            <?php else: ?>
                <?php foreach ($search_results as $train): ?>
                    <div class="train-card">
                        <div class="train-header">
                            <div class="train-name"><?php echo htmlspecialchars($train['train_name']); ?> (<?php echo htmlspecialchars($train['train_number']); ?>)</div>
                            <div class="train-type"><?php echo htmlspecialchars($train['train_type']); ?></div>
                        </div>

                        <div class="train-info">
                            <!-- Departure -->
                            <div class="info-group">
                                <i class="fas fa-clock info-icon"></i>
                                <div class="info-details">
                                    <span class="info-label">Departure</span>
                                    <span class="info-value"><?php echo date('H:i', strtotime($train['departure_time'])); ?></span>
                                </div>
                            </div>

                            <!-- Arrival -->
                            <div class="info-group">
                                <i class="fas fa-clock info-icon"></i>
                                <div class="info-details">
                                    <span class="info-label">Arrival</span>
                                    <span class="info-value"><?php echo date('H:i', strtotime($train['arrival_time'])); ?></span>
                                </div>
                            </div>

                            <!-- Duration -->
                            <div class="info-group">
                                <i class="fas fa-hourglass-half info-icon"></i>
                                <div class="info-details">
                                    <span class="info-label">Duration</span>
                                    <span class="info-value"><?php echo $train['duration_calc']; ?></span>
                                </div>
                            </div>

                            <!-- Fare -->
                            <div class="info-group">
                                <i class="fas fa-indian-rupee-sign info-icon"></i>
                                <div class="info-details">
                                    <span class="info-label">Fare</span>
                                    <span class="info-value">₹<?php echo number_format($train['selected_fare'], 2); ?></span>
                                </div>
                            </div>
                        </div>

                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="train_number" value="<?php echo htmlspecialchars($train['train_number']); ?>">
                            <input type="hidden" name="from_station" value="<?php echo htmlspecialchars($train['from_station']); ?>">
                            <input type="hidden" name="to_station" value="<?php echo htmlspecialchars($train['to_station']); ?>">
                            <input type="hidden" name="journey_date" value="<?php echo htmlspecialchars($_POST['journey_date']); ?>">
                            <input type="hidden" name="class" value="<?php echo htmlspecialchars($_POST['class']); ?>">
                            <input type="hidden" name="fare" value="<?php echo htmlspecialchars($train['selected_fare']); ?>">
                    
                            <button type="button" class="btn" onclick="window.location.href='booktickets.php?train=<?php echo urlencode($train['train_number']); ?>&class=<?php echo urlencode($_POST['class']); ?>&date=<?php echo urlencode($_POST['journey_date']); ?>&from=<?php echo urlencode($train['from_station']); ?>&to=<?php echo urlencode($train['to_station']); ?>&fare=<?php echo urlencode($train['selected_fare']); ?>'">
                                <i class="fas fa-ticket"></i>
                                Book Now
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <!-- Scripts -->
    
    <script src="./script/login_script.js"></script>
    <script>
        AOS.init();
        const journeyDateInput = document.querySelector('#journey_date');
        if (journeyDateInput) {
            const today = new Date().toISOString().split('T')[0];
            journeyDateInput.setAttribute('min', today);
            
            const maxDate = new Date();
            maxDate.setMonth(maxDate.getMonth() + 4);
            journeyDateInput.setAttribute('max', maxDate.toISOString().split('T')[0]);
        }
    </script>
</body>

    <!-- Footer -->
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
                        <li>Address: Kanhar Hotsel, IIT Bhilai, Chhatisgarh</li>
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
<?php
// Close database connection
$conn->close();
?>