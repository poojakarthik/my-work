var Popup_Employee_Details_Permissions	= Class.create(Reflex_Popup,
{
	initialize	: function($super, bRenderMode, iEmployeeId)
	{
		$super(50);
		
		this.bRenderMode				= bRenderMode;
		this.iOperationTreeCount		= 0;
		this.oOperationProfiles			= {};
		this.hOperationProfileChildren	= [];
		this.iNewOperationProfileId		= null;
		
		if (Number(iEmployeeId) > 0)
		{
			// Employee Id passed -- load permissions via JSON
			this.oEmployee	= Employee.getForId(iEmployeeId);
			this.oEmployee.getPermissions(this.buildContent.bind(this));
		}
		else if (bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			// New Employee
			this.buildContent(new Employee());
		}
		else
		{
			throw "Invalid Employee reference '" + iEmployeeId + "'";
		}
	},
	
	buildContent	: function(oEmployee)
	{
		// Cache employee
		this.oEmployee	= oEmployee;
		
		// Build Content
		this._oPage	= 	$T.div({class: 'employee-details-permissions'},
							$T.div({class: 'section employee-details-permissions-profiles'},
								$T.div({class: 'section-header'},
									$T.div({class: 'section-header-title'},
										$T.img({src: '../admin/img/template/group_key.png', alt: '', title: 'Profiles'}),
										$T.span('Profiles')
									)
								),
								$T.div({class: 'section-content section-content-fitted'},
									this.buildContentOperationProfiles(),
									$T.div({class: 'employee-details-permissions-empty'},
										'There are no profiles selected'
									)
								)
							),
							$T.div({class: 'section employee-details-permissions-operations'},
								$T.div({class: 'section-header'},
									$T.div({class: 'section-header-title'},
										$T.img({src: '../admin/img/template/key.png', alt: '', title: 'Permission Overrides'}),
										$T.span('Permission Overrides')
									)
								),
								$T.div({class: 'section-content section-content-fitted'},
									this.buildContentOperations(),
									$T.div({class: 'employee-details-permissions-operations-empty'},
										'There are no permission overrides selected'
									)
								)
							)
						);
		
		// Create buttons
		this.oEditButton		= 	$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/user_edit.png', alt: '', title: 'Edit'}),
										$T.span('Edit')
									);
		this.oCloseButton		= 	$T.button({class: 'icon-button'},
										$T.span('Close')
									);
		this.oSaveButton		= 	$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/tick.png', alt: '', title: 'Save'}),
										$T.span('Save')
									);
		this.oCancelEditButton	=	$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/delete.png', alt: '', title: 'Cancel'}),
										$T.span('Cancel')
									);
		this.oCancelNewButton	=	$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/delete.png', alt: '', title: 'Cancel'}),
										$T.span('Cancel')
									);
		this.oNewProfileButton	=	$T.button({class: 'icon-button'},
										$T.img({src: '../admin/img/template/group_add.png', alt: '', title: 'Create Profile'}),
										$T.span('Create Profile')
									);

		// Bind event handlers
		this.oCancelEditButton.observe('click', this.setControlMode.bind(this, Control_Field.RENDER_MODE_VIEW));
		this.oCancelNewButton.observe('click', this.hide.bind(this, false));
		this.oCloseButton.observe('click', this.hide.bind(this, false));
		this.oSaveButton.observe('click', this._saveButtonClick.bind(this));
		this.oEditButton.observe('click', this.setControlMode.bind(this, Control_Field.RENDER_MODE_EDIT));
		this.oNewProfileButton.observe('click', this._createNewProfile.bind(this));
		
		// Initialise interface features
		this.setFooterButtons([this.oEditButton, this.oCloseButton], true);
		this.setControlMode(this.bRenderMode);
		
		// Update the Popup
		this.setTitle("Manage Employee Permissions");
		this.addCloseButton();
		this.setIcon("../admin/img/template/user_key.png");
		this.setContent(this._oPage);
		this.display();
		
		this.oLoading	= new Reflex_Popup.Loading('Getting Permissions...');
		this.oLoading.display();
	},
	
	buildContentOperationProfiles	: function()
	{
		// Create tree, given callback for when it has loaded all of the operations
		this.iOperationTreeCount++;
		this.oOperationProfilesTree	= 	new Operation_Tree(
											Operation_Tree.RENDER_OPERATION_PROFILE, 
											null,
											Operation_Profile.getAllIndexed.bind(Operation_Profile),
											this._operationTreeReady.bind(this),
											this._operationProfileChange.bind(this)
										);		
		return	$T.div({class: 'employee-details-permissions-profiles-tree'},
					this.oOperationProfilesTree.getElement()
				);
	},
	
	buildContentOperations	: function()
	{
		// Create tree, given callback for when it has loaded all of the operations
		this.iOperationTreeCount++;
		this.oOperationsTree	= 	new Operation_Tree(
										Operation_Tree.RENDER_OPERATION, 
										null,
										Operation.getAllIndexed.bind(Operation),
										this._operationTreeReady.bind(this)
									);
		return	$T.div(this.oOperationsTree.getElement());
	},
	
	_operationTreeReady	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Wait until all operation tree's are ready
			this.iOperationTreeCount--;
			
			if (this.iOperationTreeCount)
			{
				return;
			}
			
			// Get child operations for all operation profiles
			Operation_Profile.getAllChildOperations(this._operationTreeReady.bind(this));
		}
		else
		{
			// Got child operations for profiles
			this.hOperationProfileChildren	= oResponse.aOperations;
			
			if (this.iNewOperationProfileId)
			{
				this._selectNewOperationProfile();
			}
			else
			{
				// Set the tree values
				this._selectDefaultTreeValues();
				
				if (this.oLoading)
				{
					// Hide loading popup
					this.oLoading.hide();
					delete this.oLoading;
				}
			}
		}
	},
	
	_save	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			var aOperationProfileIds	= this.oOperationProfilesTree.getSelected();
			var aOperationIds			= this.oOperationsTree.getSelected(true);
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
			
			// Show loading
			this.oLoading	= new Reflex_Popup.Loading('Saving...');
			this.oLoading.display();
			
			// Make the AJAX request
			this.oEmployee.setPermissions(aOperationProfileIdsToSave, aOperationIds, this._save.bind(this));
		}
		else
		{
			// Got response, close popup
			this.oLoading.hide();
			delete this.oLoading;
			this.hide();
		}
	},
	
	_selectDefaultTreeValues	: function()
	{
		// Clear trees 
		this.oOperationProfilesTree.deSelectAll(false);
		this.oOperationsTree.deSelectAll(true);
		
		// Set the employee's saved profiles & operations
		this.oOperationProfilesTree.setSelected(this.oEmployee.aOperationProfileIds, true);
		
		// Build profile child operations array
		var aChildOperations	= [];
		var aOperationProileIds	= this.oOperationProfilesTree.getSelected();
		var iProfileId			= null;
		var oProfile			= null;
		var iPrereqProfileId	= null;
		
		for (var i = 0; i < aOperationProileIds.length; i++)
		{
			iProfileId	= aOperationProileIds[i];
			
			// Add the profiles direct children
			if (this.hOperationProfileChildren[iProfileId])
			{
				for (var j = 0; j < this.hOperationProfileChildren[iProfileId].length; j++)
				{
					aChildOperations.push(this.hOperationProfileChildren[iProfileId][j]);
				}
			}
		}
		
		// Select profile child operations
		this.oOperationsTree.setSelected(aChildOperations, true, true);
		
		// Select specific operations
		this.oOperationsTree.setSelected(this.oEmployee.aOperationIds, true);
		
		if (this.bRenderMode == Control_Field.RENDER_MODE_VIEW)
		{
			// Expand the operations tree
			this.oOperationsTree.oControl.expandAll();
		}
		
		this._checkForNoPermissions(null, null, null, true);
	},
	
	_selectNewOperationProfile	: function(oOperationProfile)
	{
		if (this.oLoading)
		{
			this.oLoading.hide();
			delete this.oLoading;
		}
		
		this.oOperationProfilesTree.deSelectAll(false);
		this.oOperationsTree.deSelectAll(true);
		this.oOperationProfilesTree.setSelected([this.iNewOperationProfileId], true);
		
		var aOperationProileIds	= this.oOperationProfilesTree.getSelected(); 
		var aChildOperations	= [];
		var iProfileId			= null;
		
		for (var i = 0; i < aOperationProileIds.length; i++)
		{
			iProfileId	= aOperationProileIds[i];
			
			if (this.hOperationProfileChildren[iProfileId])
			{
				for (var j = 0; j < this.hOperationProfileChildren[iProfileId].length; j++)
				{
					aChildOperations.push(this.hOperationProfileChildren[iProfileId][j]);
				}
			}
		}
		
		this.oOperationsTree.setSelected(aChildOperations, true, true);
		this._checkForNoPermissions(null, null, null, true);
		this.iNewOperationProfileId	= null;
	},
	
	display		: function($super)
	{
		// If we have loaded, then display, otherwise automatically display once loaded
		if (this._oPage)
		{
			$super();
			return true;
		}
		else
		{
			this.bDisplayOnLoad	= true;
			return false;
		}
	},
	
	setControlMode	: function(bControlMode)
	{
		switch (bControlMode)
		{
			case Control_Field.RENDER_MODE_EDIT:
				// Change footer buttons
				this.setFooterButtons(
					[
					 	this.oNewProfileButton,
					 	this.oSaveButton,
					 	(this.oEmployee.oProperties.Id) ? this.oCancelEditButton : this.oCancelNewButton
					], 
					true
				);
				
				// Send Tree Grids into Edit mode
				this.oOperationProfilesTree.setEditable(true);
				this.oOperationsTree.setEditable(true);
				break;
				
			case Control_Field.RENDER_MODE_VIEW:
				// Change footer buttons
				this.setFooterButtons([this.oEditButton, this.oCloseButton], true);
				
				// Send Tree Grids into Read-Only mode
				this.oOperationProfilesTree.setEditable(false);
				this.oOperationsTree.setEditable(false);
				break;
			
			default:
				throw "Invalid Control Mode '" + bControlMode + "'";
		}
		
		this.bRenderMode	= bControlMode;
		
		// Reset the Permissions Trees to saved values
		this._selectDefaultTreeValues();
	},
	
	_operationProfileChange	: function()
	{
		// Build profile child operations array from the current profile tree state
		var aOperationProfileIds	= this.oOperationProfilesTree.getSelected();
		var aChildOperations		= [];
		var iProfileId				= null;
		
		for (var i = 0; i < aOperationProfileIds.length; i++)
		{
			iProfileId	= aOperationProfileIds[i];
			
			for (var iOperationId in this.hOperationProfileChildren[iProfileId])
			{
				if (!isNaN(this.hOperationProfileChildren[iProfileId][iOperationId]))
				{
					aChildOperations.push(this.hOperationProfileChildren[iProfileId][iOperationId]);
				}
			}
		}
		
		// Get selected override operations
		var aOperationIds	= this.oOperationsTree.getSelected(true);
		
		// Clear operations tree 
		this.oOperationsTree.deSelectAll(true);
		
		// Select profile child operations in the operations tree
		this.oOperationsTree.setSelected(aChildOperations, true, true);
		
		// Select operations from before the clear
		this.oOperationsTree.setSelected(aOperationIds, true, false);
	},
	
	_createNewProfile	: function()
	{
		new Popup_Operation_Profile_Edit(
			Control_Field.RENDER_MODE_EDIT, 
			null, 
			this._profileSaved.bind(this),
			this.oOperationProfilesTree.getSelected(), 
			this.oOperationsTree.getSelected(true)
		);
	},
	
	_profileSaved	: function(iOperationProfileId)
	{
		// Remove the profile tree
		var oTreeContainer	= this._oPage.select('div.employee-details-permissions-profiles div.section-content').first();
		var oTree			= oTreeContainer.select('div.employee-details-permissions-profiles-tree').first();
		oTree.remove();
		
		// Create a new profile tree, with the new profile
		this.iNewOperationProfileId	= iOperationProfileId;
		oTreeContainer.appendChild(this.buildContentOperationProfiles());
		
		// Make sure it's in the right render mode
		this.oOperationProfilesTree.setEditable(this.bRenderMode);
	},
		
	_checkForNoPermissions	: function(bOnClose, fnOnYes, fnOnNo, bNoPopup)
	{
		var aOperationIds			= null;
		var aOperationProfileIds	= null;
		
		if (bOnClose)
		{
			aOperationProfileIds	= this.oEmployee.aOperationProfileIds;
			aOperationIds			= this.oEmployee.aOperationIds;	
		}
		else
		{
			aOperationProfileIds	= this.oOperationProfilesTree.getSelected();
			aOperationIds			= this.oOperationsTree.getSelected();
		}
		
		var oProfilesEmpty	= this._oPage.select('div.employee-details-permissions-empty').first();
		if ((aOperationProfileIds.length == 0) && (this.bRenderMode == Control_Field.RENDER_MODE_VIEW) && this.oOperationProfilesTree._bLoaded)
		{
			oProfilesEmpty.show();
		}
		else
		{
			oProfilesEmpty.hide();
		}
		
		var oPermissionsEmpty	= this._oPage.select('div.employee-details-permissions-operations-empty').first();
		if ((aOperationIds.length == 0) && (this.bRenderMode == Control_Field.RENDER_MODE_VIEW) && this.oOperationsTree._bLoaded)
		{
			oPermissionsEmpty.show();
		}
		else
		{
			oPermissionsEmpty.hide();
		}
		
		if (aOperationProfileIds.length == 0 && aOperationIds.length == 0)
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
		
	}
});

