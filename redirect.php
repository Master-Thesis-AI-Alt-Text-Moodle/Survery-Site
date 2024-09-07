<?php
// You can customize this message
$message = "Thank you for your interest. As you've declined the use of cookies, we cannot proceed with the evaluation.";

// You can customize the redirect URL
$redirect_url = "https://riespatrick.de/";

// Wait for 5 seconds before redirecting
header("refresh:5;url=$redirect_url");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
        <h1 class="text-2xl font-bold mb-4">Redirecting</h1>
        <p class="mb-4"><?php echo $message; ?></p>
        <p>You will be redirected in 5 seconds. If not, <a href="<?php echo $redirect_url; ?>" class="text-blue-600 hover:underline">click here</a>.</p>
    </div>
</body>
</html>