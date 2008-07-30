<?php

class Application_Handler_Ticketing extends Application_Handler
{
	// Handle a request for the home page of the ticketing system
	public function System($subPath)
	{
		return $this->Tickets($subPath, FALSE);
	}

	// Handle a request for the home page of the ticketing system
	public function Tickets($subPath, $bolOwnTickets=TRUE)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			Application()->InsufficientPrivilegeDie();
		}

		// We need to load all of the tickets matching any passed criteria (in $_POST)
		// Criteria include columns (array(field)), sorting (array(field=>direction)), offset (int, default=0) and limit (int, default=30) as well as:
		// owner_id (=user_id), ticket_id, account_id, ticketing_status_id

		$defaultLimit = 24;

		if (array_key_exists('last', $_REQUEST) && array_key_exists('ticketing', $_SESSION) && array_key_exists('lastTicketList', $_SESSION['ticketing']))
		{
			$lastQuery = unserialize($_SESSION['ticketing']['lastTicketList']);
			$sort = $lastQuery['sort'];
			$columns = $lastQuery['columns'];
			$filter = $lastQuery['filter'];
			$limit = $lastQuery['limit'];
			$offset = $lastQuery['offset'];
			$bolOwnTickets = $lastQuery['own_tickets'];

			if (array_key_exists('offset', $_REQUEST))
			{
				$offset = max(intval($_REQUEST['offset']), 0);
			}
			if (array_key_exists('limit', $_REQUEST))
			{
				$limit = intval($_REQUEST['limit']); 
			}
			if ($limit <= 0)
			{
				$limit = $defaultLimit;
			}
		}
		else
		{
			$sort = array();
			if (array_key_exists('sort', $_REQUEST))
			{
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
			}
	
			$columns = array();
			if (array_key_exists('columns', $_REQUEST))
			{
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
			}
	
			$offset = max(array_key_exists('offset', $_REQUEST) ? intval($_REQUEST['offset']) : 0, 0);
			$limit = array_key_exists('limit', $_REQUEST) ? intval($_REQUEST['limit']) : $defaultLimit;
			if ($limit <= 0)
			{
				$limit = $defaultLimit;
			}
	
			$ownerId = array_key_exists('ownerId', $_REQUEST) ? (strlen($_REQUEST['ownerId']) ? intval($_REQUEST['ownerId']) : NULL) : NULL;
			$statusId = array_key_exists('statusId', $_REQUEST) ? (strlen($_REQUEST['statusId']) ? intval($_REQUEST['statusId']) : NULL) : NULL;
			$categoryId = array_key_exists('categoryId', $_REQUEST) ? (strlen($_REQUEST['categoryId']) ? intval($_REQUEST['categoryId']) : NULL) : NULL;

			// If viewing own tickets, enforce owner id to be the id of the currently logged in user
			if ($bolOwnTickets)
			{
				$user = Ticketing_User::getCurrentUser();
				$ownerId = $user->id;
			}

			$filter = array();
			if ($ownerId !== NULL)
			{
				$filter['ownerId'] = array('value' => $ownerId, 'comparison' => '=');
			}

			if ($statusId !== NULL)
			{
				$filter['statusId'] = array('value' => $statusId, 'comparison' => '=');
			}

			if ($categoryId !== NULL)
			{
				$filter['categoryId'] = array('value' => $categoryId, 'comparison' => '=');
			}
		}

		$detailsToRender = array();
		$detailsToRender['own_tickets'] = $bolOwnTickets;
		$detailsToRender['columns'] = $columns;
		$detailsToRender['sort'] = $sort;
		$detailsToRender['filter'] = $filter;

		$detailsToRender['ticket_count'] = Ticketing_Ticket::countMatching($filter);

		if ($detailsToRender['ticket_count'] <= $offset)
		{
			$offset = $detailsToRender['ticket_count'] - ($detailsToRender['ticket_count'] % $limit);
		}

		$detailsToRender['offset'] = $offset;
		$detailsToRender['limit'] = $limit;
		$_SESSION['ticketing']['lastTicketList'] = serialize($detailsToRender);

		$detailsToRender['tickets'] = Ticketing_Ticket::findMatching($columns, $sort, $filter, $offset, $limit);
		$detailsToRender['users'] = Ticketing_User::listAll();
		$detailsToRender['statuses'] = Ticketing_Status::listAll();
		$detailsToRender['categories'] = Ticketing_Category::listAll();

		$this->LoadPage('ticketing_tickets', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	public function Ticket($subPath)
	{
		$currentUser = Ticketing_User::getCurrentUser();
		if (!$currentUser->isUser())
		{
			AuthenticatedUser()->InsufficientPrivilegeDie();
		}

		$permittedActions = $currentUser->isAdminUser() 
			? array('view', 'edit', 'delete', 'reassign', 'assign', 'take', 'create') 
			: array('view', 'edit', 'delete', 'take', 'create');

		// WIP :: Kinda obvious ... this needs filling out a bit!
		$action = count($subPath) ? strtolower(array_shift($subPath)) : 'view';
		if (!array_search($action, $permittedActions))
		{
			$action = 'view';
		}

		

		switch ($action)
		{
			case 'delete':
				$ticket->delete();
				break;

			case 'view':
			default:
		}

		// We need to load the details of the ticket specified by a ticket_id in $_REQUEST
		$detailsToRender = array();
		$detailsToRender['action'] = $action;
		$detailsToRender['ticket'] = Ticketing_Ticket::getForId($_REQUEST['ticketId']);
		$detailsToRender['permitted_actions'] = $permittedActions;

		$this->LoadPage('ticketing_ticket', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	public function Contact($subPath)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			Application()->InsufficientPrivilegeDie();
		}

		// We need to load the details of the contact specified by a contact_id in $_REQUEST
		$detailsToRender = array();
		$detailsToRender['ticketId'] = array_key_exists('ticketId', $_REQUEST) ? $_REQUEST['ticketId'] : NULL;
		$detailsToRender['correspondance_id'] = array_key_exists('correspondanceId', $_REQUEST) ? $_REQUEST['correspondanceId'] : NULL;
		$detailsToRender['contact'] = Ticketing_Contact::getForId($_REQUEST['contactId']);

		$this->LoadPage('ticketing_contact', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	public function Correspondance($subPath)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			Application()->InsufficientPrivilegeDie();
		}

		// WIP :: Kinda obvious ... this needs filling out a bit!
		// We need to load the details of the contact specified by a contact_id in $_REQUEST
		$detailsToRender = array();
		$detailsToRender['ticketId'] = array_key_exists('ticketId', $_REQUEST) ? $_REQUEST['ticketId'] : NULL;
		$detailsToRender['correspondance'] = Ticketing_Correspondance::getForId($_REQUEST['correspondanceId']);

		$this->LoadPage('ticketing_correspondance', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	public function Attachment($subPath)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			Application()->InsufficientPrivilegeDie();
		}

		// WIP :: Kinda obvious ... this needs filling out a bit!
		$detailsToRender = array();
		$detailsToRender['ticketId'] = array_key_exists('ticketId', $_REQUEST) ? $_REQUEST['ticketId'] : NULL;
		$detailsToRender['correspondanceId'] = array_key_exists('correspondanceId', $_REQUEST) ? $_REQUEST['correspondanceId'] : NULL;
		$detailsToRender['attachment'] = Ticketing_Attachment::getForId($_REQUEST['attachmentId']);

		$this->LoadPage('ticketing_attachment', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}
}

?>
