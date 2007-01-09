<?
	
	//----------------------------------------------------------------------------//
	// intranetstyle.php
	//----------------------------------------------------------------------------//
	/**
	 * intranetstyle.php
	 *
	 * Controls a Style object specifically for the Intranet
	 *
	 * Controls a Style object specifically for the Intranet
	 *
	 * @file		intranetstyle.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.12
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	 
	//----------------------------------------------------------------------------//
	// IntranetStyle
	//----------------------------------------------------------------------------//
	/**
	 * IntranetStyle
	 *
	 * Controls a Style object specifically for the Intranet
	 *
	 * Controls a Style object specifically for the Intranet
	 *
	 *
	 * @prefix		its
	 *
	 * @package		intranet_app
	 * @class		IntranetStyle
	 * @extends		Style
	 */
	
	class IntranetStyle extends Style
	{
		
		//------------------------------------------------------------------------//
		// _athAuthentication
		//------------------------------------------------------------------------//
		/**
		 * _athAuthentication
		 *
		 * The Authentication class
		 *
		 * The Authentication class
		 *
		 * @type	Authentication
		 *
		 * @property
		 */
		
		private $_athAuthentication;
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Creates a new IntranetStyle Controller
		 *
		 * Creates a new IntranetStyle Controller
		 *
		 * @param	String				$strWebDir				The place where this application resides
		 * @param	Authentication		$athAuthentication		The Authentication Class currently in use
		 *
		 * @method
		 */
		
		function __construct (&$strWebDir, Authentication $athAuthentication)
		{
			parent::__construct ($strWebDir);
			
			$this->_athAuthentication = $this->attachObject ($athAuthentication);
		}
		
		//------------------------------------------------------------------------//
		// Output
		//------------------------------------------------------------------------//
		/**
		 * Output()
		 *
		 * Saves session and Outputs
		 *
		 * Saves the session information for the employee to the database and outputs the
		 * data to the screen
		 *
		 * @param	String				$strXSLFilename		The XSLT Template being translated
		 * @return	Void
		 *
		 * @method
		 */
		
		
		public function Output ($strXSLFilename)
		{
			if ($this->_athAuthentication->isAuthenticated ())
			{
				$this->_athAuthentication->AuthenticatedEmployee ()->Save ();
			
				if (DEBUG_MODE == TRUE)
				{
					// Get user permission
					$intUserPermission = $this->_athAuthentication->AuthenticatedEmployee ()->Pull('Priviledges')->GetValue();
	
					// Check if the user is allowed to view debug info
					if (HasPermission($intUserPermission, $arrPage['Permission']))
					{
						$oblstrSystemDebug = $this->attachObject (new dataString ('SystemDebug', SystemDebug ()));
					}
				}
			}
			
			parent::Output ($strXSLFilename);
		}
	}
	 
?>
