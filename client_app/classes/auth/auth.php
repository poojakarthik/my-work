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
	
	class Authentication extends ApplicationBaseClass
	{
		
		private $strAuthenticatedUser;
		
		function __construct ()
		{
			if (isset ($_COOKIE ['SessionID']) && isset ($_COOKIE ['Id']))
			{
				$selAuthenticated = new StatementUpdate ("Contact", "count(*)", "Id LIKE <Id> AND SessionID = <SessionID>");
				
				if ($selAuthenticated->Execute(Array("Id" => $_COOKIE ['Id'], "Id" => $_COOKIE ['Id'])))
				{
					throw new Exception ("You are logged in :)");
				} else {
					throw new Exception ("You are not logged in :(");
				}
			}
		}
		
		public function getAuthentication ()
		{
			return $this->strAuthenticatedUser !== null;
		}
	}
	
?>