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
	}

};