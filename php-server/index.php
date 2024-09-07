<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>Study: Image-Text Evaluation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
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
        #cookieBanner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #f3f4f6;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    <div id="app" class="max-w-3xl mx-auto">
        <!-- Content will be dynamically loaded here -->
    </div>

    <div id="cookieBanner" class="hidden">
        <p class="mb-2">This website uses cookies and collects some user data to enhance your experience and for evaluation purposes. Please read our <a href="privacy_policy.php" class="text-blue-600 hover:underline">Privacy Policy</a> for more information.</p>
        <div class="space-x-4">
            <button id="acceptCookies" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">Accept</button>
            <button id="declineCookies" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition duration-300">Decline</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="app.js"></script>
    <script>
        // Cookie banner logic
        if (!localStorage.getItem('cookiesAccepted')) {
            document.getElementById('cookieBanner').classList.remove('hidden');
        }
        document.getElementById('acceptCookies').addEventListener('click', function() {
            localStorage.setItem('cookiesAccepted', 'true');
            document.getElementById('cookieBanner').classList.add('hidden');
        });
        document.getElementById('declineCookies').addEventListener('click', function() {
            // Redirect to a neutral page or external site
            //window.location.href = 'https://www.example.com';
        });
    </script>
</body>
</html>