<?php

class Ticketing_Contact_Account
{
	private function __construct()
	{
		
	}

	/**
	 * Associates a ticketing contact (passed by id or as an object associated with a contact) 
	 * with an account (passed by id or as an object associated with an account).
	 * If the passed 'account' is a ticket, all correspondance contacts for that ticket are also
	 * associated with the account.
	 * 
	 * @param $mixContact mixed Ticketing Conatact Id (numeric), Ticketing Contact, Ticketing Ticket or Ticketing Correspondance
	 * @param $mixAccount mixed Account Id (numeric), Account or Ticketing Ticket
	 */	
	public static function associate($mixContact, $mixAccount)
	{
		$accountId = $mixAccount instanceof Account ? $mixAccount->id : 
					 ($mixAccount instanceof Ticketing_Ticket ? $mixAccount->accountId
					 : intval($mixAccount));
		$contactId = $mixContact instanceof Ticeting_Contact ? $mixContact->id : 
					 ($mixContact instanceof Ticeting_Ticket ? $mixContact->contactId : 
					 ($mixContact instanceof Ticeting_Correspondance ? $mixContact->contactId 
					 : intval($mixAccount)));

		if (!$accountId)
		{
			// There is no account to associate with contacts
			return TRUE;
		}

		$selAssociation = new StatementSelect('ticketing_contact_account', array('id'), array('ticketing_contact_id = <TCID> AND account_id = <ACID>'));
		$arrWhere = array('TCID' => $contactId, 'ACID' => $accountId);
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

		// If the contact was passed as a ticket, we need to ensure that all of the ticket
		// correspondances are associated with the ticket account
		if ($mixContact instanceof Ticketing_Ticket)
		{
			$arrColumns = array('account_id' => 't.account_id', 'contact_id' => 'c.contact_id');

			$strTables = "
					ticketing_ticket t
					JOIN ticketing_correspondance c
					ON t.id = c.ticket_id
					LEFT OUTER JOIN ticketing_contact_account a
					ON t.account_id = a.account_id
					AND c.id = a.ticketing_contact_id
					WHERE t.account_id IS NOT NULL 
					and a.id IS NULL
			";

			$strWhere = "
					t.id = <TICKET_ID>
					t.account_id IS NOT NULL 
					and a.id IS NULL
			";

			$arrWhere = array('TICKET_ID' => $mixContact->id);

			$strGroupBy = " t.account_id, c.contact_id ";

			$selUnassociated = new StatementSelect($strTables, $arrColumns, $strWhere, "", "", $strGroupBy);
			if (($mixReturn = $selUnassociated->Execute()) === FALSE)
			{
				throw new Exception('Failed to check for contacts unassociated with accounts: ' . $selUnassociated->Error());
			}
			while ($row = $selUnassociated->Fetch())
			{
				self::associate($row['contact_id'], $row['account_id']);
			}
		}

		return TRUE;
	}

	public static function listAccountsForContact()
	{
		// WIP ?? This should be implemented with a view to being used when entering a correspondance/ticket.
		// If a contact has been identified, the user/contact should be able to select an account
	}
}

?>
