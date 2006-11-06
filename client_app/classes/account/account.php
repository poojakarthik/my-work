<?php
	
	class Account extends dataObject
	{
		
		private $_cntContact;
		
		function __construct (&$cntContact, $intAccount)
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
			
			$rowAccount = $selAccounts->Fetch ($this);
		}
		
		public function getInvoices ()
		{
			$selInvoices = new StatementSelect ("Invoice", "Id", "Account = <Account>");
			$selInvoices->Execute(Array("Account" => $this->Pull ("Id")->getValue ()));
			
			$oblarrInvoices = new dataArray ("Invoices", "Invoice");
			
			while ($arrInvoice = $selInvoices->Fetch ())
			{
				$oblarrInvoices->Push (new Invoice ($this->_cntContact, $arrInvoice ['Id']));
			}
			
			return $oblarrInvoices;
		}
	}
	
?>
