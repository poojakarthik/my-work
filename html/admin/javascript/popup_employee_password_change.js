var Popup_Employee_Password_Change	= Class.create(Reflex_Popup,
{
	initialize	: function($super, iEmployeeId)
	{
		$super(40);
		
		this.oEmployee	= new Employee(iEmployeeId);
		this.buildUI();
	},
	
	buildUI	: function()
	{
		// Get a hash of controls for editing the employees details
		this.oControls	= this.oEmployee.getPasswordControls();
		
		// Build Content
		this._oPage	= 	$T.div({class: 'employee-password-change'},
							$T.div({class: 'section'},
								$T.div({class: 'section-header'},
									$T.div({class: 'section-header-title'},
										$T.img({src: '../admin/img/template/view.png', alt: '', title: 'New Password'}),
										$T.span('New Password')
									)
								),
								$T.div({class: 'section-content section-content-fitted'},
									$T.div(
										$T.table({class: 'input'},
											$T.tbody(
												// TR's added below
											)
										)
									)
								),
								$T.div({class: 'section-footer'})
							)
						);
		
		// Sort out which fields to display
		var aFields	= ['PassWord', 'PassWordConfirm'];
		
		// Display fields
		var oCredentials	= this._oPage.select('tbody').first();
		var sFieldName		= null;
		var oControl		= null;
		
		for (var i = 0; i < aFields.length; i++)
		{
			sFieldName	= aFields[i];
			oControl	= this.oControls[sFieldName];
			
			if (oControl)
			{
				oCredentials.appendChild(oControl.generateInputTableRow().oElement);
				oControl.setRenderMode(Control_Field.RENDER_MODE_EDIT);
			}
		}
		
		this.oControls.PassWord.setDependant(this.oControls.PassWordConfirm);
		this.oControls.PassWordConfirm.setValidateFunction(this._passwordConfirm.bind(this));
		
		// Create buttons
		this.oSaveButton	= 	$T.button({class: 'icon-button'},
									$T.img({src: '../admin/img/template/tick.png', alt: '', title: 'Save'}),
									$T.span('Save')
								);
		this.oCancelButton	=	$T.button({class: 'icon-button'},
									$T.img({src: '../admin/img/template/delete.png', alt: '', title: 'Cancel'}),
									$T.span('Cancel')
								);
		
		// Bind event handlers
		this.oCancelButton.observe('click', this.hide.bind(this));
		this.oSaveButton.observe('click', this._save.bind(this));
		
		this.setFooterButtons([this.oSaveButton, this.oCancelButton], true);
		
		// Update the Popup
		this.setTitle("Change Employee Password");
		this.addCloseButton();
		this.setIcon("../admin/img/template/user_edit.png");
		this.setContent(this._oPage);
		this.display();		
		return true;
	},
	
	_passwordConfirm	: function()
	{
		return (this.oControls.PassWordConfirm.getElementValue() === this.oControls.PassWord.getElementValue())
	},
	
	_save	: function(event)
	{
		this.oEmployee.changePassword(this.hide.bind(this));
	}
});

