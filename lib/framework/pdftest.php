<?php
require_once('pdf_builder.php');

$pdfTest = new VixenPdf('Vixen Developer Manual', '(Appendix C)', 'Database Table Descriptions', 'Jared Herbohn');

$pdfTest->Date('18-06-2007');
$pdfTest->Revision('123');

$pdfTest->AddHeading('heading1');

$pdfTest->AddText('this is text');

$pdfTest->AddHeading('heading2');

$pdfTest->AddText('more text');

$pdfTest->AddTable(array(array("title1" => "data1", "title2" => "data2"), array("title1" => "data3", "title2" => "data4")));

$pdfTest->RenderPdf();
?>
