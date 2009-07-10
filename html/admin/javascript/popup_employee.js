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
		oTabPage.domElement.appendChild(oTabPage.oProfiles.domElement);
		
		// Populate
		var oCSR										= {}
		oCSR.oContent									= {};
		oCSR.oContent[Control_Tree_Grid.COLUMN_LABEL]	= 'Customer Service Representative';
		oCSR.oContent[Control_Tree_Grid.COLUMN_VALUE]	= 7;
		oCSR.oControl									= new Control_Tree_Grid_Node(oCSR.oContent);
		oTabPage.oProfiles.oControl.appendChild(oCSR.oControl);
		
		var oFlexAdmin										= {}
		oFlexAdmin.oContent									= {};
		oFlexAdmin.oContent[Control_Tree_Grid.COLUMN_LABEL]	= 'Flex Admin';
		oFlexAdmin.oContent[Control_Tree_Grid.COLUMN_VALUE]	= 2;
		oFlexAdmin.oControl									= new Control_Tree_Grid_Node(oFlexAdmin.oContent);
		oTabPage.oProfiles.oControl.appendChild(oFlexAdmin.oControl);
		
		var oCSM										= {}
		oCSM.oContent									= {};
		oCSM.oContent[Control_Tree_Grid.COLUMN_LABEL]	= 'Customer Service Manager';
		oCSM.oContent[Control_Tree_Grid.COLUMN_VALUE]	= 4;
		oCSM.oControl									= new Control_Tree_Grid_Node(oCSM.oContent);
		oTabPage.oProfiles.oControl.appendChild(oCSM.oControl);
		oCSM.oControl.appendChild(Object.clone(oCSR.oControl));
		//--------------------------------------------------------------------//
		
		/*
		// Available Profiles
		//--------------------------------------------------------------------//
		// Create
		oTabPage.oProfiles.oAvailableProfiles				= {};
		oTabPage.oProfiles.oAvailableProfiles.domElement	= document.createElement('div');
		oTabPage.oProfiles.domElement.appendChild(oTabPage.oProfiles.oAvailableProfiles.domElement);
		
		oTabPage.oProfiles.oAvailableProfiles.oTitle						= {};
		oTabPage.oProfiles.oAvailableProfiles.oTitle.domElement				= document.createElement('h4');
		oTabPage.oProfiles.oAvailableProfiles.oTitle.domElement.innerHTML	= 'Available Profiles';
		oTabPage.oProfiles.oAvailableProfiles.domElement.appendChild(oTabPage.oProfiles.oAvailableProfiles.oTitle.domElement);
		
		oTabPage.oProfiles.oAvailableProfiles.oControl	= new Control_Select_List();
		oTabPage.oProfiles.oAvailableProfiles.domElement.appendChild(oTabPage.oProfiles.oAvailableProfiles.oControl.getElement());
		
		// Populate
		var oAvailableProfile								= {};
		oAvailableProfile[Control_Select_List.COLUMN_LABEL]	= 'Customer Service Representative';
		oAvailableProfile[Control_Select_List.COLUMN_VALUE]	= 1;
		oTabPage.oProfiles.oAvailableProfiles.oControl.add(new Control_Select_List_Option(oAvailableProfile, true));
		//--------------------------------------------------------------------//
		
		// Selected Profiles
		//--------------------------------------------------------------------//
		// Create
		oTabPage.oProfiles.oSelectedProfiles							= {};
		oTabPage.oProfiles.oSelectedProfiles.domElement					= document.createElement('div');
		oTabPage.oProfiles.oSelectedProfiles.domElement.style.textAlign	= 'right';
		oTabPage.oProfiles.domElement.appendChild(oTabPage.oProfiles.oSelectedProfiles.domElement);
		
		oTabPage.oProfiles.oSelectedProfiles.oTitle							= {};
		oTabPage.oProfiles.oSelectedProfiles.oTitle.domElement				= document.createElement('h4');
		oTabPage.oProfiles.oSelectedProfiles.oTitle.domElement.innerHTML	= 'Selected Profiles';
		oTabPage.oProfiles.oSelectedProfiles.domElement.appendChild(oTabPage.oProfiles.oSelectedProfiles.oTitle.domElement);
		
		oTabPage.oProfiles.oSelectedProfiles.oControl	= new Control_Select_List();
		oTabPage.oProfiles.oSelectedProfiles.domElement.appendChild(oTabPage.oProfiles.oSelectedProfiles.oControl.getElement());
		
		// Populate
		var oSelectedProfile								= {};
		oSelectedProfile[Control_Select_List.COLUMN_LABEL]	= 'Flex Admin';
		oSelectedProfile[Control_Select_List.COLUMN_VALUE]	= 6;
		oTabPage.oProfiles.oSelectedProfiles.oControl.add(new Control_Select_List_Option(oSelectedProfile, true));
		//--------------------------------------------------------------------//
		
		// Configure and link Select Lists
		var oAvailableProfilesColumns								= {}
		oAvailableProfilesColumns[Control_Select_List.COLUMN_LABEL]	= {sType: Control_Select_List.COLUMN_TYPE_TEXT};
		oAvailableProfilesColumns['sendToSelected']					= {sType: Control_Select_List.COLUMN_TYPE_SEND, oSendDestination: oTabPage.oProfiles.oSelectedProfiles.oControl, sIconSource: '../admin/img/template/new.png'};
		oTabPage.oProfiles.oAvailableProfiles.oControl.setColumns(oAvailableProfilesColumns);
		
		var oSelectedProfilesColumns								= {}
		oSelectedProfilesColumns['sendToAvailable']					= {sType: Control_Select_List.COLUMN_TYPE_SEND, oSendDestination: oTabPage.oProfiles.oAvailableProfiles.oControl, sIconSource: '../admin/img/template/remove.png'};
		oSelectedProfilesColumns[Control_Select_List.COLUMN_LABEL]	= {sType: Control_Select_List.COLUMN_TYPE_TEXT};
		oTabPage.oProfiles.oSelectedProfiles.oControl.setColumns(oSelectedProfilesColumns);
		*/
		
		return oTabPage;
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
	}
});