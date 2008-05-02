<?php

require_once "./lib/pdf/Flex_Pdf.php";

//ob_start();

//echo "Creating PDF from invoiceData.xml and example/template.xsl...<br>";
$xmlData = file_get_contents("./invoiceData.xml");
$documentTypeIdOrXsltString =  file_get_contents("./template.xsl");

$customerGroupId = null;
$effectiveDate = null;

try
{

	//tsplit("", true);
	for ($i = 0; $i < 1; $i++)
	{
		// Extend the time limit as this is in a loop...
		set_time_limit(120);
		
		$pdfTemplate = new Flex_Pdf_Template($customerGroupId, $effectiveDate, $documentTypeIdOrXsltString, $xmlData, Flex_Pdf_Style::MEDIA_EMAIL, TRUE);
		//tsplit("created template, creating document...");
		$pdf = $pdfTemplate->createDocument();
	
		//tsplit("created document, saving pdf...");
	
		//ob_end_clean();
		//exit;
		header("Content-type: application/pdf;");
		echo $pdf->render();
		//ob_flush();
	
		//$pdf->save("./_junk_/pdf_dev.$i.pdf");
		//tsplit("releasing resources...");
		
		//unset($pdf);
		$pdfTemplate->destroy();
		unset($pdfTemplate);
		//tsplit("", true);
	}

}
catch (Exception $e)
{
	echo "<pre>" . $e->getMessage() . "</pre>";
}

ob_end_clean();

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

?>
