<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Fuzzygueño – Fuzzywires & Custom Handmade Gifts'; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .nav-auth {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .nav-auth a {
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .nav-auth .btn-login {
            color: white;
            background: rgba(255, 255, 255, 0.2);
        }
        .nav-auth .btn-login:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .nav-auth .btn-signup {
            background: white;
            color: var(--pink);
        }
        .nav-auth .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .nav-auth .btn-logout {
            color: white;
            background: rgba(255, 255, 255, 0.2);
        }
        .nav-auth .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        .cart-icon-link {
            position: relative;
            display: inline-block;
            padding: 8px 20px;
            background: white;
            color: var(--pink);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .cart-icon-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent-blue);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }
        .user-info {
            color: white;
            font-weight: 500;
            padding: 8px 15px;
        }
    </style>
</head>
<body>

