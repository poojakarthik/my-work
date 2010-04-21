<?php
//---------------------------------------------------------------------------//
// Ticketing User Activity Summary (RR009)
//---------------------------------------------------------------------------//

$arrDocReq		= array();
$arrSQLSelect	= array();
$arrSQLFields	= array();

$arrDataReport['Name']			= "Ticketing User Activity Summary";
$arrDataReport['Summary']		= "Ticketing activity statistics for each active user of the ticketing system";
$arrDataReport['RenderMode']	= REPORT_RENDER_INSTANT;
$arrDataReport['Priviledges']	= 8192;											// Proper Admin
//$arrDataReport['Priviledges']	= 1;											// Live
$arrDataReport['CreatedOn']		= date("Y-m-d");
$arrDataReport['SQLTable']		= "
		Employee
		INNER JOIN ticketing_user ON Employee.Id = ticketing_user.employee_id
		LEFT JOIN 
		(	/* tickets_created_by_user */

			SELECT		tth.modified_by_user_id AS user_id, 
						COUNT(tth.id) AS total				/* Total number of tickets created by the user */
			FROM		ticketing_ticket_history AS tth 
						INNER JOIN
						(	/* first_ticket_history_record_id */

							SELECT		ticket_id, MIN(id) AS id
							FROM		ticketing_ticket_history
							GROUP BY	ticket_id

						) AS first_ticket_history_record_id ON tth.id = first_ticket_history_record_id.id
			WHERE		tth.modified_by_user_id IS NOT NULL 
						AND DATE(tth.modified_datetime) BETWEEN <EarliestActionDate> AND <LatestActionDate> /* The ticket must have been created during the timeframe */
			GROUP BY	tth.modified_by_user_id

		) AS tickets_created_by_user ON ticketing_user.id = tickets_created_by_user.user_id
		LEFT JOIN
		(	/* ticket_assignment_actions_made_to_user */

			SELECT		ticket_state.owner_id		AS user_id, 
						COUNT(ticket_state.id)		AS total	/* Total number of times the ticket assignment action has been made to the user */
			FROM		(	/* find all instances of a ticket being changed during the timeframe considered */

							SELECT	tth.id					AS id,
									MAX(tth_previous.id)	AS previous_id
							FROM	ticketing_ticket_history AS tth 
									LEFT JOIN ticketing_ticket_history AS tth_previous ON tth.ticket_id = tth_previous.ticket_id AND tth.id > tth_previous.id
							WHERE	DATE(tth.modified_datetime) BETWEEN <EarliestActionDate> AND <LatestActionDate> /* The assignment action must have been made during the timeframe */
							GROUP BY tth.id

						) AS history_joiner 
						INNER JOIN	ticketing_ticket_history AS ticket_state ON history_joiner.id = ticket_state.id 
						LEFT JOIN	ticketing_ticket_history AS ticket_previous_state ON history_joiner.previous_id = ticket_previous_state.id
			WHERE		NOT (ticket_state.owner_id <=> ticket_previous_state.owner_id) /* The owner of the ticket has been changed (NULL safe) (this should also handle users being assigned a ticket, when the ticket is created) */
			GROUP BY	ticket_state.owner_id

		) AS ticket_assignment_actions_made_to_user ON ticketing_user.id = ticket_assignment_actions_made_to_user.user_id
		LEFT JOIN
		(	/* correspondance_items_created_by_user */

			SELECT		user_id, 
						COUNT(id) AS total /* Total number of correspondance items created by the user */
			FROM		ticketing_correspondance
			WHERE		DATE(creation_datetime) BETWEEN <EarliestActionDate> AND <LatestActionDate> /* The correspondence must have been created during the timeframe */
			GROUP BY	user_id

		) AS correspondance_items_created_by_user ON ticketing_user.id = correspondance_items_created_by_user.user_id
		LEFT JOIN
		(	/* ticket_completion_actions_made_by_user */

			SELECT		ticket_state.modified_by_user_id	AS user_id,
						COUNT(ticket_state.id)				AS total	/* Total number of times the user has set a ticket to COMPLETED */
			FROM		(	/* find all instances of a ticket being changed during the timeframe considered */

							SELECT	tth.id					AS id,
									MAX(tth_previous.id)	AS previous_id
							FROM	ticketing_ticket_history AS tth 
									LEFT JOIN ticketing_ticket_history AS tth_previous ON tth.ticket_id = tth_previous.ticket_id AND tth.id > tth_previous.id
							WHERE 	DATE(tth.modified_datetime) BETWEEN <EarliestActionDate> AND <LatestActionDate> /* The change must have been made during the timeframe */
							GROUP BY tth.id

						) AS history_joiner
						INNER JOIN	ticketing_ticket_history AS ticket_state ON history_joiner.id = ticket_state.id 
						LEFT JOIN	ticketing_ticket_history AS ticket_previous_state ON history_joiner.previous_id = ticket_previous_state.id
			WHERE		NOT (ticket_state.status_id <=> ticket_previous_state.status_id) /* The status of the ticket must have changed (NULL safe) (handles a ticket being set to complete when it is created) */
						AND ticket_state.status_id = (SELECT id FROM ticketing_status WHERE const_name = 'TICKETING_STATUS_COMPLETED') /* The status must have changed to COMPLETED */
			GROUP BY	ticket_state.modified_by_user_id

		) AS ticket_completion_actions_made_by_user ON ticketing_user.id = ticket_completion_actions_made_by_user.user_id
";
$arrDataReport['SQLWhere']		= "	ticketing_user.permission_id != (SELECT id FROM ticketing_user_permission WHERE const_name = 'TICKETING_USER_PERMISSION_NONE') /* Must have permission to use ticketing system */
									"/*AND Employee.Archived = 0 *//* Only active ticketing users */."

									ORDER BY User ASC";
$arrDataReport['SQLGroupBy']	= "";


// Documentation Reqs
$arrDocReq[]	= "DataReport";
$arrDataReport['Documentation']	= serialize($arrDocReq);

// SQL Select
$arrSQLSelect['User']									['Value']	= "CONCAT(Employee.FirstName, ' ', Employee.LastName)";

$arrSQLSelect['Tickets Created']						['Value']	= "COALESCE(tickets_created_by_user.total, 0)";
$arrSQLSelect['Tickets Created']						['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Ticket Assignment Actions Made to User']	['Value']	= "COALESCE(ticket_assignment_actions_made_to_user.total, 0)";
$arrSQLSelect['Ticket Assignment Actions Made to User']	['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Correspondence Items Created']			['Value']	= "COALESCE(correspondance_items_created_by_user.total, 0)";
$arrSQLSelect['Correspondence Items Created']			['Type']	= EXCEL_TYPE_INTEGER;

$arrSQLSelect['Ticket Completion Actions Made by User']	['Value']	= "COALESCE(ticket_completion_actions_made_by_user.total, 0)";
$arrSQLSelect['Ticket Completion Actions Made by User']	['Type']	= EXCEL_TYPE_INTEGER;

$arrDataReport['SQLSelect'] = serialize($arrSQLSelect);

$arrSQLFields['EarliestActionDate']	= Array(
											'Type'					=> "dataDate",
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Earliest Action Date",
											);
$arrSQLFields['LatestActionDate']	= Array(
											'Type'					=> "dataDate",
											'Documentation-Entity'	=> "DataReport",
											'Documentation-Field'	=> "Latest Action Date",
											);

// SQL Fields
$arrDataReport['SQLFields'] = serialize($arrSQLFields);


?>