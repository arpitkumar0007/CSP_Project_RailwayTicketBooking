<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

function debugLog($message) {
    file_put_contents('debug_session.log', date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

$rawData = file_get_contents('php://input');
debugLog("Received Raw Data: " . $rawData);

$data = json_decode($rawData, true);

debugLog("Decoded Data: " . print_r($data, true));

if (isset($data['user_id'])) {
    $_SESSION['user_id'] = $data['user_id'];
    $_SESSION['email'] = $data['email'];
    $_SESSION['name'] = $data['name'];
    
    debugLog("Session set for user: " . $data['email']);
    
    $response = ['status' => 'success'];
    
    // Check for booking redirect
    if (isset($data['is_booking_redirect']) && $data['is_booking_redirect'] === true) {
        $response['redirect'] = 'booking';
        debugLog("Redirect set to booking");
    }
    
    echo json_encode($response);
} else {
    debugLog("No user ID provided");
    echo json_encode(['status' => 'error']);
}