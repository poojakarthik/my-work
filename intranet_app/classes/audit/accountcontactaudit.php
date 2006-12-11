<?php
	
	class AccountContactAudit extends dataObject
	{
		
		private $_actAccount;
		private $_cntContact;
		
		function __construct (Account $actAccount, Contact $cntContact=null)
		{
			parent::__construct ('AuditItem');
			
			$this->_actAccount = $this->Push ($actAccount);
			
			if ($cntContact)
			{
				$this->_cntContact = $this->Push ($cntContact);
			}
		}
		
		public function getAccount ()
		{
			return $this->_actAccount;
		}
		
		public function getContact ()
		{
			return $this->_cntContact;
		}
	}
	
?>
