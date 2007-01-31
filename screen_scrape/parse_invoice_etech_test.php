<?php
	
	system ("clear;");
	
	require ("config/application_loader.php");
	require ("decode_etech.php");
	
	$objDecode = new VixenDecode (Array ());
	
	$arrInvoice = $objDecode->FetchInvoiceDetail ();
	
	echo "<pre>";
	print_r ($arrInvoice);
	
?>
