<?php
	
	//----------------------------------------------------------------------------//
	// account.php
	//----------------------------------------------------------------------------//
	/**
	 * account.php
	 *
	 * File containing Account Class
	 *
	 * File containing Account Class
	 *
	 * @file		account.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Account
	//----------------------------------------------------------------------------//
	/**
	 * Account
	 *
	 * An account in the Database
	 *
	 * An account in the Database
	 *
	 *
	 * @prefix	act
	 *
	 * @package		intranet_app
	 * @class		Account
	 * @extends		dataObject
	 */
	
	class Account extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Account
		 *
		 * Constructor for a new Account
		 *
		 * @param	Integer		$intId		The Id of the Account being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the account information and Store it ...
			$selAccount = new StatementSelect ('Account', '*', 'Id = <Id>', null, 1);
			$selAccount->useObLib (TRUE);
			$selAccount->Execute (Array ('Id' => $intId));
			
			if ($selAccount->Count () <> 1)
			{
				throw new Exception ('Account does not exist.');
			}
			
			$selAccount->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Account', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
