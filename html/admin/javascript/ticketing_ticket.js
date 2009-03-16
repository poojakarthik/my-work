/* Blah */
$validateAccounts = null;
$selectedContactValue = null;

objTicketCategoryNotesRows = {};
elmCategory = null;

function accountNumberChange()
{
	var accountId = $ID('accountId');
	var ticketId = $ID('ticketId');
	var serviceId = $ID('serviceId');

	if (accountId.value.length != 10) // WIP: HACK (will only work for Telco blue)
	{
		accountId.className = 'invalid';
		return;
	}
	if (accountId.lastAjax == accountId.value)
	{
		accountId.className = '';
		return;
	}
	accountId.lastAjax = accountId.value;
	accountId.className = '';
	$validateAccounts(accountId.value, ticketId.value);
}

function validatedAccount(response)
{
	var accountId = $ID('accountId');
	if (!response['isValid'])
	{
		accountId.className = 'invalid';
	}
	else
	{
		accountId.className = 'valid';
	}
	populateContactList(response['contacts']);
	populateServiceList(response['services']);
	setCustomerGroup(response['customerGroupName']);
	populateCustomerGroupEmailList(response['customerGroupEmails']);
}

function populateContactList(contacts)
{
	var contactId = $ID('contactId');
	emptyElement(contactId);
	for (var i = 0, l = contacts.length; i < l; i++)
	{
		var id = contacts[i]['id'];
		var name = contacts[i]['name'];
		var option = document.createElement('option');
		option.value = id;
		option.selected = id == $selectedContactValue;
		option.appendChild(document.createTextNode(name));
		contactId.appendChild(option);
	}
	
	contactId.className = '';
}

function setCustomerGroup(strCustomerGroupName)
{
	var customerGroup = $ID('customerGroupName');
	while (customerGroup.firstChild)
	{
		customerGroup.removeChild(customerGroup.firstChild)
	}
	customerGroup.appendChild(document.createTextNode(strCustomerGroupName));
}

function populateServiceList(services)
{
	var serviceId = $ID('serviceId');
	emptyElement(serviceId);
	for (var i = 0, l = services.length; i < l; i++)
	{
		var id = services[i]['service_id'];
		var name = services[i]['fnn'] +" ("+ services[i]['status_description'] +")";
		var option = document.createElement('option');
		option.value = id;
		option.selected = services[i]['selected'];
		option.appendChild(document.createTextNode(name));
		serviceId.appendChild(option);
	}
}

function populateCustomerGroupEmailList(customerGroupEmails)
{
	var customerGroupEmailId = $ID('customerGroupEmailId');
	if (customerGroupEmailId != undefined)
	{
		emptyElement(customerGroupEmailId);
		for (var i = 0, l = customerGroupEmails.length; i < l; i++)
		{
			var id = customerGroupEmails[i].id;
			var name = customerGroupEmails[i].name;
			var option = document.createElement('option');
			option.value = id;
			option.selected = customerGroupEmails[i].isDefault;
			option.appendChild(document.createTextNode(name));
			customerGroupEmailId.appendChild(option);
		}
	}
}

function emptyElement(el)
{
	for (var i = el.length - 1; i >= 0; i--)
	{
		el.removeChild(el.childNodes[i]);
	}
}

function viewContactDetails()
{
	var accountIdInput = $ID('accountId');
	var accountId = null;
	if (accountIdInput.className != 'invalid')
	{
		accountId = accountIdInput.value;
	}

	var contactIdInput = $ID('contactId');
	var contactId = null;
	if (contactIdInput.tagName == 'SELECT')
	{
		var selectedIndex = contactIdInput.selectedIndex;
		if (selectedIndex >= 0 && selectedIndex < contactIdInput.childNodes.length)
		{
			contactId = contactIdInput.childNodes[selectedIndex].value;
		}
	}
	else
	{
		contactId = contactIdInput.value;
	}

	if (contactId == null)
	{
		alert('Please select a contact.');
		return false;
	}

	Ticketing_Contact.displayContact(contactId, accountId);
	return false;
}

function addContactCallBack(newContact)
{
	$selectedContactValue = newContact['contactId'];
	$ID('accountId').lastAjax = null;
	$updateContacts($ID('accountId').value, $ID('ticketId').value);
}

function updatedContacts(response)
{
	populateContactList(response['contacts']);
}

function addContact()
{
	var accountIdInput = $ID('accountId');
	if (accountIdInput.className == 'invalid' || accountIdInput.value == '')
	{
		alert('Please enter a valid account number.');
		return false;
	}
	var accountId = accountIdInput.value;
	Ticketing_Contact.displayContact(null, accountId, addContactCallBack);
	return false;
}

function categoryChange()
{
	if (elmCategory.value == elmCategory.lastCategoryId)
	{
		// The category hasn't changed
		return;
	}
	
	// The category has changed.  Hide all the category notes rows
	for (var i in objTicketCategoryNotesRows)
	{
		objTicketCategoryNotesRows[i].style.display = 'none';
	}
	
	// Show the notes specific to this category, if there is one
	if (objTicketCategoryNotesRows[elmCategory.value] != undefined)
	{
		objTicketCategoryNotesRows[i].style.display = 'table-row';
	}
	
	elmCategory.lastCategoryId = elmCategory.value;
}	

function revertTicketDetails(ticketId)
{
	var remoteFunction = jQuery.json.jsonFunction(revertTicketDetailsReturnHandler, null, 'Ticketing', 'getTicketDetails');
	remoteFunction(ticketId);
}

function revertTicketDetailsReturnHandler(response)
{
	var accountId = $ID('accountId');
	if (!response['isValid'])
	{
		accountId.className = 'invalid';
	}
	else
	{
		accountId.className = '';
	}
	accountId.value = response['accountId'];
	accountId.lastAjax = accountId.value;
	$selectedContactValue = response['selectedContactId'];
	
	populateContactList(response['contacts']);
	populateServiceList(response['services']);
	setCustomerGroup(response['customerGroupName']);
}


function onTicketingLoad()
{
	var ticket		= $ID('ticketId');
	
	if (!ticket)
	{
		// The page has been rendered in a static mode
		return;
	}
	
	var ticketId	= ticket.value;
	var accountId	= $ID('accountId');

	if (ticketId == '')
	{
		// This is a new ticket, will include the form elements for the initial correspondence
		var arrSpecificNotes = document.getElementsByClassName('TicketCategoryNotes');
		for (var i=0,j=arrSpecificNotes.length; i<j; i++)
		{
			objTicketCategoryNotesRows[arrSpecificNotes[i].id] = arrSpecificNotes[i];
		}
		
		if (arrSpecificNotes.length > 0)
		{
			// Add listeners
			elmCategory = $ID('categoryId');
			elmCategory.lastCategoryId = null;
			Event.observe(elmCategory, 'blur', categoryChange);
			Event.observe(elmCategory, 'keyup', categoryChange);
			Event.observe(elmCategory, 'change', categoryChange);
		}
	}

	if (accountId == undefined || accountId == null)
	{
		return;
	}
	
	accountId.lastAjax = accountId.value;

	remoteClass = 'Ticketing';
	remoteMethod = 'validateAccount';
	$validateAccounts = jQuery.json.jsonFunction(validatedAccount, null, remoteClass, remoteMethod);
	$updateContacts = jQuery.json.jsonFunction(updatedContacts, null, remoteClass, remoteMethod);

	Event.observe(accountId, 'blur', accountNumberChange);
	Event.observe(accountId, 'keyup', accountNumberChange);
	Event.observe(accountId, 'change', accountNumberChange);
}

Event.observe(window, 'load', onTicketingLoad, false);
