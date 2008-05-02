<?php

// If the SHARED_BASE_PATH has not been defined (i.e. If this is being used outside of Flex)...
if (!defined('SHARED_BASE_PATH'))
{
	define("SHARED_BASE_PATH", realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR));
	set_include_path(get_include_path() . PATH_SEPARATOR . SHARED_BASE_PATH);
}

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
    		$contents = $this->pages[$i]->applyPageCounts($l);
    	}
    }

}

?>
