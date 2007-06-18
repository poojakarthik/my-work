<?php
require_once('pdf_builder.php');

$pdfTest = new VixenPdf('Vixen Developer Manual', '(Appendix C)', 'Database Table Descriptions', 'Jared Herbohn');

$pdfTest->Date('18-06-2007');
$pdfTest->Revision('123');

$pdfTest->RenderPdf();
?>
