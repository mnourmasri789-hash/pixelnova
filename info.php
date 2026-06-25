<?php
phpinfo();


echo "PDO: ";
var_dump(extension_loaded('pdo'));

echo "<br>PDO_MYSQL: ";
var_dump(extension_loaded('pdo_mysql'));

echo "<br>MYSQLI: ";
var_dump(extension_loaded('mysqli'));
