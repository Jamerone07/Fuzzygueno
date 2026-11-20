<?php
/**
 * Helper script to create .env file
 * Run this once: http://localhost/TECNO/create_env.php
 */

$env_content = "MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=madijamerone@gmail.com
MAIL_PASSWORD=vwinwvucqxfxzmwd
";

$env_path = __DIR__ . '/.env';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_env'])) {
    $result = file_put_contents($env_path, $env_content);
    if ($result !== false) {
        $success = true;
        $message = ".env file created successfully!";
    } else {
        $error = "Failed to create .env file. Please create it manually.";
    }
} else {
    $exists = file_exists($env_path);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create .env File - Fuzzygueño</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        button { padding: 15px 30px; background: #87CEEB; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>📝 Create .env File</h1>
    
    <?php if (isset($success)): ?>
        <div class="success">
            <strong>✅ Success!</strong> <?php echo $message; ?><br>
            The .env file has been created at: <code><?php echo htmlspecialchars($env_path); ?></code><br>
            <a href="test_email.php" style="color: #155724; font-weight: bold;">→ Test Email Now</a>
        </div>
    <?php elseif (isset($error)): ?>
        <div class="error">
            <strong>❌ Error:</strong> <?php echo $error; ?>
        </div>
    <?php elseif (isset($exists) && $exists): ?>
        <div class="info">
            <strong>ℹ️ .env file already exists!</strong><br>
            If you need to recreate it, you can delete it first or click the button below to overwrite it.
        </div>
    <?php endif; ?>
    
    <div class="info">
        <h3>What this will create:</h3>
        <p>The .env file will contain:</p>
        <pre><?php echo htmlspecialchars($env_content); ?></pre>
        <p><strong>File location:</strong> <code><?php echo htmlspecialchars($env_path); ?></code></p>
    </div>
    
    <form method="POST">
        <button type="submit" name="create_env">Create .env File</button>
    </form>
    
    <div class="info" style="margin-top: 30px;">
        <h3>Manual Creation (if button doesn't work):</h3>
        <ol>
            <li>Open Notepad or any text editor</li>
            <li>Copy and paste this content:
                <pre>MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=madijamerone@gmail.com
MAIL_PASSWORD=vwinwvucqxfxzmwd</pre>
            </li>
            <li>Save the file as <code>.env</code> (with the dot at the beginning)</li>
            <li>Make sure to save it in: <code><?php echo htmlspecialchars(__DIR__); ?></code></li>
            <li>When saving in Notepad, change "Save as type" to "All Files (*.*)"</li>
            <li>Make sure the filename is exactly <code>.env</code> (not .env.txt)</li>
        </ol>
    </div>
</body>
</html>

