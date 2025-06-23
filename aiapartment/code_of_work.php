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
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">HomeEasy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Buy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Rent</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Sell</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Get a mortgage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Find an Agent</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="logout.php" class="btn btn-outline-primary">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Welcome Box -->
    <div class="container mt-4">
        <div class="welcome-box">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3>Welcome, <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong>!</h3>
                    <p class="mb-0">Continue exploring properties or update your preferences to find your perfect match.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="form.php" class="btn btn-primary">Update Preferences</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero" style="background-image: url('/api/placeholder/1200/500');">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 hero-content">
                    <h1>Find the right home at the right price</h1>
                    <p>Search homes across the country and get personalized recommendations based on your preferences.</p>
                    
                    <!-- Search Box -->
                    <div class="search-box d-flex align-items-center">
                        <div class="flex-grow-1">
                            <input type="text" class="search-input" placeholder="Enter an address, neighborhood, city, or ZIP code">
                        </div>
                        <div>
                            <button class="search-button">
                                <i class="fas fa-search me-2"></i>Search
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3>Find Your Home</h3>
                        <p>Browse thousands of listings to find the perfect home for you and your family.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3>Get a Cash Offer</h3>
                        <p>Sell your home directly to us and skip the hassle of listing and showings.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3>Connect with Agents</h3>
                        <p>Find top local agents who can help you buy or sell your home.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Properties Section -->
    <section class="properties">
        <div class="container">
            <div class="section-title">
                <h2>Featured Properties</h2>
                <p>Explore our handpicked selection of properties recommended for you</p>
            </div>
            
            <div class="row">
                <!-- Property 1 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="property-card">
                        <div class="property-image" style="background-image: url('/api/placeholder/400/200');"></div>
                        <div class="property-content">
                            <div class="property-price">$695,000</div>
                            <div class="property-address">123 Main Street, Chicago, IL 60601</div>
                            <div class="property-details">
                                <div class="property-detail">
                                    <i class="fas fa-bed"></i>
                                    <span>4 beds</span>
                                </div>
                                <div class="property-detail">
                                    <i class="fas fa-bath"></i>
                                    <span>3 baths</span>
                                </div>
                                <div class="property-detail">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>3,102 sqft</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Property 2 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="property-card">
                        <div class="property-image" style="background-image: url('/api/placeholder/400/200');"></div>
                        <div class="property-content">
                            <div class="property-price">$438,000</div>
                            <div class="property-address">456 Oak Avenue, Chicago, IL 60602</div>
                            <div class="property-details">
                                <div class="property-detail">
                                    <i class="fas fa-bed"></i>
                                    <span>3 beds</span>
                                </div>
                                <div class="property-detail">
                                    <i class="fas fa-bath"></i>
                                    <span>2 baths</span>
                                </div>
                                <div class="property-detail">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>2,200 sqft</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Property 3 -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="property-card">
                        <div class="property-image" style="background-image: url('/api/placeholder/400/200');"></div>
                        <div class="property-content">
                            <div class="property-price">$846,000</div>
                            <div class="property-address">789 Pine Lane, Chicago, IL 60603</div>
                            <div class="property-details">
                                <div class="property-detail">
                                    <i class="fas fa-bed"></i>
                                    <span>5 beds</span>
                                </div>
                                <div class="property-detail">
                                    <i class="fas fa-bath"></i>
                                    <span>4 baths</span>
                                </div>
                                <div class="property-detail">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>4,500 sqft</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="#" class="btn btn-outline-primary">View All Properties</a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container text-center">
            <h2>Ready to Make the Easy Move?</h2>
            <p class="mb-4">Get a cash offer today. See the ways we can help you sell your home.</p>
            <a href="form.php" class="btn cta-button">Get Started</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="footer-logo">HomeEasy</div>
                    <p>Making it simple to find and secure your perfect home.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <div class="footer-links">
                        <h5>Services</h5>
                        <ul>
                            <li><a href="#">Buy a Home</a></li>
                            <li><a href="#">Sell a Home</a></li>
                            <li><a href="#">Rent a Home</a></li>
                            <li><a href="#">Mortgage</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <div class="footer-links">
                        <h5>About</h5>
                        <ul>
                            <li><a href="#">Our Story</a></li>
                            <li><a href="#">Careers</a></li>
                            <li><a href="#">Press</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <div class="footer-links">
                        <h5>Stay Connected</h5>
                        <p>Subscribe to our newsletter for the latest updates.</p>
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Your email">
                            <button class="btn btn-light" type="button">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 HomeEasy. All rights reserved.</p>
                <p>
                    <a href="#" class="text-decoration-none me-3">Terms & Conditions</a> | 
                    <a href="#" class="text-decoration-none">Privacy Policy</a>
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>