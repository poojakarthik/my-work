var Popup_Employee	= Class.create(Reflex_Popup,
{
	initialize	: function($super, bolRenderMode, mixEmployee, bolDisplayOnLoad)
	{
		$super(40);
		this.setTitle("Employee");
		this.addCloseButton();
		this.setIcon("../admin/img/template/user_edit.png");
		
		this.domEditButton				= document.createElement('button');
		this.domEditButton.innerHTML	= "<img class='icon' src='../admin/img/template/user_edit.png' alt='' />Edit";
		
		this.domCloseButton				= document.createElement('button');
		this.domCloseButton.innerHTML	= "Close";
		
		this.domSaveButton				= document.createElement('button');
		this.domSaveButton.innerHTML	= "<img class='icon' src='../admin/img/template/tick.png' alt='' />Save";
		
		this.domCancelEditButton			= document.createElement('button');
		this.domCancelEditButton.innerHTML	= "<img class='icon' src='../admin/img/template/delete.png' alt='' />Cancel";
		
		this.domCancelNewButton				= document.createElement('button');
		this.domCancelNewButton.innerHTML	= "<img class='icon' src='../admin/img/template/delete.png' alt='' />Cancel";
		
		this.addEventListeners();
		
		this.setFooterButtons([this.domEditButton, this.domCloseButton], true);
		
		this.bolDisplayOnLoad	= (bolDisplayOnLoad || bolDisplayOnLoad === undefined) ? true : false;
		
		this.bolInitialRenderMode	= bolRenderMode;
		
		this.oOperations		= {};
		this.oOperationProfiles	= {};
		
		if (mixEmployee instanceof Employee)
		{
			// Employee object passed -- build immediately
			this.objEmployee	= mixEmployee;
			this.buildContent();
		}
		else if (Number(mixEmployee) > 0)
		{
			// Employee Id passed -- load via JSON
			//alert("Loading Employee with Id '" + mixEmployee + "'");
			this.objEmployee	= Employee.getForId(mixEmployee, this.buildContent.bind(this));
		}
		else if (bolRenderMode == Control_Field.RENDER_MODE_EDIT)
		{
			// New Employee
			this.objEmployee	= new Employee();
			this.buildContent();
		}
		else
		{
			throw "Invalid Employee reference '" + mixEmployee + "'";
			//this.buildContent();
			//throw "mixEmployee is not an Employee Id or Employee Object!";
		}
	},
	
	buildContent	: function()
	{		
		// Build Content
		this._objPage			= {};
		this._objPage.domElement	= document.createElement('div');
		
		// Create a Tab Group
		this.objControlTabGroup	= new Control_Tab_Group(this._objPage.domElement, false, true);
		
		//--------------------------------------------------------------------//
		// Create Tabs
		//--------------------------------------------------------------------//
		this.arrTabs	= {};
		
		// Details Tab
		this.arrTabs.Details	= this.buildContentDetails();
		this.objControlTabGroup.addTab('Details', new Control_Tab('Details', this.arrTabs.Details.domElement, '../admin/img/template/view.png'));
		
		// Permissions Tab
		this.arrTabs.Permissions	= this.buildContentPermissions();
		this.objControlTabGroup.addTab('Permissions', new Control_Tab('Permissions', this.arrTabs.Permissions.domElement, '../admin/img/template/key.png'));
		//--------------------------------------------------------------------//
		
		this.setControlMode(this.bolInitialRenderMode);
		
		// Update the Popup
		this.setContent(this._objPage.domElement);
		if (this.bolDisplayOnLoad)
		{
			this.display();
		}
		
		return true;
	},
	
	buildContentDetails	: function()
	{
		var objTabPage					= {};
		objTabPage.domElement			= document.createElement('div');
		//objTabPage.domElement.innerHTML	= '[ Details ]';
		
		// Table
		objTabPage.table						= {};
		objTabPage.table.domElement				= document.createElement('table');
		objTabPage.table.domElement.className	= 'input';
		objTabPage.domElement.appendChild(objTabPage.table.domElement);
		
		// Table Body
		objTabPage.table.tbody				= {};
		objTabPage.table.tbody.domElement	= document.createElement('tbody');
		objTabPage.table.domElement.appendChild(objTabPage.table.tbody.domElement);
		
		//----------------------------- CONTENTS -----------------------------//
		
		var objControls	= this.objEmployee.getControls();
		
		// Username
		objTabPage.table.tbody.UserName					= {};
		objTabPage.table.tbody.UserName.objControl		= objControls.UserName;
		objTabPage.table.tbody.UserName.tr				= objControls.UserName.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.UserName.tr.domElement);
		
		// First Name
		objTabPage.table.tbody.FirstName				= {};
		objTabPage.table.tbody.FirstName.objControl		= objControls.FirstName;
		objTabPage.table.tbody.FirstName.tr				= objControls.FirstName.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.FirstName.tr.domElement);
		
		// Last Name
		objTabPage.table.tbody.LastName					= {};
		objTabPage.table.tbody.LastName.objControl		= objControls.LastName;
		objTabPage.table.tbody.LastName.tr				= objControls.LastName.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.LastName.tr.domElement);
		
		// Date of Birth
		objTabPage.table.tbody.DOB					= {};
		objTabPage.table.tbody.DOB.objControl		= objControls.DOB;
		objTabPage.table.tbody.DOB.tr				= objControls.DOB.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.DOB.tr.domElement);
		
		// Email
		objTabPage.table.tbody.Email				= {};
		objTabPage.table.tbody.Email.objControl		= objControls.Email;
		objTabPage.table.tbody.Email.tr				= objControls.Email.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.Email.tr.domElement);
		
		// Extension
		objTabPage.table.tbody.Extension				= {};
		objTabPage.table.tbody.Extension.objControl		= objControls.Extension;
		objTabPage.table.tbody.Extension.tr				= objControls.Extension.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.Extension.tr.domElement);
		
		// Phone
		objTabPage.table.tbody.Phone				= {};
		objTabPage.table.tbody.Phone.objControl		= objControls.Phone;
		objTabPage.table.tbody.Phone.tr				= objControls.Phone.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.Phone.tr.domElement);
		
		// Mobile
		objTabPage.table.tbody.Mobile					= {};
		objTabPage.table.tbody.Mobile.objControl		= objControls.Mobile;
		objTabPage.table.tbody.Mobile.tr				= objControls.Mobile.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.Mobile.tr.domElement);
		
		// Password
		objTabPage.table.tbody.PassWord				= {};
		objTabPage.table.tbody.PassWord.objControl	= objControls.PassWord;
		objTabPage.table.tbody.PassWord.tr			= objControls.PassWord.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.PassWord.tr.domElement);
		
		// Password Confirm
		objTabPage.table.tbody.PassWordConfirm				= {};
		objTabPage.table.tbody.PassWordConfirm.objControl	= objControls.PassWordConfirm;
		objTabPage.table.tbody.PassWordConfirm.tr			= objControls.PassWordConfirm.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.PassWordConfirm.tr.domElement);
		
		// Role
		objTabPage.table.tbody.user_role_id					= {};
		objTabPage.table.tbody.user_role_id.objControl		= objControls.user_role_id;
		objTabPage.table.tbody.user_role_id.tr				= objControls.user_role_id.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.user_role_id.tr.domElement);
		
		// Archived
		objTabPage.table.tbody.Archived				= {};
		objTabPage.table.tbody.Archived.objControl	= objControls.Archived;
		objTabPage.table.tbody.Archived.tr			= objControls.Archived.generateInputTableRow();
		objTabPage.table.tbody.domElement.appendChild(objTabPage.table.tbody.Archived.tr.domElement);
		
		//--------------------------------------------------------------------//
		
		// Set Password Field Dependency
		objControls.PassWord.setDependant(objControls.PassWordConfirm);
		objControls.PassWordConfirm.setValidateFunction(this._passwordConfirm.bind(this));
		
		return objTabPage;
	},
	
	_passwordConfirm	: function()
	{
		//alert(this.arrTabs.Details.table.tbody.PassWordConfirm.objControl.getElementValue() + " === " + this.arrTabs.Details.table.tbody.PassWord.objControl.getElementValue());
		return (this.arrTabs.Details.table.tbody.PassWordConfirm.objControl.getElementValue() === this.arrTabs.Details.table.tbody.PassWord.objControl.getElementValue());
	},
	
	buildContentPermissions	: function()
	{
		var oTabPage	= {};
		oTabPage.domElement				= document.createElement('div');
		
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
		oTabPage.oProfiles.oControl.addDataType(Popup_Employee.TREE_GRID_DATATYPE_OPERATION_PROFILE.sName, Popup_Employee.TREE_GRID_DATATYPE_OPERATION_PROFILE.sDescription, Popup_Employee.TREE_GRID_DATATYPE_OPERATION_PROFILE.sIconSource);
		oTabPage.oProfiles.oControl.addDataType(Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sName, Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sDescription, Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sIconSource);
		//--------------------------------------------------------------------//
		
		// Operations Tree Grid
		//--------------------------------------------------------------------//
		// Create
		oTabPage.oOperations						= {};
		oTabPage.oOperations.oControl				= new Control_Tree_Grid();
		oTabPage.oOperations.domElement				= oTabPage.oOperations.oControl.getElement();
		oTabPage.oOperations.oControl.getTable().addClassName('permissions');
		oTabPage.domElement.appendChild(oTabPage.oOperations.domElement);
		
		// Set Columns
		oTabPage.oOperations.oColumns									= {};
		oTabPage.oOperations.oColumns[Control_Tree_Grid.COLUMN_CHECK]	= {};
		oTabPage.oOperations.oColumns[Control_Tree_Grid.COLUMN_LABEL]	= {};
		oTabPage.oOperations.oControl.setColumns(oTabPage.oOperations.oColumns);
		
		// Set DataTypes
		oTabPage.oOperations.oControl.addDataType(Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sName, Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sDescription, Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sIconSource, this.onOperationCheck.bind(this));
		//--------------------------------------------------------------------//
		
		this._populatePermissionsTrees();
		
		return oTabPage;
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
				this.arrTabs.Permissions.oProfiles.oControl.appendChild(oNode.oControl);
			}
			this.arrTabs.Permissions.oProfiles.oControl.render();
			//----------------------------------------------------------------//
			
			// Populate Operations Grid
			//----------------------------------------------------------------//
			oOperations						= jQuery.json.arrayAsObject(oOperations);
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
					this.arrTabs.Permissions.oOperations.oControl.appendChild(oNode.oControl);
				}
			}
			this.arrTabs.Permissions.oOperations.oControl.render();
			//----------------------------------------------------------------//
		}
		else
		{
			// Get Permissions
			this.objEmployee.getPermissions(this._populatePermissionsTrees.bind(this), true);
		}
	},
	
	display		: function($super)
	{
		// If we have loaded, then display, otherwise automatically display once loaded
		if (this._objPage)
		{
			$super();
			return true;
		}
		else
		{
			this.bolDisplayOnLoad	= true;
			return false;
		}
	},
	
	setControlMode	: function(bolControlMode)
	{
		//alert('Set Control Mode...');
		var oTreeGridColumns	= {};
		switch (bolControlMode)
		{
			case Control_Field.RENDER_MODE_EDIT:
				// Change footer buttons
				this.setFooterButtons([this.domSaveButton, (this.objEmployee.objProperties.Id) ? this.domCancelEditButton : this.domCancelNewButton], true);
				
				// Show "Confirm Password"
				this.arrTabs.Details.table.tbody.PassWordConfirm.tr.domElement.style.display	= 'table-row';
				
				// Send Tree Grids into Edit mode
				this.arrTabs.Permissions.oProfiles.oControl.setEditable(true);
				this.arrTabs.Permissions.oOperations.oControl.setEditable(true);
				break;
				
			case Control_Field.RENDER_MODE_VIEW:
				// Change footer buttons
				this.setFooterButtons([this.domEditButton, this.domCloseButton], true);
				
				// Hide "Confirm Password"
				this.arrTabs.Details.table.tbody.PassWordConfirm.tr.domElement.style.display	= 'none';
				
				// Send Tree Grids into Read-Only mode
				this.arrTabs.Permissions.oProfiles.oControl.setEditable(false);
				this.arrTabs.Permissions.oOperations.oControl.setEditable(false);
				break;
			
			default:
				throw "Invalid Control Mode '" + bolControlMode + "'";
		}
		
		for (i in this.arrTabs.Details.table.tbody)
		{
			if (i != 'domElement')
			{
				this.arrTabs.Details.table.tbody[i].objControl.setRenderMode(bolControlMode);
			}
		}
	},
	
	hide	: function($super)
	{
		this.removeEventListeners();
		$super();
	},
	
	addEventListeners	: function()
	{
		this.arrEventHandlers						= {};
		this.arrEventHandlers.setControlModeEdit	= this.setControlMode.bind(this, Control_Field.RENDER_MODE_EDIT);
		this.arrEventHandlers.setControlModeView	= this.setControlMode.bind(this, Control_Field.RENDER_MODE_VIEW);
		this.arrEventHandlers.hide					= this.hide.bind(this);
		this.arrEventHandlers.save					= $Alert.curry('Saving!');
		
		this.domEditButton.addEventListener('click'			, this.arrEventHandlers.setControlModeEdit	, false);
		this.domCancelEditButton.addEventListener('click'	, this.arrEventHandlers.setControlModeView	, false);
		this.domCancelNewButton.addEventListener('click'	, this.arrEventHandlers.hide				, false);
		this.domCloseButton.addEventListener('click'		, this.arrEventHandlers.hide				, false);
		this.domSaveButton.addEventListener('click'			, this.arrEventHandlers.save				, false);
	},
	
	removeEventListeners	: function()
	{
		this.domEditButton.removeEventListener('click'			, this.arrEventHandlers.setControlModeEdit	, false);
		this.domCancelEditButton.removeEventListener('click'	, this.arrEventHandlers.setControlModeView	, false);
		this.domCancelNewButton.removeEventListener('click'		, this.arrEventHandlers.hide				, false);
		this.domCloseButton.removeEventListener('click'			, this.arrEventHandlers.hide				, false);
		this.domSaveButton.removeEventListener('click'			, this.arrEventHandlers.save				, false);
	},
	
	onOperationCheck	: function(oTreeGridNode)
	{
		var iValue	= oTreeGridNode.getValue();
		if (!this.oOperations || iValue === undefined || !this.oOperations[iValue])
		{
			throw "Unknown Operation '"+iValue+"'";
		}
		this.updateOperationSelected(iValue, oTreeGridNode.isSelected());
	},
	
	updateOperationSelected	: function(iOperation, bSelected)
	{
		// Update Cache
		//this.oOperations[iOperation].bEmployeeHasPermission	= bSelected;
		
		// Cascade update to all Node Instances
		for (var i = 0; i < this.oOperations[iOperation].aInstances.length; i++)
		{
			this.oOperations[iOperation].aInstances[i].setSelected(bSelected, true);
		}
		
		// Update prerequisites
		for (var i = 0; i < this.oOperations[iOperation].aPrerequisites.length; i++)
		{
			if (bSelected && !this.oOperations[this.oOperations[iOperation].aPrerequisites[i]].bEmployeeHasPermission)
			{
				this.updateOperationSelected(this.oOperations[iOperation].aPrerequisites[i], true);
			}
		}
		
		// Update dependants
		for (var i = 0; i < this.oOperations[iOperation].aDependants.length; i++)
		{
			if (!bSelected && this.oOperations[this.oOperations[iOperation].aDependants[i]].bEmployeeHasPermission)
			{
				this.updateOperationSelected(this.oOperations[iOperation].aDependants[i], false);
			}
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
		
		var oControlGridNodeData	= new Control_Tree_Grid_Node_Data(oContent, Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sName);
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
		
		var oControlGridNodeData	= new Control_Tree_Grid_Node_Data(oContent, Popup_Employee.TREE_GRID_DATATYPE_OPERATION_PROFILE.sName);
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
		
		var oControlGridNodeData	= new Control_Tree_Grid_Node_Data(oContent, Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sName);
		
		// We don't really need to register this as an Instance, because it's only there for informational purposes
		oOperation.aInstances.push(oControlGridNodeData);
		
		return oControlGridNodeData;
	}
});

Popup_Employee.operationToTreeGridNode	= function(oOperation, bWithDependants)
{
	bWithDependants	= bWithDependants ? true : false;
	
	//alert("Adding Operation '"+oOperation.name+"'");
	
	var oContent								= {};
	oContent[Control_Tree_Grid.COLUMN_LABEL]	= oOperation.name;
	oContent[Control_Tree_Grid.COLUMN_VALUE]	= oOperation.id;
	
	if (bWithDependants)
	{
		oContent[Control_Tree_Grid.COLUMN_CHECK]	= {mValue: oOperation.id, bChecked: oOperation.bEmployeeHasPermission};
	}
	
	var oControlGridNodeData	= new Control_Tree_Grid_Node_Data(oContent, Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sName);
	oOperation.aInstances.push(oControlGridNodeData);
	
	if (bWithDependants && oOperation.oDependants)
	{
		oOperation.oDependants	= jQuery.json.arrayAsObject(oOperation.oDependants);
		for (iOperationId in oOperation.oDependants)
		{
			oControlGridNodeData.appendChild(Popup_Employee.operationToTreeGridNode(oOperation.oDependants[iOperationId], bWithDependants));
		}
	}
	
	return oControlGridNodeData;
};

Popup_Employee.operationProfileToTreeGridNode	= function(oOperationProfile)
{
	//alert("Adding Operation Profile '"+oOperationProfile.name+"'");
	
	var oContent								= {};
	oContent[Control_Tree_Grid.COLUMN_LABEL]	= oOperationProfile.name;
	oContent[Control_Tree_Grid.COLUMN_VALUE]	= oOperationProfile.id;
	
	var oControlGridNodeData	= new Control_Tree_Grid_Node_Data(oContent, Popup_Employee.TREE_GRID_DATATYPE_OPERATION_PROFILE.sName);
	//oOperationProfile.aInstances.push(oControlGridNodeData);
	
	// Sub-Profiles
	if (oOperationProfile.oOperationProfiles)
	{
		oOperationProfile.oOperationProfiles	= jQuery.json.arrayAsObject(oOperationProfile.oOperationProfiles);
		for (iProfileId in oOperationProfile.oOperationProfiles)
		{
			oControlGridNodeData.appendChild(Popup_Employee.operationProfileToTreeGridNode(oOperationProfile.oOperationProfiles[iProfileId]));
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
			oControlGridNodeData.appendChild(Popup_Employee.operationToTreeGridNode(oOperationProfile.oOperations[iOperationId]));
		}
	}
	else
	{
		//alert(oOperationProfile.name + ' has no sub-Operations');
	}
	
	return oControlGridNodeData;
};

Popup_Employee.TREE_GRID_DATATYPE_OPERATION					= {};
Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sName			= 'operation';
Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sDescription	= 'Operation';
Popup_Employee.TREE_GRID_DATATYPE_OPERATION.sIconSource		= '../admin/img/template/operation.png';

Popup_Employee.TREE_GRID_DATATYPE_OPERATION_PROFILE					= {};
Popup_Employee.TREE_GRID_DATATYPE_OPERATION_PROFILE.sName			= 'operation_profile';
Popup_Employee.TREE_GRID_DATATYPE_OPERATION_PROFILE.sDescription	= 'Operation Profile';
Popup_Employee.TREE_GRID_DATATYPE_OPERATION_PROFILE.sIconSource		= '../admin/img/template/operation_profile.png';