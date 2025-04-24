<?php
// Database connection class
class Config {
    private static $pdo = null;
    
    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "citypulse_events"; // Your database name
            
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