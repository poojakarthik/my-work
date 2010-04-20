var Popup_Employee_Details	= Class.create(Reflex_Popup,
{
	initialize	: function($super, bRenderMode, mEmployee, bDisplayOnLoad)
	{
		$super(40);
		
		this.bDisplayOnLoad				= (bDisplayOnLoad || bDisplayOnLoad === undefined) ? true : false;
		this.bRenderMode				= bRenderMode;
		this.hFieldTRs					= {};
		this.oOperationProfiles			= {};
		this.iOperationTreeReadyCount	= 0;
		this.hOperationProfileChildren	= [];
		
		if (mEmployee instanceof Employee)
		{
			// Employee object passed -- build immediately
			this.oEmployee	= mEmployee;
			this.buildContent();
		}
		else if (Number(mEmployee) > 0)
		{
			// Employee Id passed -- load via JSON
			this.oEmployee	= Employee.getForId(mEmployee, this.buildContent.bind(this));
		}
		else if (bRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			// New Employee
			this.oEmployee	= new Employee();
			this.buildContent();
		}
		else
		{
			throw "Invalid Employee reference '" + mEmployee + "'";
		}
	},
	
	buildContent	: function()
	{
		// Get a hash of controls for editing the employees details
		this.oControls	= this.oEmployee.getControls();
		
		// Build Content
		this._oPage	= 	$T.div({class: 'employee-details'},
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
									this.buildContentDetails()
								),
								$T.div({class: 'section-footer employee-details-buttons'})
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
		var oPermissionsButton	= this._oPage.select('button.employee-details-permissions').first();
		oPermissionsButton.observe('click', this._managePermissions.bind(this));
		
		// Bind event handlers
		this.oCancelEditButton.observe('click', this.setControlMode.bind(this, Control_Field.RENDER_MODE_VIEW));
		this.oCancelNewButton.observe('click', this.hide.bind(this));
		this.oCloseButton.observe('click', this.hide.bind(this));
		this.oSaveButton.observe('click', this._save.bind(this));
		this.oEditButton.observe('click', this.setControlMode.bind(this, Control_Field.RENDER_MODE_EDIT));
			
		this.setFooterButtons([this.oEditButton, this.oCloseButton], true);
		this.setControlMode(this.bRenderMode);
		
		// Update the Popup
		this.setTitle("Employee");
		this.addCloseButton();
		this.setIcon("../admin/img/template/user_edit.png");
		this.setContent(this._oPage);
		
		if (this.bDisplayOnLoad)
		{
			this.display();
		}
		
		return true;
	},
	
	buildContentDetails	: function()
	{
		var oTabPage	= 	$T.div(
								$T.table({class: 'input'},
									$T.tbody(
										// TR's added below
									)
								)
							);
		
		// Add each desired control to the table, cache the rows for each field
		var oTBody		= oTabPage.select('table > tbody').first();
		var sFieldName	= null;
		var oTR		 	= null;
		
		for (var i = 0; i < Popup_Employee_Details.FIELDS.length; i++)
		{
			sFieldName	= Popup_Employee_Details.FIELDS[i];
			oTR			= this.oControls[sFieldName].generateInputTableRow().oElement;
			oTBody.appendChild(oTR);
			this.hFieldTRs[sFieldName]	= oTR;
		}
		
		// Set Password Field Dependency
		this.oControls.PassWord.setDependant(this.oControls.PassWordConfirm);
		this.oControls.PassWordConfirm.setValidateFunction(this._passwordConfirm.bind(this));
		return oTabPage;
	},
	
	_passwordConfirm	: function()
	{
		return (this.oControls.PassWordConfirm.getElementValue() === this.oControls.PassWord.getElementValue())
	},
	
	_save	: function(event, oEmployee)
	{
		if (typeof oEmployee == 'undefined')
		{
			this.oEmployee.save(this._save.bind(this));
		}
		else
		{
			this.hide();
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
				this.setFooterButtons([this.oSaveButton, (this.oEmployee.oProperties.Id) ? this.oCancelEditButton : this.oCancelNewButton], true);
				
				// Show "Confirm Password"
				this.hFieldTRs['PassWordConfirm'].style.display	= 'table-row';
				break;
				
			case Control_Field.RENDER_MODE_VIEW:
				// Change footer buttons
				this.setFooterButtons([this.oEditButton, this.oCloseButton], true);
				
				// Hide "Confirm Password"
				this.hFieldTRs['PassWordConfirm'].style.display	= 'none';
				break;
			
			default:
				throw "Invalid Control Mode '" + bControlMode + "'";
		}
		
		// Call setRenderMode(bControlMode) on all controls
		for (var i = 0; i < Popup_Employee_Details.FIELDS.length; i++)
		{
			this.oControls[Popup_Employee_Details.FIELDS[i]].setRenderMode(bControlMode);
		}
		
		this.bRenderMode	= bControlMode;
	},
		
	// Override
	setFooterButtons	: function($super, aButtons)
	{
		var oSectionFooter	= this._oPage.select('div.employee-details-buttons').first();
		
		if (oSectionFooter)
		{
			// Remove existing
			var aCurrentButtons	= oSectionFooter.select('button.icon-button');
			
			for (var i = 0; i < aCurrentButtons.length; i++)
			{
				aCurrentButtons[i].remove();
			}
			
			// Add new
			for (var i = 0; i < aButtons.length; i++)
			{
				oSectionFooter.appendChild(aButtons[i]);
			}
		}
	},
	
	_managePermissions	: function()
	{
		new Popup_Employee_Details_Permissions(Control_Field.RENDER_MODE_VIEW, this.oEmployee.oProperties.Id);
	}
});

Popup_Employee_Details.FIELDS	=	[
                         	 	 	'UserName', 
                         	 	 	'FirstName', 
                         	 	 	'LastName', 
                         	 	 	'DOB', 
                         	 	 	'Email', 
                         	 	 	'Extension', 
                         	 	 	'Phone', 
                         	 	 	'Mobile', 
                         	 	 	'PassWord', 
                         	 	 	'PassWordConfirm', 
                         	 	 	'user_role_id', 
                         	 	 	'Archived',
                         	 	 	'ticketing_permission'
                         	 	 ];

