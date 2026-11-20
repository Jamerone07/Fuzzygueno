<?php
session_start();
require_once 'db.php';

$page_title = 'Fuzzygueño – Fuzzywires & Custom Handmade Gifts';

// Get all products from database
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY id ASC");
if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}

include 'head.php';
include 'header.php';
?>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Handmade Fuzzywire Gifts Made With Love</h1>
                <p class="hero-description">Beautiful fuzzywire flowers, bouquets, and custom keychains crafted with care and attention to detail. Each piece is uniquely made just for you!</p>
                <a href="#order" class="btn btn-primary">Order Now</a>
            </div>
            <div class="hero-decoration">
                <span class="decoration-icon">🌸</span>
                <span class="decoration-icon">💐</span>
                <span class="decoration-icon">🌺</span>
            </div>
        </div>
    </section>

    <!-- Product Gallery Section -->
    <section id="products" class="products">
        <div class="container">
            <h2 class="section-title">Our Products</h2>
            <p class="section-subtitle">Choose from our collection or customize your own!</p>
            
            <div class="product-grid">
                <?php if (empty($products)): ?>
                    <p style="text-align: center; grid-column: 1 / -1; color: var(--text-light);">
                        No products available. Please run the database_schema.sql file to add products.
                    </p>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="product-card-link" style="text-decoration: none; color: inherit;">
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="product-overlay">
                                        <span class="heart-icon">💖</span>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    <p class="product-price">₱<?php echo number_format($product['price'], 2); ?></p>
                                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Custom Order Form Section -->
    <section id="order" class="order-form-section">
        <div class="container">
            <h2 class="section-title">Place Your Custom Order</h2>
            <p class="section-subtitle">Fill out the form below and we'll get back to you soon!</p>
            
            <form class="order-form" id="orderForm" method="POST" action="sendOrder.php">
                <div class="form-group">
                    <label for="name" class="form-label">Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" required placeholder="Your full name">
                </div>

                <div class="form-group">
                    <label for="contact" class="form-label">Contact (FB/Instagram/Phone) <span class="required">*</span></label>
                    <input type="text" id="contact" name="contact" class="form-input" required placeholder="Your contact information">
                </div>

                <div class="form-group">
                    <label for="product-type" class="form-label">Product Type (Price may vary due to customization) <span class="required">*</span></label>
                    
                    <select id="product-type" name="product-type" class="form-input" required>
                        <option value="">Select a product...</option>
                        <option value="single-stem">Single Stem Fuzzywire Flower (Custom)</option>
                        <option value="standard-bouquet">Standard Bouquet (Custom)</option>
                        <option value="big-bouquet">Big Bouquet (Custom)</option>
                        <option value="keychains">Keychains (Custom)</option>
                        <option value="custom">Custom Design of Your Choice</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="colors-design" class="form-label">Flower Colors or Keychain Design <span class="required">*</span></label>
                    <textarea id="colors-design" name="colors-design" class="form-input form-textarea" required placeholder="Describe your preferred colors or design (e.g., pink and lavender flowers, or a cat keychain)"></textarea>
                </div>

                <div class="form-group">
                    <label for="quantity" class="form-label">Quantity <span class="required">*</span></label>
                    <input type="number" id="quantity" name="quantity" class="form-input" required min="1" placeholder="How many items?">
                </div>

                <div class="form-group">
                    <label for="message" class="form-label">Message (Optional)</label>
                    <textarea id="message" name="message" class="form-input form-textarea" placeholder="Any special requests or notes for your order..."></textarea>
                </div>

                <button type="submit" class="btn btn-submit">Submit Order</button>
            </form>
        </div>
    </section>

    <!-- Shipping & Delivery Info Section -->
    <section id="shipping" class="shipping-section">
        <div class="container">
            <h2 class="section-title">Delivery & Shipping</h2>
            <div class="shipping-content">
                <div class="shipping-card">
                    <div class="shipping-icon">📍</div>
                    <h3>Pickup Location</h3>
                    <p>Available for pickup at our location. Details will be provided after order confirmation.</p>
                </div>
                <div class="shipping-card">
                    <div class="shipping-icon">🚚</div>
                    <h3>Available Delivery Areas</h3>
                    <p>We offer delivery to various areas. Please contact us to confirm if we deliver to your location.</p>
                </div>
                <div class="shipping-card">
                    <div class="shipping-icon">⏰</div>
                    <h3>Estimated Delivery Time</h3>
                    <p>Delivery times vary based on location and order complexity. We'll provide an estimate after order confirmation.</p>
                </div>
                <div class="shipping-card">
                    <div class="shipping-icon">💰</div>
                    <h3>Delivery Fee</h3>
                    <p>To be updated. We will confirm delivery details and fees after ordering.</p>
                </div>
            </div>
            <p class="shipping-note">💝 <strong>Note:</strong> We will confirm delivery details after ordering.</p>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about" class="about-section">
        <div class="container">
            <h2 class="section-title">About Fuzzygueño</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>Welcome to Fuzzygueño! We are a small handmade business passionate about creating beautiful fuzzywire flowers, bouquets, and custom keychains.</p>
                    <p>Each piece is carefully crafted by hand with love and attention to detail. We believe that handmade gifts carry a special warmth and personal touch that makes them truly unique.</p>
                    <p>Whether you're looking for a single stem to brighten someone's day, a beautiful bouquet for a special occasion, or a personalized keychain, we're here to help bring your vision to life!</p>
                    <div class="about-icons">
                        <span class="about-icon">🌷</span>
                        <span class="about-icon">💐</span>
                        <span class="about-icon">🌸</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <p class="section-subtitle">We'd love to hear from you! Reach out through any of these channels:</p>
            
            <div class="contact-links">
                <a href="https://facebook.com/fuzzygueno" target="_blank" class="contact-card" rel="noopener noreferrer">
                    <div class="contact-icon">📘</div>
                    <h3>Facebook</h3>
                    <p>Visit our Facebook page</p>
                </a>
                <a href="https://instagram.com/fuzzygueno" target="_blank" class="contact-card" rel="noopener noreferrer">
                    <div class="contact-icon">📷</div>
                    <h3>Instagram</h3>
                    <p>Follow us on Instagram</p>
                </a>
                <a href="mailto:info@fuzzygueno.com" class="contact-card">
                    <div class="contact-icon">✉️</div>
                    <h3>Email</h3>
                    <p>Send us an email</p>
                </a>
                <a href="https://m.me/fuzzygueno" target="_blank" class="contact-card messenger-btn" rel="noopener noreferrer">
                    <div class="contact-icon">💬</div>
                    <h3>Messenger</h3>
                    <p>Chat with us directly</p>
                </a>
            </div>
        </div>
    </section>

    <!-- FAQs Section -->
    <section id="faq" class="faq-section">
        <div class="container">
            <h2 class="section-title">Frequently Asked Questions</h2>
            
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>How long do custom orders take?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Custom orders typically take 3-7 business days depending on the complexity and current order volume. We'll provide a specific timeline when you place your order.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Are designs customizable?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Yes! We love creating custom designs. You can choose colors, styles, and even request specific themes. Just let us know your preferences in the order form!</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Do you accept bulk orders?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Yes, we accept bulk orders! Please contact us directly through Messenger, email, or our contact form to discuss bulk order pricing and timelines.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>What payment methods do you accept?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>We accept various payment methods including GCash, PayMaya, and bank transfers. Payment details will be provided after order confirmation.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Can I track my order?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-answer">
                        <p>Yes! Once your order is confirmed and shipped, we'll provide you with tracking information and updates on your order status.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include 'footer.php'; ?>

