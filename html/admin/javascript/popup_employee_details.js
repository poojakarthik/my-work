var Popup_Employee_Details	= Class.create(Reflex_Popup,
{
	initialize	: function($super, bRenderMode, mEmployee, bDisplayOnLoad)
	{
		$super(40);
		
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
		this.oCancelNewButton.observe('click', this.hide.bind(this));
		this.oCloseButton.observe('click', this.hide.bind(this));
		this.oSaveButton.observe('click', this._save.bind(this));
		this.oEditButton.observe('click', this.setControlMode.bind(this, Control_Field.RENDER_MODE_EDIT));
		
		this.bDisplayOnLoad				= (bDisplayOnLoad || bDisplayOnLoad === undefined) ? true : false;
		this.bInitialRenderMode			= bRenderMode;
		this.hFieldTRs					= {};
		this.oOperationProfiles			= {};
		this.iOperationTreeReadyCount	= 0;
		
		this.hOperationProfileChildren	= [];
		this.aOperationIds				= [];
		this.aOperationProfileIds		= [];
		
		this.setFooterButtons([this.oEditButton, this.oCloseButton], true);
		
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
		this._oPage	= $T.div();
		
		// Create a Tab Group
		this.oControlTabGroup	= new Control_Tab_Group(this._oPage, false, true);
		
		//--------------------------------------------------------------------//
		// Create Tabs
		//--------------------------------------------------------------------//
		// Details Tab
		this.oControlTabGroup.addTab(
			'Details', 
			new Control_Tab(
				'Details', 
				this.buildContentDetails(), 
				'../admin/img/template/view.png'
			)
		);
		
		// Operation Profiles Tab
		this.oControlTabGroup.addTab(
			'Operation_Profiles', 
			new Control_Tab(
				'Operation Profiles', 
				this.buildContentOperationProfiles(), 
				'../admin/img/template/key.png'
			)
		);
		
		// Operations Tab
		this.oControlTabGroup.addTab(
			'Operations', 
			new Control_Tab(
				'Operations', 
				this.buildContentOperations(), 
				'../admin/img/template/key.png'
			)
		);
		//--------------------------------------------------------------------//
		
		this.setControlMode(this.bInitialRenderMode);
		
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
	
	buildContentOperationProfiles	: function()
	{
		// Create tree, given callback for when it has loaded all of the operations
		this.oOperationProfilesTree	= 	new Operation_Tree(
											Operation_Tree.RENDER_HEIRARCHY_INCLUDES, 
											null,
											Operation_Profile.getAllIndexed.bind(Operation_Profile),
											this._operationTreeReady.bind(this)
										);
		return	$T.div(
					this.oOperationProfilesTree.getElement()
				);
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
		return	$T.div(
					this.oOperationsTree.getElement()
				);
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
			
			// Retrieve Employee Permissions
			this.oEmployee.getPermissions(this._loadPermissions.bind(this));
		}
	},
	
	_save	: function()
	{
		alert('save');
	},
	
	_loadPermissions	: function(aOperationIds, aOperationProfileIds)
	{
		//aOperationProfileIds	= [1];
		aOperationProfileIds	= [2];
		
		this.aOperationIds			= aOperationIds;
		this.aOperationProfileIds	= aOperationProfileIds;
		this._selectDefaultTreeValues();
	},
	
	_selectDefaultTreeValues	: function()
	{
		// Select operation profiles
		this.oOperationProfilesTree.setSelected(this.aOperationProfileIds, true);
		
		// Build profile child operations array
		var aChildOperations	= [];
		var iProfileId			= null;
		
		for (var i = 0; i < this.aOperationProfileIds.length; i++)
		{
			iProfileId	= this.aOperationProfileIds[i];
			
			for (var iOperationId in this.hOperationProfileChildren[iProfileId])
			{
				aChildOperations.push(this.hOperationProfileChildren[iProfileId][iOperationId]);
			}
		}
		
		// Select profile child operations
		this.oOperationsTree.setSelected(aChildOperations, true, true);
		
		// Select specific operations
		this.oOperationsTree.setSelected(this.aOperationIds, true);
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
				
				// Send Tree Grids into Edit mode
				this.oOperationsTree.setEditable(true);
				this.oOperationProfilesTree.setEditable(true);
				break;
				
			case Control_Field.RENDER_MODE_VIEW:
				// Change footer buttons
				this.setFooterButtons([this.oEditButton, this.oCloseButton], true);
				
				// Hide "Confirm Password"
				this.hFieldTRs['PassWordConfirm'].style.display	= 'none';
				
				// Send Tree Grids into Read-Only mode
				this.oOperationsTree.setEditable(false);
				this.oOperationProfilesTree.setEditable(false);
				break;
			
			default:
				throw "Invalid Control Mode '" + bControlMode + "'";
		}
		
		// Reset the Permissions Trees to saved values
		this._selectDefaultTreeValues();
		
		// Call setRenderMode(bControlMode) on all controls
		for (var i = 0; i < Popup_Employee_Details.FIELDS.length; i++)
		{
			this.oControls[Popup_Employee_Details.FIELDS[i]].setRenderMode(bControlMode);
		}
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
                         	 	 	'Archived'
                         	 	 ];

