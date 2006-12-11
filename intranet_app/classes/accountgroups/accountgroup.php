<?php
	
	//----------------------------------------------------------------------------//
	// accountgroup.php
	//----------------------------------------------------------------------------//
	/**
	 * accountgroup.php
	 *
	 * File containing Account Group Class
	 *
	 * File containing Account Group Class
	 *
	 * @file		accountgroup.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// AccountGroup
	//----------------------------------------------------------------------------//
	/**
	 * AccountGroup
	 *
	 * An Account Group in the Database
	 *
	 * An Account Group in the Database
	 *
	 *
	 * @prefix	agr
	 *
	 * @package		intranet_app
	 * @class		AccountGroup
	 * @extends		dataObject
	 */
	
	class AccountGroup extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Account Group Object
		 *
		 * Constructor for a new Account Group Object
		 *
		 * @param	Integer		$intId		The Id of the Account Group being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the account information and Store it ...
			$selAccountGroup = new StatementSelect ('AccountGroup', '*', 'Id = <Id>', null, '1');
			$selAccountGroup->useObLib (TRUE);
			$selAccountGroup->Execute (Array ('Id' => $intId));
			
			if ($selAccountGroup->Count () != 1)
			{
				throw new Exception ('No such Account Group');
			}
			
			$selAccountGroup->Fetch ($this);
			
			// Construct the object
			parent::__construct ('AccountGroup', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// getAccount
		//------------------------------------------------------------------------//
		/**
		 * getAccount()
		 *
		 * Get an Account
		 *
		 * Get an Account if it is Located in this Account Group
		 *
		 * @param	Integer		$intId		The Id of the Account being Retrieved
		 *
		 * @method
		 */
		
		function getAccount ($intId)
		{
			// Pull all the account information and Store it ...
			$selAccount = new StatementSelect ('Account', 'Id', 'AccountGroup = <AccountGroup> AND Id = <Id>');
			$selAccount->Execute (Array ('AccountGroup' => $this->Pull ('Id')->getValue (), 'Id' => $intId));
			
			if ($arrAccount = $selAccount->Fetch ())
			{
				return new Account ($arrAccount ['Id']);
			}
			
			return null;
		}
	}
	
?>
