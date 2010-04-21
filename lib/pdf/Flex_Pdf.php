<?php

// Add the lib directory to the include path, as it is required for the Zend library
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/' . ".." . '/'));

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
		$this->applyPageCountsAndLinks();
		
		return parent::render($newSegmentOnly, $outputStream);
	}

	private function applyPageCountsAndLinks()
	{
		$arrLinkDestinations = array();
		for ($i = 0, $l = count($this->pages); $i < $l; $i++)
		{
			$contents = $this->pages[$i]->applyPageCounts($l);
			$arrLinkDestinations = array_merge($arrLinkDestinations, $this->pages[$i]->getLinkTargets());
		}
		
		$nrLinkTargets = count($arrLinkDestinations);
		
		if ($nrLinkTargets)
		{
			// NOTE: This is no good for if we are appending to an existing pdf, as it would destroy links from the original document!!
			$this->_trailer->Root->Names = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());
			$this->_trailer->Root->Names->Dests = $this->_objFactory->newObject(new Zend_Pdf_Element_Dictionary());

			ksort($arrLinkDestinations);
			$arrTargetNames = array_keys($arrLinkDestinations);
			$from = new Zend_Pdf_Element_String($arrTargetNames[0]);
			$to = new Zend_Pdf_Element_String($arrTargetNames[$nrLinkTargets - 1]);
			$this->_trailer->Root->Names->Dests->Limits = new Zend_Pdf_Element_Array(Array($from, $to));
			
			$arrNames = array();
			foreach($arrLinkDestinations as $strTargetName => $objTarget)
			{
				$arrNames[] = new Zend_Pdf_Element_String($strTargetName);
				$arrNames[] = $objTarget;
			}
			
			$this->_trailer->Root->Names->Dests->Names = new Zend_Pdf_Element_Array($arrNames);
		}
	}

	public function getNrPages()
	{
		return count($this->pages);
	}
}

?>
