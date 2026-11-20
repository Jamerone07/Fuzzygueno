<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle remove item
if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: cart.php');
    exit;
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: cart.php');
    exit;
}

// Get cart items
$stmt = $conn->prepare("
    SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.image 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? 
    ORDER BY c.id DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<?php
$page_title = 'Shopping Cart - Fuzzygueño';
include 'head.php';
?>
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .cart-title {
            text-align: center;
            color: var(--text-dark);
            margin-bottom: 30px;
            font-size: 32px;
        }
        .cart-items {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(255, 182, 217, 0.2);
            padding: 30px;
            margin-bottom: 30px;
        }
        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto auto auto;
            gap: 20px;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
        }
        .cart-item-info h3 {
            color: var(--text-dark);
            margin-bottom: 10px;
        }
        .cart-item-info a {
            color: var(--text-dark);
            text-decoration: none;
        }
        .cart-item-info a:hover {
            color: var(--pink);
        }
        .cart-item-price {
            color: var(--pink);
            font-weight: 600;
            font-size: 18px;
        }
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .cart-item-quantity input {
            width: 60px;
            padding: 8px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
        }
        .cart-item-quantity button {
            padding: 8px 15px;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .cart-item-total {
            color: var(--text-dark);
            font-weight: 600;
            font-size: 18px;
        }
        .cart-item-remove {
            color: #c62828;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            padding: 5px 10px;
        }
        .cart-item-remove:hover {
            color: #d32f2f;
        }
        .cart-summary {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(255, 182, 217, 0.2);
            padding: 30px;
        }
        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .cart-summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            font-size: 24px;
            font-weight: 700;
            color: var(--pink);
        }
        .checkout-btn {
            width: 100%;
            padding: 15px;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        .checkout-btn:hover {
            transform: translateY(-2px);
        }
        .checkout-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }
        .empty-cart-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 15px;
            }
            .cart-item-image {
                width: 80px;
                height: 80px;
            }
            .cart-item-price,
            .cart-item-quantity,
            .cart-item-total,
            .cart-item-remove {
                grid-column: 2;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="cart-container">
        <h2 class="cart-title">🛒 Shopping Cart</h2>
        
        <?php if (empty($cart_items)): ?>
            <div class="cart-items">
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h3>Your cart is empty</h3>
                    <p>Start shopping to add items to your cart!</p>
                    <a href="index.php" class="btn btn-primary" style="margin-top: 20px; display: inline-block;">Continue Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="cart-item-image">
                        
                        <div class="cart-item-info">
                            <h3><a href="product.php?id=<?php echo $item['product_id']; ?>">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </a></h3>
                        </div>
                        
                        <div class="cart-item-price">
                            ₱<?php echo number_format($item['price'], 2); ?>
                        </div>
                        
                        <div class="cart-item-quantity">
                            <form method="POST" style="display: flex; align-items: center; gap: 10px;">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                       min="1" required>
                                <button type="submit" name="update_quantity">Update</button>
                            </form>
                        </div>
                        
                        <div class="cart-item-total">
                            ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                        
                        <a href="cart.php?remove=<?php echo $item['id']; ?>" 
                           class="cart-item-remove" 
                           onclick="return confirm('Remove this item from cart?')">🗑️</a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <div class="cart-summary-row">
                    <span>Subtotal:</span>
                    <span>₱<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="cart-summary-total">
                    <span>Total:</span>
                    <span>₱<?php echo number_format($total, 2); ?></span>
                </div>
                <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

