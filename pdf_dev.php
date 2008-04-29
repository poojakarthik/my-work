<?php

define("ZEND_PATH", dirname(__FILE__) . "/lib");
define("SHARED_BASE_PATH", dirname(__FILE__) . "/lib/");

set_include_path(get_include_path() . PATH_SEPARATOR . ZEND_PATH);

require_once "pdf/Flex_Pdf.php";

//ob_start();

echo "Creating PDF from invoiceData.xml and example/template.xsl...<br>";
if (file_exists("pdf_dev.pdf")) unlink("pdf_dev.pdf");
$xmlData = file_get_contents(ZEND_PATH . "/pdf/pdf_templates/example/invoiceData.xml");

$documentTypeIdOrXsltString =  file_get_contents(ZEND_PATH . "/pdf/pdf_templates/example/template.xsl");

function tsplit($comment="", $newDocument=FALSE)
{
	static $start, $docStart, $lastSplit, $split, $docCount, $lastMem;
	$now = microtime(true);
	$mem = floor(memory_get_usage(true) / 1000000);
	if (!isset($start))
	{
		if (!$newDocument) return;
		$start = $split = $lastSplit = $docStart = $now;
		$lastMem = $mem;
		$docCount = 0;
	}
	$lastSplit = $split;
	$split = $now;
	if ($newDocument) 
	{ 
		if ($docCount) echo "<br>Document $docCount completed in " . ($split - $docStart) . " seconds; Memory usage: " . $mem  . "Mb (+" . ($mem - $lastMem) . "Mb)<br>";
		$docStart = $now; $docCount++;
		echo "<br>Document $docCount started; Memory usage: " . $mem . "Mb (+" . ($mem - $lastMem) . "Mb)...<br>";
		$lastMem = $mem;
		flush();
	}
	if (!$comment) return;
	echo "<br>Document $docCount:: time elapsed: " . ($split - $start) . "; time on document: " . ($split - $docStart) . "; time since last split: " . ($split - $lastSplit) . "; Memory usage: " . $mem . "Mb (+" . ($mem - $lastMem) . "Mb). :: $comment<br>";
	$lastMem = $mem;
	flush();
}

$start = microtime(true);

$customerGroupId = null;
$effectiveDate = null;

tsplit("", true);
for ($i = 0; $i < 1; $i++)
{
	set_time_limit(120);
	$pdfTemplate = new Flex_Pdf_Template($customerGroupId, $effectiveDate, $documentTypeIdOrXsltString, $xmlData, TRUE, Flex_Pdf_Style::MEDIA_PRINT);
	tsplit("created template, creating document...");
	$pdf = $pdfTemplate->createDocument();

//ob_clean();
//exit;
//header("Content-type: application/pdf;");
//echo $pdf->render();
	tsplit("created document, saving pdf...");
	$pdf->save("./_junk_/pdf_dev.$i.pdf");
	tsplit("releasing resources...");
	unset($pdf);
	$pdfTemplate->destroy();
	unset($pdfTemplate);
	tsplit("", true);
}

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

//echo "OK";

?>
