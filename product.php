<?php
session_start();
require_once 'db.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

$is_logged_in = isset($_SESSION['user_id']);
$page_title = htmlspecialchars($product['name']) . ' - Fuzzygueño';
include 'head.php';
?>
    <style>
        .product-detail-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(255, 182, 217, 0.2);
        }
        .product-detail-image {
            width: 100%;
            border-radius: 15px;
            overflow: hidden;
        }
        .product-detail-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        .product-detail-info h1 {
            color: var(--text-dark);
            margin-bottom: 20px;
            font-size: 32px;
        }
        .product-detail-price {
            font-size: 36px;
            color: var(--pink);
            font-weight: 700;
            margin-bottom: 20px;
        }
        .product-detail-description {
            color: var(--text-light);
            line-height: 1.8;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .add-to-cart-btn {
            padding: 15px 40px;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
        }
        .add-to-cart-btn:hover {
            transform: translateY(-2px);
        }
        .add-to-cart-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .login-prompt {
            background: #fff3cd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        .login-prompt a {
            color: var(--pink);
            font-weight: 600;
            text-decoration: none;
        }
        .login-prompt a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
            }
        }
    </style>
<?php include 'header.php'; ?>
    
    <div class="product-detail-container">
        <div class="product-detail">
            <div class="product-detail-image">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            
            <div class="product-detail-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="product-detail-price">₱<?php echo number_format($product['price'], 2); ?></div>
                <div class="product-detail-description">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
                
                <?php if (!$is_logged_in): ?>
                    <div class="login-prompt">
                        Please <a href="login.php">login</a> to add items to your cart.
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit" class="add-to-cart-btn" <?php echo $is_logged_in ? '' : 'disabled'; ?>>
                        🛒 Add to Cart
                    </button>
                </form>
            </div>
        </div>
    </div>
    
<?php include 'footer.php'; ?>

