var Popup_Operation_Profile_Edit	= Class.create(Reflex_Popup,
{
	initialize	: function($super, bRenderMode, iProfileId, fnOnSave, aPrerequisites, aOperationIds)
	{
		$super(40);
		
		this.bRenderMode				= bRenderMode;
		this.iOperationTreeCount		= 0;
		this.hOperationProfileChildren	= [];
		this.fnOnSave					= fnOnSave;
		
		if (Number(iProfileId) > 0)
		{
			// Profile Id passed -- load permissions via JSON
			this.setTitle("Edit Permission Profile");
			this.oOperationProfile	= Operation_Profile.getForId(iProfileId, this.buildContent.bind(this));
		}
		else if (bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			// New Profile
			this.setTitle("Add Permission Profile");
			
			// Cache the prerequisites and child operations
			this.aOperationIds				= (aOperationIds ? aOperationIds : []);
			var oOperationProfile			= new Operation_Profile();
			oOperationProfile.oProperties	=	{
													aPrerequisites	: (aPrerequisites ? aPrerequisites : []),
													status_id		: 1, // STATUS_ACTIVE
													name			: '',
													description		: ''
												};
			
			// Create interface
			this.buildContent(oOperationProfile);
		}
		else
		{
			throw "Invalid Operation_Profile reference '" + iProfileId + "'";
		}
	},
	
	buildContent	: function(oOperationProfile)
	{
		this.oOperationProfile	= oOperationProfile;
		this.oControls			= this.oOperationProfile.getControls();
		
		// Build Content
		this._oPage	= 	$T.div({class: 'operation-profile-edit'},
							$T.div({class: 'section operation-profile-edit-details'},
								$T.div({class: 'section-header'},
									$T.div({class: 'section-header-title'},
										$T.img({src: '../admin/img/template/view.png', alt: '', title: 'Details'}),
										$T.span('Details')
									)
								),
								$T.div({class: 'section-content section-content-fitted'},
									$T.div(
										$T.table({class: 'input'},
											$T.tbody({class: 'operation-profile-edit-details'}
												// TR's added below
											)
										)
									)
								)
							),
							$T.div({class: 'section operation-profile-edit-profiles'},
								$T.div({class: 'section-header'},
									$T.div({class: 'section-header-title'},
										$T.img({src: '../admin/img/template/group_key.png', alt: '', title: 'Profiles'}),
										$T.span('Includes...')
									)
								),
								$T.div({class: 'section-content section-content-fitted'},
									this.buildContentOperationProfiles(),
									$T.div({class: 'operation-profile-edit-empty'},
										'There are no profiles selected'
									)
								)
							),
							$T.div({class: 'section operation-profile-edit-profiles-parents'},
									$T.div({class: 'section-header'},
										$T.div({class: 'section-header-title'},
											$T.img({src: '../admin/img/template/group_key.png', alt: '', title: 'Profiles'}),
											$T.span('Is a part of...')
										)
									),
									$T.div({class: 'section-content section-content-fitted'},
										this.buildContentParentOperationProfiles()
									)
								),
							$T.div({class: 'section operation-profile-edit-operations'},
								$T.div({class: 'section-header'},
									$T.div({class: 'section-header-title'},
										$T.img({src: '../admin/img/template/key.png', alt: '', title: 'Permission Overrides'}),
										$T.span('Permission Overrides')
									)
								),
								$T.div({class: 'section-content section-content-fitted'},
									this.buildContentOperations(),
									$T.div({class: 'operation-profile-edit-operations-empty'},
										'There are no permission overrides selected'
									)
								)
							)
						);
		
		var oDetailsTBody	= this._oPage.select('tbody.operation-profile-edit-details').first();
		for (var sFieldName in this.oControls)
		{
			oDetailsTBody.appendChild(this.oControls[sFieldName].generateInputTableRow().oElement);
		}
		
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
		
		// Bind event handlers
		this.oCancelEditButton.observe('click', this.setControlMode.bind(this, Control_Field.RENDER_MODE_VIEW));
		this.oCancelNewButton.observe('click', this.hide.bind(this, false));
		this.oCloseButton.observe('click', this.hide.bind(this, false));
		this.oSaveButton.observe('click', this._saveButtonClick.bind(this));
		this.oEditButton.observe('click', this.setControlMode.bind(this, Control_Field.RENDER_MODE_EDIT));
		
		// Initialise interface features
		this.setFooterButtons([this.oEditButton, this.oCloseButton], true);
		this.setControlMode(this.bRenderMode);
		
		// Update the Popup
		this.addCloseButton();
		this.setIcon("../admin/img/template/user_key.png");
		this.setContent(this._oPage);
		this.display();
		
		this.oLoading	= new Reflex_Popup.Loading('Getting Details...');
		this.oLoading.display();
	},
	
	buildContentOperationProfiles	: function()
	{
		// Set up data function for the tree
		var fnData	= Operation_Profile.getAllIndexed.bind(Operation_Profile);
		
		if (this.oOperationProfile.oProperties.id)
		{
			fnData	= Operation_Profile.getAllPrerequisitesAndNonRelatedIndexed.bind(Operation_Profile, this.oOperationProfile.oProperties.id);
		}
		
		// Create tree, given callback for when it has loaded all of the operations
		this.iOperationTreeCount++;
		this.oOperationProfilesTree	= 	new Operation_Tree(
											Operation_Tree.RENDER_OPERATION_PROFILE, 
											null,
											fnData,
											this._operationTreeReady.bind(this),
											this._operationProfileChange.bind(this)
										);
		
		return	$T.div(this.oOperationProfilesTree.getElement());
	},
	
	buildContentParentOperationProfiles	: function()
	{
		var fnData	= null;
		
		// Only get data for the parent profiles tree if editing a profile, not if creating
		if (this.oOperationProfile.oProperties.id)
		{
			fnData	= Operation_Profile.getAllDependantsIndexed.bind(Operation_Profile, this.oOperationProfile.oProperties.id),
			this.iOperationTreeCount++;
		}
		
		// Create tree, given callback for when it has loaded all of the operation profiles
		this.oParentOperationProfilesTree	= 	new Operation_Tree(
													Operation_Tree.RENDER_OPERATION_PROFILE, 
													null,
													fnData,
													this._operationTreeReady.bind(this)
												);
		this.oParentOperationProfilesTree.setEditable(false);
		
		return	$T.div(this.oParentOperationProfilesTree.getElement());
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
				this.setFooterButtons([this.oSaveButton, (this.oOperationProfile.oProperties.id) ? this.oCancelEditButton : this.oCancelNewButton], true);
				
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
		
		for (var sFieldName in this.oControls)
		{
			this.oControls[sFieldName].setRenderMode(bControlMode);
		}
		
		// Reset the Permissions Trees to saved values
		this._selectDefaultTreeValues();
	},
		
	_operationTreeReady	: function(oResponse)
	{
		if (typeof oResponse == 'undefined')
		{
			// Wait until both operation tree's are ready
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
			
			// aOperationIds already set if creating a new profile
			if (this.oOperationProfile.oProperties.id)
			{
				this.aOperationIds	= this.hOperationProfileChildren[this.oOperationProfile.oProperties.id];
			}
			
			// Set the tree values
			this._selectDefaultTreeValues();
			
			// Hide loading popup
			this.oLoading.hide();
			delete this.oLoading;
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
			
			// Make the AJAX request
			this.oOperationProfile.save(aOperationProfileIdsToSave, aOperationIds, this._save.bind(this));
		}
		else
		{
			this.hide();
			
			if (this.fnOnSave)
			{
				this.fnOnSave(oResponse.iId);
			}
		}
	},
	
	_selectDefaultTreeValues	: function()
	{
		// Clear trees 
		this.oOperationProfilesTree.deSelectAll(false);
		this.oOperationsTree.deSelectAll(true);
		
		// Select all parent profiles
		this.oParentOperationProfilesTree.selectAll(true);
		
		// Select operation profiles
		var aPrerequisites	= this.oOperationProfile.oProperties.aPrerequisites;
		this.oOperationProfilesTree.setSelected(aPrerequisites, true);
		
		// Build profile child operations array
		var aChildOperations		= [];
		var aOperationProfileIds	= this.oOperationProfilesTree.getSelected();
		var iProfileId				= null;
		
		for (var i = 0; i < aOperationProfileIds.length; i++)
		{
			iProfileId	= aOperationProfileIds[i];
			
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
		
		// Select specific operations children
		this.oOperationsTree.setSelected(this.aOperationIds, true);
		
		if (this.bRenderMode == Control_Field.RENDER_MODE_VIEW)
		{
			// Expand the operations tree
			this.oOperationsTree.oControl.expandAll();
		}
		
		this._checkForNoPermissions(null, null, null, true);
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
	
	_checkForNoPermissions	: function(bOnClose, fnOnYes, fnOnNo, bNoPopup)
	{
		var aOperationIds				= null;
		var aOperationProfileIds		= null;
		
		if (bOnClose)
		{
			aOperationProfileIds	= this.oOperationProfile.oProperties.aPrerequisites;
			aOperationIds			= this.aOperationIds;	
		}
		else
		{
			aOperationProfileIds	= this.oOperationProfilesTree.getSelected();
			aOperationIds			= this.oOperationsTree.getSelected();
		}
		
		var oProfilesEmpty	= this._oPage.select('div.operation-profile-edit-empty').first();
		if ((aOperationProfileIds.length == 0) && (this.bRenderMode == Control_Field.RENDER_MODE_VIEW) && this.oOperationProfilesTree._bLoaded)
		{
			oProfilesEmpty.show();
		}
		else
		{
			oProfilesEmpty.hide();
		}
		
		var oPermissionsEmpty	= this._oPage.select('div.operation-profile-edit-operations-empty').first();
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
						$T.div('You have not selected any permissions for this profile'),
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

