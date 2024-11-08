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
?>