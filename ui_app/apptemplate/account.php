<?php

class Account
{
	function View()
	{	
		/*
		// Check perms
		$this->PermissionOrDie($pagePerms)	// dies if no permissions
		$this->UserHasPerm($pagePerms) 		// returns false if none, true if they do
		*/
		
		/*
		if ($this->Dbo->Account->Id->IsValid())
			//Load account + stuff
			$this->Dbo->Account->Load()
			$this->Dbo->MyObject->Account = $this->Dbo->Account->Id->Value
			$this->Dbo->MyObject->Load()
		
			// Context menu options
			$this->ContextMenu->Account->ViewAccount($this->Dbo->Account-Id->Value)
			Menu
			   |--Account
				|--View Account
			// Load page
			$this->LoadPage('AccountView')
		
		else
			// Load error page
			$this->LoadPage('AccountError')
		*/
		/*
		//for additional functionality like change of lessee
		$someThing = $this->Module->Account->Function()
		??? how do modules appear in the map ???
		*/
		$this->LoadPage('Account_View');
		
	}
}
