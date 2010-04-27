
var Popup_Data_Report_Permission	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iDataReportId)
	{
		$super(40);
		
		this.iDataReportId			= iDataReportId;
		this.iInterfaceReadyCount	= 0;
		this._buildUI();
	},
	
	_buildUI	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Get the data reports permissions
			var fnGetPermissions	=	jQuery.json.jsonFunction(
											this._buildUI.bind(this), 
											this._ajaxError.bind(this, true),
											'DataReport',
											'getPermissionsForId'
										);
			fnGetPermissions(this.iDataReportId);
		}
		else if (oResponse.Success)
		{
			// Cache saved permissions
			this.aEmployeeIds			= oResponse.aEmployeeIds;
			this.aOperationProfileIds	= oResponse.aOperationProfileIds;
			
			// Build Content
			this._oPage	= 	$T.div({class: 'data-report-permissions'},
								$T.div({class: 'section data-report-permissions-employees'},
									$T.div({class: 'section-header'},
										$T.div({class: 'section-header-title'},
											$T.img({src: '../admin/img/template/contact_small.png', alt: '', title: 'Employees'}),
											$T.span('Employees')
										)
									),
									$T.div({class: 'section-content'},
										this.buildContentEmployees()
									)
								),
								$T.div({class: 'section data-report-permissions-profiles'},
									$T.div({class: 'section-header'},
										$T.div({class: 'section-header-title'},
											$T.img({src: '../admin/img/template/group_key.png', alt: '', title: 'Permission Profiles'}),
											$T.span('Permission Profiles')
										)
									),
									$T.div({class: 'section-content section-content-fitted'},
										this.buildContentOperationProfiles(),
										$T.div({class: 'data-report-permissions-profiles-empty'},
											'There are no profiles selected'
										)
									)
								)
							);
			
			// Create buttons
			this.oSaveButton	= 	$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/tick.png', alt: '', title: 'Save'}),
										$T.span('Save')
									);
			this.oCloseButton	= 	$T.button({class: 'icon-button'},
										$T.span('Close')
									);
			
			// Bind event handlers
			this.oSaveButton.observe('click', this._saveButtonClick.bind(this));
			this.oCloseButton.observe('click', this.hide.bind(this, false));
			
			// Initialise interface features
			this.setFooterButtons([this.oSaveButton, this.oCloseButton], true);
			this._checkForNoPermissions(false, null, null, true);
			
			// Update the Popup
			this.setTitle("Edit Data Report Permissions");
			this.addCloseButton();
			this.setIcon("../admin/img/template/user_key.png");
			this.setContent(this._oPage);
			this.display();
			
			this.oLoading	= new Reflex_Popup.Loading('Getting Permissions...');
			this.oLoading.display();
		}
		else
		{
			// AJAX Error
			this._ajaxError(true, oResponse);
		}
	},
	
	buildContentEmployees	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			this.oEmployeeFromSelect	= $T.select({size: Popup_Data_Report_Permission.EMPLOYEE_LIST_SIZE});
			this.oEmployeeToSelect		= $T.select({size: Popup_Data_Report_Permission.EMPLOYEE_LIST_SIZE});
			
			this.oEmployeeFromSelect.observe('click', this._moveEmployeeToRight.bind(this));
			this.oEmployeeToSelect.observe('click', this._moveEmployeeToLeft.bind(this));
			
			this.iInterfaceReadyCount++;
			
			var fnGetEmployees	=	jQuery.json.jsonFunction(
										this.buildContentEmployees.bind(this),
										this._ajaxError.bind(this, true), 
										'Employee', 
										'getActive'
									);
			fnGetEmployees();
			
			return	$T.ul({class: 'reset horizontal'},
						$T.li({class: 'data-report-permissions-employee-from'},
							$T.div({class: 'data-report-permissions-employee-list-title'},
								'Available'
							),
							this.oEmployeeFromSelect
						),
						$T.li({class: 'data-report-permissions-employee-to'},
							$T.div({class: 'data-report-permissions-employee-list-title'},
								'Permitted'
							),this.oEmployeeToSelect
						)
					);
		}
		else
		{
			// Got response, add employees to list
			var oEmployees	= jQuery.json.arrayAsObject(oResponse.aEmployees);
			this.hEmployees	= {};
			var oEmployee	= null;
			var aToSort		= [];
			
			for (var iId in oEmployees)
			{
				if (!isNaN(iId))
				{
					oEmployee	= oEmployees[iId];
					
					var oNewOption	= 	$T.option({class: 'data-report-permissions-employee', value: oEmployee.Id},
											$T.img({class: 'data-report-permissions-employee-remove', src: Popup_Data_Report_Permission.REMOVE_IMAGE_SOURCE, alt: 'Remove', title: 'Remove'}),
											$T.span({class: 'data-report-permissions-employee-name'},
												oEmployee.FirstName + ' ' + oEmployee.LastName
											),
											$T.img({class: 'data-report-permissions-employee-add', src: Popup_Data_Report_Permission.ADD_IMAGE_SOURCE, alt: 'Add', title: 'Add'}),
											$T.div({class: 'data-report-permissions-employee-clear'})
										);
					
					this.oEmployeeFromSelect.appendChild(oNewOption);
					
					// Store the array index
					oEmployee.iListIndex	= this.oEmployeeFromSelect.options.length - 1;
					oEmployee.bSelected		= false;
					
					this.hEmployees[oEmployee.Id]	= oEmployee;
					aToSort.push(oEmployee);
				}
			}
			
			// TESTING
			var oSorter	= 	new Reflex_Sorter(
								[
								 	{
								 		sField		: 'FirstName',
								 		fnCompare	: Reflex_Sorter.stringGreaterThan
								 	}
			           	  	    ]
							);
			debugger;
			oSorter.sort(aToSort);
			// END TESTING
			
			// Start the interface
			this._interfaceReady();
		}
	},
	
	buildContentOperationProfiles	: function()
	{
		// Create tree, given callback for when it has loaded all of the operations
		this.iInterfaceReadyCount++;
		this.oOperationProfilesTree	= 	new Operation_Tree(
											Operation_Tree.RENDER_OPERATION_PROFILE, 
											null,
											Operation_Profile.getAllIndexed.bind(Operation_Profile),
											this._interfaceReady.bind(this)
										);		
		return	$T.div({class: 'data-report-permissions-profiles-tree'},
					this.oOperationProfilesTree.getElement()
				);
	},
	
	_interfaceReady	: function()
	{
		// Wait until all operation tree's are ready
		this.iInterfaceReadyCount--;
		
		if (this.iInterfaceReadyCount)
		{
			return;
		}
		
		// Tree is ready, set the default values
		this._setDefaultValues();
		
		if (this.oLoading)
		{
			// Hide loading popup
			this.oLoading.hide();
			delete this.oLoading;
		}
	},
	
	_save	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Get the list of operation profile ids to save
			var aOperationProfileIds	= this.oOperationProfilesTree.getSelected();
			var oOperationProfiles		= this.oOperationProfilesTree.oOperations;
			var hPrerequisite			= {};
			
			// Flag all of the prerequisite profiles from each selected one
			for (var i = 0; i < aOperationProfileIds.length; i++)
			{
				var iOperationProfileId	= aOperationProfileIds[i];
				
				if (iOperationProfileId != null)
				{
					// For each prerequisite...
					var aPrerequisites	= oOperationProfiles[iOperationProfileId].aPrerequisites;
					
					for (var j = 0; j < aPrerequisites.length; j++)
					{
						// See if it exists in the aOperationProfileIds, if so flag it as such
						var sProfileId	= aPrerequisites[j].toString();
						
						if (sProfileId != iOperationProfileId)
						{
							for (var k = 0; k < aOperationProfileIds.length; k++)
							{
								if (aOperationProfileIds[k] == sProfileId)
								{
									hPrerequisite[sProfileId]	= true;
									break;
								}
							}
						}
					}
				}
			}
	
			// For each operation profile id, ignore it if flagged as a prerequisite
			var aOperationProfileIdsToSave	= [];
			
			for (var i = 0; i < aOperationProfileIds.length; i++)
			{
				if (!hPrerequisite[aOperationProfileIds[i]])
				{
					aOperationProfileIdsToSave.push(aOperationProfileIds[i]);
				}
			}
			
			// Get the employee ids to save
			var aEmployeeIds	= this._getSelectedEmployeeIds();
			
			// Show loading
			this.oLoading	= new Reflex_Popup.Loading('Saving...');
			this.oLoading.display();
			
			// Make the AJAX request
			var fnSetPermissions	= jQuery.json.jsonFunction(this._save.bind(this), this._ajaxError.bind(this), 'DataReport', 'setPermissionsForId');
			fnSetPermissions(this.iDataReportId, aEmployeeIds, aOperationProfileIdsToSave);
		}
		else
		{
			// Got response, close popup
			this.oLoading.hide();
			delete this.oLoading;
			this.hide();
		}
	},
	
	_checkForNoPermissions	: function(bOnClose, fnOnYes, fnOnNo, bNoPopup)
	{
		var aOperationProfileIds	= null;
		var aEmployeeIds			= null;
		
		if (bOnClose)
		{
			aOperationProfileIds	= this.aOperationProfileIds;
			aEmployeeIds			= this.aEmployeeIds;
		}
		else
		{
			aOperationProfileIds	= this.oOperationProfilesTree.getSelected();
			aEmployeeIds			= this._getSelectedEmployeeIds();
		}
		
		var oProfilesEmpty	= this._oPage.select('div.data-report-permissions-profiles-empty').first();
		if ((aOperationProfileIds.length == 0) && (this.bRenderMode == Control_Field.RENDER_MODE_VIEW) && this.oOperationProfilesTree._bLoaded)
		{
			oProfilesEmpty.show();
		}
		else
		{
			oProfilesEmpty.hide();
		}
		
		if (aOperationProfileIds.length == 0 && aEmployeeIds.length == 0)
		{
			// None selected, show alert
			if (!bNoPopup)
			{
				Reflex_Popup.yesNoCancel(
					$T.div(
						$T.div('You have not selected any permissions for this employee'),
						$T.div('Are you sure you want to ' + (bOnClose ? 'close' : 'save') + '?')
					),
					{fnOnYes: fnOnYes, fnOnNo: fnOnNo}
				);
			}
			
			return false;
		}
		
		return true;
	},
	
	_saveButtonClick	: function()
	{
		if (this._checkForNoPermissions(false, this._save.bind(this)))
		{
			this._save();
		}
	},
	
	_ajaxError	: function(bHideOnClose, oResponse)
	{
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		if (oResponse.Success == false)
		{
			var oConfig	= {sTitle: 'Error', fnOnClose: (bHideOnClose ? this.hide.bind(this) : null)};
			
			if (oResponse.Message)
			{
				Reflex_Popup.alert(oResponse.Message, oConfig);
			}
			else if (oResponse.ERROR)
			{
				Reflex_Popup.alert(oResponse.ERROR, oConfig);
			}
		}
	},
	
	_setDefaultValues	: function()
	{
		// Profiles
		this.oOperationProfilesTree.setEditable(true);
		this.oOperationProfilesTree.setSelected(this.aOperationProfileIds, true);
		
		// Employees
		var oEmployee	= null;
		var iId			= null;
		for (var i = 0; i < this.aEmployeeIds.length; i++)
		{
			iId	= this.aEmployeeIds[i];
			this._selectEmployee(iId);
		}
		
		this._checkForNoPermissions(false, null, null, true);
	},
	
	_moveEmployeeToRight	: function()
	{
		var oOptionToMove	= this.oEmployeeFromSelect.options[this.oEmployeeFromSelect.selectedIndex];
		var iEmployeeId		= oOptionToMove.value;
		var oEmployee		= this.hEmployees[iEmployeeId];
		this.oEmployeeToSelect.appendChild(oOptionToMove);
	},
	
	_moveEmployeeToLeft	: function()
	{
		var oOptionToMove	= this.oEmployeeToSelect.options[this.oEmployeeToSelect.selectedIndex];
		var iEmployeeId		= oOptionToMove.value;
		var oEmployee		= this.hEmployees[iEmployeeId];
		
		// Re-position the option in it's original spot
		var aOptions		= this.oEmployeeFromSelect.options;
		var oCurrentOption	= null;
		var oPreviousOption	= null;
		var bMoved			= false;
		
		for (var i = 0; i < aOptions.length; i++)
		{
			oCurrentOption	= aOptions[i];
			iCurrentIndex	= this.hEmployees[oCurrentOption.value].iListIndex;
			
			if (oEmployee.iListIndex > iCurrentIndex)
			{
				continue;
			}
			else
			{
				this.oEmployeeFromSelect.insertBefore(oOptionToMove, oCurrentOption);
				bMoved	= true;
				break;
			}
		}
		
		if (!bMoved)
		{
			this.oEmployeeFromSelect.appendChild(oOptionToMove);
		}
	},
	
	_selectEmployee	: function(iEmployeeId)
	{
		var oEmployee		= this.hEmployees[iEmployeeId];
		var oOptionToMove	= this.oEmployeeFromSelect.options[oEmployee.iListIndex];
		this.oEmployeeToSelect.appendChild(oOptionToMove);
	},
	
	_getSelectedEmployeeIds	: function()
	{
		var aEmployeeIds	= [];
		
		for (var i = 0; i < this.oEmployeeToSelect.options.length; i++)
		{
			aEmployeeIds.push(this.oEmployeeToSelect.options[i].value);
		}
		
		return aEmployeeIds;
	}
});

Popup_Data_Report_Permission.ADD_IMAGE_SOURCE		= '../admin/img/template/new.png';
Popup_Data_Report_Permission.REMOVE_IMAGE_SOURCE	= '../admin/img/template/remove.png';

Popup_Data_Report_Permission.EMPLOYEE_LIST_SIZE		= 9;
