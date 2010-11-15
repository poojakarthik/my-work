var EmployeeEdit = {

	bolEditing: false,
	selAvailablePerms: null,
	selSelectedPerms: null,
	bolPerms: true,

	toggle: function()
	{
		if (EmployeeEdit.bolPerms && EmployeeEdit.selAvailablePerms == null)
		{
			EmployeeEdit.selAvailablePerms = EmployeeEdit.get('AvailablePermissions').cloneNode(true);
			EmployeeEdit.selSelectedPerms = EmployeeEdit.get('SelectedPermissions').cloneNode(true);
		}

		EmployeeEdit.bolEditing = !EmployeeEdit.bolEditing;			
		EmployeeEdit.toggleDivs('Employee');
		if (EmployeeEdit.bolPerms) EmployeeEdit.toggleDivs('Permissions');
		EmployeeEdit.toggleDivs('EmployeeButtons');

		if (!EmployeeEdit.bolEditing)
		{
			EmployeeEdit.reset();
		}
	},

	reset: function(strDivIdPrefix)
	{
		EmployeeEdit.get('VixenForm_Employee').reset();

		if (!EmployeeEdit.bolPerms) return;

		var select = EmployeeEdit.get('AvailablePermissions');
		var newSelect = EmployeeEdit.selAvailablePerms.cloneNode(true);
		select.parentNode.replaceChild(newSelect, select);

		select = EmployeeEdit.get('SelectedPermissions');
		newSelect = EmployeeEdit.selSelectedPerms.cloneNode(true);
		select.parentNode.replaceChild(newSelect, select);
	},

	toggleDivs: function(strDivIdPrefix)
	{
		var divEdit = EmployeeEdit.get(strDivIdPrefix + '.Edit');
		var divView = EmployeeEdit.get(strDivIdPrefix + '.View');
		
		divEdit.style.visibility = 'hidden';
		divView.style.visibility = 'hidden';
		
		divEdit.style.display = 'none';
		divView.style.display = 'none';

		var divDisplay = EmployeeEdit.bolEditing ? divEdit : divView;

		divDisplay.style.display = 'block';
		divDisplay.style.visibility = 'visible';
	},

	get: function(id)
	{
		return document.getElementById(id);
	},
	
	showReassignPopup	: function(iId, fnCallback, bCloseIfNone, sMessage)
	{
		var fnShowPopup	= function(iId)
		{
			var oPopup	= new Popup_Employee_Reassign_Tasks(iId, fnCallback, bCloseIfNone, sMessage);
		};
		
		JsAutoLoader.loadScript(
			[
			 	'../ui/javascript/dataset_ajax.js',
				'../ui/javascript/reflex_validation.js',
			 	'../ui/javascript/control_field.js',
			 	'../ui/javascript/control_field_select.js',
			 	'javascript/employee.js',
			 	'javascript/ticketing_user.js',
			 	'javascript/followup_reassign_reason.js',
			 	'javascript/popup_employee_reassign_tasks.js'
			],
			fnShowPopup.curry(iId)
		);
	},
	
	checkForAssignedTasks	: function(iId, fnCallback)
	{
		var oArchivedCheckbox	= EmployeeEdit.get('Employee.Archived');
		if (oArchivedCheckbox && oArchivedCheckbox.checked)
		{
			// Trying to archive the employee, check for assigned tasks
			EmployeeEdit.showReassignPopup(
				iId, 
				fnCallback, 
				true, 
				'This Employee has Tickets and/or Follow-Ups still assigned to them, please reassign these tasks before you archive the Employee:'
			);
		}
		else
		{
			// Proceed with save
			fnCallback();
		}
	}
};