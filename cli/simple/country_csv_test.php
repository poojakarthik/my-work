<?php

// Framework
require_once('../../lib/classes/Flex.php');
Flex::load();

// Get all Countrys
echo "\n>>ALL COUNTRIES>>>";
print_r(Address_Country_CSV::getAll());
echo "<<<\n";
/*
echo "\n>>BEFORE ROLLOUT 80>>>";
print_r(Address_Country_CSV::getAll(Address_Country_CSV::GET_MODE_BEFORE_REVISION, 80));
echo "<<<\n";

echo "\n>>BEFORE ROLLOUT 100>>>";
print_r(Address_Country_CSV::getAll(Address_Country_CSV::GET_MODE_BEFORE_REVISION, 100));
echo "<<<\n";

echo "\n>>BEFORE ROLLOUT 171>>>";
print_r(Address_Country_CSV::getAll(Address_Country_CSV::GET_MODE_BEFORE_REVISION, 171));
echo "<<<\n";

echo "\n>>BEFORE ROLLOUT 200>>>";
print_r(Address_Country_CSV::getAll(Address_Country_CSV::GET_MODE_BEFORE_REVISION, 200));
echo "<<<\n";
*/

echo "\n>>TO ROLLOUT 80>>>";
print_r(Address_Country_CSV::getAll(Address_Country_CSV::GET_MODE_TO_REVISION, 80));
echo "<<<\n";

echo "\n>>TO ROLLOUT 100>>>";
print_r(Address_Country_CSV::getAll(Address_Country_CSV::GET_MODE_TO_REVISION, 100));
echo "<<<\n";

echo "\n>>TO ROLLOUT 171>>>";
print_r(Address_Country_CSV::getAll(Address_Country_CSV::GET_MODE_TO_REVISION, 171));
echo "<<<\n";

echo "\n>>TO ROLLOUT 200>>>";
print_r(Address_Country_CSV::getAll(Address_Country_CSV::GET_MODE_TO_REVISION, 200));
echo "<<<\n";


?>