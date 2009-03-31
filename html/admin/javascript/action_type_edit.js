// Class: Action_Type_Edit
// Handles the creation/editing of Flex Documents
var Action_Type_Edit	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function(intActionTypeId)
	{
		var fncCallback	= intActionTypeId ? this._loadForId.bind(this, intActionTypeId) : this._render.bind(this, null);
		Flex.Constant.loadConstantGroup(new Array('action_association_type', 'action_type_detail_requirement', 'active_status'), fncCallback);
	},
	
	_loadForId	: function(intActionTypeId, fncCallback)
	{
		var fncJsonFunc		= jQuery.json.jsonFunction(this._render.bind(this), null, 'ActionType', 'getForId');
		fncJsonFunc(intActionTypeId);
	},
	
	_loadForIdResponse	: function(objResponse)
	{
		if (objResponse)
		{
			if (objResponse.Success)
			{
				this._render(objResponse.objActionType);
			}
			else if (objResponse.Message)
			{
				$Alert(objResponse.Message);
				return false;
			}
		}
		else
		{
			$Alert(objResponse);
			return false;
		}
	},
	
	_render		: function(objActionType)
	{
		this.strMode			= (objActionType) ? 'Edit' : 'New';
		
		this.pupEdit	= new Reflex_Popup(35);
		this.pupEdit.setTitle(this.strMode+' Action Type');
		this.pupEdit.setIcon("../admin/img/template/page_white_edit.png");
		
		this.objActionType		= (objActionType) ? objActionType : null;
		
		//alert(objActionType.toSource());
		
		// Popup Contents
		this.elmEncapsulator				= document.createElement('div');
		this.elmEncapsulator.style.margin	= "0.5em";
		
		this.elmForm				= document.createElement('form');
		/*this.elmForm.method			= 'POST';
		this.elmForm.enctype		= 'multipart/form-data';
		this.elmForm.action			= '../admin/reflex.php/ActionType/Save';*/
		this.elmEncapsulator.appendChild(this.elmForm);
		
		this.elmInputsDIV			= document.createElement('div');
		this.elmForm.appendChild(this.elmInputsDIV);
		
		if (objActionType)
		{
			this.elmInputName			= document.createElement('input');
			this.elmInputName.name		= "Action_Type_Edit_Id";
			this.elmInputName.type		= 'hidden';
			this.elmInputName.value		= objActionType.id;
			this.elmInputsDIV.appendChild(this.elmInputName);
		}
		
		this.elmInputsTable					= document.createElement('table');
		this.elmInputsTable.className		= "reflex";
		this.elmInputsTable.style.textAlign	= "left";
		this.elmInputsDIV.appendChild(this.elmInputsTable);
		
		this.elmInputsTableBody		= document.createElement('tbody');
		this.elmInputsTable.appendChild(this.elmInputsTableBody);
		
		// NAME
		this.elmInputsTRName			= document.createElement('tr');
		this.elmInputsTableBody.appendChild(this.elmInputsTRName);
		
		this.elmInputsTHName			= document.createElement('th');
		this.elmInputsTHName.className	= "label";
		this.elmInputsTHName.innerHTML	= "Name :";
		this.elmInputsTRName.appendChild(this.elmInputsTHName);
		
		this.elmInputsTDName			= document.createElement('td');
		this.elmInputsTDName.className	= "input";
		this.elmInputsTRName.appendChild(this.elmInputsTDName);
		
		if (objActionType)
		{
			// EDIT MODE: Can't edit the Name
			this.elmSpanName				= document.createElement('span');
			this.elmSpanName.innerHTML		= (objActionType) ? objActionType.name : '';
			this.elmInputsTDName.appendChild(this.elmSpanName);
		}
		else
		{
			// NEW MODE
			this.elmInputName				= document.createElement('input');
			this.elmInputName.name			= "Action_Type_Edit_Name";
			this.elmInputName.type			= 'text';
			this.elmInputName.maxLength		= 255;
			this.elmInputName.value			= (objActionType) ? objActionType.name : '';
			this.elmInputsTDName.appendChild(this.elmInputName);
		}
		
		// DESCRIPTION
		this.elmInputsTRDescription				= document.createElement('tr');
		this.elmInputsTableBody.appendChild(this.elmInputsTRDescription);
		
		this.elmInputsTHDescription				= document.createElement('th');
		this.elmInputsTHDescription.className	= "label";
		this.elmInputsTHDescription.innerHTML	= "Description :";
		this.elmInputsTRDescription.appendChild(this.elmInputsTHDescription);
		
		this.elmInputsTDDescription				= document.createElement('td');
		this.elmInputsTDDescription.className	= "input";
		this.elmInputsTRDescription.appendChild(this.elmInputsTDDescription);
		
		this.elmInputDescription				= document.createElement('input');
		this.elmInputDescription.name			= "Action_Type_Edit_Description";
		this.elmInputDescription.type			= 'text';
		this.elmInputDescription.maxLength		= 1024;
		this.elmInputDescription.value			= (objActionType) ? objActionType.description : '';
		this.elmInputsTDDescription.appendChild(this.elmInputDescription);
		
		// DETAILS
		this.elmInputsTRDetails					= document.createElement('tr');
		this.elmInputsTableBody.appendChild(this.elmInputsTRDetails);
		
		this.elmInputsTHDetails					= document.createElement('th');
		this.elmInputsTHDetails.className		= "label";
		this.elmInputsTHDetails.innerHTML		= "Details Required :";
		this.elmInputsTRDetails.appendChild(this.elmInputsTHDetails);
		
		this.elmInputsTDDetails					= document.createElement('td');
		this.elmInputsTDDetails.className		= "input";
		this.elmInputsTRDetails.appendChild(this.elmInputsTDDetails);
		
		this.elmInputDetails					= document.createElement('select');
		this.elmInputDetails.name				= "Action_Type_Edit_Details";
		this.elmInputsTDDetails.appendChild(this.elmInputDetails);
		
		this.arrInputDetailsOptions				= new Array();
		
		var arrDetailRequirement	= Flex.Constant.arrConstantGroups.action_type_detail_requirement;
		for (mixValue in arrDetailRequirement)
		{
			elmInputDetailsOption					= document.createElement('option');
			elmInputDetailsOption.value				= mixValue;
			elmInputDetailsOption.innerHTML			= arrDetailRequirement[mixValue].Description;
			elmInputDetailsOption.selected			= (objActionType && objActionType.action_type_detail_requirement_id == mixValue) ? 'selected' : '';
			this.elmInputDetails.appendChild(elmInputDetailsOption);
			
			this.arrInputDetailsOptions.push(elmInputDetailsOption);
		}
		
		// ASSOCIATION TYPES
		this.elmInputsTRAssociation				= document.createElement('tr');
		this.elmInputsTableBody.appendChild(this.elmInputsTRAssociation);
		
		this.elmInputsTHAssociation				= document.createElement('th');
		this.elmInputsTHAssociation.className	= "label";
		this.elmInputsTHAssociation.innerHTML	= "Associate With :";
		this.elmInputsTRAssociation.appendChild(this.elmInputsTHAssociation);
		
		this.elmInputsTDAssociation				= document.createElement('td');
		this.elmInputsTDAssociation.className	= "input";
		this.elmInputsTRAssociation.appendChild(this.elmInputsTDAssociation);
		
		var arrAssociationTypes	= Flex.Constant.arrConstantGroups.action_association_type;
		alert(arrAssociationTypes.toSource());
		for (var i in arrAssociationTypes)
		{
			alert(i);
			if (objActionType)
			{
				// EDIT: Can't change
				if (objActionType.arrAssociationTypes.indexOf(i) > -1)
				{
					elmInputAssociationSpan					= document.createElement('span');
					elmInputAssociationSpan.innerHTML		= arrAssociationTypes[i].Description;
					this.elmInputsTDAssociation.appendChild(elmInputAssociationSpan);
				}
			}
			else
			{
				elmInputAssociationOption				= document.createElement('input');
				elmInputAssociationOption.type			= 'checkbox';
				elmInputAssociationOption.id			= 'Action_Type_Edit_Association_[' + arrAssociationTypes[i] + ']';
				elmInputAssociationOption.name			= elmInputAssociationOption.id;
				elmInputAssociationOption.value			= i;
				this.elmInputsTDAssociation.appendChild(elmInputAssociationOption);
				
				elmLabelAssociationOption				= document.createElement('label');
				elmLabelAssociationOption.setAttribute('for', elmInputAssociationOption.id);
				elmLabelAssociationOption.innerHTML		= arrAssociationTypes[i].Description;
				this.elmInputsTDAssociation.appendChild(elmLabelAssociationOption);
			}
			
			if (i < objActionType.arrAssociationTypes.length - 1)
			{
				this.elmInputsTDAssociation.appendChild(document.createElement('br'));
			}
		}
		
		// AUTOMATIC ONLY
		this.elmInputsTRAutomatic				= document.createElement('tr');
		this.elmInputsTableBody.appendChild(this.elmInputsTRAutomatic);
		
		this.elmInputsTHAutomatic				= document.createElement('th');
		this.elmInputsTHAutomatic.className	= "label";
		this.elmInputsTHAutomatic.innerHTML	= "Method :";
		this.elmInputsTRAutomatic.appendChild(this.elmInputsTHAutomatic);
		
		this.elmInputsTDAutomatic				= document.createElement('td');
		this.elmInputsTDAutomatic.className		= "input";
		this.elmInputsTRAutomatic.appendChild(this.elmInputsTDAutomatic);
		
		if (objActionType)
		{
			// EDIT: Can't change
			elmInputAutomaticSpan					= document.createElement('span');
			elmInputAutomaticSpan.innerHTML				= (objActionType.is_automatic_only ? 'Automatic' : 'Quick Action');
			this.elmInputsTDAutomatic.appendChild(elmInputAutomaticSpan);
		}
		else
		{
			elmInputAssociationOptionYes			= document.createElement('input');
			elmInputAssociationOptionYes.type		= 'radio';
			elmInputAssociationOptionYes.id			= 'Action_Type_Edit_Automatic_Yes';
			elmInputAssociationOptionYes.name		= 'Action_Type_Edit_Automatic';
			elmInputAssociationOptionYes.value		= 1;
			this.elmInputsTDAutomatic.appendChild(elmInputAssociationOptionYes);
			
			elmLabelAssociationOptionYes			= document.createElement('label');
			elmLabelAssociationOptionYes.setAttribute('for', elmInputAssociationOptionYes.id);
			elmLabelAssociationOptionYes.innerHTML	= 'Automatic';
			this.elmInputsTDAutomatic.appendChild(elmLabelAssociationOptionYes);

			this.elmInputsTDAutomatic.appendChild(document.createElement('br'));
			
			elmInputAssociationOptionNo				= document.createElement('input');
			elmInputAssociationOptionNo.type		= 'radio';
			elmInputAssociationOptionNo.id			= 'Action_Type_Edit_Automatic_No';
			elmInputAssociationOptionNo.name		= 'Action_Type_Edit_Automatic';
			elmInputAssociationOptionNo.value		= 0;
			this.elmInputsTDAutomatic.appendChild(elmInputAssociationOptionNo);
			
			elmLabelAssociationOptionNo				= document.createElement('label');
			elmLabelAssociationOptionNo.setAttribute('for', elmInputAssociationOptionNo.id);
			elmLabelAssociationOptionNo.innerHTML	= 'Quick Action';
			this.elmInputsTDAutomatic.appendChild(elmLabelAssociationOptionNo);
		}
		
		// SYSTEM
		this.elmInputsTRSystem					= document.createElement('tr');
		this.elmInputsTableBody.appendChild(this.elmInputsTRSystem);
		
		this.elmInputsTHSystem					= document.createElement('th');
		this.elmInputsTHSystem.className		= "label";
		this.elmInputsTHSystem.innerHTML		= "Nature :";
		this.elmInputsTRSystem.appendChild(this.elmInputsTHSystem);
		
		this.elmInputsTDSystem					= document.createElement('td');
		this.elmInputsTDSystem.className		= "input";
		this.elmInputsTRSystem.appendChild(this.elmInputsTDSystem);
		
		this.elmInputSystem						= document.createElement('input');
		this.elmInputSystem.name				= "Action_Type_Edit_System";
		this.elmInputSystem.type				= 'hidden';
		this.elmInputSystem.value				= (objActionType && objActionType.is_system) ? 1 : 0;
		this.elmInputsDIV.appendChild(this.elmInputSystem);

		this.elmInputSystemSpan					= document.createElement('span');
		this.elmInputSystemSpan.innerHTML		= (this.elmInputSystem.value) ? 'System' : 'Custom';
		this.elmInputsTDSystem.appendChild(this.elmInputSystemSpan);
		
		// STATUS
		this.elmInputsTRStatus				= document.createElement('tr');
		this.elmInputsTableBody.appendChild(this.elmInputsTRStatus);
		
		this.elmInputsTHStatus				= document.createElement('th');
		this.elmInputsTHStatus.className	= "label";
		this.elmInputsTHStatus.innerHTML	= "Status :";
		this.elmInputsTRStatus.appendChild(this.elmInputsTHStatus);
		
		this.elmInputsTDStatus				= document.createElement('td');
		this.elmInputsTDStatus.className	= "input";
		this.elmInputsTRStatus.appendChild(this.elmInputsTDStatus);
		
		var arrActiveStatus	= Flex.Constant.arrConstantGroups.active_status;
		if (!objActionType)
		{
			// New: Can't change
			this.elmInputStatus							= document.createElement('hidden');
			this.elmInputStatus.name					= "Action_Type_Edit_Status";
			this.elmInputStatus.value					= $CONSTANT.ACTIVE_STATUS_ACTIVE;
			this.elmInputsTDStatus.appendChild(this.elmInputStatus);
			
			elmInputStatusSpan							= document.createElement('span');
			elmInputStatusSpan.innerHTML				= arrActiveStatus[$CONSTANT.ACTIVE_STATUS_ACTIVE].Description;
			this.elmInputsTDStatus.appendChild(elmInputStatusSpan);
		}
		else
		{
			this.elmInputStatus							= document.createElement('select');
			this.elmInputStatus.name					= "Action_Type_Edit_Status";
			this.elmInputsTDStatus.appendChild(this.elmInputStatus);
			
			this.arrInputStatusOptions				= new Array();
			
			for (mixValue in arrActiveStatus)
			{
				elmInputStatusOption					= document.createElement('option');
				elmInputStatusOption.value				= mixValue;
				elmInputStatusOption.innerHTML			= arrActiveStatus[mixValue].Description;
				elmInputStatusOption.selected			= (objActionType && objActionType.active_status_id == mixValue) ? 'selected' : '';
				this.elmInputStatus.appendChild(elmInputStatusOption);
				
				this.arrInputStatusOptions.push(elmInputStatusOption);
			}
		}
		
		// BUTTONS
		this.elmButtonsDIV					= document.createElement('div');
		this.elmButtonsDIV.style.textAlign	= 'center';
		this.elmForm.appendChild(this.elmButtonsDIV);
		
		this.elmSubmit				= document.createElement('input');
		this.elmSubmit.name			= "Action_Type_Edit_Submit";
		this.elmSubmit.type			= "button";
		this.elmSubmit.value		= "Save";
		this.elmButtonsDIV.appendChild(this.elmSubmit);
		
		this.elmCancel				= document.createElement('input');
		this.elmCancel.name			= "Action_Type_Edit_Cancel";
		this.elmCancel.type			= "button";
		this.elmCancel.value		= "Cancel";
		this.elmButtonsDIV.appendChild(this.elmCancel);
		
		this._registerEventHandlers();
		
		this.pupEdit.setContent(this.elmEncapsulator);
		this.pupEdit.display();
	},
	
	_submit	: function()
	{
		// Ensure that all fields are populated
		var arrErrors	= new Array();

		if (!this.elmInputName.value.replace(/(^\s+|\s+$)/g, '').length)
		{
			arrErrors.push("[!] Please enter a Name for the Action Type");
		}
		if (this.elmInputName.value.indexOf('/') > -1)
		{
			arrErrors.push("[!] The specified Name contains illegal '/' characters");
		}
		if (this.elmInputFile && this.elmInputFile.disabled == false && !this.elmInputFile.value)
		{
			arrErrors.push("[!] Please select a File to upload");
		}
		
		if (arrErrors.length)
		{
			var strError	= "There is an error with your input.  Please satisfy the following requirements before submitting again:<br />";
			for (i = 0; i < arrErrors.length; i++)
			{
				strError	+=  "<br />" + arrErrors[i];
			}
			$Alert(strError);
			return false;
		}
		
		// Show the Loading Splash
		Vixen.Popup.ShowPageLoadingSplash("Saving...", null, null, null, 1);
		
		$Alert("Save!");
		return false;
		
		// Perform AJAX query
		var fncJsonFunc		= jQuery.json.jsonFunction(this._submitResponse.bind(this), null, 'ActionType', 'save');
		fncJsonFunc(arrLoadConstantGroups);
	},
	
	_submitResponse	: function(objResponse)
	{
		Vixen.Popup.ClosePageLoadingSplash();
		if (objResponse.Success)
		{
			$Alert("The Action Type '"+this.elmInputName.value+"' has been successfully saved.", null, null, null, "Save Successful", this._close.bind(this, null, true));
			return true;
		}
		else if (objResponse.Success == undefined)
		{
			$Alert(objResponse.toSource());
			return false;
		}
		else
		{
			$Alert(objResponse.Message);
			return false;
		}
	},
	
	_close	: function(eEvent, bolConfirmed)
	{
		if (bolConfirmed)
		{
			// Confirmed
			this._unregisterEventHandlers();
			this.pupEdit.hide();
		}
		else if (bolConfirmed == undefined)
		{
			// Prompt
			var strPopupId	= 'Flex_Action_Type_Edit_Cancel_'+(Math.round(Math.random()*100));
			Vixen.Popup.YesNoCancel("Are you sure you want to cancel and revert all changes?", this._close.bind(this, null, true), Vixen.Popup.Close.bind(Vixen.Popup, strPopupId), null, null, strPopupId, "Revert Changes");
		}
		else
		{
			// Do nothing
		}
	},
	
	_registerEventHandlers	: function()
	{
		this.elmSubmit.addEventListener('click', this._submit.bindAsEventListener(this), false);
		this.elmCancel.addEventListener('click', this._close.bindAsEventListener(this), false);
	},
	
	_unregisterEventHandlers	: function()
	{
		this.elmSubmit.removeEventListener('click', this._submit.bindAsEventListener(this), false);
		this.elmCancel.removeEventListener('click', this._close.bindAsEventListener(this), false);
	}
});