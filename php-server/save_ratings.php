<?php
session_start();
header('Content-Type: application/json');

function logAttempt($message) {
    $logFile = 'evaluation_attempts.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "$timestamp - $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Verify CSRF token
$headers = getallheaders();
$received_token = isset($headers['X-Csrf-Token']) ? $headers['X-Csrf-Token'] : 
                 (isset($headers['X-CSRF-Token']) ? $headers['X-CSRF-Token'] : null);

if (!$received_token || $received_token !== $_SESSION['csrf_token']) {
    logAttempt("Invalid CSRF token from IP: " . $_SERVER['REMOTE_ADDR']);
    echo json_encode(["success" => false, "error" => "Invalid request"]);
    exit;
}

// Check if the session has already completed an evaluation
if (isset($_SESSION['evaluation_completed']) && $_SESSION['evaluation_completed']) {
    logAttempt("Repeated evaluation attempt from IP: " . $_SERVER['REMOTE_ADDR']);
    echo json_encode(["success" => false, "error" => "Evaluation already completed"]);
    exit;
}

// IP-based rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
$rateLimitFile = 'rate_limit.json';
$rateLimitData = json_decode(file_get_contents($rateLimitFile), true) ?: [];

$currentTime = time();
$timeWindow = 3600; // 1 hour
$maxAttempts = 30;

if (isset($rateLimitData[$ip])) {
    $rateLimitData[$ip] = array_filter($rateLimitData[$ip], function($timestamp) use ($currentTime, $timeWindow) {
        return $timestamp > $currentTime - $timeWindow;
    });
    
    if (count($rateLimitData[$ip]) >= $maxAttempts) {
        logAttempt("Rate limit exceeded for IP: $ip");
        echo json_encode(["success" => false, "error" => "Rate limit exceeded"]);
        exit;
    }
}

$rateLimitData[$ip][] = $currentTime;
file_put_contents($rateLimitFile, json_encode($rateLimitData));

// Browser fingerprinting
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
$fingerprint = md5($ip . $userAgent . $acceptLanguage);

// Database connection
$servername = "";
$username = "";
$password = "";
$dbname = "testdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Connection failed"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$imageIndex = intval($data['imageIndex']);
$ratings = $data['ratings'];

// Map models to rating fields
$modelFieldMap = [
    'blip-base_cpu' => 'rating_1',
    'gpt4-mini-low' => 'rating_2',
    'InternVL2-1B' => 'rating_3',
    'uform-gen2' => 'rating_4',
    'vit-gpt2' => 'rating_5'
];

$mappedRatings = [];
foreach ($modelFieldMap as $model => $field) {
    $mappedRatings[$field] = intval($ratings[$model]);
}

// Timestamp checking
$currentTime = time();
$minTimeBetweenRatings = 3; // seconds

if (isset($_SESSION['last_rating_time']) && ($currentTime - $_SESSION['last_rating_time'] < $minTimeBetweenRatings)) {
    logAttempt("Rating submitted too quickly from IP: $ip");
    echo json_encode(["success" => false, "error" => "Please take your time to evaluate"]);
    exit;
}

$_SESSION['last_rating_time'] = $currentTime;

// Save ratings
$stmt = $conn->prepare("INSERT INTO image_ratings (image_index, rating_1, rating_2, rating_3, rating_4, rating_5, user_agent, ip_address, session_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iiiiissss", $imageIndex, $mappedRatings['rating_1'], $mappedRatings['rating_2'], $mappedRatings['rating_3'], $mappedRatings['rating_4'], $mappedRatings['rating_5'], $userAgent, $ip, $fingerprint);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>