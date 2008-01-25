<?php

define('IN_FS', true);

require_once("/usr/share/flyspray/htdocs/header.php");

$user = new User(1);

$arrTask = array();

$arrTask['task_id'] = 1 ;

if(backend::add_comment($arrTask, "TEST"))
{
	echo "True";
}

?>
