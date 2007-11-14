<?php
$intTime = time();
$intCount = 0;
while ($intCount < 5)
{
	if ($intTime + 1 < time())
	{
		$intTime = time();
		$intCount++;
		echo "$intCount\n";
	}
}
//throw new Exception("DIE");

?>