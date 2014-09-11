<?php

//----------------------------------------------------------------------------//
// ApplicationTemplate
//----------------------------------------------------------------------------//
/**
 * ApplicationTemplate
 *
 * The ApplicationTemplate class
 *
 * The ApplicationTemplate class
 * 
 *
 * @package	ui_app
 * @class	ApplicationTemplate
 * @extends BaseTemplate
 */
class ApplicationTemplate extends ApplicationBaseClass
{
	public $Module;
	protected $_objAjax;
	protected $_bolModal = FALSE;
	protected $_intTemplateMode;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * constructor
	 *
	 * @return		void
	 * @method
	 */
	function __construct()
	{
		parent::__construct();
		$this->Module = new ModuleLoader();
	}

	//------------------------------------------------------------------------//
	// instance
	//------------------------------------------------------------------------//
	/**
	 * instance()
	 *
	 * Returns a singleton instance of this class
	 *
	 * Returns a singleton instance of this class
	 *
	 * @return	__CLASS__
	 *
	 * @method
	 */
	public static function instance()
	{
		static $instance;
		if (!isset($instance))
		{
			$instance = new self();
		}
		return $instance;
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
	 * @param		string	$strPageName	The name of the page to load
	 *
	 * @return		void
	 * @method
	 */
	function LoadPage($strPageName, $intContext=HTML_CONTEXT_DEFAULT)
	{
		if ($this->_intTemplateMode == AJAX_MODE)
		{
			// load AJAX template
			require_once(TEMPLATE_BASE_DIR."ajax_template/" . strtolower($strPageName) . ".php");
		}
		else
		{
			// create new page object
			$this->Page = new Page;
			
			// Pass on modality
			if ($this->IsModal())
			{
				$this->Page->SetModal(TRUE);
			}
			
			// load required page
			require_once(TEMPLATE_BASE_DIR."page_template/" . strtolower($strPageName) . ".php");
		}
	}
	
	//------------------------------------------------------------------------//
	// SetMode
	//------------------------------------------------------------------------//
	/**
	 * SetMode()
	 *
	 * Sets the mode of the template
	 * 
	 * Sets the mode of the template
	 *
	 * @param		int	$intMode	The mode number to set
	 *								ie AJAX_MODE, HTML_MODE
	 * @param		obj	$objAjax	optional Ajax object
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SetMode($intMode, $objAjax=NULL)
	{
		$this->_intTemplateMode = $intMode;
		$this->_objAjax = $objAjax;
	}
	
	//------------------------------------------------------------------------//
	// SetModal
	//------------------------------------------------------------------------//
	/**
	 * SetModal()
	 *
	 * Sets the modality of the template
	 * 
	 * Sets the modality of the template
	 *
	 * @param		int	$bolModal	Whether the page is to be rendered as a modal (complete) page
	 *
	 * @return		void
	 * @method
	 *
	 */
	function SetModal($bolModal)
	{
		$this->_bolModal = $bolModal;
	}
	
	//------------------------------------------------------------------------//
	// IsModal
	//------------------------------------------------------------------------//
	/**
	 * IsModal()
	 *
	 * Sets the modality of the template
	 * 
	 * Sets the modality of the template
	 *
	 * @param		void
	 *
	 * @return		boolean	whether the page is to be rendered as modal (complete) or not
	 * @method
	 *
	 */
	function IsModal()
	{
		return $this->_bolModal;
	}
}

?>
