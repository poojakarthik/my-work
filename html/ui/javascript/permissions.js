// PickList script- By Sean Geraty (http://www.freewebs.com/sean_geraty/)
// Visit JavaScript Kit (http://www.javascriptkit.com) for this JavaScript and 100s more
// Please keep this notice intact

// Control flags for list selection and sort sequence
// Sequence is on option value (first 2 chars - can be stripped off in form processing)
// It is assumed that the select list is in sort sequence initially

var EmployeePermissions = {

	singleSelect:  true,	// Allows an item to be selected once only
	sortAvailable: true,	// Only effective if above flag set to true
	sortSelected:  true,	// Will order the picklist in sort sequence
	clearSelections: true,	// Whether or not to mark options as unselected after moving them

	availableList: null,
	selectedList: null,

	init: function() 
	{
		//debugger;
		EmployeePermissions._findElements();

		if (EmployeePermissions.availableList == null) return;
		
		if (EmployeePermissions.availableList.options.length > 0)
		{
			EmployeePermissions.availableList.options[0].selected = true;
			EmployeePermissions.availableList.options[0].defaultSelected = true;
		}
		
		EmployeePermissions._sortOptionsList(EmployeePermissions.availableList);
		EmployeePermissions._sortOptionsList(EmployeePermissions.selectedList);
	},

	// Adds a selected item into the picklist
	addSelections: function() 
	{
		EmployeePermissions._moveOptions(true);
	},

	// Deletes an item from the picklist
	removeSelections: function() 
	{
		EmployeePermissions._moveOptions(false);
	},

	_findElements: function()
	{
		//debugger;
		EmployeePermissions.availableList = document.getElementById("AvailablePermissions");
		EmployeePermissions.selectedList  = document.getElementById("SelectedPermissions");
	},

	_ensureInitialized: function()
	{
		if (EmployeePermissions.selectedList == null) EmployeePermissions._findElements();
	},

	_moveOptions: function(isPick)
	{
		//debugger;
		EmployeePermissions._ensureInitialized();
		var fromList = isPick ? EmployeePermissions.availableList : EmployeePermissions.selectedList;
		var toList   = isPick ? EmployeePermissions.selectedList  : EmployeePermissions.availableList;

		var singleSelect = EmployeePermissions.singleSelect;
		var sort = (isPick ? EmployeePermissions.sortSelected : EmployeePermissions.sortAvailable) && (isPick || singleSelect);

		for (var fromIndex = fromList.options.length - 1; fromIndex >= 0; fromIndex--) 
		{
			if (fromList.options[fromIndex].selected)
			{
				if (singleSelect)	toList.appendChild(fromList.options[fromIndex]);
				else if (isPick)	toList.appendChild(fromList.options[fromIndex].cloneNode(true));
				else 			fromOptions.removeChild(fromList.options[fromIndex]);
				if (EmployeePermissions.clearSelections && (singleSelect || isPick))
				{
					toList.options[toList.options.length - 1].selected = false;
				}
			}
		}

		if (sort && (isPick || singleSelect)) EmployeePermissions._sortOptionsList(toList);
	},

	_sortOptionsList: function(list)
	{
		// see: http://en.wikipedia.org/wiki/Cocktail_sort
		var options = list.options;
		var b = 0;
		var t = options.length - 1;
		var swap = true;

		while(swap) {
			swap = false;
			for(var i = b; i < t; ++i) 
			{
				if ( options[i].innerHTML > options[i+1].innerHTML ) {
					options[i+1].parentNode.insertBefore(options[i+1], options[i]);
					swap = true;
				}
			} // for
			t--;

			if (!swap) break;

			for(var i = t; i > b; --i) 
			{
				if ( options[i].innerHTML < options[i-1].innerHTML ) {
					options[i+1].parentNode.insertBefore(options[i], options[i-1]);
					swap = true;
				}
			} // for
			b++;

		} // while(swap)
	}
}
