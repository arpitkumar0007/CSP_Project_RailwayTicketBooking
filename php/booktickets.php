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
function generatePNR() {
    return mt_rand(1000000000, 9999999999);
}

// Function to generate seat number
function generateSeatNumber($class, $berth_preference) {
    // Initialize variables
    $coach_number = '';
    $berth_code = '';
    $max_seats = 0;

    switch ($class) {
        case '1A':
            $coach_number = 'HA' . mt_rand(1, 3);
            $max_seats = 18;
            break;
        case '2A':
            $coach_number = 'A' . mt_rand(1, 5);
            $max_seats = 46;
            break;
        case '3A':
            $coach_number = 'B' . mt_rand(1, 7);
            $max_seats = 64;
            break;
        case 'SL':
            $coach_number = 'S' . mt_rand(1, 10);
            $max_seats = 72;
            break;
        default:
            $coach_number = 'S1';
            $max_seats = 72;
    }

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

function generateNumberForBerthType($max_seats, $remainder) {
    $possible_numbers = range($remainder, $max_seats - 2, 3);
    return $possible_numbers[array_rand($possible_numbers)];
}

function generateSideNumber($max_seats, $is_lower) {
    if ($is_lower) {
        $possible_numbers = range(7, $max_seats, 8);
    } else {
        $possible_numbers = range(8, $max_seats, 8);
    }
    return $possible_numbers[array_rand($possible_numbers)];
}

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

if (isset($_POST['book_ticket'])) {
    $pnr = generatePNR();
    $seat_details = generateSeatNumber($_POST['class'], $_POST['berth_preference']);
    
    $class_mapping = [
        'ac1' => '1A',
        'ac2' => '2A',
        'ac3' => '3A',
        'sleeper' => 'SL'
    ];
    
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
        
        if (!$stmt->bind_param("ssssississsssd",
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
)) {
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
    <title>Book Tickets - RailwayYatri</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>
    <link rel="stylesheet" href="./css/booktickets.css">
    <link rel="stylesheet" href="./css/common.css">
    
</head>
<body>
    <div class="container">
    
        <?php if (!$search_performed && !$booking_success): ?>
            <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Search Trains</h2>
            <form method="POST">
                <div class="grid">
                    <div class="form-group">
                        <label>From Station</label>
                        <select name="from_station" class="form-control" required>
                            <option value="">Select Departure Station</option>
                            <?php foreach ($stations as $station): ?>
                                <option value="<?php echo htmlspecialchars($station); ?>">
                                    <?php echo htmlspecialchars($station); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>To Station</label>
                        <select name="to_station" class="form-control" required>
                            <option value="">Select Arrival Station</option>
                            <?php foreach ($stations as $station): ?>
                                <option value="<?php echo htmlspecialchars($station); ?>">
                                    <?php echo htmlspecialchars($station); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid">
                    <div class="form-group">
                        <label>Journey Date</label>
                        <input type="date" name="journey_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                    <label>Class</label>
                        <select name="class" class="form-control" required>
                            <option value="1A">First AC (1A)</option>
                            <option value="2A">Second AC (2A)</option>
                            <option value="3A">Third AC (3A)</option>
                            <option value="SL">Sleeper (SL)</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="search_trains" class="btn">Search Trains</button>
            </form>
        <?php endif; ?>

        <?php if ($search_performed && !isset($_POST['show_booking_form'])): ?>
            <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Available Trains</h2>
            <?php foreach ($search_results as $train): ?>
                <div class="train-card">
    <h3><?php echo htmlspecialchars($train['train_name']); ?> (<?php echo htmlspecialchars($train['train_number']); ?>)</h3>
    <div class="grid" style="margin: 1rem 0;">
        <div>
            <p>Departure: <?php echo date('h:i A', strtotime($train['departure_time'])); ?></p>
            <p>Arrival: <?php echo date('h:i A', strtotime($train['arrival_time'])); ?></p>
        </div>
        <div>
            <p>Duration: <?php 
                $duration = new DateTime($train['duration_calc']);
                echo $duration->format('H:i'); ?> hrs</p>
            <p>Type: <?php echo htmlspecialchars($train['train_type']); ?></p>
        </div>
    </div>
    <div class="fare-details">
        <p>Fare: ₹<?php echo number_format($train['selected_fare'], 2); ?></p>
    </div>
    <form method="POST" class="booking-form">
        <input type="hidden" name="train_number" value="<?php echo htmlspecialchars($train['train_number']); ?>">
        <input type="hidden" name="from_station" value="<?php echo htmlspecialchars($_POST['from_station']); ?>">
        <input type="hidden" name="to_station" value="<?php echo htmlspecialchars($_POST['to_station']); ?>">
        <input type="hidden" name="journey_date" value="<?php echo htmlspecialchars($_POST['journey_date']); ?>">
        <input type="hidden" name="class" value="<?php echo htmlspecialchars($_POST['class']); ?>">
        <input type="hidden" name="fare" value="<?php echo htmlspecialchars($train['selected_fare']); ?>">
        <button type="submit" name="show_booking_form" class="btn">Book Now</button>
    </form>
</div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (isset($_POST['show_booking_form'])): ?>
            <h2 style="color: white; text-align: center; margin-bottom: 2rem;">Passenger Details</h2>
            <form method="POST">
                <input type="hidden" name="train_number" value="<?php echo htmlspecialchars($_POST['train_number']); ?>">
                <input type="hidden" name="from_station" value="<?php echo htmlspecialchars($_POST['from_station']); ?>">
                <input type="hidden" name="to_station" value="<?php echo htmlspecialchars($_POST['to_station']); ?>">
                <input type="hidden" name="journey_date" value="<?php echo htmlspecialchars($_POST['journey_date']); ?>">
                <input type="hidden" name="class" value="<?php echo htmlspecialchars($_POST['class']); ?>">
                <input type="hidden" name="fare" value="<?php echo htmlspecialchars($_POST['fare']); ?>">

                <div class="grid">
                    <div class="form-group">
                        <label>Passenger Name</label>
                        <input type="text" name="passenger_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" class="form-control" required min="1" max="120">
                    </div>
                </div>

                <div class="grid">
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Berth Preference</label>
                        <select name="berth_preference" class="form-control" required>
                            <option value="Lower">Lower Berth</option>
                            <option value="Middle">Middle Berth</option>
                            <option value="Upper">Upper Berth</option>
                            <option value="Side Lower">Side Lower</option>
                            <option value="Side Upper">Side Upper</option>
                        </select>
                    </div>
                </div>

                <div class="grid">
                    <div class="form-group">
                    <label>Document Type</label>
                    <select name="document_type" class="form-control" required>
                            <option value="Aadhar">Aadhar Card</option>
                            <option value="PAN">PAN Card</option>
                            <option value="Passport">Passport</option>
                            <option value="Driving License">Driving License</option>
                            <option value="Voter ID">Voter ID</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Document Number</label>
                        <input type="text" name="document_number" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Total Fare</label>
                    <input type="text" value="₹<?php echo htmlspecialchars($_POST['fare']); ?>" class="form-control" readonly>
                </div>

                <button type="submit" name="book_ticket" class="btn">Confirm Booking</button>
            </form>
        <?php endif; ?>
    </div>
    <?php if ($booking_success): ?>
    <div class="container">
    <div class="success-message">
        <h2>Booking Successful!</h2>
        <div class="booking-details" id="ticketDetails">
            <p><strong>PNR Number:</strong> <?php echo htmlspecialchars($generated_pnr); ?></p>
            <p><strong>Coach:</strong> <?php echo htmlspecialchars($generated_seat['coach']); ?></p>
            <p><strong>Seat Number:</strong> <?php echo htmlspecialchars($generated_seat['berth_type'] . $generated_seat['seat_number']); ?></p>
            
            <!-- Add hidden fields for PDF generation -->
            <div id="additionalTicketInfo" style="display: none;">
                <p><strong>Passenger Name:</strong> <?php echo htmlspecialchars($_POST['passenger_name']); ?></p>
                <p><strong>Age:</strong> <?php echo htmlspecialchars($_POST['age']); ?></p>
                <p><strong>Gender:</strong> <?php echo htmlspecialchars($_POST['gender']); ?></p>
                <p><strong>From:</strong> <?php echo htmlspecialchars($_POST['from_station']); ?></p>
                <p><strong>To:</strong> <?php echo htmlspecialchars($_POST['to_station']); ?></p>
                <p><strong>Journey Date:</strong> <?php echo htmlspecialchars($_POST['journey_date']); ?></p>
                <p><strong>Class:</strong> <?php echo htmlspecialchars($_POST['class']); ?></p>
                <p><strong>Fare:</strong> ₹<?php echo htmlspecialchars($_POST['fare']); ?></p>
            </div>
        </div>
        <button onclick="generatePDF()" class="btn" style="margin-top: 1rem;">Download Ticket</button>
    </div>
    </div>
    <!-- Add jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Set font
    doc.setFont('helvetica');

    // Add header
    doc.setFontSize(20);
    doc.setTextColor(30, 60, 114); // #1e3c72
    doc.text('RailwayYatri E-Ticket', 105, 20, { align: 'center' });

    // Add logo or decorative element
    doc.setDrawColor(30, 60, 114);
    doc.setLineWidth(0.5);
    doc.line(20, 25, 190, 25);

    // Get ticket details
    const ticketDetails = document.getElementById('ticketDetails');
    const additionalInfo = document.getElementById('additionalTicketInfo');

    // Set font size for content
    doc.setFontSize(12);
    doc.setTextColor(0, 0, 0);

    // Add PNR and basic info
    doc.setFontSize(14);
    doc.text('PNR Details', 20, 40);
    doc.setFontSize(12);

    // Extract text content
    const pnrElement = ticketDetails.querySelector('p:first-child');
    const coachElement = ticketDetails.querySelector('p:nth-child(2)');
    const seatElement = ticketDetails.querySelector('p:nth-child(3)');

    if (pnrElement && coachElement && seatElement) {
        const pnr = pnrElement.textContent;
        const coach = coachElement.textContent;
        const seat = seatElement.textContent;

        // Add main ticket information
        doc.text(pnr, 20, 50);
        doc.text(coach, 20, 60);
        doc.text(seat, 20, 70);
    }

    // Add separator
    doc.line(20, 80, 190, 80);

    // Add passenger details
    doc.setFontSize(14);
    doc.text('Passenger Details', 20, 95);
    doc.setFontSize(12);

    // Get additional info
    const additionalDetails = additionalInfo.getElementsByTagName('p');
    let yPos = 105;

    for (let detail of additionalDetails) {
        doc.text(detail.textContent, 20, yPos);
        yPos += 10;
    }

    // Add footer
    doc.setFontSize(10);
    doc.text('This is a computer generated ticket and does not require signature.', 105, 270, { align: 'center' });

    // Generate PDF name using PNR
    const pnrNumber = pnrElement ? pnrElement.textContent.split(':')[1].trim() : 'UNKNOWN';
    const fileName = `ticket_${pnrNumber}.pdf`;

    // Save the PDF
    doc.save(fileName);
}
    </script>
<?php endif; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out'
        });

        // Disable past dates in the journey date picker
        const journeyDateInput = document.querySelector('input[name="journey_date"]');
        if (journeyDateInput) {
            const today = new Date().toISOString().split('T')[0];
            journeyDateInput.setAttribute('min', today);
            
            // Set max date to 4 months from today
            const maxDate = new Date();
            maxDate.setMonth(maxDate.getMonth() + 4);
            journeyDateInput.setAttribute('max', maxDate.toISOString().split('T')[0]);
        }

        // Validate age input
        const ageInput = document.querySelector('input[name="age"]');
        if (ageInput) {
            ageInput.addEventListener('input', function() {
                if (this.value < 1) this.value = 1;
                if (this.value > 120) this.value = 120;
            });
        }

        // Form validation
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = 'red';
                    } else {
                        field.style.borderColor = '';
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                }
            });
        });
    </script>
    <script src="./script/login_script.js"></script>

</body>
</html>

<?php
$conn->close();
?>