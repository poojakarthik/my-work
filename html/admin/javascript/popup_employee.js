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
		/*
		// Populate
		var oCSR										= {}
		oCSR.oContent									= {};
		oCSR.oContent[Control_Tree_Grid.COLUMN_LABEL]	= {sLabel: 'Customer Service Representative', sIconSource: '../admin/img/template/operation_profile.png'};
		oCSR.oContent[Control_Tree_Grid.COLUMN_VALUE]	= 7;
		oCSR.oContent[Control_Tree_Grid.COLUMN_CHECK]	= true;
		oCSR.oControl									= new Control_Tree_Grid_Node_Data(oCSR.oContent);
		oTabPage.oProfiles.oControl.appendChild(oCSR.oControl);
		/*
		var oFlexAdmin										= {}
		oFlexAdmin.oContent									= {};
		oFlexAdmin.oContent[Control_Tree_Grid.COLUMN_LABEL]	= {sLabel: 'Flex Admin', sIconSource: '../admin/img/template/operation_profile.png'};
		oFlexAdmin.oContent[Control_Tree_Grid.COLUMN_VALUE]	= 2;
		oFlexAdmin.oContent[Control_Tree_Grid.COLUMN_CHECK]	= true;
		oFlexAdmin.oControl									= new Control_Tree_Grid_Node_Data(oFlexAdmin.oContent);
		oTabPage.oProfiles.oControl.appendChild(oFlexAdmin.oControl);
		/*
		var oCSM										= {}
		oCSM.oContent									= {};
		oCSM.oContent[Control_Tree_Grid.COLUMN_LABEL]	= {sLabel: 'Customer Service Manager', sIconSource: '../admin/img/template/operation_profile.png'};
		oCSM.oContent[Control_Tree_Grid.COLUMN_VALUE]	= 4;
		oCSM.oContent[Control_Tree_Grid.COLUMN_CHECK]	= true;
		oCSM.oControl									= new Control_Tree_Grid_Node_Data(oCSM.oContent);
		oTabPage.oProfiles.oControl.appendChild(oCSM.oControl);
		/*
		delete(oCSR.oContent[Control_Tree_Grid.COLUMN_CHECK]);
		
		oCSM.oCSR			= {};
		oCSM.oCSR.oControl	= new Control_Tree_Grid_Node_Data(oCSR.oContent);
		oCSM.oCSR.oControl.getElement().addClassName('informational');
		oCSM.oControl.appendChild(oCSM.oCSR.oControl);
		/*
		
		var oEditAccountDetails											= {}
		oEditAccountDetails.oContent									= {};
		oEditAccountDetails.oContent[Control_Tree_Grid.COLUMN_LABEL]	= {sLabel: 'Edit Account Details', sIconSource: '../admin/img/template/operation.png'};
		oEditAccountDetails.oContent[Control_Tree_Grid.COLUMN_VALUE]	= 9;
		oEditAccountDetails.oControl									= new Control_Tree_Grid_Node_Data(oEditAccountDetails.oContent);
		oEditAccountDetails.oControl.getElement().addClassName('informational');
		oCSR.oControl.appendChild(oEditAccountDetails.oControl);
		
		oCSM.oCSR.oEditAccountDetails			= {};
		oCSM.oCSR.oEditAccountDetails.oControl	= new Control_Tree_Grid_Node_Data(oEditAccountDetails.oContent);
		oCSM.oCSR.oEditAccountDetails.oControl.getElement().addClassName('informational');
		oCSM.oCSR.oControl.appendChild(oCSM.oCSR.oEditAccountDetails.oControl);
		*/
		
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
			// Populate Profiles Grid
			//----------------------------------------------------------------//
			oOperationProfiles	= jQuery.json.arrayAsObject(oOperationProfiles);
			for (iProfileId in oOperationProfiles)
			{
				var oNode										= {};
				oNode.oControl									= Popup_Employee.operationProfileToTreeGridNode(oOperationProfiles[iProfileId]);
				oNode.oContent									= oNode.oControl.getContent();
				oNode.oContent[Control_Tree_Grid.COLUMN_CHECK]	= {bChecked: oOperationProfiles[iProfileId].bEmployeeHasPermission};
				oNode.oControl.setContent(oNode.oContent);
				this.arrTabs.Permissions.oProfiles.oControl.appendChild(oNode.oControl);
			}
			this.arrTabs.Permissions.oProfiles.oControl.render();
			//----------------------------------------------------------------//
			
			// Populate Operations Grid
			//----------------------------------------------------------------//
			oOperations						= jQuery.json.arrayAsObject(oOperations);
			var oOperationDependencyTree	= Operation.buildDependencyTree(oOperations);
			this.oOperations				= oOperations;
			//Reflex_Debug.asHTMLPopup(this.oOperations);
			/*for (iOperationId in oOperations)
			{
				var oNode										= {};
				oNode.oControl									= Popup_Employee.operationToTreeGridNode(oOperations[iOperationId]);
				oNode.oContent									= oNode.oControl.getContent();
				oNode.oContent[Control_Tree_Grid.COLUMN_CHECK]	= {bChecked: oOperations[iOperationId].bEmployeeHasPermission};
				oNode.oControl.setContent(oNode.oContent);
				this.arrTabs.Permissions.oOperations.oControl.appendChild(oNode.oControl);
			}*/
			for (iOperationId in oOperationDependencyTree)
			{
				var oNode										= {};
				oNode.oControl									= this.operationToDependencyTree(oOperations[iOperationId]);
				oNode.oContent									= oNode.oControl.getContent();
				//oNode.oContent[Control_Tree_Grid.COLUMN_CHECK]	= {mValue: this.oOperations[iOperationId].id, bChecked: this.oOperations[iOperationId].bEmployeeHasPermission};
				oNode.oControl.setContent(oNode.oContent);
				this.arrTabs.Permissions.oOperations.oControl.appendChild(oNode.oControl);
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
		switch (bolControlMode)
		{
			case Control_Field.RENDER_MODE_EDIT:
				// Change footer buttons
				this.setFooterButtons([this.domSaveButton, (this.objEmployee.objProperties.Id) ? this.domCancelEditButton : this.domCancelNewButton], true);
				
				// Show "Confirm Password"
				this.arrTabs.Details.table.tbody.PassWordConfirm.tr.domElement.style.display	= 'table-row';
				break;
				
			case Control_Field.RENDER_MODE_VIEW:
				// Change footer buttons
				this.setFooterButtons([this.domEditButton, this.domCloseButton], true);
				
				// Hide "Confirm Password"
				this.arrTabs.Details.table.tbody.PassWordConfirm.tr.domElement.style.display	= 'none';
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
		
		// Update Cache
		this.oOperations[iValue].bEmployeeHasPermission	= oTreeGridNode.isSelected();
		
		// Cascade update to all other Nodes
		for (var i = 0; i < this.oOperations[iValue].aInstances.length; i++)
		{
			this.oOperations[iValue].aInstances[i].setSelected(this.oOperations[iValue].bEmployeeHasPermission, true);
		}
		
		// Update prerequisites
		for (var i in this.oOperations[iValue].oPrerequisites)
		{
			if (this.oOperations[iValue].bEmployeeHasPermission && !this.oOperations[i].bEmployeeHasPermission)
			{
				this.oOperations[iValue].oPrerequisites[i].setSelected(false);
			}
		}
		
		// Update dependants
		for (var i in this.oOperations[iValue].oDependants)
		{
			if (!this.oOperations[iValue].bEmployeeHasPermission && this.oOperations[i].bEmployeeHasPermission)
			{
				this.oOperations[iValue].oDependants[i].setSelected(false);
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
		
		// Update Cache
		this.oOperationProfiles[iValue].bEmployeeHasPermission	= oTreeGridNode.isSelected();
		
		// Cascade update to all other Nodes
		for (var i = 0; i < this.oOperationProfiles[iValue].aInstances.length; i++)
		{
			this.oOperationProfiles[iValue].aInstances[i].setSelected(this.oOperationProfiles[iValue].bEmployeeHasPermission);
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
			for (var i = 0; i < oOperation.aDependants.length)
			{
				oControlGridNodeData.appendChild(this.operationToTreeGridNode(this.oOperations[oOperation.oDependants[i]]));
			}
		}
		
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