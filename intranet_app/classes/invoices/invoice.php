<?php
	
	//----------------------------------------------------------------------------//
	// Invoice.php
	//----------------------------------------------------------------------------//
	/**
	 * Invoice.php
	 *
	 * File containing Invoice Class
	 *
	 * File containing Invoice Class
	 *
	 * @file		Invoice.php
	 * @language	PHP
	 * @package		intranet_app
	 * @author		Bashkim 'bash' Isai
	 * @version		6.11
	 * @copyright	2006 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	
	//----------------------------------------------------------------------------//
	// Invoice
	//----------------------------------------------------------------------------//
	/**
	 * Invoice
	 *
	 * An Invoice in the Database
	 *
	 * An Invoice in the Database
	 *
	 *
	 * @prefix		inv
	 *
	 * @package		intranet_app
	 * @class		Invoice
	 * @extends		dataObject
	 */
	
	class Invoice extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// __construct
		//------------------------------------------------------------------------//
		/**
		 * __construct()
		 *
		 * Constructor for a new Invoice
		 *
		 * Constructor for a new Invoice
		 *
		 * @param	Integer		$intId		The Id of the Invoice being Retrieved
		 *
		 * @method
		 */
		
		function __construct ($intId)
		{
			// Pull all the Invoice information and Store it ...
			$selInvoice = new StatementSelect ('Invoice', '*', 'Id = <Id>', null, 1);
			$selInvoice->useObLib (TRUE);
			$selInvoice->Execute (Array ('Id' => $intId));
			
			if ($selInvoice->Count () <> 1)
			{
				throw new Exception ('Invoice does not exist.');
			}
			
			$selInvoice->Fetch ($this);
			
			// Construct the object
			parent::__construct ('Invoice', $this->Pull ('Id')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Account
		//------------------------------------------------------------------------//
		/**
		 * Account()
		 *
		 * Get the Account the Invoice was Charged to
		 *
		 * Get the Account the Invoice was Charged to
		 *
		 * @return	Account
		 *
		 * @method
		 */
		
		function Account ()
		{
			return new Account ($this->Pull ('Account')->getValue ());
		}
		
		//------------------------------------------------------------------------//
		// Charges
		//------------------------------------------------------------------------//
		/**
		 * Charges()
		 *
		 * Get the Charges the Invoice has
		 *
		 * Get the Charges the Invoice has
		 *
		 * @return	Charges
		 *
		 * @method
		 */
		
		public function Charges ()
		{
			$cgsCharges = new Charges ();
			$cgsCharges->Constrain ('Account'		, '=', $this->Pull ('Account')->getValue ());
			$cgsCharges->Constrain ('InvoiceRun'	, '=', $this->Pull ('InvoiceRun')->getValue ());
			return $cgsCharges;
		}
		
		//------------------------------------------------------------------------//
		// ServiceTotals
		//------------------------------------------------------------------------//
		/**
		 * ServiceTotals()
		 *
		 * Get a list of Service Totals on this Invoice
		 *
		 * Get a list of Service Totals on this Invoice
		 *
		 * @return	ServiceTotals
		 *
		 * @method
		 */
		
		public function ServiceTotals ()
		{
			$stlServiceTotals = new ServiceTotals;
			$stlServiceTotals->Constrain ('InvoiceRun',	'=',	$this->Pull ('InvoiceRun')->getValue ());
			$stlServiceTotals->Constrain ('Account',	'=',	$this->Pull ('Account')->getValue ());
			
			return $stlServiceTotals;
		}
		
		//------------------------------------------------------------------------//
		// ServiceTotal
		//------------------------------------------------------------------------//
		/**
		 * ServiceTotal()
		 *
		 * Get a Service Total
		 *
		 * Get a Service Totals on this Invoice
		 *
		 * @return	ServiceTotal
		 *
		 * @method
		 */
		
		public function ServiceTotal ($intServiceTotal)
		{
			$selServiceTotal = new StatementSelect (
				'ServiceTotal', 
				'Id', 
				'Id = <Id> AND InvoiceRun = <InvoiceRun>', 
				null, 
				1
			);
			
			$selServiceTotal->Execute (Array ('Id' => $intServiceTotal, 'InvoiceRun' => $this->Pull ('InvoiceRun')->getValue ()));
			
			if ($selServiceTotal->Count () <> 1)
			{
				throw new Exception ('Invoice does not exist.');
			}
			
			return new ServiceTotal ($intServiceTotal);
		}
		
		//------------------------------------------------------------------------//
		// CDRs
		//------------------------------------------------------------------------//
		/**
		 * CDRs()
		 *
		 * Get the CDRs the Invoice has
		 *
		 * Get the CDRs the Invoice has
		 *
		 * @return	CDRs_Invoiced
		 *
		 * @method
		 */
		
		public function CDRs ()
		{
			return new CDRs_Invoiced ($this);
		}
		
		//------------------------------------------------------------------------//
		// Dispute
		//------------------------------------------------------------------------//
		/**
		 * Dispute()
		 *
		 * Apply a Dispute against this Invoice
		 *
		 * Apply a Dispute against this Invoice
		 *
		 * @param	Float			$fltDisputed			The amount to set the Dispute as (inc. GST)
		 * @return	void
		 *
		 * @method
		 */
		
		public function Dispute ($fltDisputed)
		{
			$fltDisputed = floatval (str_replace ('$', '', $fltDisputed));
			
			//TODO!bash! [  DONE  ]		make sure $fltDisputed !> Invoice.Total
			if ($fltDisputed > $this->Pull ('Total')->getValue () + $this->Pull ('Tax')->getValue ())
			{
				throw new Exception ('Dispute Too High');
			}
			
			$arrDispute = Array (
				'Disputed'		=> $fltDisputed,
				'Status'		=> INVOICE_DISPUTED
			);
			
			$updDispute = new StatementUpdate ('Invoice', 'Id = <Id>', $arrDispute);
			$updDispute->Execute ($arrDispute, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
		
		//------------------------------------------------------------------------//
		// Resolve
		//------------------------------------------------------------------------//
		/**
		 * Resolve()
		 *
		 * Resolve a Dispute
		 *
		 * Resolve a Dispute
		 *
		 * @param	AuthenticatedEmployee		$aemAuthenticatedEmployee	The person who is performing this action
		 * @param	Integer						$intResolveMethod			(CONSTANT) The method in which this dispute will be resolved
		 * @param	Float						$fltAmount					The amount which will be changed to the Account, if customer to Pay $X.XX
		 * @return	void
		 *
		 * @method
		 */
		
		public function Resolve (AuthenticatedEmployee $aemAuthenticatedEmployee, $intResolveMethod, $fltAmount)
		{
			if ($this->Pull ('Status')->getValue () <> INVOICE_DISPUTED)
			{
				throw new Exception ('Invoice Not Disputed');
			}
			
			switch ($intResolveMethod)
			{
				case DISPUTE_RESOLVE_FULL_PAYMENT:
					// If the full amount is required to be paid (for example, Dispute was Denied)
					$fltBalance = $this->Pull ('Balance')->getValue ();
					
					break;
					
				case DISPUTE_RESOLVE_PARTIAL_PAYMENT:
					// If a payment is required for a particular amount of a Dispute
					
					// generate a credit for Invoice.Disputed - $fltAmount
					$arrCredit = Array (
						'AccountGroup'	=> $this->Pull ('AccountGroup')->getValue (),
						'Account'		=> $this->Pull ('Account')->getValue (),
						'Service'		=> NULL,
						'InvoiceRun'	=> NULL,
						'CreatedBy'		=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
						'CreatedOn'		=> new MySQLFunction ('NOW()'),
						'ApprovedBy'	=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
						'ChargeType'	=> '',
						'Description'	=> 'Invoice Dispute (Invoice: #' . $this->Pull ('Id')->getValue () . ')',
						'ChargedOn'		=> NULL,
						'Nature'		=> NATURE_CR,
						'Amount'		=> $this->Pull ('Disputed')->getValue () - $fltAmount,
						'Status'		=> CHARGE_APPROVED
					);
					
					$fltBalance = $this->Pull ('Balance')->getValue () + $fltAmount;
					
					break;
					
				case DISPUTE_RESOLVE_NO_PAYMENT:
					// generate a credit for Invoice.Disputed
					$arrCredit = Array (
						'AccountGroup'	=> $this->Pull ('AccountGroup')->getValue (),
						'Account'		=> $this->Pull ('Account')->getValue (),
						'Service'		=> NULL,
						'InvoiceRun'	=> NULL,
						'CreatedBy'		=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
						'CreatedOn'		=> new MySQLFunction ('NOW()'),
						'ApprovedBy'	=> $aemAuthenticatedEmployee->Pull ('Id')->getValue (),
						'ChargeType'	=> '',
						'Description'	=> 'Invoice Dispute (Invoice: #' . $this->Pull ('Id')->getValue () . ')',
						'ChargedOn'		=> NULL,
						'Nature'		=> NATURE_CR,
						'Amount'		=> $this->Pull ('Disputed')->getValue (),
						'Status'		=> CHARGE_APPROVED
					);
					break;
					
				default:
					throw new Exception ('Invalid Resolution');
			}
			
			if ($arrCredit)
			{
				$insCredit = new StatementInsert ('Charge', $arrCredit);
				$intCredit = $insCredit->Execute ($arrCredit);
			}
			
			// Invoice.Disputed = 0
			// if Balance > 0	Status = INVOICE_COMMITTED
			// else				Status = INVOICE_SETTLED
			$arrDispute = Array (
				'Disputed'		=> 0,
				'Status'		=> ($fltBalance > 0) ? INVOICE_COMMITTED : INVOICE_SETTLED
			);
			
			$updDispute = new StatementUpdate ('Invoice', 'Id = <Id> AND Status = ' . INVOICE_DISPUTED, $arrDispute);
			$updDispute->Execute ($arrDispute, Array ('Id' => $this->Pull ('Id')->getValue ()));
		}
	}
	
?>
