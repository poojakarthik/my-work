<?php
echo "start\n";
for ($i = 1; $i < 10; $i++)
{
	echo "Sleeping for {$i}s";
	/*for ($t = 0; $t < 500000; $t++)
	{
		
	}*/
	sleep(1);
	echo "...slept!\n";
}
echo "start\n";
?>