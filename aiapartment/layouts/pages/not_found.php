<?php
// Start of the PHP 404 error page
http_response_code(404); // Set HTTP response code to 404
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Global styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
            text-align: center;
            color: #333;
        }
        h1 {
            font-size: 10rem;
            font-weight: 600;
            color: #ff4757;
            margin: 0;
        }
        .error-message {
            font-size: 1.5rem;
            color: #555;
            margin: 20px 0;
        }
        .btn {
            padding: 15px 30px;
            background-color: #ff4757;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn:hover {
            background-color: #e84118;
            transform: scale(1.1);
        }
        .btn:active {
            transform: scale(1);
        }
        .home-icon {
            font-size: 50px;
            color: #ff4757;
            margin-top: 20px;
            animation: bounce 2s infinite;
        }

        /* Animation for bouncing effect */
        @keyframes bounce {
            0% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0); }
        }

        .error-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .message-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="error-container">
    <div class="message-container">
        <h1>404</h1>
        <p class="error-message">Oops! The page you're looking for doesn't exist.</p>
        <a href="index.php" class="btn">Go Back to Home</a>
        <i class="fas fa-home home-icon"></i> <!-- Font Awesome Home Icon -->
    </div>
</div>

</body>
</html>
