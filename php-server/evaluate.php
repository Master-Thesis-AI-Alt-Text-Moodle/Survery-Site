<?php
$imageDir = '/images/';
$jsonFile = 'all_image_texts.json';
session_start();

$allTexts = json_decode(file_get_contents($jsonFile), true);
$images = array_filter(scandir($_SERVER['DOCUMENT_ROOT'] . $imageDir), function($file) {
    return !is_dir($file) && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
});

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$totalImages = count($images);

if ($id >= $totalImages) {
    header("Location: thank_you.php");
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

$nextId = $id + 1;
$prevId = $id - 1;
?>
<?php
// PHP code remains the same as before, just update any comments to English if needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>Image Evaluation - Image <?php echo $id + 1; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fafc;
        }
        .evaluation-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .slider {
            -webkit-appearance: none;
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: #e2e8f0;
            outline: none;
        }
        .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%; 
            background: #4a5568;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }
        .slider::-webkit-slider-thumb:hover {
            background: #2d3748;
        }
        .slider::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #4a5568;
            cursor: pointer;
            transition: background .15s ease-in-out;
        }
        .slider::-moz-range-thumb:hover {
            background: #2d3748;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <header class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-800">Image Evaluation</h1>
            <p class="text-sm text-gray-600">Image <?php echo $id + 1; ?> of <?php echo $totalImages; ?></p>
        </header>
        
        <div class="evaluation-card p-6 mb-8">
            <div class="mb-6">
                <img src="<?php echo $imageDir . $image; ?>" alt="Image <?php echo $id + 1; ?>" class="w-full h-auto object-contain rounded-md" style="max-height: 300px;">
            </div>

            <div class="space-y-6">
                <?php foreach ($texts as $index => $text): ?>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <p class="text-sm font-medium text-gray-700 mb-2"><?php echo htmlspecialchars($text); ?></p>
                        <input type="range" min="1" max="5" value="3" class="slider" id="slider-<?php echo $index; ?>">
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Poor</span>
                            <span>Average</span>
                            <span>Excellent</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-8 flex justify-between items-center">
                <?php if ($prevId >= 0): ?>
                    <a href="?id=<?php echo $prevId; ?>" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300 transition duration-300">Previous</a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>

                <?php if ($nextId < $totalImages): ?>
                    <a href="?id=<?php echo $nextId; ?>" onclick="return submitRatings(<?php echo $id; ?>)" class="bg-blue-500 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-600 transition duration-300">Next</a>
                <?php else: ?>
                    <a href="thank_you.php" onclick="return submitRatings(<?php echo $id; ?>)" class="bg-green-500 text-white px-4 py-2 rounded-md text-sm hover:bg-green-600 transition duration-300">Finish</a>
                <?php endif; ?>
            </div>
        </div>

        <footer class="text-center text-gray-500 text-xs">
            © 2024 Image-Text Evaluation. All rights reserved.
        </footer>
    </div>

    <script>
function submitRatings(imageIndex) {
    const ratings = [];
    for (let i = 0; i < 5; i++) {
        const slider = document.getElementById(`slider-${i}`);
        ratings.push(slider.value);
    }

    fetch('save_ratings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            imageIndex: imageIndex,
            ratings: ratings
        }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Ratings saved successfully');
            // Optionally, you can add a visual confirmation for the user here
        } else {
            console.error('Error saving ratings:', data.error);
            alert('Error saving ratings: ' + data.error);
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        if (error.message === "Failed to fetch") {
            alert('Network error. Please check your internet connection and try again.');
        } else {
            alert('Error saving ratings: ' + error.message);
        }
    });

    return true;
}
    </script>
</body>
</html>