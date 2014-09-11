/*** Ticket History stuff ***/
$toggleBlock = null

function toggleAttachmentBlacklistOverride(button)
{
	button.disabled = true;
	var block = button.value == 'Block Download';
	var id = parseInt(button.getAttribute('attachmentId'));
	$toggleBlock(id, !block);
}

function toggleAttachmentBlacklistOverrideReturnHandler(response)
{
	var attachmentId	= response['id'];
	var blocked			= !response['allowOverride'];
	var button			= $ID('correspondence_attachment_toggle_' + attachmentId);
	var link			= $ID('correspondence_attachment_' + attachmentId);
	button.value		= blocked ? 'Unblock Download' : 'Block Download';
	link.className		= blocked ? 'blocked-downloadable-attachment' : 'downloadable-attachment';
	button.disabled		= false;
}

function onTicketingAttachmentListLoad()
{
	remoteClass = 'Ticketing';
	remoteMethod = 'changeAttachmentBlacklistOverride';
	$toggleBlock = jQuery.json.jsonFunction(toggleAttachmentBlacklistOverrideReturnHandler, null, remoteClass, remoteMethod);
}

Event.observe(window, 'load', onTicketingAttachmentListLoad, false);

function toggleShowHistoryRecordDetails(id, button)
{
	var elmDetails = $ID('ticket_history_record_details_'+ id);
	var elmSummary = $ID('ticket_history_record_'+ id);
	if (button.className == 'expand-button-expanded')
	{
		// Retract the details
		elmDetails.className	= elmDetails.getAttribute('altClass') +' ticket-history-details-row-hidden';
		elmSummary.className	= elmSummary.getAttribute('altClass') +' ticket-history-summary-row';
		button.className		= 'expand-button-retracted';
	}
	else
	{
		// Expand the details
		elmDetails.className	= elmDetails.getAttribute('altClass') +' ticket-history-details-row-displayed';
		elmSummary.className	= elmSummary.getAttribute('altClass') +' ticket-history-summary-row-displaying-detail-row';
		button.className		= 'expand-button-expanded';
	}
}

function toggleShowAllHistoryRecordDetails(button)
{
	var elmTable = $ID('ticket_history_table');
	var arrContentRows;
	var arrSummaryRows;
	var arrButtons;
	var strNewClassForContentRows;
	var strNewClassForSummaryRows;
	var strNewClassForButtons;
	var i, j;
	
	if (button.className == 'expand-button-expanded')
	{
		// Retract all rows that are currently expanded
		arrContentRows				= elmTable.getElementsByClassName('ticket-history-details-row-displayed');
		arrSummaryRows				= elmTable.getElementsByClassName('ticket-history-summary-row-displaying-detail-row');
		arrButtons					= elmTable.getElementsByClassName('expand-button-expanded');
		strNewClassForContentRows   = 'ticket-history-details-row-hidden';
		strNewClassForSummaryRows   = 'ticket-history-summary-row';
		strNewClassForButtons		= 'expand-button-retracted';
	}
	else
	{
		// Expand all rows that are currently retracted
		arrContentRows				= elmTable.getElementsByClassName('ticket-history-details-row-hidden');
		arrSummaryRows				= elmTable.getElementsByClassName('ticket-history-summary-row');
		arrButtons					= elmTable.getElementsByClassName('expand-button-retracted');
		strNewClassForContentRows   = 'ticket-history-details-row-displayed';
		strNewClassForSummaryRows   = 'ticket-history-summary-row-displaying-detail-row';
		strNewClassForButtons		= 'expand-button-expanded';
	}
	
	while (arrContentRows.length > 0)
	{
		arrContentRows[0].className = arrContentRows[0].getAttribute('altClass') +' '+ strNewClassForContentRows;
	}

	while (arrSummaryRows.length > 0)
	{
		arrSummaryRows[0].className = arrSummaryRows[0].getAttribute('altClass') +' '+ strNewClassForSummaryRows;
	}
	
	while (arrButtons.length > 0)
	{
		arrButtons[0].className = strNewClassForButtons;
	}
	
}
