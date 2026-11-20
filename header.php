<?php
if (!isset($header_included)) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once 'db.php';
}

$is_logged_in = isset($_SESSION['user_id']);
$cart_count = 0;

if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $cart_count = $row['total'] ?? 0;
    }
    $stmt->close();
}
?>
    <!-- Navigation & Logo -->
    <header class="header">
        <div class="container">
            <div class="logo-container">
                <a href="index.php">
                    <img src="images/logo-placeholder.png" alt="Fuzzygueño Logo" class="logo">
                </a>
            </div>
            <nav class="nav">
                <a href="index.php#home" class="nav-link">Home</a>
                <a href="index.php#products" class="nav-link">Products</a>
                <a href="index.php#order" class="nav-link">Custom Order</a>
                <a href="index.php#shipping" class="nav-link">Shipping</a>
                <a href="index.php#about" class="nav-link">About</a>
                <a href="index.php#contact" class="nav-link">Contact</a>
                <a href="index.php#faq" class="nav-link">FAQ</a>
                
                <div class="nav-auth">
                    <?php if ($is_logged_in): ?>
                        <span class="user-info">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="cart.php" class="cart-icon-link">
                            🛒 Cart
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="logout.php" class="btn-logout">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">Login</a>
                        <a href="signup.php" class="btn-signup">Sign Up</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

