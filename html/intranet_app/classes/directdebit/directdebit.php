<?php
	
	//----------------------------------------------------------------------------//
	// directdebit.php
	//----------------------------------------------------------------------------//
	/**
	 * directdebit.php
	 *
	 * File containing Direct Debit Class
	 *
	 * File containing Direct Debit Class
	 *
	 * @file		directdebit.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// directdebit
	//----------------------------------------------------------------------------//
	/**
	 * directdebit
	 *
	 * An Direct Debit in the Database
	 *
	 * An Direct Debit in the Database
	 *
	 *
	 * @prefix	crc
	 *
	 * @package		intranet_app
	 * @class		DirectDebit
	 * @extends		dataObject
	 */
	
	class DirectDebit extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Direct Debit
		 *
		 * Constructor for a new Direct Debit
		 *
		 * @param	Integer		$intId		The Id of the Direct Debit being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Direct Debit information and Store it ...
			$selDirectDebit = new StatementSelect ('DirectDebit', '*', 'Id = <Id>', null, 1);
			$selDirectDebit->useObLib (TRUE);
			$selDirectDebit->Execute (Array ('Id' => $intId));
			
			if ($selDirectDebit->Count () <> 1)
			{
				throw new Exception ('Direct Debit does not exist.');
			}
			
			$selDirectDebit->Fetch ($this);
			
			// Construct the object
			parent::__construct ('DirectDebit', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
