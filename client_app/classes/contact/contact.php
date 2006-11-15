<?php
	
	class Contact extends dataObject
	{
		
		private $_cntContact;
		
		function __construct (&$cntContact, $Id)
		{
			$this->_cntContact =& $cntContact;
			
			parent::__construct ("Contact");
			
			if (!$this->_cntContact->isCustomerContact () && $Id <> $this->_cntContact->Pull ("Id")->getValue ())
			{
				throw new Exception ("You are not authorised to view this contact");
			}
			
			// Check their session is valid ...
			$selContact = new StatementSelect (
				"Contact", "*", "Id = <Id> AND AccountGroup = <AccountGroup>"
			);
			
			$selContact->useObLib (TRUE);
			$selContact->Execute (
				Array (
					"Id" => 			$Id,
					"AccountGroup" =>	$this->_cntContact->Pull ("AccountGroup")->getValue ()
				)
			);
			
			if ($selContact->Count () <> 1)
			{
				throw new Exception ("Could not find a contact with the Id requested");
			}
			
			$selContact->Fetch ($this);
		}
		
		public function setPassword ($strPassWord)
		{
			$objPassWord = new MySQLFunction ("SHA1(<PassWord>)");
			$objPassWord->setParameters (Array ("PassWord"=>$strPassWord));
			
			$arrUpdate = Array ("PassWord" => $objPassWord);
			
			$arrWhere = Array ("Id" => $this->Pull ("Id")->getValue ());
			
			$updUpdateStatement = new StatementUpdate("Contact", "Id = <Id>", $arrUpdate);
			$updUpdateStatement->Execute ($arrUpdate, $arrWhere);
		}
		
		public function setProfile ($strTitle, $strFirstName, $strLastName, $strDOB_year, $strDOB_month, $strDOB_day, $strJobTitle, $strEmail, $strPhone, $strMobile, $strFax)
		{	
			$arrUpdate = Array (
				"Title" =>			$strTitle,
				"FirstName" =>		$strFirstName,
				"LastName" =>		$strLastName,
				"DOB" =>			sprintf ("%04d", $strDOB_year) . "-" . sprintf ("%02d", $strDOB_month) . "-" . sprintf ("%02d", $strDOB_day),
				"JobTitle" =>		$strJobTitle,
				"Email" =>			$strEmail,
				"Phone" =>			$strPhone,
				"Mobile" =>			$strMobile,
				"Fax" =>			$strFax,
			);
			
			$arrWhere = Array ("Id" => $this->Pull ("Id")->getValue ());
			
			$updUpdateStatement = new StatementUpdate("Contact", "Id = <Id>", $arrUpdate);
			$updUpdateStatement->Execute ($arrUpdate, $arrWhere);
		}
	}
	
?>
