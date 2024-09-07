<?php
session_start();
header('Content-Type: application/json');

$imageDir = '/images/';
$jsonFile = 'all_image_texts.json';

$allTexts = json_decode(file_get_contents($jsonFile), true);
$images = array_filter(scandir($_SERVER['DOCUMENT_ROOT'] . $imageDir), function($file) {
    return !is_dir($file) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
});

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$totalImages = count($images);

if ($id >= $totalImages) {
    echo json_encode(['error' => 'Image not found']);
    exit;
}

$image = array_values($images)[$id];
$texts = $allTexts[$image]['texts'] ?? [
    "Default text 1 for $image",
    "Default text 2 for $image",
    "Default text 3 for $image",
    "Default text 4 for $image",
    "Default text 5 for $image"
];

$models = [
    'blip-base_cpu',
    'gpt4-mini-low',
    'InternVL2-1B',
    'uform-gen2',
    'vit-gpt2'
];

// Create an array of model-text pairs
$modelTextPairs = array_combine($models, $texts);

// Randomize the order of the pairs
$keys = array_keys($modelTextPairs);
shuffle($keys);
$randomizedPairs = array_combine($keys, array_map(function($key) use ($modelTextPairs) {
    return $modelTextPairs[$key];
}, $keys));

echo json_encode([
    'imagePath' => $imageDir . $image,
    'modelTextPairs' => $randomizedPairs,
    'totalImages' => $totalImages
]);
?>