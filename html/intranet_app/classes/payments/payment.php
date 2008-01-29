<?php
	
	//----------------------------------------------------------------------------//
	// Payment.php
	//----------------------------------------------------------------------------//
	/**
	 * Payment.php
	 *
	 * File containing Payment Class
	 *
	 * File containing Payment Class
	 *
	 * @file		Payment.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Payment
	//----------------------------------------------------------------------------//
	/**
	 * Payment
	 *
	 * An Payment in the Database
	 *
	 * An Payment in the Database
	 *
	 *
	 * @prefix	pay
	 *
	 * @package		intranet_app
	 * @class		Payment
	 * @extends		dataObject
	 */
	
	class Payment extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Payment
		 *
		 * Constructor for a new Payment
		 *
		 * @param	Integer		$intId		The Id of the Payment being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Payment information and Store it ...
			$selPayment = new StatementSelect ('Payment', '*', 'Id = <Id>', null, 1);
			$selPayment->useObLib (TRUE);
			$selPayment->Execute (Array ('Id' => $intId));
			
			if ($selPayment->Count () <> 1)
			{
				throw new Exception ('Payment does not exist.');
			}
			
			$selPayment->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Payment', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Account
		//------------------------------------------------------------------------//
		/**
		 * Account()
		 *
		 * Gets the Account that the Payment was added to
		 *
		 * Gets the Account that the Payment was added to. If there is no account, 
		 * this returns NULL
		 *
		 * @return	Account
		 *
		 * @method
		 */
		
		public function Account ()
		{
			if (!$this->_actAccount)
			{
				$intAccount = $this->Pull ('Account')->getValue ();
				
				if ($intAccount == NULL)
				{
					return NULL;
				}
				
				$this->_actAccount = new Account ($intAccount);
			}
			
			return $this->_actAccount;
		}
	}
	
?>
