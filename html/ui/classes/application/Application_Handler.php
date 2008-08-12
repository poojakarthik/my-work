<?php

abstract class Application_Handler extends ApplicationTemplate
{
	public function __construct()
	{
		parent::__construct();
	}

	//------------------------------------------------------------------------//
	// LoadPage
	//------------------------------------------------------------------------//
	/**
	 * LoadPage()
	 *
	 * Loads a Page to the Application
	 * 
	 * Loads a Page to the Application, using any AJAX templates it finds
	 *
	 * @param		string	$strPageName	The name of the page to load (can contain '_' to match namespacing sub-directories)
	 *
	 * @return		void
	 * @method
	 */
	function LoadPage($strPageName, $intContext=HTML_CONTEXT_DEFAULT, $mxdDataToRender=NULL)
	{
		$strPageName = strtolower($strPageName);
		$parts = explode('_', $strPageName);
		array_unshift($parts, '');
		$path = TEMPLATE_BASE_DIR.(($this->_intTemplateMode == AJAX_MODE) ? "ajax_template" : "page_template");
		foreach($parts as $part)
		{
			$path .= array_shift($parts) . '/';
			$file = $path.$strPageName.'.php';
			if (file_exists($file)) break;
		}
		if (!file_exists($file))
		{
			throw new Exception("Failed to find $strPageName template ($file) in " . TEMPLATE_BASE_DIR.(($this->_intTemplateMode == AJAX_MODE) ? "ajax_template" : "page_template") . ".");
		}
		if ($this->_intTemplateMode == AJAX_MODE)
		{
			// load AJAX template
			require_once($file);
		}
		else
		{
			// create new page object
			$this->Page = new Application_Page($mxdDataToRender);
			
			// Pass on modality
			if ($this->IsModal())
			{
				$this->Page->SetModal(TRUE);
			}
			
			// load required page
			require_once($file);
		}
	}
}

?>
