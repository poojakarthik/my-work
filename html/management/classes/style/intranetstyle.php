<?php
	
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
		 * @param	String		$strXSLFilename			The XSLT Template being translated
		 * @param	Array		$arrQuickList			[Optional] An associative array of Name/Value Pairs of Quicklist Details
		 * @return	Void
		 *
		 * @method
		 */
		
		
		public function Output ($strXSLFilename, $arrQuickList=Array())
		{
			// Quick List
			if (is_array ($arrQuickList))
			{
				$oblarrQuickList = $this->attachObject (new dataArray ('QuickList'));
				
				foreach ($arrQuickList as $strName => $mixValue)
				{
					// No Blanks
					if ($mixValue)
					{
						$oblarrQuickList->Push (new dataString ($strName, $mixValue));
					}
				}
			}
			
			// Now
			$this->attachObject (new dataDatetime ('Now', date ("Y-m-d H:i:s", time ())));
			
			// If the Employee is Logged in
			if ($this->_athAuthentication->isAuthenticated ())
			{
				// Save the Employee Session Information
				$this->_athAuthentication->AuthenticatedEmployee ()->Save ();
				
				// Hack - Chuck in a flag for whether or not they can use the ticketing system
				VixenRequire('lib/ticketing/Ticketing_User.php');
				$bolCanUseTicketing = TICKETING_USER_PERMISSION_NONE != Ticketing_User::getPermissionForEmployeeId($this->_athAuthentication->AuthenticatedEmployee()->Pull('Id')->getValue ());
				$oblstrTicketing = $this->attachObject (new dataString ('Ticketing', ($bolCanUseTicketing ? '1' : '0')));
				
				// If we are in DEBUG MODE and we have permission to view a Debug then Output the Debug
				if (DEBUG_MODE == TRUE)
				{
					// Get user permission
					$intUserPermission = $this->_athAuthentication->AuthenticatedEmployee ()->Pull('Privileges')->GetValue();
	
					// Check if the user is allowed to view debug info
					if (HasPermission($intUserPermission, PERMISSION_DEBUG))
					{
						$oblstrSystemDebug = $this->attachObject (new dataString ('SystemDebug', SystemDebug ()));
						$this->InsertDOM (Array('IsDebug'=> "1" ), 'PermissionDEBUG');
					}
					else
					{
						$this->InsertDOM (Array('IsDebug'=> 0 ), 'PermissionDEBUG');
					}
				}
				
				// Attach the Serialized GET and POST details
				// This is done only if the Person is logged in so you don't
				// see any of the Sensitive information (such as Passwords)
				$oblarrDataSerialised = $this->attachObject (new dataArray ('DataSerialised'));
				$oblarrDataSerialised->Push (new dataString ("GET",		serialize ($_GET)));
				$oblarrDataSerialised->Push (new dataString ("POST",	serialize ($_POST)));
			}
			
			$this->InsertDOM (Array('IsDebug'=> 0 ), 'PermissionDEBUG');
			parent::Output ($strXSLFilename);
		}
	}
	 
?>
