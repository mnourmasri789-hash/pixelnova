<?php

echo "PDO: ";
var_dump(extension_loaded('pdo_mysql'));

echo "<br>MYSQLI: ";
var_dump(extension_loaded('mysqli'));

echo "<br><br>";

try {
    $pdo = new PDO(
        "mysql:host=" . getenv('MYSQLHOST') .
        ";port=" . getenv('MYSQLPORT') .
        ";dbname=" . getenv('MYSQLDATABASE'),
        getenv('MYSQLUSER'),
        getenv('MYSQLPASSWORD')
    );

    echo "CONNECTED SUCCESSFULLY";
} catch (Exception $e) {
    echo $e->getMessage();
}
