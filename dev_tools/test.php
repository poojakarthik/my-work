
<?php
require_once ('class_builder.php');

echo '<pre>' ;

$objClass = new ClassBuilder('c documentation
m ReadFiles $strDir
m Upload strFile strDir strUser strName
m ShowVersions $strFileDir');

//$objClass->Process();
if(!$objClass->Process())
{
	echo $objClass->strError;
}

$objClass->OutputString();


echo '</pre>' ;


?>
