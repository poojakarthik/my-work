<?php
	
	class InvoiceService extends dataObject
	{
		
		private $_cntContact;
		private $_actAccount;
		
		private $_oblarrInvoiceServices;
		
		function __construct (&$cntContact, $intInvoice, $intService)
		{
			parent::__construct ("InvoiceService");
			
			$this->_cntContact =& $cntContact;
			
			// Firstly, we want to get the information about this invoice. If
			// we are a contact for the accoutn group, we are authenticated to
			// do this through the account group variable. Otherwise this must
			// be authenticated through the user's Account.
			
			$selServiceDetails = new StatementSelect ("ServiceTotal", "*", "Invoice = <Invoice> AND Service = <Service>");
			$selServiceDetails->Execute(Array("Invoice" => $intInvoice, "Service" => $intService));
			
			// Use ObLib and set all this information in the object
			$selServiceDetails->useObLib (TRUE);
			
			if ($selServiceDetails->Count () <> 1)
			{
				throw new Exception ("There is no invoice with the ID you requested");
			}
			
			$selServiceDetails->Fetch ($this);
		}
	}
	
?>
