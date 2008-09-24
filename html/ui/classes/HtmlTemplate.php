<?php

//----------------------------------------------------------------------------//
// HtmlTemplate
//----------------------------------------------------------------------------//
/**
 * HtmlTemplate
 *
 * The HtmlTemplate class
 *
 * The HtmlTemplate class
 *
 *
 * @package	ui_app
 * @class	HtmlTemplate
 * @extends BaseTemplate
 */
class HtmlTemplate extends BaseTemplate
{
	protected $_strMethod;
	protected $_strForm;
	protected $_strTemplate;
	protected $_objAjax;
	protected $_intTemplateMode;
	protected $_bolModal = FALSE;

	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	protected $_intContext;

	//------------------------------------------------------------------------//
	// _strContainerDivId
	//------------------------------------------------------------------------//
	/**
	 * _strContainerDivId
	 *
	 * Stores the Id of the div element that contains the rendered contents of this HtmlTemplate
	 *
	 * Stores the Id of the div element that contains the rendered contents of this HtmlTemplate
	 * Currently this is only used for updating a div through an ajax call
	 * If required, it should be set in the constructor of the HtmlTemplate
	 *
	 * @type		string
	 *
	 * @property
	 */
	protected $_strContainerDivId;

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
	// LoadJavascript
	//------------------------------------------------------------------------//
	/**
	 * LoadJavascript()
	 *
	 * Loads a js file to the internal array (appends it)
	 * 
	 * Loads a js file to the internal array (appends it)
	 *
	 * @param		string	$strFilename	The name of the js file to load
	 *
	 * @return		void
	 * @method
	 *
	 */
	function LoadJavascript($strFilename)
	{
		// add $strFilename to global javascript function array
		if (!array_key_exists('*arrJavaScript', $GLOBALS) || !is_array($GLOBALS['*arrJavaScript']))
		{
			$GLOBALS['*arrJavaScript'] = array();
		}

		$GLOBALS['*arrJavaScript'][] = $strFilename;
	}

	//------------------------------------------------------------------------//
	// FormStart
	//------------------------------------------------------------------------//
	/**
	 * FormStart()
	 *
	 * Echos the starting tag of an html form element, which will be handled by an AppTemplate method 
	 * 
	 * Echos the starting tag of an html form element, which will be handled by an AppTemplate method
	 *
	 * @param		string	$strId			Uniquely identifies the form
	 * @param		string	$strTemplate	AppTemplate class which will be called to process the form, on submittion
	 * 										(do not include the AppTemaplte prefix to the class name)
	 * @param		string	$strMethod		Method of the AppTemplate class, which will be executed when the form is submitted
	 * @param		string	$arrParams		Any parameters to pass to the AppTemplate Method as GET variables
	 * 										(ie $arrParams['Account.Id'] = 1000123456)
	 * @return		void
	 * @method
	 */
	function FormStart($strId, $strTemplate, $strMethod, $arrParams=NULL)
	{
		$this->_strMethod = $strMethod;
		$this->_strForm = "VixenForm_$strId";
		$this->_strTemplate = $strTemplate;
		
		$strParams = "";
		if (is_array($arrParams))
		{
			foreach($arrParams AS $strKey=>$strValue)
			{
				$arrParams[$strKey] = "$strKey=$strValue";
			}
			$strParams = "?".implode('&', $arrParams);
		}
		
		echo "<form id='{$this->_strForm}' method='post' action='flex.php/$strTemplate/$strMethod/$strParams'>\n";
		echo "<input type='hidden' value='$strId' name='VixenFormId' />\n";
	}
	
	//------------------------------------------------------------------------//
	// FormEnd
	//------------------------------------------------------------------------//
	/**
	 * FormEnd()
	 *
	 * Echos the closing tag of an html form element 
	 * 
	 * Echos the closing tag of an html form element
	 *
	 * @return		void
	 * @method
	 */
	function FormEnd()
	{
		echo "</form>\n";
	}
	
	//------------------------------------------------------------------------//
	// Submit
	//------------------------------------------------------------------------//
	/**
	 * Submit()
	 *
	 * Echos html code to create an input submit button element 
	 * 
	 * Echos html code to create an input submit button element
	 * 
	 * @param	string	$strLabel		The value/label for the submit button
	 * @param	string	$strStyleClass	optional, CSS class for the "input submit" element
	 *
	 * @return		void
	 * @method
	 */
	function Submit($strLabel, $strStyleClass="InputSubmit")
	{
		echo "<input type='submit' class='$strStyleClass' name='VixenButtonId' value='$strLabel'></input>\n";
	}
	
	//------------------------------------------------------------------------//
	// Button
	//------------------------------------------------------------------------//
	/**
	 * Button()
	 *
	 * Echos html code to create an input button element 
	 * 
	 * Echos html code to create an input button element
	 * 
	 * @param	string	$strLabel		The value/label for the input button element
	 * @param	string	$strHref		value for the onclick property of the input button element
	 * @param	string	$strStyleClass	optional, CSS class for the input button element
	 *
	 * @return		void
	 * @method
	 */
	function Button($strLabel, $strHref, $strStyleClass="InputSubmit")
	{
		$strName = "VixenButton_". str_replace(" ", "", $strLabel);
		
		// Change all the single quotes in $strHref to their html safe versions, so that it doesn't escape
		// out of the onlick='...' prematurely (this also converts double quotes)
		$strHref = htmlspecialchars($strHref, ENT_QUOTES);
		echo "<input type='button' class='$strStyleClass' id='$strName' name='$strName' value='$strLabel' onclick='$strHref'></input>\n";
	}
	
	//------------------------------------------------------------------------//
	// AjaxSubmit
	//------------------------------------------------------------------------//
	/**
	 * AjaxSubmit()
	 *
	 * Echos html code to create an input button element which submits, via ajax, the most recently declared form
	 * 
	 * Echos html code to create an input button element which submits, via ajax, the most recently declared form
	 * 
	 * @param	string	$strLabel		The value/label for the input button element
	 * @param	string	$strTemplate	optional, Name of the AppTemplate which will contain the method which will be used
	 * 									to handle the submittion.  This defaults to whatever AppTemplate was specified
	 * 									in the most recent call to FormStart
	 * @param	string	$strMethod		optional, Method of the AppTemplate which will be executed by the ajax call
	 * 									This defaults to whatever Method was specified
	 * 									in the most recent call to FormStart
	 * @param	string	$strTargetType	?
	 * @param	string	$strStyleClass	optional, CSS class for the input button element
	 *
	 * @return		void
	 * @method
	 */
	function AjaxSubmit($strLabel, $strTemplate=NULL, $strMethod=NULL, $strTargetType=NULL, $strStyleClass="InputSubmit", $strButtonId='VixenButtonId')
	{
		$strTarget = '';
		$strId = '';
		$strSize = '';
		
		if (!$strTemplate)
		{
			$strTemplate = $this->_strTemplate;
		}
		if (!$strMethod)
		{
			$strMethod = $this->_strMethod;
		}
		if (is_object($this->_objAjax))
		{
			//echo $this->_objAjax->TargetType;
			$strTarget = $this->_objAjax->TargetType;
			$strId = $this->_objAjax->strId;
			$strSize = $this->_objAjax->strSize;
		}
		
		if ($strTargetType !== NULL)
		{
			$strTarget = $strTargetType;
		}
		
		echo "<input type='button' value='$strLabel' class='$strStyleClass' id='$strButtonId' name='VixenButtonId' onclick=\"Vixen.Ajax.SendForm('{$this->_strForm}', '$strLabel','$strTemplate', '$strMethod', '$strTarget', '$strId', '$strSize', '{$this->_strContainerDivId}')\"></input>\n";
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
