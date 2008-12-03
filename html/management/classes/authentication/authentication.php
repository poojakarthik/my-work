<?php

	//----------------------------------------------------------------------------//
	// authentication.php
	//----------------------------------------------------------------------------//
	/**
	 * authentication.php
	 *
	 * Contains the Authentication Class
	 *
	 * Contains the Class which Manages Authentication within the System
	 *
	 * @file	authentication.php
	 * @language	PHP
	 * @package	intranet_app
	 * @author	Bashkim 'Bash' Isai
	 * @version	6.10
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license	NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Authentication
	//----------------------------------------------------------------------------//
	/**
	 * Authentication
	 *
	 * Class for Authenticating Employees into the System
	 *
	 * A class that controls the Authentication of an Employee which will ultimately allow
	 * them access into the Intranet Application
	 *
	 *
	 * @prefix	ath
	 *
	 * @package	intranet_app
	 * @class	Authentication
	 * @extends	dataObject
	 */
	
	class Authentication extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// aemAuthenticatedEmployee
		//------------------------------------------------------------------------//
		/**
		 * aemAuthenticatedEmployee
		 *
		 * An object of type AuthenticatedEmployee (or NULL)
		 *
		 * If the session is not Authenticated, this property would be NULL. If the
		 * session is Authenticated, this property would be an object which represents 
		 * the person who is currently logged into the system.
		 *
		 * @type	AuthenticatedEmployee
		 *
		 * @property
		 */
		
		private $aemAuthenticatedEmployee;
		
		//------------------------------------------------------------------------//
		// Authentication
		//------------------------------------------------------------------------//
		/**
		 * Authentication()
		 *
		 * Constructor for Authentication
		 *
		 * Constructs a new Authentication Class which Controls access to the Web Site
		 *
		 * @method
		 */
		
		function __construct ()
		{
			// Construct the Object
			parent::__construct ("Authentication");

			// If the authentication wants to see if it can come through ...
			if ($_SESSION['LoggedIn'] && $_SESSION['SessionExpire'] > time())
			{
				// Mark the Session as Authenticated
				try
				{
					$this->aemAuthenticatedEmployee = $this->Push (new AuthenticatedEmployee);
	
					// Revalidate the session so they can have another 20 minutes (or 7 days if GOD)
					$_SESSION['SessionExpire'] = time() + ($_SESSION['User']['Privileges'] == USER_PERMISSION_GOD ? (60 * 60 * 24 * 7) : (60 * 20));
				}
				catch(Exception $e)
				{
					$_SESSION['LoggedIn'] = FALSE;
				}
			}
			else
			{
				$_SESSION['LoggedIn'] = FALSE;
			}
		}
		
		//------------------------------------------------------------------------//
		// isAuthenticated
		//------------------------------------------------------------------------//
		/**
		 * isAuthenticated()
		 *
		 * Checks Authentication
		 *
		 * Returns a Boolean representing whether or not the user is logged into the system
		 *
		 * @return	Boolean
		 *
		 * @method
		 */
		
		public function isAuthenticated ()
		{
			return $this->aemAuthenticatedEmployee instanceOf AuthenticatedEmployee;
		}
		
		//------------------------------------------------------------------------//
		// AuthenticatedEmployee
		//------------------------------------------------------------------------//
		/**
		 * AuthenticatedEmployee()
		 *
		 * Access for AuthenticatedEmployee
		 *
		 * Gives the ability to perform methods and functions on the current AuthenticatedEmployee
		 *
		 * @return	Boolean
		 *
		 * @method
		 */
		
		public function &AuthenticatedEmployee ()
		{
			return $this->aemAuthenticatedEmployee;
		}
		
		//------------------------------------------------------------------------//
		// Login
		//------------------------------------------------------------------------//
		/**
		 * Login()
		 *
		 * Attempts a Session Authentication
		 *
		 * Attempts to Authenticate the Session (Identified by UserName and PassWord)
		 * against an Employee
		 *
		 * @param	String		$strUserName		The UserName of the Attempted Authentication
		 * @param	String		$strPassWord		The PassWord of the Attempted Authentication
		 *
		 * @return	Boolean
		 *
		 * @method
		 */
		public function Login ($strUserName, $strPassWord)
		{
			// Get the Id of the Employee (Identified by UserName and PassWord combination)
			$selSelectStatement = new StatementSelect (
				"Employee", 
				"*", 
				"UserName = <UserName> AND PassWord = SHA1(<PassWord>) AND Archived = 0", 
				NULL, 
				"1"
			);

			$selSelectStatement->Execute(Array("UserName"=>$strUserName, "PassWord"=>$strPassWord));

			// If the employee could not be found, return false
			if ($selSelectStatement->Count () <> 1)
			{
				$_SESSION['LoggedIn'] = FALSE;
				return FALSE;
			}

			$currentUser = $selSelectStatement->Fetch();

			// If data exists in the session but is for another user, clear it out
			if (!array_key_exists('User', $_SESSION) || $_SESSION['User']['Id'] != $currentUser['Id'])
			{
				$_SESSION = array();
			}

			// If we reach this part of the Method, the session is authenticated.
			// Therefore, we have to store the Authentication
			$_SESSION['User'] = $currentUser;
			$_SESSION['LoggedIn'] = TRUE;
			$_SESSION['LoggedInTimestamp'] = time();
			setcookie('LoggedInTimestamp', $_SESSION['LoggedInTimestamp']);

			// Updating information
			$_SESSION['SessionDuration'] = ($_SESSION['User']['Privileges'] == USER_PERMISSION_GOD ? (60 * 60 * 24 * 7) : (60 * 20));
			$_SESSION['SessionExpire'] = time() + $_SESSION['SessionDuration'];
			return TRUE;
		}

		//------------------------------------------------------------------------//
		// Logout
		//------------------------------------------------------------------------//
		/**
		 * Logout()
		 *
		 * Removes Session Information
		 *
		 * Removes Session Information
		 *
		 * @return	Boolean
		 *
		 * @method
		 */
		 
		 public function Logout ()
		 {
			$_SESSION = array();
			$_SESSION['LoggedIn'] = FALSE;
			return true;
		 }
	}
?>
