var Popup_Employee_Details_Permissions	= Class.create(Reflex_Popup,
{
	initialize	: function($super, bRenderMode, iEmployeeId)
	{
		$super(40);
		
		this.bRenderMode				= bRenderMode;
		this.oOperationProfiles			= {};
		this.iOperationTreeReadyCount	= 0;
		this.hOperationProfileChildren	= [];
		
		this.oLoading	= new Reflex_Popup.Loading('Getting Permissions...');
		this.oLoading.display();
		
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
									this.buildContentOperationProfiles()
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
									this.buildContentOperations()
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
										$T.img({src: '../admin/img/template/new.png', alt: '', title: 'Create Profile'}),
										$T.span('Create Profile')
									);

		// Bind event handlers
		this.oCancelEditButton.observe('click', this.setControlMode.bind(this, Control_Field.RENDER_MODE_VIEW));
		this.oCancelNewButton.observe('click', this.hide.bind(this));
		this.oCloseButton.observe('click', this.hide.bind(this));
		this.oSaveButton.observe('click', this._save.bind(this));
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
		return true;
	},
		
	buildContentOperationProfiles	: function()
	{
		// Create tree, given callback for when it has loaded all of the operations
		this.oOperationProfilesTree	= 	new Operation_Tree(
											Operation_Tree.RENDER_HEIRARCHY_INCLUDES, 
											null,
											Operation_Profile.getAllIndexed.bind(Operation_Profile),
											this._operationTreeReady.bind(this),
											this._operationProfileChange.bind(this)
										);
		return	$T.div(this.oOperationProfilesTree.getElement());
	},
	
	buildContentOperations	: function()
	{
		// Create tree, given callback for when it has loaded all of the operations
		this.oOperationsTree	= 	new Operation_Tree(
										Operation_Tree.RENDER_HEIRARCHY_GROUPED, 
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
			// Wait until both operation tree's are ready
			if (this.iOperationTreeReadyCount < 1)
			{
				this.iOperationTreeReadyCount++;
				return;
			}
			
			// Get child operations for all operation profiles
			Operation_Profile.getAllChildOperations(this._operationTreeReady.bind(this));
		}
		else
		{
			// Got child operations for profiles
			this.hOperationProfileChildren	= oResponse.aOperations;
			
			// Set the tree values
			this._selectDefaultTreeValues();
			
			// Hide loading popup
			this.oLoading.hide();
			delete this.oLoading;
		}
	},
	
	_save	: function(event, oResponse)
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
			this.oEmployee.setPermissions(aOperationProfileIdsToSave, aOperationIds, this._save.bind(this, null));
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
		// Select operation profiles
		this.oOperationProfilesTree.setSelected(this.oEmployee.aOperationProfileIds, true);
		
		// Build profile child operations array
		var aChildOperations	= [];
		var iProfileId			= null;
		
		for (var i = 0; i < this.oEmployee.aOperationProfileIds.length; i++)
		{
			iProfileId	= this.oEmployee.aOperationProfileIds[i];
			
			for (var iOperationId in this.hOperationProfileChildren[iProfileId])
			{
				if (!isNaN(this.hOperationProfileChildren[iProfileId][iOperationId]))
				{
					aChildOperations.push(this.hOperationProfileChildren[iProfileId][iOperationId]);
				}
			}
		}
		
		// Select profile child operations
		this.oOperationsTree.setSelected(aChildOperations, true, true);
		
		// Select specific operations
		this.oOperationsTree.setSelected(this.oEmployee.aOperationIds, true);
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
					 	this.oSaveButton,
					 	(this.oEmployee.oProperties.Id) ? this.oCancelEditButton : this.oCancelNewButton,
					 	this.oNewProfileButton
					], 
					true
				);
				
				// Send Tree Grids into Edit mode
				this.oOperationsTree.setEditable(true);
				this.oOperationProfilesTree.setEditable(true);
				break;
				
			case Control_Field.RENDER_MODE_VIEW:
				// Change footer buttons
				this.setFooterButtons([this.oEditButton, this.oCloseButton], true);
				
				// Send Tree Grids into Read-Only mode
				this.oOperationsTree.setEditable(false);
				this.oOperationProfilesTree.setEditable(false);
				break;
			
			default:
				throw "Invalid Control Mode '" + bControlMode + "'";
		}
		
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
		this.oOperationsTree.deSelectAll();
		
		// Select profile child operations in the operations tree
		this.oOperationsTree.setSelected(aChildOperations, true, true);
		
		// Select operations from before the clear
		this.oOperationsTree.setSelected(aOperationIds, true, false);
	},
	
	_createNewProfile	: function()
	{
		alert('create new profile');
	}
});

