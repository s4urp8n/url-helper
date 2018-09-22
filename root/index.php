<?php
require "../vendor/autoload.php";

echo serialize(\Zver\CurrentURL::load());

echo "<pre>";
print_r($_SERVER);
print_r($_GET);
echo "</pre>";
