<?php

//----------------------------------------------------------------------------//
// BrowserInfo
//----------------------------------------------------------------------------//
/**
 * BrowserInfo
 *
 * The BrowserInfo class - stores details relating to the user's browser
 *
 * The BrowserInfo class - stores details relating to the user's browser
 *
 *
 * @package	ui_app
 * @class	BrowserInfo
 */
class BrowserInfo
{
	// CurrentBrowser will be set to either BROWSER_NS, BROWSER_IE or 0 if it can not be determined what the browser is
	private $_intCurrentBrowser = NULL;
	private $_bolIsIE;
	private $_bolIsNS;
	private $_bolIsSupported;

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
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Accessor method for the magic variables "CurrentBrowser", "IsIE", "IsNS", "IsSupported"
	 *
	 * Accessor method for the magic variables "CurrentBrowser", "IsIE", "IsNS", "IsSupported"
	 *
	 * @param	string	$strMagicVariable	Name of the magic variable you want to retrieve.
	 *										
	 * @return	mix							"CurrentBrowser" will return BROWSER_NS or BROWSER_IE
	 *										"IsIE", "IsNS" and , "IsSupported" return TRUE or FALSE
	 * @method
	 */
	function __get($strMagicVariable)
	{
		if ($this->_intCurrentBrowser === NULL)
		{
			// The member variables have not been initialised, so do it now
			$this->_Initialise();
		}
		
		switch (strtolower($strMagicVariable))
		{
			case "currentbrowser":
				return $this->_intCurrentBrowser;
				break;
			case "isie":
				return $this->_bolIsIE;
				break;
			case "isns":
				return $this->_bolIsNS;
				break;
			case "issupported":
				return $this->_bolIsSupported;
				break;
			default:
				// This case should never occur, and means the programmer has a syntax error in their code, so die gracefully
				echo "ERROR: BrowserInfo->$strMagicVariable does not exist\n";
				die;
				break;
		}
	}
	
	//------------------------------------------------------------------------//
	// _Initialise
	//------------------------------------------------------------------------//
	/**
	 * _Initialise()
	 *
	 * Initialises the private member variables of this class
	 *
	 * Initialises the private member variables of this class
	 *
	 * @return	void
	 * @method
	 */
	private function _Initialise()
	{
		if (stristr($_SERVER ['HTTP_USER_AGENT'], 'Firefox') !== FALSE)
		{
			// Server is Firefox (netscape) 
			// NOTE: What would happen if someone was actually using Netscape Navigator instead of Firefox?
			$this->_intCurrentBrowser = BROWSER_NS;
			$this->_bolIsIE = FALSE;
			$this->_bolIsNS = TRUE;
		}
		elseif (stristr($_SERVER ['HTTP_USER_AGENT'], 'MSIE') !== FALSE)
		{
			// Browser is MS Internet Explorer
			$this->_intCurrentBrowser = BROWSER_IE;
			$this->_bolIsIE = TRUE;
			$this->_bolIsNS = FALSE;
		}
		elseif (stristr($_SERVER ['HTTP_USER_AGENT'], 'Prism') !== FALSE)
		{
			$this->_intCurrentBrowser = BROWSER_PR;
			$this->_bolIsIE = FALSE;
			$this->_bolIsNS = TRUE;
		}
		elseif (stristr($_SERVER ['HTTP_USER_AGENT'], 'Safari') !== FALSE)
		{
			$this->_intCurrentBrowser = BROWSER_SF;
			$this->_bolIsIE = FALSE;
			$this->_bolIsNS = TRUE;
		}
		else
		{
			// I don't know what browser it is.  It certainly isn't supported by any of our systems
			$this->_intCurrentBrowser = 0;
			$this->_bolIsIE = FALSE;
			$this->_bolIsNS = FALSE;
		}
		
		$this->_bolIsSupported = (bool)(($this->_intCurrentBrowser & SUPPORTED_BROWSERS) != 0);
	}
}

?>
