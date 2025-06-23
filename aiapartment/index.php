<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeEasy | Find Your Perfect Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #f97316;
            --light-bg: #f8fafc;
            --dark-text: #0f172a;
            --light-text: #64748b;
        }
        
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--dark-text);
            background-color: var(--light-bg);
        }
        
        /* Header */
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            background-color: white;
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color);
        }
        
        .nav-link {
            font-weight: 600;
            color: var(--dark-text);
            margin: 0 0.5rem;
        }
        
        /* Hero Section */
        .hero {
            background-size: cover;
            background-position: center;
            padding: 4rem 0;
            position: relative;
        }
        
        .hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3));
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            max-width: 600px;
        }
        
        /* Search Box */
        .search-box {
            background-color: white;
            border-radius: 8px;
            padding: 0.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .search-input {
            border: none;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            width: 100%;
            outline: none;
        }
        
        .search-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        
        .search-button:hover {
            background-color: var(--secondary-color);
        }
        
        /* Features Section */
        .features {
            padding: 4rem 0;
        }
        
        .feature-card {
            background-color: white;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            background-color: rgba(37, 99, 235, 0.1);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .feature-icon i {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        /* Property Section */
        .properties {
            padding: 4rem 0;
            background-color: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .section-title p {
            color: var(--light-text);
            max-width: 600px;
            margin: 0 auto;
        }
        
        .property-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .property-image {
            height: 200px;
            background-size: cover;
            background-position: center;
        }
        
        .property-content {
            padding: 1.5rem;
        }
        
        .property-price {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .property-address {
            color: var(--light-text);
            margin-bottom: 1rem;
        }
        
        .property-details {
            display: flex;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .property-detail {
            display: flex;
            align-items: center;
        }
        
        .property-detail i {
            margin-right: 0.5rem;
            color: var(--light-text);
        }
        
        /* CTA Section */
        .cta {
            padding: 4rem 0;
            background-color: var(--primary-color);
            color: white;
        }
        
        .cta h2 {
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .cta-button {
            background-color: white;
            color: var(--primary-color);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 4px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        
        .cta-button:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
        }
        
        /* User Welcome Box */
        .welcome-box {
            background-color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Footer */
        footer {
            background-color: #0f172a;
            color: white;
            padding: 4rem 0 2rem;
        }
        
        .footer-logo {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .footer-links h5 {
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .footer-links ul {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #94a3b8;
        }
        
        .social-links {
            margin-top: 1rem;
        }
        
        .social-links a {
            color: white;
            margin: 0 0.5rem;
            font-size: 1.25rem;
        }
    </style>
     <style>
        /* Property Slider Section */
        .property-slider-section {
            padding: 4rem 0;
            background-color: var(--light-bg);
        }
        
        .property-slider-container {
            position: relative;
            padding: 0 50px;
        }
        
        .property-slider {
            display: flex;
            overflow-x: hidden;
            scroll-behavior: smooth;
            gap: 20px;
            padding: 20px 0;
        }
        
        .property-slide {
            min-width: calc(25% - 15px);
            flex: 0 0 calc(25% - 15px);
            transition: transform 0.3s ease;
        }
        
        .property-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            background-color: white;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .property-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .property-label {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: var(--accent-color);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .property-content {
            padding: 1.5rem;
        }
        
        .property-price {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .property-address {
            color: var(--light-text);
            margin-bottom: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .property-details {
            display: flex;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .property-detail {
            display: flex;
            align-items: center;
        }
        
        .property-detail i {
            margin-right: 0.5rem;
            color: var(--light-text);
        }
        
        .slider-control {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background-color: white;
            border-radius: 50%;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: background-color 0.3s ease;
        }
        
        .slider-control:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .slider-prev {
            left: 0;
        }
        
        .slider-next {
            right: 0;
        }
        
        .slider-indicators {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
            gap: 8px;
        }
        
        .slider-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #cbd5e1;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .slider-indicator.active {
            background-color: var(--primary-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 1199.98px) {
            .property-slide {
                min-width: calc(33.333% - 14px);
                flex: 0 0 calc(33.333% - 14px);
            }
        }
        
        @media (max-width: 991.98px) {
            .property-slide {
                min-width: calc(50% - 10px);
                flex: 0 0 calc(50% - 10px);
            }
        }
        
        @media (max-width: 575.98px) {
            .property-slide {
                min-width: 100%;
                flex: 0 0 100%;
            }
            
            .property-slider-container {
                padding: 0 30px;
            }
        }
    </style>
</head>
<body>
    <?php include_once "./layouts/nav.php"; ?>

    

</body>
</html>