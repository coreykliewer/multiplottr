<?php
$servername = "localhost";
$username = "[REDACTED_CONFIG_VALUE]";
$password = "[REDACTED_CONFIG_VALUE]";
$databasename = "[REDACTED_ACCOUNT]";




try {
    $conn = new PDO("mysql:host=$servername;dbname=$databasename", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully"; 
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }
?>
