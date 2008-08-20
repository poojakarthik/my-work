var EmployeeView = {

	TABLE_ID: "EmployeeTable",

	Update: function()
	{
		var obj = {};
		obj.Search = {};
		obj.Search.Archived = document.getElementById("chbArchived").checked ? 1 : 0;
		var strSortedField = Vixen.TableSort.getSortedField(EmployeeView.TABLE_ID);
		if (strSortedField != null)
		{
			obj.Search.OrderBy = strSortedField;
			if (!Vixen.TableSort.getSortedAscending(EmployeeView.TABLE_ID))
			{
				obj.Search.OrderDesc = true;
			}
		}

		// Need to load up the employee list and replace with the 'EmployeeTableDiv'
		Vixen.Ajax.CallAppTemplate("Employee", "EmployeeListAjax", obj, "Div", true, false, EmployeeView.HandleAjaxResponse);
	},

	HandleAjaxResponse: function(req)
	{
		var responseText = req.responseText;
		//alert(responseText);
		var div = document.createElement("DIV");
		div.innerHTML = responseText;
		var table = div.getElementsByTagName("TABLE")[0];
		// Remove the header row
		table.rows[0].parentNode.removeChild(table.rows[0]);
		Vixen.TableSort.setTableRows(EmployeeView.TABLE_ID, table.rows);
	}
}