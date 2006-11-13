<?php
	
//----------------------------------------------------------------------------//
// account.php
//----------------------------------------------------------------------------//
/**
 * account.php
 *
 * Contains information about accounts
 *
 * Contains and controls information regarding accounts in the database
 *
 * @file	account.php
 * @language	PHP
 * @package	client_app
 * @author	Bashkim Isai
 * @version	6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license	NOT FOR EXTERNAL DISTRIBUTION
 *
 */
	 
	//----------------------------------------------------------------------------//
	// account
	//----------------------------------------------------------------------------//
	/**
	 * account
	 *
	 * Contains information about accounts
	 *
	 * Contains and controls information regarding accounts in the database
	 *
	 *
	 * @prefix	act
	 *
	 * @package	client_app
	 * @class	<ClassName||InstanceName>
	 * @extends	dataObject
	 */
	
	class Account extends dataObject
	{
		
		//------------------------------------------------------------------------//
		// _cntContact
		//------------------------------------------------------------------------//
		/**
		 * _cntContact
		 *
		 * The logged in person
		 *
		 * The Object representing the person who is currently logged in (AuthenticatedContact Object)
		 *
		 * @type	AuthenticatedContact
		 *
		 * @property
		 */
		 
		private $_cntContact;
		
		//------------------------------------------------------------------------//
		// Account
		//------------------------------------------------------------------------//
		/**
		 * Account()
		 *
		 * Account representative ObLib object
		 *
		 * An ObLib dataObject representing an account
		 *
		 * @param	AuthenticatedContact		cntContact	[Reference] The authenticated user who we are currently logged in as
		 * @param	Integer 					intAccount	The account which we wish to view
		 *
		 * @method
		 */
		
		function __construct (AuthenticatedContact &$cntContact, $intAccount)
		{
			parent::__construct ("Account");
			
			$this->_cntContact =& $cntContact;
			
			// Check their session is valid ...
			$selAccounts = new StatementSelect ("Account", "*", "Id = <Id>");
			$selAccounts->useObLib (TRUE);
			$selAccounts->Execute(Array("Id" => $intAccount));
			
			if ($selAccounts->Count () <> 1)
			{
				throw new Exception ("Class Account could not be instantiated because it could not be found in the database");
			}
			
			// If the session is valid, then put the information about the account in the object
			$selAccounts->Fetch ($this);
		}
		
		//------------------------------------------------------------------------//
		// getInvoices
		//------------------------------------------------------------------------//
		/**
		 * getInvoices()
		 *
		 * Gets a list of invoices
		 *
		 * Gets a list of all the invoices associated with this account
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function getInvoices ()
		{
			// Get all the invoices in the account
			$selInvoices = new StatementSelect ("Invoice", "Id", "Account = <Account>");
			$selInvoices->Execute(Array("Account" => $this->Pull ("Id")->getValue ()));
			
			// Create a new ObLib object to contain the invoices in
			$oblarrInvoices = new dataArray ("Invoices", "Invoice");
			
			// Loop through all the invoices
			while ($arrInvoice = $selInvoices->Fetch ())
			{
				// Push the invoice into the ObLib object
				$oblarrInvoices->Push (new Invoice ($this->_cntContact, $arrInvoice ['Id']));
			}
			
			// Return the ObLib Object
			return $oblarrInvoices;
		}
		
		//------------------------------------------------------------------------//
		// getServices
		//------------------------------------------------------------------------//
		/**
		 * getServices()
		 *
		 * Gets a list of Services
		 *
		 * Gets a list of all the services associated with this account
		 *
		 * @return	dataArray
		 *
		 * @method
		 */
		
		public function getServices ()
		{
			// Create a new ObLib array named Services containing Service classes
			$oblarrServices = new dataArray ("Services", "Service");
			
			// Pull the list of services that are associated with this account
			$selServices = new StatementSelect ("Service", "Id", "Account = <Account>");
			$selServices->Execute(Array("Account" => $this->Pull ("Id")->getValue ()));
			
			// Put the services in the ObLib array
			while ($arrService = $selServices->Fetch ())
			{
				$oblarrServices->Push (new Service ($this->_cntContact, $arrService ['Id']));
			}
			
			// Return the ObLib array of Services
			return $oblarrServices;
		}
		
		//------------------------------------------------------------------------//
		// getService
		//------------------------------------------------------------------------//
		/**
		 * getService()
		 *
		 * Gets a service from the database
		 *
		 * Gets a service from the database as a Service object. Specifically, this 
		 * is not account specific, it's only placed here to make life easier.
		 *
		 * @param	Integer		intId	The Id of the service wishing to be retrieved
		 * @return	Service
		 *
		 * @method
		 */
		
		public function getService ($intId)
		{
			// Return the service
			return new Service ($this->_cntContact, $intId);
		}
	}
	
?>
