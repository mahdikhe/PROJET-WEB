<?php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'khelilmahdi60@gmail.com');
define('SMTP_PASS', 'nlyh thqf tbuq wbbx');
define('SMTP_PORT', 587); // Typically 587 for TLS or 465 for SSL
define('SMTP_CHARSET', 'UTF-8');
// Database connection class
class Config {
    private static $pdo = null;
    
    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "user"; // Your database name
            
            try {
                self::$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (Exception $e) {
                die('Error: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>