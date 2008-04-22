<?php

define("ZEND_PATH", dirname(__FILE__) . "/lib");

set_include_path(get_include_path() . PATH_SEPARATOR . ZEND_PATH);

require_once "pdf/Flex_Pdf.php";

echo "<hr>Creating PDF from invoiceData.xml and example/template.xsl...<br>";
unlink("pdf_dev.pdf");
$xmlData = file_get_contents(ZEND_PATH . "/pdf/pdf_templates/example/invoiceData.xml");
$pdfTemplate = new Flex_Pdf_Template("example", $xmlData, TRUE);
$pdf = $pdfTemplate->createDocument();
$pdf->save("pdf_dev.pdf");

/*
echo "<hr>Creating PDF from invoice.xml...<br>";
$xmlData = file_get_contents(ZEND_PATH . "/pdf/pdf_templates/example/invoice.xml");
$pdfTemplate = new Flex_Pdf_Template("example", $xmlData, FALSE);
$pdf = $pdfTemplate->createDocument();
$pdf->save("from_invoice_xml.pdf");
*/



/*
echo "<hr>Outputting XML to file...<br>";
//$xmlData = file_get_contents(ZEND_PATH . "/pdf/pdf_templates/example/invoice.xml");
//$pdfTemplate = new Flex_Pdf_Template("example", $xmlData, FALSE);
$xml = $pdfTemplate->createDocumentXML();
$f = fopen("from_invoice_xml.xml", "w+b");
fwrite($f, $xml);
fclose($f);


// Create new PDF document.

echo "<hr>Creating PDF from output XML file...<br>";
$xmlData = file_get_contents(dirname(__FILE__)."/from_invoice_xml.xml");
$pdfTemplate = new Flex_Pdf_Template("example", $xmlData, FALSE);
$pdf = $pdfTemplate->createDocument();
$pdf->save("from_invoice_xml_xml.pdf");

*/

echo "OK";

?>
