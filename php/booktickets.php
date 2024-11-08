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