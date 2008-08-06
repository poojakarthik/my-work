var Ticketing_Contact = Class.create();
Object.extend(Ticketing_Contact.prototype, {

	container: null,
	viewPane: null,
	editPane: null,
	accountId: null,
	contactId: null,
	editPanePopulated: false,
	viewPanePopulated: false,
	currentPaneIsView: null,
	callback: null,
	details: null,

	initialize: function(contactId, accountId, createdCallback)
	{
		this.callback = createdCallback;
		this.accountId = (accountId == undefined) ? null : accountId;
		this.contactId = (contactId == undefined) ? null : contactId;

		if (this.accountId == null && this.contactId == null)
		{
			throw new Exception('Both account and contact Ids are null.');
		}

		this.container = document.createElement('div');

		this.viewPane = document.createElement('div');
		this.container.appendChild(this.viewPane);
		this.currentPaneIsView = true;

		this.editPane = document.createElement('div');
		this.container.appendChild(this.editPane);

		if (this.contactId != null)
		{
			this.currentPaneIsView = false;
			Ticketing_Contact.getContactDetails(this.displayDetails.bind(this), this.contactId);
		}
		else
		{
			var details = { title: null, firstName: null, lastName: null, jobTitle: null, email: null, fax: null, mobile: null, phone: null };
			this.displayDetails(details);
		}
	},

	togglePanes: function()
	{
		if (this.currentPaneIsView)
		{
			this.populateEditPane();
			this.editPane.style.display = 'block';
			this.viewPane.style.display = 'none';
		}
		else
		{
			this.populateViewPane();
			this.viewPane.style.display = 'block';
			this.editPane.style.display = 'none';
			this.destroyEditPane();
		}
		this.currentPaneIsView = !this.currentPaneIsView;
	},

	destroyEditPane: function()
	{
		this.editPane.innerHTML = '';
		this.editPanePopulated = false;
	},

	populateEditPane: function()
	{
		this.editPanePopulated = true;
		var table = document.createElement('table');
		var tr = null, td = null, input = null, button = null;

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Title:'));
		input = document.createElement('input');input.type = 'text'; input.name='title'; input.value=this.details['title'];
		td = tr.addCell();td.appendChild(input);

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('First Name:'));
		input = document.createElement('input');input.type = 'text'; input.name='firstName'; input.value=this.details['firstName'];
		td = tr.addCell();td.appendChild(input);

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Last Name:'));
		input = document.createElement('input');input.type = 'text'; input.name='lastName'; input.value=this.details['lastName'];
		td = tr.addCell();td.appendChild(input);

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Job Title:'));
		input = document.createElement('input');input.type = 'text'; input.name='jobTitle'; input.value=this.details['jobTitle'];
		td = tr.addCell();td.appendChild(input);

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Email:'));
		input = document.createElement('input');input.type = 'text'; input.name='email'; input.value=this.details['email'];
		td = tr.addCell();td.appendChild(input);

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Fax:'));
		input = document.createElement('input');input.type = 'text'; input.name='fax'; input.value=this.details['fax'];
		td = tr.addCell();td.appendChild(input);

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Mobile:'));
		input = document.createElement('input');input.type = 'text'; input.name='mobile'; input.value=this.details['mobile'];
		td = tr.addCell();td.appendChild(input);

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Phone:'));
		input = document.createElement('input');input.type = 'text'; input.name='phone'; input.value=this.details['phone'];
		td = tr.addCell();td.appendChild(input);

		tr = table.addRow();
		td = tr.addCell();
		td.colspan = 2;

		button = document.createElement('input');
		button.type = 'button';
		button.className = 'reflex-button';
		button.value = button.name = 'Cancel';
		td.appendChild(button);

		if (this.contactId == null) 
		{
			Event.observe('click', button, this.destroy.bind(this));
		}
		else 
		{
			Event.observe('click', button, this.togglePanes.bind(this));
		}

		button = document.createElement('input');
		button.type = 'button';
		button.className = 'reflex-button';
		button.value = button.name = 'Save';
		td.appendChild(button);
		Event.observe('click', button, this.submitDetails.bind(this));

		this.editPane.appendChild(table);
	},

	populateViewPane: function()
	{
		this.viewPanePopulated = true;
		var table = document.createElement('table');
		var tr = null, td = null;

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Title:'));
		td = tr.addCell();td.appendChild(document.createTextNode(this.details['title']));

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('First Name:'));
		td = tr.addCell();td.appendChild(document.createTextNode(this.details['firstName']));

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Last Name:'));
		td = tr.addCell();td.appendChild(document.createTextNode(this.details['lastName']));

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Job Title:'));
		td = tr.addCell();td.appendChild(document.createTextNode(this.details['jobTitle']));

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Email:'));
		td = tr.addCell();td.appendChild(document.createTextNode(this.details['email']));

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Fax:'));
		td = tr.addCell();td.appendChild(document.createTextNode(this.details['fax']));

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Mobile:'));
		td = tr.addCell();td.appendChild(document.createTextNode(this.details['mobile']));

		tr = table.addRow();
		td = tr.addCell();td.className = 'title';td.appendChild(document.createTextNode('Phone:'));
		td = tr.addCell();td.appendChild(document.createTextNode(this.details['phone']));

		button = document.createElement('input');
		button.type = 'button';
		button.className = 'reflex-button';
		button.value = button.name = 'Cancel';
		td.appendChild(button);
		Event.observe('click', button, this.destroy.bind(this));

		if (this.accountId != null)
		{
			tr = table.addRow();
			td = tr.addCell();
			td.colspan = 2;
			var button = document.createElement('input');
			button.type = 'button';
			button.className = 'reflex-button';
			button.value = button.name = 'Edit';
			td.appendChild(button);
			Event.observe('click', button, this.togglePanes.bind(this));
		}

		this.viewPane.appendChild(table);
	},

	submitDetails: function()
	{
		if (this.accountId == null) return;
		var inputs = this.editPane.getElementsByTagName('input');
		var values = {};
		for (var i = 0, l = inputs.length; i < l; i++)
		{
			values[inputs.name] = inputs.value;
		}
		Ticketing_Contact.setContactDetails(this.displaySavedDetails.bind(this),
			values.title, 
			values.firstName, 
			values.lastName, 
			values.jobTitle,
			values.email, 
			values.fax, 
			values.mobile, 
			values.phone, 
			this.accountId
		);
	},

	displaySavedDetails: function(details)
	{
		this.contactId = details['contactId'];
		this.callback(details);
		this.displayDetails(details);
	},

	displayDetails: function(details)
	{
		this.details = details;
		this.viewPane.innerHTML = '';
		this.editPane.innerHTML = '';
		this.viewPane.style.display = this.editPane.style.display = 'none';
		this.editPanePopulated = this.viewPanePopulated = false;
		if (this.contactId == null)
		{
			this.populateEditPane();
			this.currentPaneIsView = false;
			this.editPane.style.display = 'block';
		}
		else
		{
			this.populateViewPane();
			this.currentPaneIsView = true;
			this.viewPane.style.display = 'block';
		}
	},

	destroy: function()
	{
		if (this.container == null) return;
		this.container.parentNode.removeChild(this.container);
		this.container = null;
	}

});

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

	remoteGetDetails: jQuery.json.jsonFunction(Ticketing_Contact.onGetDetails, null, 'Ticketing', 'getContactDetails'),

	remoteSetDetails: jQuery.json.jsonFunction(Ticketing_Contact.onSetDetails, null, 'Ticketing', 'setContactDetails'),

	getContactDetails: function (contactId, callback)
	{
		Ticketing_Contact.callback = callback;
		Ticketing_Contact.remoteGetDetails(contactId);
	},

	setContactDetails: function (callback, contactId, title, firstName, lastName, jobTitle, email, fax, mobile, phone, accountId)
	{
		Ticketing_Contact.callback = callback;
		Ticketing_Contact.remoteSetDetails(contactId, title, firstName, lastName, jobTitle, email, fax, mobile, phone, accountId);
	},

	displayContact: function(contactId)
	{
		Ticketing_Contact.hideContact();
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