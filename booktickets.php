<?php
$pre_filled_train = null;
if (isset($_GET['train']) &&
isset($_GET['class']) &&
isset($_GET['date']) &&
isset($_GET['from']) &&
isset($_GET['to']) &&
isset($_GET['fare'])) {
$pre_filled_train = [
'train_number' => $_GET['train'],
'class' => $_GET['class'],
'journey_date' => $_GET['date'],
'from_station' => $_GET['from'],
'to_station' => $_GET['to'],    
'fare' => $_GET['fare']
];
}

$display_fare = $pre_filled_train ? $pre_filled_train['fare'] :
(isset($_POST['fare']) ? $_POST['fare'] : '0');
?>
<?php
$pre_filled_train = null;
if (isset($_GET['train'])) {
$pre_filled_train = [
'train_number' => $_GET['train'] ?? '',
'class' => $_GET['class'] ?? '',
'journey_date' => $_GET['date'] ?? '',
'from_station' => $_GET['from'] ?? '',
'to_station' => $_GET['to'] ?? '',
'fare' => $_GET['fare'] ?? ''
];
}
?>

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
function generatePNR($conn)
{
$pnr = null;

while ($pnr === null) {
// Generate a unique 10-digit PNR
$pnr = str_pad(mt_rand(1, 9999999999), 10, "0", STR_PAD_LEFT);

// Check if the PNR already exists in the database
$check_query = "SELECT COUNT(*) as count FROM booking WHERE pnr_number = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("s", $pnr);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
// If the PNR already exists, generate a new one
$pnr = null;
}

$stmt->close();
}

return $pnr;
}



// Function to generate seat number
$allocated_seats = [];

function generateSeatNumber($class, $berth_preference, $allocated_seats)
{
// Initialize variables
$coach_number = '';
$berth_code = '';
$seat_number = '';
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
do {
$seat_number = generateNumberForBerthType($max_seats, 1);
} while (in_array($coach_number . '-' . $berth_code . $seat_number, $allocated_seats));
break;
case 'Middle':
$berth_code = 'MB';
do {
$seat_number = generateNumberForBerthType($max_seats, 2);
} while (in_array($coach_number . '-' . $berth_code . $seat_number, $allocated_seats));
break;
case 'Upper':
$berth_code = 'UB';
do {
$seat_number = generateNumberForBerthType($max_seats, 3);
} while (in_array($coach_number . '-' . $berth_code . $seat_number, $allocated_seats));
break;
case 'Side Lower':
$berth_code = 'SL';
do {
$seat_number = generateSideNumber($max_seats, true);
} while (in_array($coach_number . '-' . $berth_code . $seat_number, $allocated_seats));
break;
case 'Side Upper':
$berth_code = 'SU';
do {
$seat_number = generateSideNumber($max_seats, false);
} while (in_array($coach_number . '-' . $berth_code . $seat_number, $allocated_seats));
break;
default:
$berth_code = 'LB';
do {
$seat_number = generateNumberForBerthType($max_seats, 1);
} while (in_array($coach_number . '-' . $berth_code . $seat_number, $allocated_seats));
}

$full_seat = $coach_number . '-' . $berth_code . $seat_number;
$allocated_seats[] = $full_seat;
return [
'coach' => $coach_number,
'berth_type' => $berth_code,
'seat_number' => $seat_number,
'full_seat' => $full_seat
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
try {
// Start transaction
$conn->begin_transaction();

// Generate PNR
$pnr = generatePNR($conn);
$booking_success = false;
$all_passenger_seats = [];

// Get passenger count from the form
$passenger_count = count($_POST['passenger_name']);

for ($i = 0; $i < $passenger_count; $i++) {
$seat_details = generateSeatNumber($_POST['class'], $_POST['berth_preference'][$i], $allocated_seats);
$allocated_seats[] = $seat_details['full_seat'];

$insert_query = "INSERT INTO booking (
pnr_number, train_number, passenger_name, gender, age,
document_type, document_number, berth_preference, allocated_seat,
booking_date, journey_date, from_station, to_station, class, fare
) VALUES (
?, ?, ?, ?, ?,
?, ?, ?, ?,
CURDATE(), ?, ?, ?, ?, ?
)";

$stmt = $conn->prepare($insert_query);
if (!$stmt) {
throw new Exception("Prepare failed: " . $conn->error);
}

if (
!$stmt->bind_param(
"ssssississsssd",
$pnr,
$_POST['train_number'],
$_POST['passenger_name'][$i],
$_POST['gender'][$i],
$_POST['age'][$i],
$_POST['document_type'][$i],
$_POST['document_number'][$i],
$_POST['berth_preference'][$i],
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

$all_passenger_seats[] = [
'name' => $_POST['passenger_name'][$i],
'age' => $_POST['age'][$i],
'gender' => $_POST['gender'][$i],
'seat_details' => $seat_details
];

$stmt->close();
}

// If we got here, everything worked
$conn->commit();
$booking_success = true;
$generated_pnr = $pnr;
$generated_seats = $all_passenger_seats;

} catch (Exception $e) {
// Roll back the transaction on error
$conn->rollback();
die("Booking failed: " . $e->getMessage());
}
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="logo.jpg" />
<title>Book Tickets</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-auth-compat.js"></script>
<link rel="stylesheet" href="./css/booktickets.css">
<link rel="stylesheet" href="./css/common.css">




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
<p>Book your journey with comfort and ease</p>
</header>

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
<option value="<?php echo htmlspecialchars($station); ?>"
<?php
if ($pre_filled_train && $pre_filled_train['from_station'] == $station) {
echo 'selected';
}
?>>
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
<option value="<?php echo htmlspecialchars($station); ?>"
<?php
if ($pre_filled_train && $pre_filled_train['to_station'] == $station) {
echo 'selected';
}
?>>
<?php echo htmlspecialchars($station); ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>
<div class="grid">
<div class="form-group">
<label>Journey Date</label>
<input type="date" name="journey_date" class="form-control" required
<?php
if ($pre_filled_train && $pre_filled_train['journey_date']) {
echo 'value="' . htmlspecialchars($pre_filled_train['journey_date']) . '"';
}
?>>
</div>
<div class="form-group">
<label>Class</label>
<select name="class" class="form-control" required>
<option value="1A" <?php echo ($pre_filled_train && $pre_filled_train['class'] == '1A') ? 'selected' : ''; ?>>First AC (1A)</option>
<option value="2A" <?php echo ($pre_filled_train && $pre_filled_train['class'] == '2A') ? 'selected' : ''; ?>>Second AC (2A)</option>
<option value="3A" <?php echo ($pre_filled_train && $pre_filled_train['class'] == '3A') ? 'selected' : ''; ?>>Third AC (3A)</option>
<option value="SL" <?php echo ($pre_filled_train && $pre_filled_train['class'] == 'SL') ? 'selected' : ''; ?>>Sleeper (SL)</option>
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
<h3><?php echo htmlspecialchars($train['train_name']); ?>
(<?php echo htmlspecialchars($train['train_number']); ?>)</h3>
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
<input type="hidden" name="train_number"
value="<?php echo htmlspecialchars($train['train_number']); ?>">
<input type="hidden" name="from_station"
value="<?php echo htmlspecialchars($_POST['from_station']); ?>">
<input type="hidden" name="to_station" value="<?php echo htmlspecialchars($_POST['to_station']); ?>">
<input type="hidden" name="journey_date"
value="<?php echo htmlspecialchars($_POST['journey_date']); ?>">
<input type="hidden" name="class" value="<?php echo htmlspecialchars($_POST['class']); ?>">
<input type="hidden" name="fare" value="<?php echo htmlspecialchars($train['selected_fare']); ?>">
<button type="submit" name="show_booking_form" class="btn">Book Now</button>
</form>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (isset($_POST['show_booking_form']) || $pre_filled_train): ?>
<form method="POST" id="bookingForm">
<input type="hidden" name="train_number" value="<?php
echo $pre_filled_train ? htmlspecialchars($pre_filled_train['train_number']) : htmlspecialchars($_POST['train_number']);
?>">
<input type="hidden" name="from_station" value="<?php
echo $pre_filled_train ? htmlspecialchars($pre_filled_train['from_station']) : htmlspecialchars($_POST['from_station']);
?>">
<input type="hidden" name="to_station" value="<?php
echo $pre_filled_train ? htmlspecialchars($pre_filled_train['to_station']) : htmlspecialchars($_POST['to_station']);
?>">
<input type="hidden" name="journey_date" value="<?php
echo $pre_filled_train ? htmlspecialchars($pre_filled_train['journey_date']) : htmlspecialchars($_POST['journey_date']);
?>">
<input type="hidden" name="class" value="<?php
echo $pre_filled_train ? htmlspecialchars($pre_filled_train['class']) : htmlspecialchars($_POST['class']);
?>">
<input type="hidden" name="fare" value="<?php
echo $pre_filled_train ? htmlspecialchars($pre_filled_train['fare']) : htmlspecialchars($_POST['fare']);
?>">

<div id="passengersContainer">
<div class="passenger-section">
<h3 style="color: white; margin: 1rem 0;">Passenger 1</h3>
<div class="grid">
<div class="form-group">
<label>Passenger Name</label>
<input type="text" name="passenger_name[]" class="form-control" required>
</div>
<div class="form-group">
<label>Age</label>
<input type="number" name="age[]" class="form-control" required min="1" max="120">
</div>
</div>

<div class="grid">
<div class="form-group">
<label>Gender</label>
<select name="gender[]" class="form-control" required>
<option value="Male">Male</option>
<option value="Female">Female</option>
<option value="Other">Other</option>
</select>
</div>
<div class="form-group">
<label>Berth Preference</label>
<select name="berth_preference[]" class="form-control" required>
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
<select name="document_type[]" class="form-control" required>
<option value="Aadhar">Aadhar Card</option>
<option value="PAN">PAN Card</option>
<option value="Passport">Passport</option>
<option value="Driving License">Driving License</option>
<option value="Voter ID">Voter ID</option>
</select>
</div>
<div class="form-group">
<label>Document Number</label>
<input type="text" name="document_number[]" class="form-control" required>
</div>
</div>
</div>
</div>

<button type="button" onclick="addPassenger()" class="btn" style="margin: 1rem 0;">+ Add Passenger</button>

<div class="form-group">
<label>Total Fare</label>
<input type="text" id="totalFare" value="₹<?php echo number_format($display_fare, 2); ?>"
class="form-control" readonly>
</div>
<button type="submit" name="book_ticket" class="btn">Confirm Booking</button>
</form>
<script>
let passengerCount = 1;
const maxPassengers = 6;
const basefare = <?php echo json_encode($display_fare); ?>;

function updateTotalFare() {
const totalFareElement = document.getElementById('totalFare');
const newFare = basefare * passengerCount;
totalFareElement.value = `₹${newFare.toFixed(2)}`;
}

function addPassenger() {
if (passengerCount >= maxPassengers) {
alert('Maximum 6 passengers allowed per booking');
return;
}

passengerCount++;
const container = document.getElementById('passengersContainer');
const newSection = document.createElement('div');
newSection.className = 'passenger-section';
newSection.innerHTML = `
<h3 style="color: white; margin: 1rem 0;">Passenger ${passengerCount} <button type="button" onclick="removePassenger(this)" style="float: right; background: #ff4444; padding: 5px 10px; border: none; border-radius: 4px; color: white; cursor: pointer;">Remove</button></h3>
<!-- Copy the same form fields structure from the first passenger -->
<div class="grid">
<div class="form-group">
<label>Passenger Name</label>
<input type="text" name="passenger_name[]" class="form-control" required>
</div>
<div class="form-group">
<label>Age</label>
<input type="number" name="age[]" class="form-control" required min="1" max="120">
</div>
</div>

<div class="grid">
<div class="form-group">
<label>Gender</label>
<select name="gender[]" class="form-control" required>
<option value="Male">Male</option>
<option value="Female">Female</option>
<option value="Other">Other</option>
</select>
</div>
<div class="form-group">
<label>Berth Preference</label>
<select name="berth_preference[]" class="form-control" required>
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
<select name="document_type[]" class="form-control" required>
<option value="Aadhar">Aadhar Card</option>
<option value="PAN">PAN Card</option>
<option value="Passport">Passport</option>
<option value="Driving License">Driving License</option>
<option value="Voter ID">Voter ID</option>
</select>
</div>
<div class="form-group">
<label>Document Number</label>
<input type="text" name="document_number[]" class="form-control" required>
</div>
</div>
`;
container.appendChild(newSection);
updateTotalFare();
}

function removePassenger(button) {
button.closest('.passenger-section').remove();
passengerCount--;
updateTotalFare();
}

function updateTotalFare() {
const totalFareElement = document.getElementById('totalFare');
const newFare = basefare * passengerCount;
totalFareElement.value = `₹${newFare}`; // Fixed template literal syntax
}
function removePassenger(button) {
button.closest('.passenger-section').remove();
passengerCount--;
updateTotalFare();

const passengerSections = document.querySelectorAll('.passenger-section');
passengerSections.forEach((section, index) => {
const heading = section.querySelector('h3');
heading.innerHTML = `Passenger ${index + 1} ${index > 0 ? '<button type="button" onclick="removePassenger(this)" style="float: right; background: #ff4444; padding: 5px 10px; border: none; border-radius: 4px; color: white; cursor: pointer;">Remove</button>' : ''}`;
});
}
</script>
<?php endif; ?>
</div>
<?php if ($booking_success): ?>
<div class="container">
<div class="success-message">
<h2>Booking Successful!</h2>
<div class="booking-details" id="ticketDetails">
<p><strong>PNR Number:</strong> <?php echo htmlspecialchars($generated_pnr); ?></p>

<?php foreach ($generated_seats as $index => $passenger): ?>
<div class="passenger-details">
<h3>Passenger <?php echo $index + 1; ?></h3>
<p><strong>Name:</strong> <?php echo htmlspecialchars($passenger['name']); ?></p>
<p><strong>Coach:</strong> <?php echo htmlspecialchars($passenger['seat_details']['coach']); ?></p>
<p><strong>Seat Number:</strong>
<?php echo htmlspecialchars($passenger['seat_details']['berth_type'] . $passenger['seat_details']['seat_number']); ?>
</p>
</div>
<?php endforeach; ?>

<div id="additionalTicketInfo" style="display: none;">
<p><strong>From:</strong> <?php echo htmlspecialchars($_POST['from_station']); ?></p>
<p><strong>To:</strong> <?php echo htmlspecialchars($_POST['to_station']); ?></p>
<p><strong>Journey Date:</strong> <?php echo htmlspecialchars($_POST['journey_date']); ?></p>
<p><strong>Class:</strong> <?php echo htmlspecialchars($_POST['class']); ?></p>
<p><strong>Total Fare:</strong>
₹<?php echo htmlspecialchars($_POST['fare'] * count($generated_seats)); ?></p>
</div>
</div>
<button onclick="generatePDF()" class="btn" style="margin-top: 1rem;">Download Ticket</button>
</div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
// Pass PHP variables to JavaScript
const ticketData = {
    pnrNumber: '<?php echo $generated_pnr; ?>',
    trainNumber: '<?php echo addslashes($_POST['train_number']); ?>',
    trainName: '<?php 
        $train_query = $conn->prepare("SELECT train_name FROM trains WHERE train_number = ?");
        $train_query->bind_param("s", $_POST['train_number']);
        $train_query->execute();
        $train_result = $train_query->get_result();
        $train = $train_result->fetch_assoc();
        echo addslashes($train['train_name']);
    ?>',
    fromStation: '<?php echo addslashes($_POST['from_station']); ?>',
    toStation: '<?php echo addslashes($_POST['to_station']); ?>',
    journeyDate: '<?php echo $_POST['journey_date']; ?>',
    travelClass: '<?php echo $_POST['class']; ?>',
    totalFare: '<?php echo $_POST['fare'] * count($generated_seats); ?>',
    passengers: <?php echo json_encode($generated_seats); ?>
};

function generatePDF() {
if (typeof window.jspdf === 'undefined') {
console.error('jsPDF library not loaded. Please check if the library is properly included.');
return;
}

const { jsPDF } = window.jspdf;
const doc = new jsPDF();

// Create logo image object
const logoImg = new Image();
logoImg.src = 'logo.jpg';

logoImg.onerror = function () {
console.error('Error loading logo image. Continuing without logo...');
proceedWithPDFGeneration(null);
};

logoImg.onload = function () {
proceedWithPDFGeneration(logoImg);
};

function proceedWithPDFGeneration(logoImg) {
const imgWidth = 40;
let startY;

// Function to add header to each page
function addHeader(pageNumber) {
if (logoImg) {
const imgHeight = (logoImg.height * imgWidth) / logoImg.width;
doc.addImage(logoImg, 'JPG', (doc.internal.pageSize.width - imgWidth) / 2, 10, imgWidth, imgHeight);
startY = imgHeight + 20;
} else {
startY = 30;
}

// Add header text
doc.setFont('helvetica');
doc.setFontSize(20);
doc.setTextColor(30, 60, 114);
doc.text('RailwayYatri E-Ticket', doc.internal.pageSize.width / 2, startY, { align: 'center' });

// Add decorative line
doc.setDrawColor(30, 60, 114);
doc.setLineWidth(0.5);
doc.line(20, startY + 5, 190, startY + 5);

// Add page number
doc.setFontSize(10);
doc.text(`Page ${pageNumber}`, 180, 10);

return startY;
}

try {
            let pageNumber = 1;
            startY = addHeader(pageNumber);

            // Add journey details using ticketData
            doc.setFontSize(14);
            doc.setTextColor(0, 0, 0);
            doc.text(`PNR: ${ticketData.pnrNumber}`, 20, startY + 15);

            doc.setFontSize(12);
            doc.text(`Train Number: ${ticketData.trainNumber}`, 20, startY + 25);
            doc.text(`Train Name: ${ticketData.trainName}`, 120, startY + 25);
            doc.text(`From: ${ticketData.fromStation}`, 20, startY + 35);
            doc.text(`To: ${ticketData.toStation}`, 120, startY + 35);
            doc.text(`Journey Date: ${ticketData.journeyDate}`, 20, startY + 45);
            doc.text(`Class: ${ticketData.travelClass}`, 120, startY + 45);

            // Adjust subsequent positioning
            let yPos = startY + 65;
            doc.setFontSize(14);
            doc.text('Passenger Details', 20, yPos);
            doc.setFontSize(12);
            yPos += 10;

            const spacePerPassenger = 50;
            const maxYPos = 250;

            // Loop through passengers from ticketData
            ticketData.passengers.forEach((passenger, index) => {
            if (yPos + spacePerPassenger > maxYPos) {
            doc.addPage();
            pageNumber++;
            yPos = addHeader(pageNumber) + 15;
            }

            doc.text(`Passenger ${index + 1}:`, 20, yPos);
            yPos += 7;
            doc.text(`Name: ${passenger.name}`, 30, yPos);
            yPos += 7;
            doc.text(`Age: ${passenger.age}`, 30, yPos);
            yPos += 7;
            doc.text(`Gender: ${passenger.gender}`, 30, yPos);
            yPos += 7;
            doc.text(`Coach: ${passenger.seat_details.coach}`, 30, yPos);
            yPos += 7;
            doc.text(`Seat: ${passenger.seat_details.berth_type}${passenger.seat_details.seat_number}`, 30, yPos);
            yPos += 15;
        });

// Add total fare and footer
if (yPos + 50 > maxYPos) {
doc.addPage();
pageNumber++;
yPos = addHeader(pageNumber) + 15;
}

doc.text(`Total Fare: ₹${ticketData.totalFare}`, 20, yPos);

// Footer
yPos += 20;
doc.setFontSize(10);
doc.setTextColor(100, 100, 100);
doc.text('Terms and Conditions:', 20, yPos);
yPos += 7;
doc.text('1. Please carry valid ID proof during journey', 25, yPos);
yPos += 7;
doc.text('2. Arrive at least 30 minutes before departure', 25, yPos);
yPos += 7;
doc.text('3. This is a valid ticket when shown with ID proof', 25, yPos);

// Bottom line and timestamp
yPos += 15;
doc.setDrawColor(30, 60, 114);
doc.setLineWidth(0.5);
doc.line(20, yPos, 190, yPos);

doc.setFontSize(8);
doc.text('Booked on: ' + new Date().toLocaleString(), 20, yPos + 10);

// Save the PDF
doc.save(`RailwayYatri_Ticket_${ticketData.pnrNumber}.pdf`);
} catch (error) {
console.error('Error generating PDF:', error);
alert('An error occurred while generating the PDF. Please try again.');
}
}
}
</script>


<style>
.success-message {
background: white;
padding: 2rem;
border-radius: 8px;
box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
max-width: 800px;
margin: 2rem auto;
}

.booking-details {
margin-top: 1.5rem;
}

.passenger-details {
background: #f8f9fa;
padding: 1rem;
margin: 1rem 0;
border-radius: 4px;
border-left: 4px solid #1e3c72;
}

.passenger-details h3 {
color: #1e3c72;
margin-bottom: 0.5rem;
}

.passenger-details p {
margin: 0.5rem 0;
}

.btn {
background: linear-gradient(to right, #1e3c72, #2a5298);
color: white;
border: none;
padding: 0.75rem 1.5rem;
border-radius: 4px;
cursor: pointer;
transition: all 0.3s ease;
}

.btn:hover {
transform: translateY(-2px);
box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}
</style>
<?php endif; ?>
<script src="./script/login_script.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

<script>
AOS.init({
duration: 800,
easing: 'ease-in-out'
});
</script>
</body>


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