
var Popup_Employee_Details	= Class.create(Reflex_Popup,
{
	initialize	: function($super, bRenderMode, iEmployeeId, bDisableEditing, fnOnSave)
	{
		$super(40);
		
		this.bRenderMode				= bRenderMode;
		this.oOperationProfiles			= {};
		this.iOperationTreeReadyCount	= 0;
		this.hOperationProfileChildren	= [];
		this.bNewEmployee				= false;
		this.bDisableEditing			= bDisableEditing ? true : false;
		this.fnOnSave					= fnOnSave;
		
		if (Number(iEmployeeId) > 0)
		{
			// Employee Id passed -- load via JSON
			this.setTitle("Edit Employee");
			this.oEmployee	= Employee.getForId(iEmployeeId, this.buildContent.bind(this));
		}
		else if (bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			// New Employee
			this.setTitle("Add Employee");
			this.oEmployee		= new Employee();
			this.bNewEmployee	= true;
			this.buildContent();
		}
		else
		{
			throw "Invalid Employee reference '" + iEmployeeId + "'";
		}
	},
	
	buildContent	: function()
	{
		// Get a hash of controls for editing the employees details
		this.oControls	= this.oEmployee.getControls(this.bDisableEditing, this.bNewEmployee);
		
		// Build Content
		this._oPage	= 	$T.div({class: 'employee-details'},
							$T.div({class: 'section employee-details-credentials'},
								$T.div({class: 'section-header'},
									$T.div({class: 'section-header-title'},
										$T.img({src: '../admin/img/template/btn_unlocked.png', alt: '', title: 'Credentials'}),
										$T.span('Credentials')
									),
									$T.div({class: 'section-header-options'},
										$T.button({class: 'icon-button employee-details-edit-password'},
											$T.img({src: '../admin/img/template/key.png', alt: '', title: 'Change Password'}),
											$T.span('Change Password')
										)
									)
								),
								$T.div({class: 'section-content section-content-fitted'},
									$T.div(
										$T.table({class: 'input'},
											$T.tbody({class: 'employee-details-credentials'}
												// TR's added below
											)
										)
									)
								),
								$T.div({class: 'section-footer employee-details-buttons'})
							),
							$T.div({class: 'section'},
								$T.div({class: 'section-header'},
									$T.div({class: 'section-header-title'},
										$T.img({src: '../admin/img/template/view.png', alt: '', title: 'Details'}),
										$T.span('Details')
									),
									$T.div({class: 'section-header-options'},
										$T.button({class: 'icon-button employee-details-permissions'},
											$T.img({src: '../admin/img/template/user_key.png', alt: '', title: 'Manage Permissions'}),
											$T.span('Manage Permissions')
										)
									)
								),
								$T.div({class: 'section-content section-content-fitted'},
									$T.div(
										$T.table({class: 'input'},
											$T.tbody({class: 'employee-details-details'}
												// TR's added below
											)
										)
									)
								),
								$T.div({class: 'section-footer employee-details-buttons'})
							)
						);
		
		this.oManagePermissions	= this._oPage.select('button.employee-details-permissions').first();
		//this.oGodUserLabel		= this._oPage.select('div.employee-details-is-god').first();
		this.oManagePermissions.hide();
		//this.oGodUserLabel.hide();
		
		// Hide edit password button if a new employee
		var oEditPassword	= this._oPage.select('button.employee-details-edit-password').first();
		
		if (this.bNewEmployee)
		{
			oEditPassword.hide();
		}
		else
		{
			// Bind click event
			oEditPassword.observe('click', this._showEditPassword.bind(this));
		}
		
		this.buildContentDetails();
		
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
		var oPermissionsButton	= this._oPage.select('button.employee-details-permissions').first();
		oPermissionsButton.observe('click', this._managePermissions.bind(this, Control_Field.RENDER_MODE_VIEW));
		
		// Bind event handlers
		this.oCancelEditButton.observe('click', this.setControlMode.bind(this, Control_Field.RENDER_MODE_VIEW));
		this.oCancelNewButton.observe('click', this.hide.bind(this));
		this.oCloseButton.observe('click', this.hide.bind(this));
		this.oSaveButton.observe('click', this._save.bind(this));
		this.oEditButton.observe('click', this.setControlMode.bind(this, Control_Field.RENDER_MODE_EDIT));
		
		this.setControlMode(this.bRenderMode);
		
		// Update the Popup
		this.addCloseButton();
		this.setIcon("../admin/img/template/user_edit.png");
		this.setContent(this._oPage);
		this.display();		
		return true;
	},
	
	buildContentDetails	: function()
	{
		// Sort out which fields to display
		var aCredentialsFields	= ['UserName', 'PassWord', 'PassWordConfirm'];
		var aDetailsFields		= ['FirstName', 'LastName', 'DOB', 'Email', 'Extension', 'Phone', 'Mobile', 
		                   		   'user_role_id', 'ticketing_permission', 'Archived'];
		
		// Display fields
		var oCredentials	= this._oPage.select('tbody.employee-details-credentials').first();
		var oDetails		= this._oPage.select('tbody.employee-details-details').first();
		var sFieldName		= null;
		var oControl		= null;
		var oRow			= null;
		for (var i = 0; i < aCredentialsFields.length; i++)
		{
			sFieldName	= aCredentialsFields[i];
			oControl	= this.oControls[sFieldName];
			
			if (oControl)
			{
				oRow	= oControl.generateInputTableRow().oElement;
				
				if (sFieldName && this.oEmployee.oProperties.is_god)
				{
					oRow.select('td').last().appendChild(
						$T.div({class: 'employee-details-is-god'},
							$T.img({src: '../admin/img/template/lightning.png'}),
							'God User'
						)
					);
				}
				
				oCredentials.appendChild(oRow);
			}
		}
		
		for (var i = 0; i < aDetailsFields.length; i++)
		{	
			sFieldName	= aDetailsFields[i];
			oControl	= this.oControls[sFieldName];
			
			if (oControl)
			{
				oDetails.appendChild(oControl.generateInputTableRow().oElement);
			}
		}
		
		// Set Password Field Dependency
		if (this.bNewEmployee)
		{
			this.oControls.PassWord.setDependant(this.oControls.PassWordConfirm);
			this.oControls.PassWordConfirm.setValidateFunction(this._passwordConfirm.bind(this));
		}
	},
	
	_passwordConfirm	: function()
	{
		return (this.oControls.PassWordConfirm.getElementValue() === this.oControls.PassWord.getElementValue())
	},
	
	_save	: function(event)
	{
		this.oEmployee.save(this._saveComplete.bind(this));
	},
	
	_saveComplete	: function()
	{
		// Show permissions popup if a new employee
		if (this.bNewEmployee)
		{
			// Load the new employee then show the manage permissions popup
			this.oEmployee._load(this.oEmployee.oProperties.Id, this._managePermissions.bind(this, Control_Field.RENDER_MODE_EDIT));
		}
		
		this.hide();
		
		// Execute save callback
		if (this.fnOnSave)
		{
			this.fnOnSave(this.bNewEmployee);
		}
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
			return false;
		}
	},
	
	setControlMode	: function(bControlMode)
	{
		// Can't change control/render mode if editing self, no editing allowed
		if (this.bDisableEditing)
		{
			// Set to view mode
			bControlMode	= Control_Field.RENDER_MODE_VIEW;
		}
		
		if (this.bDisableEditing || this.oEmployee.oProperties.is_god || this.oEmployee.oProperties.is_logged_in_employee)
		{
			// Hide manage permissions button
			this.oManagePermissions.hide();
			
			/*if (this.oEmployee.oProperties.is_god)
			{
				// God user, show as such
				this.oGodUserLabel.show();
			}*/
		}
		
		switch (bControlMode)
		{
			case Control_Field.RENDER_MODE_EDIT:
				// Change footer buttons
				this.setFooterButtons([this.oSaveButton, (this.oEmployee.oProperties.Id) ? this.oCancelEditButton : this.oCancelNewButton], true);
				
				// Hide manage permissions button
				this.oManagePermissions.hide();
				break;
				
			case Control_Field.RENDER_MODE_VIEW:
				// Change footer buttons
				if (this.bDisableEditing)
				{
					this.setFooterButtons([this.oCloseButton], true);
				}
				else
				{
					this.setFooterButtons([this.oEditButton, this.oCloseButton], true);
				}
				
				if (!this.bNewEmployee && !this.bDisableEditing && !this.oEmployee.oProperties.is_god && !this.oEmployee.oProperties.is_logged_in_employee)
				{
					this.oManagePermissions.show();
				}
				break;
			
			default:
				throw "Invalid Control Mode '" + bControlMode + "'";
		}
		
		// Call setRenderMode(bControlMode) on all controls
		var aValidFields	= this.oEmployee.getValidProperties(this.bDisableEditing, this.bNewEmployee);
		
		for (var sFieldName in this.oControls)
		{
			if (aValidFields[sFieldName])
			{
				this.oControls[sFieldName].setRenderMode(bControlMode);
			}
			else
			{
				this.oControls[sFieldName].setRenderMode(Control_Field.RENDER_MODE_VIEW);
			}
		}
		
		this.bRenderMode	= bControlMode;
	},
		
	_managePermissions	: function(sRenderMode)
	{
		new Popup_Employee_Details_Permissions(sRenderMode, this.oEmployee.oProperties.Id);
	},
		
	_showEditPassword	: function()
	{
		if (!this.bNewEmployee)
		{
			new Popup_Employee_Password_Change(this.oEmployee.oProperties.Id);
		}
	}
});

