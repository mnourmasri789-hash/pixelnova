<?php

echo "HOST: " . getenv('MYSQLHOST') . "<br>";
echo "DB: " . getenv('MYSQLDATABASE') . "<br>";
echo "USER: " . getenv('MYSQLUSER') . "<br>";
echo "PASS: " . (getenv('MYSQLPASSWORD') ? 'FOUND' : 'NOT FOUND') . "<br>";
echo "PORT: " . getenv('MYSQLPORT') . "<br>";

?>