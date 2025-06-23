<?php
/**
 * HomeEasy - Improved Routing System
 * 
 * This file handles all routing for the HomeEasy website using separate page files.
 */

// Define the base path of your application
define('BASE_PATH', __DIR__);

/**
 * Simple Router Class
 */
class Router {
    private $routes = [];
    private $notFoundCallback;
    
    /**
     * Register a route
     * 
     * @param string $path The URL path to match
     * @param callable|string $callback The function to call or file to include when the route is matched
     */
    public function route($path, $callback) {
        $this->routes[$path] = $callback;
    }
    
    /**
     * Set a callback for 404 Not Found
     * 
     * @param callable|string $callback The function to call or file to include when no route matches
     */
    public function notFound($callback) {
        $this->notFoundCallback = $callback;
    }
    
    /**
     * Run the router on the current request
     */
    // In your router.php file, update the run() method like this:

public function run() {
    // Get the current request path
    $path = '/';
    
    if (isset($_SERVER['PATH_INFO'])) {
        $path = $_SERVER['PATH_INFO'];
    } elseif (isset($_GET['page'])) {
        // Get just the base page without query parameters
        $path = strtok($_GET['page'], '?');
    }
    
    // Remove trailing slash if it exists (except for root path)
    if ($path !== '/' && substr($path, -1) === '/') {
        $path = rtrim($path, '/');
    }
    
    // Special handling for edit and delete routes
    if (isset($_GET['page']) && (strpos($_GET['page'], 'edit') === 0 || strpos($_GET['page'], 'delete') === 0)) {
        $path = strpos($_GET['page'], 'edit') === 0 ? 'edit' : 'delete';
    }
    
    // Execute the matching route callback or include file
    if (isset($this->routes[$path])) {
        if (is_callable($this->routes[$path])) {
            call_user_func($this->routes[$path]);
        } else {
            $file = BASE_PATH . $this->routes[$path];
            if (file_exists($file)) {
                include $file;
            } else {
                echo "Error: Route file '$file' not found";
            }
        }
    } else {
        // No route matched, call the notFound callback or include file
        if ($this->notFoundCallback) {
            if (is_callable($this->notFoundCallback)) {
                call_user_func($this->notFoundCallback);
            } else {
                $file = BASE_PATH . $this->notFoundCallback;
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo "404 Page Not Found";
                }
            }
        } else {
            echo "404 Page Not Found";
        }
    }
}
}

/**
 * View Renderer - Simple template system
 */
class View {
    /**
     * Render a view with data
     * 
     * @param string $view The view file to render
     * @param array $data Data to pass to the view
     * @return string The rendered content
     */
    public static function render($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Buffer the output
        ob_start();
        
        // Include the view file
        $viewFile = BASE_PATH . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo "Error: View file '$viewFile' not found";
        }
        
        // Get the content and clean the buffer
        $content = ob_get_clean();
        
        // Return the content
        return $content;
    }
}

/**
 * Helper function to render a complete page with the layout
 * 
 * @param string $title The page title
 * @param string $content The page content
 */
function renderPage($title, $content) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?></title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <!-- Custom CSS -->
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
            
            /* Footer */
            footer {
                background-color: #0f172a;
                color: white;
                padding: 2rem 0;
                margin-top: 3rem;
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
        <!-- Additional page-specific styles can be loaded here -->
    </head>
    <body>
        <!-- Navigation Bar -->
<?php include(__DIR__ . '../pages/navbar.php');
 ?>


        <!-- Main Content -->
        <main class="container my-4">
            <?php echo $content; ?>
        </main>

        <!-- Footer -->
        <footer>
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <div class="footer-logo">HomeEasy</div>
                        <p>Find your dream home with our comprehensive property listings and expert agents.</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                    <div class="col-md-2 footer-links">
                        <h5>Quick Links</h5>
                        <ul>
                            <li><a href="?page=buy">Buy</a></li>
                            <li><a href="?page=rent">Rent</a></li>
                            <li><a href="?page=sell">Sell</a></li>
                            <li><a href="?page=mortgage">Mortgage</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3 footer-links">
                        <h5>Company</h5>
                        <ul>
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Careers</a></li>
                            <li><a href="#">Blog</a></li>
                            <li><a href="#">Contact Us</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3 footer-links">
                        <h5>Legal</h5>
                        <ul>
                            <li><a href="#">Terms of Service</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Cookie Policy</a></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; <?php echo date('Y'); ?> HomeEasy. All rights reserved.</p>
                </div>
            </div>
        </footer>

        <!-- Bootstrap JS Bundle with Popper -->
        <!-- Additional page-specific scripts can be loaded here -->
    </body>
    </html>
    <?php
}

// Function to handle logout
function logout() {
    // Only destroy if session exists
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Clear all session variables
        $_SESSION = array();
        
        // If there's a session cookie, destroy it too
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
    }
    
    // Instead of using header() redirect, use JavaScript for redirection
    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}

// Check if logout was requested
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    logout();
}

// ----------------------------------------
// Router Setup
// ----------------------------------------

// Create a new router
$router = new Router();

// Define routes using separate page files
$router->route('/', '/pages/home.php');
$router->route('/index.php', '/pages/home.php');
$router->route('buy', '/pages/buy.php');
$router->route('rent', '/pages/Cart.php');
$router->route('sell', '/pages/sell.php');
$router->route('mortgage', '/pages/mortgage.php');
$router->route('agent', '/pages/agent.php');
$router->route('login', '/pages/login.php');
$router->route('register', '/pages/register.php');
$router->route('property', '/pages/property_details.php');

// Add edit and delete routes
$router->route('edit', '/pages/edit_property.php');
$router->route('delete', '/pages/delete_property.php');
$router->route('view', '/pages/view_property.php');
$router->route('cart', '/pages/rent.php');
$router->route('feedback', '/pages/feedback.php');
$router->route('admin_reports', '/pages/admin_reports.php');
$router->route('profile', '/pages/profile.php');

$router->route('profile', '/pages/profile.php');
$router->route('maps', '/pages/maps.php');
$router->route('search', '/pages/search.php');
$router->route('config', '/pages/config.php');
$router->route('settings', '/pages/settings.php');
$router->route('db', '/pages/db.php');
$router->route('liked', '/pages/liked.php');

$router->route('add_user', '/pages/add_user.php');
$router->route('delete_user', '/pages/delete_user.php');

// Set 404 handler
$router->notFound('/pages/not_found.php');

// Run the router
$router->run();