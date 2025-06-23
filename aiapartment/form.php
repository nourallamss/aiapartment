<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Role</title>

    <!-- Bootstrap CSS (CDN Link) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS for Styling -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 400px;
            padding: 40px;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
        }

        h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 2rem;
            font-family: 'Roboto', sans-serif;
        }

        .role-button {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 1.125rem;
            border-radius: 12px;
            text-align: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.4);
        }

        .role-button:nth-child(1) {
            background-color: #667eea;
            color: white;
        }

        .role-button:nth-child(2) {
            background-color: #28a745;
            color: white;
        }

        .role-button:hover {
            transform: translateY(-5px);
        }

        .role-button:nth-child(1):hover {
            background-color: #5a55d6;
        }

        .role-button:nth-child(2):hover {
            background-color: #218838;
        }

        /* Optional: Custom arrow icon */
        .role-button:after {
            content: '→';
            margin-left: 10px;
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }

        .role-button:hover:after {
            transform: translateX(5px);
        }
        .red{
            color:red;
        }

    </style>
</head>
<body>

<div class="container">
    <h2>Select Your Role</h2>
    <button class="role-button" onclick="window.location.href='buyer.php'">Buyer</button>
    <button class="role-button red" onclick="window.location.href='seller.php'">Seller</button>
</div>

<!-- Bootstrap JS (Optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
