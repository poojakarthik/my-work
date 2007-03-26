<?php

define('IN_FS', true);

require_once("/usr/share/flyspray/htdocs/header.php");

$user = new User(1);

if(backend::add_comment(1, "TEST"))
{
	echo "True";
}

?>
