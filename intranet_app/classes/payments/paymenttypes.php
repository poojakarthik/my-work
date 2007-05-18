<?php

	//----------------------------------------------------------------------------//
	// paymenttypes.php
	//----------------------------------------------------------------------------//
	/**
	 * paymenttypes.php
	 *
	 * Contains the PaymentType object
	 *
	 * Contains the PaymentType object
	 *
	 * @file		paymenttypes.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'Bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// PaymentTypes
	//----------------------------------------------------------------------------//
	/**
	 * PaymentTypes
	 *
	 * Textual Service Types
	 *
	 * Allows Textual (named) Representation of the Constants which form Service Types
	 *
	 * @prefix	pml
	 *
	 * @package	intranet_app
	 * @class	PaymentType
	 * @extends	dataEnumerative
	 */
	
	class PaymentTypes extends dataEnumerative
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Controls a List of PaymentType
		 *
		 * Controls a List of PaymentType
		 *
		 * @param	Integer		$intPaymentType			[Optional] An Integer representation of a Service type which matches a Constant
		 *
		 * @method
		 */
		
		function __construct ($intPaymentType=null)
		{
			parent::__construct ('PaymentTypes');
			
			// Instantiate the Variable Values for possible selection
			$this->_BILLEXPRESS		= $this->Push (new PaymentType (PAYMENT_TYPE_BILLEXPRESS));
			$this->_BPAY			= $this->Push (new PaymentType (PAYMENT_TYPE_BPAY));
			$this->_CHEQUE			= $this->Push (new PaymentType (PAYMENT_TYPE_CHEQUE));
			$this->_CREDIT_CARD		= $this->Push (new PaymentType (PAYMENT_TYPE_CREDIT_CARD));
			$this->_SECUREPAY		= $this->Push (new PaymentType (PAYMENT_TYPE_SECUREPAY));
			$this->_EFT				= $this->Push (new PaymentType (PAYMENT_TYPE_EFT));
			$this->_CASH			= $this->Push (new PaymentType (PAYMENT_TYPE_CASH));
			$this->_AUSTRAL			= $this->Push (new PaymentType (PAYMENT_TYPE_AUSTRAL));
			
			$this->setValue ($intPaymentType);
		}
		
		//------------------------------------------------------------------------//
		// setValue
		//------------------------------------------------------------------------//
		/**
		 * setValue()
		 *
		 * Change the Selected Service Type
		 *
		 * Change the Selected Service Type to another Service Type
		 *
		 * @param	Integer		$intPaymentType		The value of the PaymentType Constant wishing to be set
		 * @return	Boolean							Whether or not the Select succeeded
		 *
		 * @method
		 */
		
		public function setValue ($intPaymentType)
		{
			// Select the value
			switch ($intPaymentType)
			{
				case PAYMENT_TYPE_BILLEXPRESS:		$this->Select ($this->_BILLEXPRESS);	return true;
				case PAYMENT_TYPE_BPAY:				$this->Select ($this->_BPAY);			return true;
				case PAYMENT_TYPE_CHEQUE:			$this->Select ($this->_CHEQUE);			return true;
				case PAYMENT_TYPE_CREDIT_CARD:		$this->Select ($this->_CREDIT_CARD);	return true;
				case PAYMENT_TYPE_SECUREPAY:		$this->Select ($this->_SECUREPAY);		return true;
				case PAYMENT_TYPE_EFT:				$this->Select ($this->_EFT);			return true;
				case PAYMENT_TYPE_CASH:				$this->Select ($this->_CASH);			return true;
				case PAYMENT_TYPE_AUSTRAL:			$this->Select ($this->_AUSTRAL);		return true;
				default:							return false;
			}
		}
	}
	
?>
