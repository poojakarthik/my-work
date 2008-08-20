
var Ticketing_Contact = Class.create();

Object.extend(Ticketing_Contact, {

	callback: null,

	currentDisplay: null,

	onGetDetails: function (details)
	{
		Ticketing_Contact.callback(details);
	},

	onSetDetails: function (details)
	{
		Ticketing_Contact.callback(details);
	},

	getContactDetails: function (callback, contactId)
	{
		Ticketing_Contact.callback = callback;
		Ticketing_Contact.remoteGetDetails(contactId);
	},

	setContactDetails: function (callback, contactId, title, firstName, lastName, jobTitle, email, fax, mobile, phone, accountId)
	{
		Ticketing_Contact.callback = callback;
		Ticketing_Contact.remoteSetDetails(contactId, title, firstName, lastName, jobTitle, email, fax, mobile, phone, accountId);
	},

	displayContact: function(contactId, accountId, callback)
	{
		Ticketing_Contact.hideContact();
		Ticketing_Contact.currentDisplay = new Ticketing_Contact(contactId, accountId, callback);
	},

	hideContact: function()
	{
		if (Ticketing_Contact.currentDisplay != null)
		{
			Ticketing_Contact.currentDisplay.destroy();
			Ticketing_Contact.currentDisplay = null;
		}
	}
});


Object.extend(Ticketing_Contact, {
	remoteGetDetails: jQuery.json.jsonFunction(Ticketing_Contact.onGetDetails, null, 'Ticketing', 'getContactDetails'),

	remoteSetDetails: jQuery.json.jsonFunction(Ticketing_Contact.onSetDetails, null, 'Ticketing', 'setContactDetails'),
});

Object.extend(Ticketing_Contact.prototype, {

	popup: null,
	accountId: null,
	contactId: null,
	callback: null,
	details: null,
	inputs: null,

	currentPaneIsView: false,

	initialize: function(contactId, accountId, createdCallback)
	{
		this.callback = createdCallback;
		this.accountId = (accountId == undefined) ? null : accountId;
		this.contactId = (contactId == undefined) ? null : contactId;

		if (this.accountId == null && this.contactId == null)
		{
			throw new Exception('Both account and contact Ids are null.');
		}

		var loading = document.createElement('div');
		loading.style.height = '20em';

		this.popup = new Reflex_Popup(50);

		if (this.contactId != null)
		{
			this.popup.setContent(loading);
			Ticketing_Contact.getContactDetails(this.displayDetails.bind(this), this.contactId);
			this.currentPaneIsView = true;
		}
		else
		{
			var details = { title: null, firstName: null, lastName: null, jobTitle: null, email: null, fax: null, mobile: null, phone: null };
			this.displayDetails(details);
		}
		this.popup.addCloseButton(this.destroy.bind(this));

		this.popup.display();
	},

	togglePanes: function()
	{
		if (this.currentPaneIsView)
		{
			this.populateEditPane();
		}
		else
		{
			this.populateViewPane();
		}
		this.currentPaneIsView = !this.currentPaneIsView;
	},

	destroyEditPane: function()
	{
		this.editPane.innerHTML = '';
	},

	populateEditPane: function()
	{
		var table = document.createElement('table');
		table.className = 'reflex';

		var tr = null, td = null, input = null, button = null;
		this.inputs = {};

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Title:'));
		this.inputs.title = input = document.createElement('input');input.type = 'text'; input.name='title'; input.value=this.details['title'];
		td = tr.insertCell(-1);td.appendChild(input);

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('First Name:'));
		this.inputs.firstName = input = document.createElement('input');input.type = 'text'; input.name='firstName'; input.value=this.details['firstName'];
		td = tr.insertCell(-1);td.appendChild(input);

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Last Name:'));
		this.inputs.lastName = input = document.createElement('input');input.type = 'text'; input.name='lastName'; input.value=this.details['lastName'];
		td = tr.insertCell(-1);td.appendChild(input);

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Job Title:'));
		this.inputs.jobTitle = input = document.createElement('input');input.type = 'text'; input.name='jobTitle'; input.value=this.details['jobTitle'];
		td = tr.insertCell(-1);td.appendChild(input);

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Email:'));
		this.inputs.email = input = document.createElement('input');input.type = 'text'; input.name='email'; input.value=this.details['email'];
		td = tr.insertCell(-1);td.appendChild(input);

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Fax:'));
		this.inputs.fax = input = document.createElement('input');input.type = 'text'; input.name='fax'; input.value=this.details['fax'];
		td = tr.insertCell(-1);td.appendChild(input);

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Mobile:'));
		this.inputs.mobile = input = document.createElement('input');input.type = 'text'; input.name='mobile'; input.value=this.details['mobile'];
		td = tr.insertCell(-1);td.appendChild(input);

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Phone:'));
		this.inputs.phone = input = document.createElement('input');input.type = 'text'; input.name='phone'; input.value=this.details['phone'];
		td = tr.insertCell(-1);td.appendChild(input);

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);
		td.colSpan = 2;

		buttons = [];

		buttons[0] = button = document.createElement('input');
		button.type = 'button';
		button.className = 'reflex-button';
		button.value = button.name = 'Cancel';

		if (this.contactId == null) 
		{
			Event.observe(button, 'click', this.destroy.bind(this));
		}
		else 
		{
			Event.observe(button, 'click', this.togglePanes.bind(this));
		}

		buttons[1] = button = document.createElement('input');
		button.type = 'button';
		button.className = 'reflex-button';
		button.value = button.name = 'Save';
		Event.observe(button, 'click', this.submitDetails.bind(this));

		this.popup.setTitle(this.contactId == null ? 'Add New Contact' : 'Edit Contact Details');
		this.popup.setContent(table);
		this.popup.setFooterButtons(buttons);
	},

	populateViewPane: function()
	{

		var table = document.createElement('table');
		table.className = 'reflex';

		var tr = null, td = null;

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Title:'));
		td = tr.insertCell(-1);td.appendChild(document.createTextNode(this.details['title']));

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('First Name:'));
		td = tr.insertCell(-1);td.appendChild(document.createTextNode(this.details['firstName']));

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Last Name:'));
		td = tr.insertCell(-1);td.appendChild(document.createTextNode(this.details['lastName']));

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Job Title:'));
		td = tr.insertCell(-1);td.appendChild(document.createTextNode(this.details['jobTitle']));

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Email:'));
		td = tr.insertCell(-1);td.appendChild(document.createTextNode(this.details['email']));

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Fax:'));
		td = tr.insertCell(-1);td.appendChild(document.createTextNode(this.details['fax']));

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Mobile:'));
		td = tr.insertCell(-1);td.appendChild(document.createTextNode(this.details['mobile']));

		tr = table.insertRow(-1);
		td = tr.insertCell(-1);td.className = 'title';td.appendChild(document.createTextNode('Phone:'));
		td = tr.insertCell(-1);td.appendChild(document.createTextNode(this.details['phone']));


		var buttons = [];

		buttons[0] = button = document.createElement('input');
		button.type = 'button';
		button.className = 'reflex-button';
		button.value = button.name = 'Cancel';
		Event.observe(button, 'click', this.destroy.bind(this));

		if (this.accountId != null)
		{
			buttons[1] = button = document.createElement('input');
			button.type = 'button';
			button.className = 'reflex-button';
			button.value = button.name = 'Edit';
			Event.observe(button, 'click', this.togglePanes.bind(this));
		}

		this.popup.setTitle('View Contact Details');
		this.popup.setContent(table);
		this.popup.setFooterButtons(buttons);
	},

	submitDetails: function()
	{
		if (this.accountId == null) return;

		this.inputs.firstName.className = this.inputs.firstName.value == '' ? 'invalid' : '';
		this.inputs.lastName.className = this.inputs.lastName.value == '' ? 'invalid' : '';
		this.inputs.email.className = this.inputs.email.value == '' ? 'invalid' : '';

		if (this.inputs.firstName.className == 'invalid' &&
			this.inputs.lastName.className == 'invalid' && 
			this.inputs.email.className == 'invalid') 
		{
			return $Alert('Please complete at least one of the highlighted fields and ensure that all entries are valid.');
		}

		Ticketing_Contact.setContactDetails(this.displaySavedDetails.bind(this),
			this.contactId,
			this.inputs.title.value, 
			this.inputs.firstName.value, 
			this.inputs.lastName.value, 
			this.inputs.jobTitle.value,
			this.inputs.email.value, 
			this.inputs.fax.value, 
			this.inputs.mobile.value, 
			this.inputs.phone.value, 
			this.accountId
		);
	},

	displaySavedDetails: function(details)
	{
		if (details == 'INVALID')
		{
			this.inputs.firstName.className = 'invalid';
			this.inputs.lastName.className = 'invalid';
			this.inputs.email.className = 'invalid';
			return $Alert('Please complete at least one of the highlighted fields and ensure that all entries are valid.');
		}
		var $new = this.contactId == null;
		this.contactId = details['contactId'];
		if (this.callback != undefined && typeof this.callback == 'function') this.callback(details);
		if ($new) this.destroy();
		else this.displayDetails(details);
	},

	displayDetails: function(details)
	{
		this.details = details;
		if (this.contactId == null)
		{
			this.populateEditPane();
		}
		else
		{
			this.populateViewPane();
		}
	},

	destroy: function()
	{
		this.callback = null;
		this.popup.hide();
	}

});
