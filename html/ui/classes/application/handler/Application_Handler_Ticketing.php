<?php

class Application_Handler_Ticketing extends Application_Handler
{
	// Handle a request for the home page of the ticketing system
	public function System()
	{
		// WIP :: Add permissions check!!
		// We need to load all of the tickets matching any passed criteria (in $_POST)
		// Criteria include columns (array(field)), sorting (array(field=>direction)), offset (int, default=0) and limit (int, default=30) as well as:
		// owner_id (=user_id), ticket_id, account_id, ticketing_status_id

		$defaultLimit = 30;

		$sort = array();
		foreach ($_REQUEST['sort'] as $column => $ascDesc)
		{
			if (!$ascDesc)
			{
				continue;
			}
			$column = preg_replace("/[^a-z_]+/i", '', $column);
			if (!$column)
			{
				continue;
			}
			$sort[$column] = ($ascDesc[0]) == 'a';
		}

		$columns = array();
		foreach($_REQUEST['columns'] as $column)
		{
			if (!$column)
			{
				continue;
			}
			$column = preg_replace("/[^a-z]+/i", '', $column);
			if (!$column)
			{
				continue;
			}
			$columns[] = $column;
		}

		$offset = array_key_exists('offset', $_REQUEST) ? intval($_REQUEST['offset']) : 0;
		$limit = array_key_exists('limit', $_REQUEST) ? intval($_REQUEST['limit']) : $defaultLimit;
		if (!$limit)
		{
			$limit = $defaultLimit;
		}

		$ownerId = array_key_exists('owner_id', $_REQUEST) ? intval($_REQUEST['owner_id']) : NULL;
		$ticketId = array_key_exists('ticket_id', $_REQUEST) ? intval($_REQUEST['ticket_id']) : NULL;
		$accountId = array_key_exists('account_id', $_REQUEST) ? intval($_REQUEST['account_id']) : NULL;
		$ticketingStatusId = array_key_exists('ticketing_status_id', $_REQUEST) ? intval($_REQUEST['ticketing_status_id']) : NULL;

		$filter = array();
		if ($ownerId !== NULL)
		{
			$filter['ownerId'] = array('value' => $ownerId, 'comparison' => '=');
		}
		if ($ticketId !== NULL)
		{
			$filter['ticketId'] = array('value' => $ticketId, 'comparison' => '=');
		}
		if ($accountId !== NULL)
		{
			$filter['accountId'] = array('value' => $accountId, 'comparison' => '=');
		}
		if ($ticketingStatusId !== NULL)
		{
			$filter['ticketingStatusId'] = array('value' => $ticketingStatusId, 'comparison' => '=');
		}

		$detailsToRender = array();
		$detailsToRender['columns'] = $columns;
		$detailsToRender['sort'] = $sort;
		$detailsToRender['filter'] = $filter;
		$detailsToRender['offset'] = $offset;
		$detailsToRender['limit'] = $limit;
		$detailsToRender['tickets'] = Ticketing_Ticket::findMatching($columns, $sort, $filter, $offset, $limit);

		$this->LoadPage('ticketing_tickets', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	public function Ticket()
	{
		// WIP :: Kinda obvious ... this needs filling out a bit!

		// We need to load the details of the ticket specified by a ticket_id in $_REQUEST
		$detailsToRender = array();
		$detailsToRender['ticket'] = Ticketing_Ticket::getForId($_REQUEST['ticket_id']);

		$this->LoadPage('ticketing_ticket', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	public function Contact()
	{
		// We need to load the details of the contact specified by a contact_id in $_REQUEST
		$detailsToRender = array();
		$detailsToRender['ticket_id'] = array_key_exists('ticket_id', $_REQUEST) ? $_REQUEST['ticket_id'] : NULL;
		$detailsToRender['correspondance_id'] = array_key_exists('correspondance_id', $_REQUEST) ? $_REQUEST['correspondance_id'] : NULL;
		$detailsToRender['contact'] = Ticketing_Contact::getForId($_REQUEST['contact_id']);

		$this->LoadPage('ticketing_contact', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	public function Correspondance()
	{
		// WIP :: Kinda obvious ... this needs filling out a bit!
		// We need to load the details of the contact specified by a contact_id in $_REQUEST
		$detailsToRender = array();
		$detailsToRender['ticket_id'] = array_key_exists('ticket_id', $_REQUEST) ? $_REQUEST['ticket_id'] : NULL;
		$detailsToRender['correspondance'] = Ticketing_Correspondance::getForId($_REQUEST['correspondance_id']);

		$this->LoadPage('ticketing_correspondance', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	public function Attachment()
	{
		// WIP :: Kinda obvious ... this needs filling out a bit!
		$detailsToRender = array();
		$detailsToRender['ticket_id'] = array_key_exists('ticket_id', $_REQUEST) ? $_REQUEST['ticket_id'] : NULL;
		$detailsToRender['correspondance_id'] = array_key_exists('correspondance_id', $_REQUEST) ? $_REQUEST['correspondance_id'] : NULL;
		$detailsToRender['attachment'] = Ticketing_Attachment::getForId($_REQUEST['attachment_id']);

		$this->LoadPage('ticketing_attachment', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}
}

?>
