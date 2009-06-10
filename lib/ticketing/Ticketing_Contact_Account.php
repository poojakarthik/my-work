<?php

class Ticketing_Contact_Account
{
	private function __construct()
	{
		
	}

	/**
	 * Associates a ticketing contact (passed by id or as an object associated with a contact) 
	 * with an account (passed by id or as an object associated with an account).
	 * If the passed 'account' is a ticket, all correspondence contacts for that ticket are also
	 * associated with the account.
	 * 
	 * @param $mixContact mixed Ticketing Conatact Id (numeric), Ticketing Contact, Ticketing Ticket or Ticketing Correspondance
	 * @param $mixAccount mixed Account Id (numeric), Account or Ticketing Ticket
	 */	
	public static function associate($mixContact, $mixAccount)
	{
		$contactId = $mixContact instanceof Ticketing_Contact ? $mixContact->id : 
					 ($mixContact instanceof Ticketing_Ticket ? $mixContact->contactId : 
					 ($mixContact instanceof Ticketing_Correspondance ? $mixContact->contactId 
					 : intval($mixContact)));
		$accountId = $mixAccount instanceof Account ? $mixAccount->id : 
					 ($mixAccount instanceof Ticketing_Ticket ? $mixAccount->accountId
					 : intval($mixAccount));

		if (!$accountId)
		{
			// There is no account to associate with contacts
			return TRUE;
		}

		$selAssociation = new StatementSelect('ticketing_contact_account', array('id'=>'id'), 'ticketing_contact_id = <TCID> AND account_id = <ACID>');
		$arrWhere = array('TCID' => intval($contactId), 'ACID' => intval($accountId));
		if (($outcome=$selAssociation->Execute($arrWhere)) === FALSE)
		{
			throw new Exception('Failed to check for association between account ' . $accountId . ' and contact ' . $contactId . ': ' . $selAssociation->Error());
		}

		// If there isn't already an association between them... 
		if (!$outcome)
		{
			$arrValues = array('ticketing_contact_id' => $contactId, 'account_id' => $accountId);
			$insAssociation = new StatementInsert('ticketing_contact_account', $arrValues);
			if (($outcome = $insAssociation->Execute($arrValues)) === FALSE)
			{
				throw new Exception('Failed to associate contact ' . $contactId . ' with account ' . $accountId . ': ' . $insAssociation->Error());
			}
		}

		// If the contact was passed as a ticket, we need to ensure that each contact referenced in the correspondance items
		// are now associated with the ticket account
		if ($mixContact instanceof Ticketing_Ticket)
		{
			$arrColumns = array('contact_id');

			$strTable = "ticketing_correspondance";

			$strWhere = "ticket_id = <TicketId> AND contact_id NOT IN (SELECT ticketing_contact_id FROM ticketing_contact_account WHERE account_id = <AccountId>)";

			$arrWhere = array(	'TicketId'	=> intval($mixContact->id),
								'AccountId'	=> intval($accountId)
								);

			$selUnassociated = new StatementSelect($strTable, $arrColumns, $strWhere);
			if (($mixReturn = $selUnassociated->Execute($arrWhere)) === FALSE)
			{
				throw new Exception('Failed to check for contacts unassociated with accounts: ' . $selUnassociated->Error());
			}
			while ($row = $selUnassociated->Fetch())
			{
				self::associate($row['contact_id'], $accountId);
			}
		}

		return TRUE;
	}

	public static function listAccountsForContact()
	{
		// WIP ?? This should be implemented with a view to being used when entering a correspondence/ticket.
		// If a contact has been identified, the user/contact should be able to select an account
	}
}

?>
