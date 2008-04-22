<?php

require_once "Zend/Pdf.php";
require_once "pdf/Flex_Pdf_Template.php";
require_once "pdf/Flex_Pdf_Page.php";

class Flex_Pdf extends Zend_Pdf 
{
	private $pdfData = null;
	
	public function newPage($pageSize)
	{
		return new Flex_Pdf_Page($pageSize);
	}

    public function save($filename, $updateOnly = false)
    {
 		$this->applyPageCounts();

        return parent::save($filename, $updateOnly);
    }

    public function render($newSegmentOnly = false, $outputStream = null)
    {
		$this->applyPageCounts();

		return parent::render($newSegmentOnly, $outputStream);
    }
    
    private function applyPageCounts()
    {
    	for ($i = 0, $l = count($this->pages); $i < $l; $i++)
    	{
    		echo "<hr>Telling page to reneder page $i counts<hr>";
    		$contents = $this->pages[$i]->applyPageCounts($l);
    	}
    }

}

?>
