<?php


// strtotime() benchmark tester


$strDate1	= "1985-10-02";
$strDate2	= "1985-10-20";
$strDays	= 7;
echo "<pre>";
echo "| Actions Taken\t\t\t\t\t\t\t| 1 run\t\t\t\t| 5 runs\t\t\t\t| 50 runs\t\t\t\t| 1000 runs\t\t\t\t|\n";
echo "|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|\n";

// Test 1
echo "  Convert Standard Date String '$strDate1'...\t\t\t";
// 1x
$intStart	= microtime(TRUE);
$mixResult	= strtotime($strDate1);
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\t\t";
// 5x
$intStart	= microtime(TRUE);
for ($i = 0; $i < 5; $i++)
{
	$mixResult	= strtotime($strDate1);
}
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\t\t\t";
// 50x
$intStart	= microtime(TRUE);
for ($i = 0; $i < 50; $i++)
{
	$mixResult	= strtotime($strDate1);
}
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\t\t\t";
// 1000x
$intStart	= microtime(TRUE);
for ($i = 0; $i < 1000; $i++)
{
	$mixResult	= strtotime($strDate1);
}
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\n";






// Test 2
echo "  Convert Standard Date String '$strDate1' and add $strDays days...\t";
// 1x
$intStart	= microtime(TRUE);
$mixResult	= strtotime("+$strDays days", strtotime($strDate1));
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\t\t";
// 5x
$intStart	= microtime(TRUE);
for ($i = 0; $i < 5; $i++)
{
	$mixResult	= strtotime("+$strDays days", strtotime($strDate1));
}
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\t\t\t";
// 50x
$intStart	= microtime(TRUE);
for ($i = 0; $i < 50; $i++)
{
	$mixResult	= strtotime("+$strDays days", strtotime($strDate1));
}
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\t\t\t";
// 1000x
$intStart	= microtime(TRUE);
for ($i = 0; $i < 1000; $i++)
{
	$mixResult	= strtotime("+$strDays days", strtotime($strDate1));
}
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\n";











// Test 3
echo "  Perform Prorating calculations...\t\t\t\t";
// 1x
$intStart	= microtime(TRUE);
$intEndDay		= floor(strtotime($strDate2)/86400);
$intStartDay	= floor(strtotime($strDate1)/86400);
$intEndMonth	= floor((strtotime("+ 1 month", strtotime($strDate1))/86400) - 1);
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\t\t";
// 5x
$intStart	= microtime(TRUE);
for ($i = 0; $i < 5; $i++)
{
	$intEndDay		= floor(strtotime($strDate2)/86400);
	$intStartDay	= floor(strtotime($strDate1)/86400);
	$intEndMonth	= floor((strtotime("+ 1 month", strtotime($strDate1))/86400) - 1);
}
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\t\t\t";
// 50x
$intStart	= microtime(TRUE);
for ($i = 0; $i < 50; $i++)
{
	$intEndDay		= floor(strtotime($strDate2)/86400);
	$intStartDay	= floor(strtotime($strDate1)/86400);
	$intEndMonth	= floor((strtotime("+ 1 month", strtotime($strDate1))/86400) - 1);
}
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\t\t\t";
// 1000x
$intStart	= microtime(TRUE);
for ($i = 0; $i < 1000; $i++)
{
	$intEndDay		= floor(strtotime($strDate2)/86400);
	$intStartDay	= floor(strtotime($strDate1)/86400);
	$intEndMonth	= floor((strtotime("+ 1 month", strtotime($strDate1))/86400) - 1);
}
$intEnd		= microtime(TRUE);
echo "  ".($intEnd - $intStart)."s\n";


die;
?>
