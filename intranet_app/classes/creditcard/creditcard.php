<?php
	
	//----------------------------------------------------------------------------//
	// creditcard.php
	//----------------------------------------------------------------------------//
	/**
	 * creditcard.php
	 *
	 * File containing Credit Card Class
	 *
	 * File containing Credit Card Class
	 *
	 * @file		creditcard.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// CreditCard
	//----------------------------------------------------------------------------//
	/**
	 * CreditCard
	 *
	 * An Credit Card in the Database
	 *
	 * An Credit Card in the Database
	 *
	 *
	 * @prefix	crc
	 *
	 * @package		intranet_app
	 * @class		CreditCard
	 * @extends		dataObject
	 */
	
	class CreditCard extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Credit Card
		 *
		 * Constructor for a new Credit Card
		 *
		 * @param	Integer		$intId		The Id of the Credit Card being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Credit Card information and Store it ...
			$selCreditCard = new StatementSelect ('CreditCard', '*', 'Id = <Id>', null, 1);
			$selCreditCard->useObLib (TRUE);
			$selCreditCard->Execute (Array ('Id' => $intId));
			
			if ($selCreditCard->Count () <> 1)
			{
				throw new Exception ('CreditCard does not exist.');
			}
			
			$selCreditCard->Fetch ($this);
			
			// Construct the object
			parent::__construct ('CreditCard', $this->Pull ('Id')->getValue ());
		}
	}
	
?>
