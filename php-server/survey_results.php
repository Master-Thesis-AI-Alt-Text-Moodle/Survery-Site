<?php
session_start();
// TODO: Add authentication check here to ensure only authorized users can access this page

// Database connection
$servername = "";
$username = "";
$password = "";
$dbname = "";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define models
$models = [
    'blip-base_cpu',
    'gpt4-mini-low',
    'InternVL2-1B',
    'uform-gen2',
    'vit-gpt2'
];

// Fetch survey data
$sql = "SELECT image_index, rating_1, rating_2, rating_3, rating_4, rating_5 FROM image_ratings";
$result = $conn->query($sql);

$surveyData = [];
$modelPerformance = array_fill_keys($models, ['sum' => 0, 'count' => 0]);

while($row = $result->fetch_assoc()) {
    $imageIndex = $row['image_index'];
    if (!isset($surveyData[$imageIndex])) {
        $surveyData[$imageIndex] = [
            'count' => 0,
            'ratings' => array_fill_keys($models, 0)
        ];
    }
    $surveyData[$imageIndex]['count']++;
    foreach ($models as $index => $model) {
        $rating = $row["rating_" . ($index + 1)];
        $surveyData[$imageIndex]['ratings'][$model] += $rating;
        $modelPerformance[$model]['sum'] += $rating;
        $modelPerformance[$model]['count']++;
    }
}

// Calculate averages
foreach ($surveyData as &$data) {
    foreach ($models as $model) {
        $data['ratings'][$model] /= $data['count'];
    }
}

// Calculate overall model performance
foreach ($modelPerformance as &$performance) {
    $performance['average'] = $performance['sum'] / $performance['count'];
}

// Fetch user details
$sql = "SELECT image_index, rating_1, rating_2, rating_3, rating_4, rating_5, user_agent, fingerprint, ip_address, session_id, timestamp FROM image_ratings ORDER BY timestamp DESC";
$result = $conn->query($sql);

$userDetails = [];
$userAverages = [];
while($row = $result->fetch_assoc()) {
    $userKey = $row['ip_address'] . '|' . $row['user_agent'];
    if (!isset($userAverages[$userKey])) {
        $userAverages[$userKey] = ['sum' => 0, 'count' => 0, 'timestamp' => strtotime($row['timestamp'])];
    }
    $userAverages[$userKey]['sum'] += array_sum(array_slice($row, 1, 5));
    $userAverages[$userKey]['count'] += 5;
    $userDetails[] = $row;
}

// Calculate user averages and prepare for histogram
$userAveragesForHistogram = [];
foreach ($userAverages as $key => &$avg) {
    $avg['average'] = $avg['sum'] / $avg['count'];
    $userAveragesForHistogram[] = $avg['average'];
}

// Sort userAverages by timestamp (most recent first)
uasort($userAverages, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});

$conn->close();

// Convert data to JSON for JavaScript use
$surveyDataJson = json_encode($surveyData);
$modelPerformanceJson = json_encode($modelPerformance);
$userDetailsJson = json_encode($userDetails);
$userAveragesJson = json_encode($userAverages);
$userAveragesForHistogramJson = json_encode($userAveragesForHistogram);

// Include the HTML template
include 'survey_results_template.html';
?>