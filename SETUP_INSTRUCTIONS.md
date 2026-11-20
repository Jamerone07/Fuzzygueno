# E-commerce Setup Instructions

## Database Setup

1. **Start XAMPP**
   - Start Apache and MySQL services in XAMPP Control Panel

2. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database_schema.sql` file:
     - Click on "Import" tab
     - Choose file: `database_schema.sql`
     - Click "Go"
   - OR manually create database:
     - Create a new database named `ecommerce_db`
     - Run the SQL commands from `database_schema.sql`

3. **Configure Database Connection**
   - Edit `db.php` if your MySQL credentials are different:
     ```php
     $host = "localhost";
     $user = "root";        // Change if needed
     $pass = "";            // Change if you have a password
     $db   = "ecommerce_db";
     ```

## File Structure

### Core Files
- `db.php` - Database connection
- `index.php` - Homepage with product listing
- `head.php` - HTML head section (reusable)
- `header.php` - Navigation header (reusable)
- `footer.php` - Footer (reusable)

### Authentication
- `signup.php` - User registration
- `login.php` - User login
- `logout.php` - User logout

### E-commerce Features
- `product.php` - Product details page
- `add_to_cart.php` - Add product to cart
- `cart.php` - Shopping cart page
- `checkout.php` - Checkout and order placement

### Database
- `database_schema.sql` - Database structure and sample data

## Features Implemented

✅ **User Authentication**
- User signup with email validation
- Secure password hashing (password_hash)
- Login with password verification (password_verify)
- Session management
- Logout functionality

✅ **Product Management**
- Products loaded from database
- Product detail pages
- Clickable product cards linking to detail pages

✅ **Shopping Cart**
- Add to cart (logged-in users only)
- View cart with all items
- Update quantities
- Remove items from cart
- Cart count badge in navigation

✅ **Checkout System**
- Order placement
- Cart items moved to orders table
- Order items stored in order_items table
- Cart cleared after successful order

✅ **Navigation Integration**
- Login/Signup buttons for guests
- User info and cart icon for logged-in users
- Cart badge showing item count
- Logout button

## Usage

1. **Access the Website**
   - Place all files in your XAMPP `htdocs` folder
   - Access via: `http://localhost/TECNO/index.php`

2. **Create an Account**
   - Click "Sign Up" in navigation
   - Fill in username, email, and password
   - After signup, you'll be redirected to login

3. **Login**
   - Click "Login" in navigation
   - Enter email and password
   - You'll be redirected to homepage

4. **Browse Products**
   - View products on homepage
   - Click any product card to see details

5. **Add to Cart**
   - On product detail page, click "Add to Cart"
   - Must be logged in to add items

6. **View Cart**
   - Click "Cart" icon in navigation (shows item count)
   - Update quantities or remove items
   - Click "Proceed to Checkout"

7. **Checkout**
   - Review order summary
   - Click "Place Order"
   - Order is saved and cart is cleared

## Database Tables

- **users** - User accounts (id, username, email, password, created_at)
- **products** - Product catalog (id, name, description, price, image, created_at)
- **cart** - Shopping cart items (id, user_id, product_id, quantity, created_at)
- **orders** - Order records (id, user_id, total_amount, order_status, created_at)
- **order_items** - Order line items (id, order_id, product_id, quantity, price)

## Security Features

- ✅ Password hashing with `password_hash()`
- ✅ Password verification with `password_verify()`
- ✅ Prepared statements to prevent SQL injection
- ✅ Input validation and sanitization
- ✅ Session-based authentication
- ✅ Login required for cart operations

## Notes

- All passwords are hashed using PHP's `password_hash()` function
- Email validation prevents duplicate accounts
- Cart is user-specific (tied to user_id)
- Orders are preserved in database after checkout
- Product images should be in the `images/` folder

## Troubleshooting

**Database Connection Error**
- Check XAMPP MySQL is running
- Verify credentials in `db.php`
- Ensure database `ecommerce_db` exists

**Products Not Showing**
- Run `database_schema.sql` to create products
- Check `images/` folder has product images
- Verify image paths in database match actual files

**Cart Not Working**
- Ensure user is logged in
- Check session is working (cookies enabled)
- Verify database tables are created correctly

**Styling Issues**
- Ensure `styles.css` is in the same directory
- Check browser console for missing files
- Verify all image paths are correct

