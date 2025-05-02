<?php
class config
{
    private static $pdo = null;
    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "fedi";
            
            error_log("Attempting to connect to database...");
            error_log("Server: $servername");
            error_log("Database: $dbname");
            error_log("Username: $username");
            
            try {
                // First try to connect without database
                error_log("Trying to connect to MySQL server...");
                $pdo = new PDO("mysql:host=$servername", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                error_log("Successfully connected to MySQL server");
                
                // Check if database exists
                error_log("Checking if database '$dbname' exists...");
                $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
                if ($stmt->rowCount() == 0) {
                    error_log("Database '$dbname' does not exist. Creating it...");
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
                    error_log("Database '$dbname' created successfully");
                } else {
                    error_log("Database '$dbname' already exists");
                }
                
                // Now connect to the specific database
                error_log("Connecting to database '$dbname'...");
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                    ]
                );
                error_log("Successfully connected to database '$dbname'");
            } catch (Exception $e) {
                error_log("Database connection error: " . $e->getMessage());
                error_log("Error trace: " . $e->getTraceAsString());
                die('Error: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}

// Test the connection and create database if needed
try {
    error_log("Testing database connection...");
    $pdo = config::getConnexion();
    error_log("Database connection test successful");
} catch (Exception $e) {
    error_log("Database connection test failed: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
}
?>
