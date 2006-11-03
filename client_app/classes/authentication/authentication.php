<?php
	
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//
	
//----------------------------------------------------------------------------//
// auth
//----------------------------------------------------------------------------//
/**
 * auth
 *
 * Provides a class for handling all authentication methods for the client
 * application system
 *
 * @file		auth.php
 * @language	PHP
 * @package		client_app
 * @author		Bashkim 'bash' Isai
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	 
	  
	//----------------------------------------------------------------------------//
	// Authentication
	//----------------------------------------------------------------------------//
	/**
	 * Authentication
	 *
	 * Provides a system for authenticating user login
	 *
	 * Provides a system for authenticating user login
	 *
	 *
	 * @prefix		ath
	 *
	 * @package		client_app
	 * @class		Authentication
	 */
	
	class Authentication extends dataObject
	{
		
		private $oblarrAuthenticatedUser;
		
 		//------------------------------------------------------------------------//
		// Authentication() - Constructor
		//------------------------------------------------------------------------//
		/**
		 * Authentication()
		 *
		 * Constructor for Authentication
	 	 *
		 * Constructor for Authentication
		 *
		 * @return		void
		 *
		 * @method
		 */ 
		
		function __construct ()
		{
			parent::__construct ("Authentication");
			
			// If the authentication wants to see if it can come through ...
			if (isset ($_COOKIE ['Id']) && isset ($_COOKIE ['SessionId']))
			{
				// Check their session is valid ...
				$selAuthenticated = new StatementSelect (
					"Contact", "*", 
					"Id = <Id> AND SessionID = <SessionId> AND SessionExpire > NOW()"
				);
				
				$selAuthenticated->Execute(Array("Id" => $_COOKIE ['Id'], "SessionId" => $_COOKIE ['SessionId']));
				
				// If the session is valid - revalidate the session so they can have another 20 minutes
				if ($selAuthenticated->Count () == 1)
				{
					$this->oblarrAuthenticatedUser = $this->Push ($selAuthenticated->Fetch ("User"));
					
					// Updating information
					$Update = Array("SessionExpire" => new MySQLFunction ("ADDTIME(NOW(),'00:20:00')"));
					
					// update the table
					$updUpdateStatement = new StatementUpdate("Contact", "Id = <Id>", $Update);
					$updUpdateStatement->Execute($Update, Array("Id" => $_COOKIE ['Id']));
					
					setCookie ("Id", $_COOKIE ['Id'], time () + (60 * 20), "/");
					setCookie ("SessionId", $_COOKIE ['SessionId'], time () + (60 * 20), "/");
				} else {
					// Unset the cookies so we don't have to bother with them
					setCookie ("Id", "", time () - 3600);
					setCookie ("SessionId", "", time () - 3600);
				}
			}
		}
		
 		//------------------------------------------------------------------------//
		// getAuthentication ()
		//------------------------------------------------------------------------//
		/**
		 * getAuthentication ()
		 *
		 * Get the status of authentication
	 	 *
		 * Get the status of authentication
		 *
		 * @return		boolean					true:	if they are logged in
		 *								false:	they are not logged in
		 *
		 * @method
		 */ 
		public function getAuthentication ()
		{
			return $this->oblarrAuthenticatedUser !== null;
		}
		
		public function Login ($UserName, $PassWord)
		{
			// turn off oblib so we can treat this as an array
			DatabaseAccess::$bolObLib = false;
			
			// get the ID of the person who we want to login as
			// (identified by UserName + PassWord)
			// if no rows are returned, we have do not have 
			// a correct authentication
			
			$selSelectStatement = new StatementSelect("Contact", "Id", "UserName = <UserName> AND PassWord = SHA1(<PassWord>)");
			$selSelectStatement->Execute(Array("UserName"=>$UserName, "PassWord"=>$PassWord));
			
			// No match? Then you're not authenticated!
			if ($selSelectStatement->Count () <> 1)
			{
				return false;
			}
			
			// If we're up to here, we are authenticated
			// So store the person's ID in a field
			$arrFetch = $selSelectStatement->Fetch ();
			$Id = $arrFetch ['Id'];
			
			// Turn ObLib back on
			DatabaseAccess::$bolObLib = true;
			
			// Generate a new session ID
			$SessionId = sha1(uniqid(rand(), true));
			
			// Updating information
			$Update = Array("SessionId" => $SessionId, "SessionExpire" => new MySQLFunction ("ADDTIME(NOW(),'00:20:00')"));
			
			// update the table
			$updUpdateStatement = new StatementUpdate("Contact", "UserName = <UserName> AND PassWord = SHA1(<PassWord>)", $Update);
			if ($updUpdateStatement->Execute($Update, Array("UserName" => $UserName, "PassWord" => $PassWord)) == 1)
			{
				setCookie ("Id", $Id, time () + (60 * 20), "/");
				setCookie ("SessionId", $SessionId, time () + (60 * 20), "/");
				return true;
			}
			else
			{
				return false;
			}
		}
		
		public function Logout ()
		{
			// Kill the cookies ...
			setCookie ("Id", "", time () - 3600, "/");
			setCookie ("SessionId", "", time () - 3600, "/");
			
			// Update the database to reflect nobody being logged in
			// Updating information
			$Update = Array("SessionExpire" => new MySQLFunction ("NOW()"));
			
			// update the table
			$updUpdateStatement = new StatementUpdate("Contact", "Id = <Id>", $Update);
			$updUpdateStatement->Execute($Update, Array("Id" => $_COOKIE ['Id']));
		}
	}
	
?>
