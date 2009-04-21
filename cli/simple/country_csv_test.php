<?php

// Framework
require_once('../../lib/classes/Flex.php');
Flex::load();

// Get all Countrys
$arrCountries	= Address_Country_CSV::getAll();

echo "\n>>>";
print_r($arrCountries);
echo "\n<<<\n";
?>