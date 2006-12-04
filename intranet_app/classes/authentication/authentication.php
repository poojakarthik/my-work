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
		// authenticatedEmployee
		//------------------------------------------------------------------------//
		/**
		 * AuthenticatedEmployee
		 *
		 * An object (of type AuthenticatedEmployee) or Null
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
			if (isset ($_COOKIE ['Id']) && isset ($_COOKIE ['SessionId']))
			{
				// Check their session is valid ...
				$selAuthenticated = new StatementSelect (
					"Employee", "*", 
					"Id = <Id> AND SessionID = <SessionId> AND SessionExpire > NOW()"
				);
				
				$selAuthenticated->Execute(Array("Id" => $_COOKIE ['Id'], "SessionId" => $_COOKIE ['SessionId']));
				
				// If the session is valid
				if ($selAuthenticated->Count () == 1)
				{
					// Mark the Session as Authenticated
					$usrLoggedIn = $selAuthenticated->Fetch ();
					$this->aemAuthenticatedEmployee = $this->Push (new AuthenticatedEmployee);
					
					// Revalidate the session so they can have another 20 minutes
					$Update = Array("SessionExpire" => new MySQLFunction ("ADDTIME(NOW(),'00:20:00')"));
					$updUpdateStatement = new StatementUpdate("Employee", "Id = <Id>", $Update);
					$updUpdateStatement->Execute($Update, Array("Id" => $_COOKIE ['Id']));
					
					setCookie ("Id", $_COOKIE ['Id'], time () + (60 * 20), "/intranet_app/");
					setCookie ("SessionId", $_COOKIE ['SessionId'], time () + (60 * 20), "/intranet_app/");
				} else {
					// Unset the cookies so we don't have to bother checking them
					setCookie ("Id", "", time () - 3600);
					setCookie ("SessionId", "", time () - 3600);
				}
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
			$selSelectStatement = new StatementSelect("Employee", "Id", "UserName = <UserName> AND PassWord = SHA1(<PassWord>)", null, "1");
			$selSelectStatement->Execute(Array("UserName"=>$strUserName, "PassWord"=>$strPassWord));
			
			// If the employee could not be found, return false
			if ($selSelectStatement->Count () <> 1)
			{
				return false;
			}
			
			// If we reach this part of the Method, the session is authenticated.
			// Therefore, we have to store the Authentication
			$arrFetch = $selSelectStatement->Fetch ();
			$Id = $arrFetch ['Id'];
			
			
			
			// Generate a new session ID
			$SessionId = sha1(uniqid(rand(), true));
			
			// Updating information
			$Update = Array("SessionId" => $SessionId, "SessionExpire" => new MySQLFunction ("ADDTIME(NOW(),'00:20:00')"));
			
			// update the table
			$updUpdateStatement = new StatementUpdate("Employee", "UserName = <UserName> AND PassWord = SHA1(<PassWord>)", $Update);
			
			// If we successfully update the database table
			if ($updUpdateStatement->Execute($Update, Array("UserName" => $strUserName, "PassWord" => $strPassWord)) == 1)
			{
				setCookie ("Id", $Id, time () + (60 * 20), "/");
				setCookie ("SessionId", $SessionId, time () + (60 * 20), "/");
				return true;
			}
			
			return false;
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
		 	// Check if the person is logged in because we don't want unauthenticated users
		 	// to try logging authenticated users out
		 	
		 	if (!$this->isAuthenticated ())
		 	{
		 		return false;
		 	}
		 	
			// Updating information
			$Update = Array("SessionExpire" => new MySQLFunction ("NOW()"));
			
			// update the table
			$updUpdateStatement = new StatementUpdate("Employee", "Id = <Id> AND SessionId = <SessionId>", $Update);
			
			// If we successfully update the database table
			$updUpdateStatement->Execute($Update, Array("Id" => $_COOKIE ['Id'], "SessionId" => $_COOKIE ['SessionId']));
			
			// Unset the cookies so we don't have to bother checking them
			setCookie ("Id", "", time () - 3600);
			setCookie ("SessionId", "", time () - 3600);
			
			return true;
		 }
	}
	
?>
