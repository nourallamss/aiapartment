<?php
/**
 * Shopping Cart Page
 * Displays all properties added to cart and handles checkout process
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = 'localhost';
$db = 'forfree';
$user = 'root';
$pass = '';

// Initialize variables
$cart_items = [];
$total = 0;
$error = null;
$success = null;

// Handle remove item action
if (isset($_GET['remove']) && isset($_SESSION['cart'])) {
    $remove_id = intval($_GET['remove']);
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $remove_id) {
            unset($_SESSION['cart'][$key]);
            $success = "Item removed from cart successfully.";
            break;
        }
    }
    // Reindex array after removal
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// Handle update quantities
if (isset($_POST['update_cart']) && isset($_SESSION['cart'])) {
    foreach ($_POST['quantity'] as $id => $quantity) {
        $quantity = intval($quantity);
        if ($quantity < 1) {
            $quantity = 1;
        }
        
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $id) {
                $item['quantity'] = $quantity;
                break;
            }
        }
    }
    $success = "Cart updated successfully.";
}

// Handle checkout
if (isset($_POST['checkout']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    try {
        // Connect to database
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Process each item in cart
        foreach ($_SESSION['cart'] as $item) {
            // Here you would typically:
            // 1. Verify property is still available
            // 2. Create an order record
            // 3. Update inventory/status
            // For this example, we'll just log the transaction
            $stmt = $pdo->prepare("INSERT INTO orders (property_id, user_email, price, created_at) 
                                  VALUES (?, ?, ?, NOW())");
            $stmt->execute([
                $item['id'],
                $_SESSION['email'] ?? 'guest',
                $item['price']
            ]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Clear cart
        unset($_SESSION['cart']);
        
        // Success message
        $success = "Checkout successful! Thank you for your purchase.";
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Checkout failed: " . $e->getMessage();
        error_log("Checkout error: " . $e->getMessage());
    }
}

// Get cart items
if (isset($_SESSION['cart'])) {
    $cart_items = $_SESSION['cart'];
    
    // Calculate total
    foreach ($cart_items as $item) {
        $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
        $total += floatval($item['price']) * $quantity;
    }
}

// Build the content
ob_start();
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
    </ol>
</nav>

<h1 class="mb-4">
    <i class="fas fa-shopping-cart text-primary me-2"></i>Your Shopping Cart
</h1>

<!-- Messages -->
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if (empty($cart_items)): ?>
<div class="empty-cart text-center py-5">
    <i class="fas fa-cart-arrow-down fa-4x text-muted mb-4"></i>
    <h3 class="mb-3">Your cart is empty</h3>
    <p class="text-muted mb-4">Looks like you haven't added any properties to your cart yet.</p>
    <a href="?page=sell" class="btn btn-primary btn-lg">
        <i class="fas fa-home me-2"></i>Browse Properties
    </a>
</div>
<?php else: ?>
<form method="post">
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th width="120">Image</th>
                                    <th>Property</th>
                                    <th width="120">Price</th>
                                    <th width="140">Quantity</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                                            <img src="<?php echo $item['image']; ?>" 
                                                 class="img-fluid rounded" 
                                                 alt="Property image"
                                                 style="width: 80px; height: 60px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 80px; height: 60px;">
                                                <i class="fas fa-home text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['location']); ?></h6>
                                        <small class="text-muted">ID: <?php echo $item['id']; ?></small>
                                    </td>
                                    <td class="fw-bold text-success">
                                        $<?php echo number_format(floatval($item['price']), 2); ?>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="quantity[<?php echo $item['id']; ?>]" 
                                               value="<?php echo isset($item['quantity']) ? $item['quantity'] : 1; ?>" 
                                               min="1" 
                                               class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <a href="?page=cart&remove=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           title="Remove"
                                           onclick="return confirm('Are you sure you want to remove this item?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                        <a href="?page=view&id=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary ms-1"
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between">
                        <a href="?page=sell" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                        </a>
                        <button type="submit" name="update_cart" class="btn btn-outline-primary">
                            <i class="fas fa-sync-alt me-2"></i>Update Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt me-2"></i>Order Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (10%):</span>
                        <span>$<?php echo number_format($total * 0.1, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 fw-bold">
                        <span>Total:</span>
                        <span class="text-success">$<?php echo number_format($total * 1.1, 2); ?></span>
                    </div>
                    <hr>
                    
                    <?php if (isset($_SESSION['email'])): ?>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo $_SESSION['email']; ?>" readonly>
                        </div>
                     
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Please <a href="?page=login" class="alert-link">login</a> to complete your purchase.
                        </div>
                        <a href="?page=login" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Checkout
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="card shadow mt-4">
               
                </div>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<style>
.empty-cart {
    background-color: #f8f9fa;
    border-radius: 0.5rem;
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.quantity-input {
    width: 70px;
    text-align: center;
}

.card {
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

@media (max-width: 991.98px) {
    .order-summary {
        margin-top: 2rem;
    }
}
</style>

<?php
$content = ob_get_clean();

// Render the complete page
renderPage("Shopping Cart | HomeEasy", $content);