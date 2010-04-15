var Popup_Employee_Details	= Class.create(Reflex_Popup,
{
	initialize	: function($super, bRenderMode, mEmployee, bDisplayOnLoad)
	{
		$super(40);
		
		this.oEditButton		= 	$T.button({class: 'icon-button'},
											$T.img({src: '../admin/img/template/user_edit.png', alt: '', title: 'Edit'}),
											$T.span('Edit')
										);
		this.oCloseButton		= 	$T.button('Close');
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
		
		this.bDisplayOnLoad		= (bDisplayOnLoad || bDisplayOnLoad === undefined) ? true : false;
		this.bInitialRenderMode	= bRenderMode;
		this.hFieldTRs			= {};
		this.oOperationProfiles	= {};
		this.oOperations		= new Operation_Tree(Operation_Tree.RENDER_HEIRARCHY_GROUPED);
		
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
		this.aTabs	= {};
		
		// Details Tab
		this.aTabs.Details	= this.buildContentDetails();
		this.oControlTabGroup.addTab('Details', new Control_Tab('Details', this.aTabs.Details, '../admin/img/template/view.png'));
		
		// Permissions Tab
		this.aTabs.Permissions	= this.buildContentPermissions();
		this.oControlTabGroup.addTab('Permissions', new Control_Tab('Permissions', this.aTabs.Permissions, '../admin/img/template/key.png'));
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
	
	buildContentPermissions	: function()
	{
		var oTabPage	= 	$T.div(
								this.oOperations.getElement()
							);
		
		// Retrieve Employee Permissions
		this.oEmployee.getPermissions(this._loadPermissions.bind(this));
		
		return oTabPage;
		
		/*
		// Profiles Tree Grid
		//--------------------------------------------------------------------//
		// Create
		oTabPage.oProfiles							= {};
		oTabPage.oProfiles.oControl					= new Control_Tree_Grid();
		oTabPage.oProfiles.domElement				= oTabPage.oProfiles.oControl.getElement();
		oTabPage.oProfiles.oControl.getTable().addClassName('permissions');
		oTabPage.domElement.appendChild(oTabPage.oProfiles.domElement);
		
		// Set Columns
		oTabPage.oProfiles.oColumns									= {};
		oTabPage.oProfiles.oColumns[Control_Tree_Grid.COLUMN_CHECK]	= {};
		oTabPage.oProfiles.oColumns[Control_Tree_Grid.COLUMN_LABEL]	= {};
		oTabPage.oProfiles.oControl.setColumns(oTabPage.oProfiles.oColumns);
		
		// Set DataTypes
		oTabPage.oProfiles.oControl.addDataType(Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION_PROFILE.sName, Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION_PROFILE.sDescription, Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION_PROFILE.sIconSource);
		oTabPage.oProfiles.oControl.addDataType(Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION.sName, Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION.sDescription, Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION.sIconSource);
		//--------------------------------------------------------------------//
		*/
	},
	
	_save	: function()
	{
		alert('save');
	},
	
	_loadPermissions	: function(aOperationIds, aOperationProfileIds)
	{
		this.oEmployee.aOperationIds		= aOperationIds;
		this.oEmployee.aOperationProfileIds	= aOperationProfileIds;
		
		// Update the Trees
		this.oOperations.setSelected(this.oEmployee.aOperationIds);
	},
	
	_populatePermissionsTrees	: function(oOperations, oOperationProfiles)
	{
		if (oOperations && oOperationProfiles)
		{
			this.oOperations		= oOperations;
			this.oOperationProfiles	= oOperationProfiles;
			
			// Populate Profiles Grid
			//----------------------------------------------------------------//
			oOperationProfiles	= jQuery.json.arrayAsObject(oOperationProfiles);
			Operation_Profile.prepareForTreeGrid(oOperationProfiles);
			for (iProfileId in oOperationProfiles)
			{
				var oNode										= {};
				oNode.oControl									= this.operationProfileToTree(oOperationProfiles[iProfileId]);
				oNode.oContent									= oNode.oControl.getContent();
				oNode.oContent[Control_Tree_Grid.COLUMN_CHECK]	= {bChecked: this.oOperationProfiles[iProfileId].bEmployeeHasPermission};
				oNode.oControl.setContent(oNode.oContent);
				this.aTabs.Permissions.oProfiles.oControl.appendChild(oNode.oControl);
			}
			this.aTabs.Permissions.oProfiles.oControl.render();
			//----------------------------------------------------------------//
			
			// Populate Operations Grid
			//----------------------------------------------------------------//
			oOperations	= jQuery.json.arrayAsObject(oOperations);
			Operation.prepareForTreeGrid(oOperations);
			for (iOperationId in oOperations)
			{
				// Add the top-level Operations (this will cascade down)
				if (!oOperations[iOperationId].aPrerequisites || !oOperations[iOperationId].aPrerequisites.length)
				{
					var oNode										= {};
					oNode.oControl									= this.operationToDependencyTree(oOperations[iOperationId]);
					oNode.oContent									= oNode.oControl.getContent();
					oNode.oControl.setContent(oNode.oContent);
					this.aTabs.Permissions.oOperations.oControl.appendChild(oNode.oControl);
				}
			}
			this.aTabs.Permissions.oOperations.oControl.render();
			//----------------------------------------------------------------//
		}
		else
		{
			// Get Permissions
			this.oEmployee.getPermissions(this._populatePermissionsTrees.bind(this), true);
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
		// Reset the Permissions Trees to saved values
		this.oOperations.setSelected(this.oEmployee.aOperationIds);
		
		var oTreeGridColumns	= {};
		switch (bControlMode)
		{
			case Control_Field.RENDER_MODE_EDIT:
				// Change footer buttons
				this.setFooterButtons([this.oSaveButton, (this.oEmployee.oProperties.Id) ? this.oCancelEditButton : this.oCancelNewButton], true);
				
				// Show "Confirm Password"
				this.hFieldTRs['PassWordConfirm'].style.display	= 'table-row';
				
				// Send Tree Grids into Edit mode
				this.oOperations.setEditable(true);
				break;
				
			case Control_Field.RENDER_MODE_VIEW:
				// Change footer buttons
				this.setFooterButtons([this.oEditButton, this.oCloseButton], true);
				
				// Hide "Confirm Password"
				this.hFieldTRs['PassWordConfirm'].style.display	= 'none';
				
				// Send Tree Grids into Read-Only mode
				this.oOperations.setEditable(false);
				break;
			
			default:
				throw "Invalid Control Mode '" + bControlMode + "'";
		}
		
		// Call setRenderMode(bControlMode) on all controls
		for (var i = 0; i < Popup_Employee_Details.FIELDS.length; i++)
		{
			this.oControls[Popup_Employee_Details.FIELDS[i]].setRenderMode(bControlMode);
		}
	},
	
	onOperationProfileCheck	: function(oTreeGridNode)
	{
		var iValue	= oTreeGridNode.getValue();
		if (!this.oOperationProfiles || iValue === undefined || !this.oOperationProfiles[iValue])
		{
			throw "Unknown Operation Profile '"+iValue+"'";
		}
		this.updateOperationProfileSelected(iValue, oTreeGridNode.isSelected());
	},
	
	updateOperationProfileSelected	: function(iOperationProfile, bSelected)
	{
		// Update Cache
		this.oOperationProfiles[iOperationProfile].bEmployeeHasPermission	= bSelected;
		
		// Cascade update to all Node Instances
		for (var i = 0; i < this.oOperationProfiles[iOperationProfile].aInstances.length; i++)
		{
			this.oOperationProfiles[iOperationProfile].aInstances[i].setSelected(this.oOperationProfiles[iOperationProfile].bEmployeeHasPermission, true);
		}
		
		// Update prerequisites
		for (var i = 0; i < this.oOperationProfiles[iOperationProfile].aPrerequisites.length; i++)
		{
			if (this.oOperationProfiles[iOperationProfile].bEmployeeHasPermission && !this.oOperationProfiles[this.oOperationProfiles[iOperationProfile].aPrerequisites[i]].bEmployeeHasPermission)
			{
				this.updateOperationSelected(this.oOperationProfiles[iOperationProfile].aPrerequisites[i], true);
			}
		}
		
		// Update dependants
		for (var i = 0; i < this.oOperationProfiles[iOperationProfile].aDependants.length; i++)
		{
			if (!this.oOperationProfiles[iOperationProfile].bEmployeeHasPermission && this.oOperationProfiles[this.oOperationProfiles[iOperationProfile].aDependants[i]].bEmployeeHasPermission)
			{
				this.updateOperationSelected(this.oOperationProfiles[iOperationProfile].aDependants[i], false);
			}
		}
	},
	
	operationToDependencyTree	: function(oOperation)
	{
		//alert("Adding Operation '"+oOperation.name+"'");
		
		var oContent								= {};
		oContent[Control_Tree_Grid.COLUMN_LABEL]	= oOperation.name;
		oContent[Control_Tree_Grid.COLUMN_VALUE]	= oOperation.id;
		oContent[Control_Tree_Grid.COLUMN_CHECK]	= {mValue: oOperation.id, bChecked: oOperation.bEmployeeHasPermission};
		
		var oControlGridNodeData	= new Control_Tree_Grid_Node_Data(oContent, Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION.sName);
		oOperation.aInstances.push(oControlGridNodeData);
		
		if (oOperation.aDependants)
		{
			for (var i = 0; i < oOperation.aDependants.length; i++)
			{
				oControlGridNodeData.appendChild(this.operationToDependencyTree(this.oOperations[oOperation.aDependants[i]]));
			}
		}
		
		return oControlGridNodeData;
	},

	operationProfileToTree	: function(oOperationProfile)
	{
		//alert("Adding Operation Profile '"+oOperationProfile.name+"'");
		
		var oContent								= {};
		oContent[Control_Tree_Grid.COLUMN_LABEL]	= oOperationProfile.name;
		oContent[Control_Tree_Grid.COLUMN_VALUE]	= oOperationProfile.id;
		
		var oControlGridNodeData	= new Control_Tree_Grid_Node_Data(oContent, Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION_PROFILE.sName);
		oOperationProfile.aInstances.push(oControlGridNodeData);
		
		// Sub-Profiles
		if (oOperationProfile.aOperationProfiles)
		{
			for (var i = 0; i < oOperationProfile.aOperationProfiles.length; i++)
			{
				oControlGridNodeData.appendChild(this.operationProfileToTree(this.oOperationProfiles[oOperationProfile.aOperationProfiles[i]]));
			}
		}
		else
		{
			//alert(oOperationProfile.name + ' has no sub-Profiles');
		}
		
		// Sub-Operations
		if (oOperationProfile.aOperations)
		{
			for (var i = 0; i < oOperationProfile.aOperations.length; i++)
			{
				oControlGridNodeData.appendChild(this.operationToTree(this.oOperations[oOperationProfile.aOperations[i]]));
			}
		}
		else
		{
			//alert(oOperationProfile.name + ' has no sub-Operations');
		}
		
		return oControlGridNodeData;
	},

	operationToTree	: function(oOperation)
	{
		//alert("Adding Operation '"+oOperation.name+"'");
		
		var oContent								= {};
		oContent[Control_Tree_Grid.COLUMN_LABEL]	= oOperation.name;
		oContent[Control_Tree_Grid.COLUMN_VALUE]	= oOperation.id;
		
		var oControlGridNodeData	= new Control_Tree_Grid_Node_Data(oContent, Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION.sName);
		
		// We don't really need to register this as an Instance, because it's only there for informational purposes
		oOperation.aInstances.push(oControlGridNodeData);
		
		return oControlGridNodeData;
	}
});

Popup_Employee_Details.operationToTreeGridNode	= function(oOperation, bWithDependants)
{
	bWithDependants	= bWithDependants ? true : false;
	
	var oContent								= {};
	oContent[Control_Tree_Grid.COLUMN_LABEL]	= oOperation.name;
	oContent[Control_Tree_Grid.COLUMN_VALUE]	= oOperation.id;
	
	if (bWithDependants)
	{
		oContent[Control_Tree_Grid.COLUMN_CHECK]	= {mValue: oOperation.id, bChecked: oOperation.bEmployeeHasPermission};
	}
	
	var oControlGridNodeData	= new Control_Tree_Grid_Node_Data(oContent, Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION.sName);
	oOperation.aInstances.push(oControlGridNodeData);
	
	if (bWithDependants && oOperation.oDependants)
	{
		oOperation.oDependants	= jQuery.json.arrayAsObject(oOperation.oDependants);
		for (iOperationId in oOperation.oDependants)
		{
			oControlGridNodeData.appendChild(Popup_Employee_Details.operationToTreeGridNode(oOperation.oDependants[iOperationId], bWithDependants));
		}
	}
	
	return oControlGridNodeData;
};

Popup_Employee_Details.operationProfileToTreeGridNode	= function(oOperationProfile)
{
	//alert("Adding Operation Profile '"+oOperationProfile.name+"'");
	
	var oContent								= {};
	oContent[Control_Tree_Grid.COLUMN_LABEL]	= oOperationProfile.name;
	oContent[Control_Tree_Grid.COLUMN_VALUE]	= oOperationProfile.id;
	
	var oControlGridNodeData	= new Control_Tree_Grid_Node_Data(oContent, Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION_PROFILE.sName);
	//oOperationProfile.aInstances.push(oControlGridNodeData);
	
	// Sub-Profiles
	if (oOperationProfile.oOperationProfiles)
	{
		oOperationProfile.oOperationProfiles	= jQuery.json.arrayAsObject(oOperationProfile.oOperationProfiles);
		for (iProfileId in oOperationProfile.oOperationProfiles)
		{
			oControlGridNodeData.appendChild(Popup_Employee_Details.operationProfileToTreeGridNode(oOperationProfile.oOperationProfiles[iProfileId]));
		}
	}
	else
	{
		//alert(oOperationProfile.name + ' has no sub-Profiles');
	}
	
	// Sub-Operations
	if (oOperationProfile.oOperations)
	{
		oOperationProfile.oOperations	= jQuery.json.arrayAsObject(oOperationProfile.oOperations);
		for (iOperationId in oOperationProfile.oOperations)
		{
			oControlGridNodeData.appendChild(Popup_Employee_Details.operationToTreeGridNode(oOperationProfile.oOperations[iOperationId]));
		}
	}
	else
	{
		//alert(oOperationProfile.name + ' has no sub-Operations');
	}
	
	return oControlGridNodeData;
};

Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION					= {};
Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION.sName			= 'operation';
Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION.sDescription	= 'Operation';
Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION.sIconSource		= '../admin/img/template/operation.png';

Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION_PROFILE					= {};
Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION_PROFILE.sName			= 'operation_profile';
Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION_PROFILE.sDescription	= 'Operation Profile';
Popup_Employee_Details.TREE_GRID_DATATYPE_OPERATION_PROFILE.sIconSource		= '../admin/img/template/operation_profile.png';

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

