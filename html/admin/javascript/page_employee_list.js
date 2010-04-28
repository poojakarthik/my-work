
var Page_Employee_List = Class.create(
{
	initialize	: function(oContainerDiv, iMaxRecordsPerPage)
	{
		// Create DataSet & pagination object (default to only active employees)
		this.oDataset		= 	new Dataset_Ajax(
									Dataset_Ajax.CACHE_MODE_NO_CACHING, 
									Page_Employee_List.DATA_SET_DEFINITION_ACTIVE
								);
		this.oPagination	= 	new Pagination(
									this._updateTable.bind(this), 
									Page_Employee_List.MAX_RECORDS_PER_PAGE,
									this.oDataset
								);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		this.oContentDiv 	= $T.div(
								$T.table({class: 'reflex highlight-rows'},
										$T.caption(
											$T.div({class: 'caption_bar'},						
												$T.div({class: 'caption_title'},
													'No records'
												),
												$T.div({class: 'caption_options'},
													$T.div({class: 'employee-list-show-archived'},
														$T.input({type: 'checkbox', class: 'employee-list-show-archived'}),
														$T.span({class: 'pointer'},
															'Show Archived Employees'
														)
													),
													$T.div(
														$T.button(
															$T.img({src: sButtonPathBase + 'first.png'})
														),
														$T.button(
															$T.img({src: sButtonPathBase + 'previous.png'})
														),
														$T.button(
															$T.img({src: sButtonPathBase + 'next.png'})
														),
														$T.button(
															$T.img({src: sButtonPathBase + 'last.png'})
														)
													)
												)
											)
										),
										$T.thead(
											$T.tr(
												$T.th('Given Name'),
												$T.th('Surname'),
												$T.th('Username'),
												$T.th('Status'),
												$T.th('Actions')
											)
										),
										$T.tbody({class: 'alternating'}
											// ...
										),
										$T.tfoot( 
											$T.tr(
												$T.th({colspan: '11'},
													$T.button({class: 'icon-button'},
														$T.img({src: Page_Employee_List.ADD_IMAGE_SOURCE, alt: '', title: 'Add Employee'}),
														$T.span('Add Employee')
													)
												)
											)
										)
									),
									$T.div({class: 'footer-pagination'},
										$T.button(
											$T.img({src: sButtonPathBase + 'first.png'})
										),
										$T.button(
											$T.img({src: sButtonPathBase + 'previous.png'})
										),
										$T.button(
											$T.img({src: sButtonPathBase + 'next.png'})
										),
										$T.button(
											$T.img({src: sButtonPathBase + 'last.png'})
										)
									)
								);
		
		// Bind events to the pagination buttons
		var aTopPageButtons		= this.oContentDiv.select('table > caption div.caption_options button');
		var aBottomPageButtons 	= this.oContentDiv.select('div.footer-pagination button');
		
		// First
		aTopPageButtons[0].observe('click', this.oPagination.firstPage.bind(this.oPagination));
		aBottomPageButtons[0].observe('click', this.oPagination.firstPage.bind(this.oPagination));
		
		//Previous		
		aTopPageButtons[1].observe('click', this.oPagination.previousPage.bind(this.oPagination));
		aBottomPageButtons[1].observe('click', this.oPagination.previousPage.bind(this.oPagination));
		
		// Next
		aTopPageButtons[2].observe('click', this.oPagination.nextPage.bind(this.oPagination));
		aBottomPageButtons[2].observe('click', this.oPagination.nextPage.bind(this.oPagination));
		
		// Last
		aTopPageButtons[3].observe('click', this.oPagination.lastPage.bind(this.oPagination));
		aBottomPageButtons[3].observe('click', this.oPagination.lastPage.bind(this.oPagination));
		
		// Setup pagination button object
		this.oPaginationButtons = {
			oTop	: {
				oFirstPage		: aTopPageButtons[0],
				oPreviousPage	: aTopPageButtons[1],
				oNextPage		: aTopPageButtons[2],
				oLastPage		: aTopPageButtons[3]
			},
			oBottom	: {
				oFirstPage		: aBottomPageButtons[0],
				oPreviousPage	: aBottomPageButtons[1],
				oNextPage		: aBottomPageButtons[2],
				oLastPage		: aBottomPageButtons[3]
			},
		};		
		
		// Bind 'add' button event, making it create a popup which calls back to load the last page of data on completion
		var oAddButton = this.oContentDiv.select('tfoot button').first();
		oAddButton.observe('click', this._addEmployee.bind(this));
		
		// Bind 'Show archived...' events
		var oArchivedCheckbox	= this.oContentDiv.select('input.employee-list-show-archived').first();
		oArchivedCheckbox.observe('click', this._showArchivedEmployees.bind(this, oArchivedCheckbox));
		
		var oArchivedLabel	= this.oContentDiv.select('div.employee-list-show-archived > span.pointer').first();
		oArchivedLabel.observe('click', this._toggleShowArchivedEmployees.bind(this, oArchivedCheckbox));
		
		// Attach content and get data
		oContainerDiv.appendChild(this.oContentDiv);
		this.oPagination.getCurrentPage();
	},
	
	_updateTable	: function(oResultSet)
	{
		var oTBody = this.oContentDiv.select('table > tbody').first();
		
		// Remove all existing rows
		while (oTBody.firstChild)
		{
			// Remove event handlers from the action buttons
			var oEditButton = oTBody.firstChild.select('img').first();
			
			if (oEditButton)
			{
				oEditButton.stopObserving();
			}
			
			// Remove the row
			oTBody.firstChild.remove();
		}
		
		// Add the new records
		var oCaptionTitle = this.oContentDiv.select('table > caption > div.caption_bar > div.caption_title').first();
		
		// Check if any results came back
		if (!oResultSet || oResultSet.intTotalResults == 0 || oResultSet.arrResultSet.length == 0)
		{
			// No records
			oCaptionTitle.innerHTML = 'No Records';
			
			oTBody.appendChild( 
								$T.tr(
									$T.td({colspan: this.oContentDiv.select('table > thead > tr').first().childNodes.length},
										'There are no records to display'
									)
								)
							);
		}
		else
		{
			// Update Page ? of ?
			var iCurrentPage		= oResultSet.intCurrentPage + 1;
			oCaptionTitle.innerHTML	= 'Page '+ iCurrentPage +' of ' + oResultSet.intPageCount;
			
			// Add the rows
			var aData	= jQuery.json.arrayAsObject(oResultSet.arrResultSet);
			
			for(var i in aData)
			{
				oTBody.appendChild(this._createTableRow(aData[i]));
			}
			
			this._updatePagination();
		}
		
		// Close the loading popup
		if (this.oLoadingOverlay)
		{
			this.oLoadingOverlay.hide();
			delete this.oLoadingOverlay;
		}
	},
	
	_createTableRow	: function(oData)
	{
		if (oData.Id != null)
		{
			var bArchived	= parseInt(oData.Archived) == 1;
			var	oTR			=	$T.tr(
									$T.td(oData.FirstName),
									$T.td(oData.LastName),
									$T.td(oData.UserName),
									$T.td({class: (bArchived ? 'employee-archived' : 'employee-active')},
										bArchived ? 'Archived' : 'Active'
									),
									$T.td({class: 'employee-list-action'},
										$T.img({class: 'pointer', src: Page_Employee_List.EDIT_IMAGE_SOURCE, alt: 'View Employee', title: 'View Employee'}),
										$T.img({class: 'pointer', src: Page_Employee_List.PERMISSION_IMAGE_SOURCE, alt: 'View Permissions', title: 'View Permissions'})
									)
								);
			
			// Add click event to the action buttons
			var oEditButton = oTR.select('td.employee-list-action > img').first();
			oEditButton.observe('click', this._editEmployee.bind(this, oData.Id));
			
			var oPermissionsButton	= oTR.select('td.employee-list-action > img').last();
			oPermissionsButton.observe('click', this._editPermissions.bind(this, oData.Id));
			
			return oTR;
		}
		else
		{
			// Invalid, return empty row
			return $T.tr();
		}
	},
	
	_updatePagination : function(iPageCount)
	{
		// Update the 'disabled' state of each pagination button
		this.oPaginationButtons.oTop.oFirstPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oFirstPage.disabled 	= true;
		this.oPaginationButtons.oTop.oPreviousPage.disabled		= true;
		this.oPaginationButtons.oBottom.oPreviousPage.disabled	= true;
		this.oPaginationButtons.oTop.oNextPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oNextPage.disabled 		= true;
		this.oPaginationButtons.oTop.oLastPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oLastPage.disabled 		= true;
		
		if (iPageCount == undefined)
		{
			// Get the page count
			this.oPagination.getPageCount(this._updatePagination.bind(this));
		}
		else
		{
			if (this.oPagination.intCurrentPage != Pagination.PAGE_FIRST)
			{
				// Enable the first and previous buttons
				this.oPaginationButtons.oTop.oFirstPage.disabled 		= false;
				this.oPaginationButtons.oBottom.oFirstPage.disabled		= false;
				this.oPaginationButtons.oTop.oPreviousPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oPreviousPage.disabled 	= false;
			}
			if (this.oPagination.intCurrentPage < (iPageCount - 1) && iPageCount)
			{
				// Enable the next and last buttons
				this.oPaginationButtons.oTop.oNextPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oNextPage.disabled 	= false;
				this.oPaginationButtons.oTop.oLastPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oLastPage.disabled 	= false;
			}
		}
	},
	
	_addEmployee	: function()
	{
		this._editEmployee(null);
	},
	
	_editEmployee	: function(iEmployeeId)
	{
		// Function to launch employee details popup
		var fnShowPopup	= function(iEmployeeId)
		{
			new Popup_Employee_Details(
				(iEmployeeId !== null ? Control_Field.RENDER_MODE_VIEW : Control_Field.RENDER_MODE_EDIT), 
				iEmployeeId,
				false, 
				this._employeeSaved.bind(this)
			);
		};
		
		// Load required js files and then show the edit employee popup
		JsAutoLoader.loadScript(
			[
				"reflex_validation",
				"reflex_style",
				"reflex_fx_reveal",
				"reflex_control",
				"reflex_control_tree",
				"reflex_control_tree_node",
				"reflex_control_tree_node_root",
				"reflex_control_tree_node_checkable",
				"date_time_picker_dynamic",
				"control_field",
				"control_field_text",
				"control_field_password",
				"control_field_checkbox",
				"control_field_date_picker",
				"control_field_select",
				"operation_tree",
				"operation",
				"status",
				"operation_profile",
				"user_role",
				"ticketing_user_permission",
				"employee",
				"popup_employee_details",
				"popup_employee_password_change",
				"popup_employee_details_permissions",
				"popup_operation_profile_edit"
     	  	], 
			fnShowPopup.bind(this, iEmployeeId), 
			true
		);
	},
	
	_editPermissions	: function(iEmployeeId)
	{
		// Function to launch employee details popup
		var fnShowPopup	= function(iEmployeeId)
		{
			new Popup_Employee_Details_Permissions(Control_Field.RENDER_MODE_VIEW, iEmployeeId);
		};
		
		// Load required js files and then show the edit employee popup
		JsAutoLoader.loadScript(
			[
				"reflex_validation",
				"reflex_style",
				"reflex_fx_reveal",
				"reflex_control",
				"reflex_control_tree",
				"reflex_control_tree_node",
				"reflex_control_tree_node_root",
				"reflex_control_tree_node_checkable",
				"control_field",
				"control_field_text",
				"control_field_select",
				"operation_tree",
				"status",
				"operation",
				"operation_profile",
				"user_role",
				"ticketing_user_permission",
				"employee",
				"popup_employee_details_permissions",
				"popup_operation_profile_edit"
			], 
			fnShowPopup.bind(this, iEmployeeId), 
			true
		);
	},
	
	_showArchivedEmployees	: function(oCheckbox)
	{
		// Update the dataset json method definition and refresh the tables current page
		if (oCheckbox.checked)
		{
			this.oDataset.setJSONDefinition(Page_Employee_List.DATA_SET_DEFINITION_ALL);
		}
		else
		{
			this.oDataset.setJSONDefinition(Page_Employee_List.DATA_SET_DEFINITION_ACTIVE);
		}
		
		this.oPagination.getCurrentPage();
	},
	
	_toggleShowArchivedEmployees	: function(oCheckbox)
	{
		oCheckbox.checked	= !oCheckbox.checked;
		this._showArchivedEmployees(oCheckbox);
	},
	
	_employeeSaved	: function(bEmployeeAdded)
	{
		if (bEmployeeAdded)
		{
			this.oPagination.lastPage(true);
		}
		else
		{
			this.oPagination.getCurrentPage();
		}
	}
});

Page_Employee_List.MAX_RECORDS_PER_PAGE		= 15;
Page_Employee_List.EDIT_IMAGE_SOURCE		= '../admin/img/template/user_edit.png';
Page_Employee_List.ADD_IMAGE_SOURCE			= '../admin/img/template/new.png';
Page_Employee_List.PERMISSION_IMAGE_SOURCE	= '../admin/img/template/operation.png';

Page_Employee_List.DATA_SET_DEFINITION_ACTIVE	= {strObject: 'Employee', strMethod: 'getDataSetActiveEmployees'};
Page_Employee_List.DATA_SET_DEFINITION_ALL		= {strObject: 'Employee', strMethod: 'getDataSetAllEmployees'};