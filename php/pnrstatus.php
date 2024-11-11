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