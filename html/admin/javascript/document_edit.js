// Class: Document_Edit
// Handles the creation/editing of Flex Documents
var Document_Edit	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function(strDocumentNature, objDocument)
	{
		var strFriendlyNature	= '';
		switch (strDocumentNature)
		{
			case 'DOCUMENT_NATURE_FILE':
				strFriendlyNature	= "Document";
				break;
				
			case 'DOCUMENT_NATURE_FOLDER':
				strFriendlyNature	= "Folder";
				break;
				
			default:
				throw "'"+strDocumentNature+"' is not a valid Document Nature";
				break;
		}
		this.strFriendlyNature	= strFriendlyNature;
		
		this.strDocumentNature	= strDocumentNature;
		this.strMode			= (objDocument) ? 'Edit' : 'New';
		
		this.pupEdit	= new Reflex_Popup(35);
		this.pupEdit.setTitle(this.strMode+' '+strFriendlyNature);
		this.pupEdit.setIcon("../admin/img/template/page_white_edit.png");
		
		this.objDocument		= (objDocument) ? objDocument : null;
		
		//alert(objDocument.toSource());
		
		// Popup Contents
		this.elmEncapsulator				= document.createElement('div');
		this.elmEncapsulator.style.margin	= "0.5em";
		
		this.elmForm				= document.createElement('form');
		this.elmForm.method			= 'POST';
		this.elmForm.enctype		= 'multipart/form-data';
		this.elmForm.action			= '../admin/reflex.php/Document/Save';
		this.elmEncapsulator.appendChild(this.elmForm);
		
		this.elmInputsDIV			= document.createElement('div');
		this.elmForm.appendChild(this.elmInputsDIV);
		
		if (objDocument)
		{
			this.elmInputName			= document.createElement('input');
			this.elmInputName.name		= "Document_Edit_Id";
			this.elmInputName.type		= 'hidden';
			this.elmInputName.value		= objDocument.id;
			this.elmInputsDIV.appendChild(this.elmInputName);
		}
		else
		{
			this.elmInputNature			= document.createElement('input');
			this.elmInputNature.name	= "Document_Edit_Nature";
			this.elmInputNature.type	= 'hidden';
			this.elmInputNature.value	= strDocumentNature;
			this.elmInputsDIV.appendChild(this.elmInputNature);
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
		
		this.elmInputName				= document.createElement('input');
		this.elmInputName.name			= "Document_Edit_Name";
		this.elmInputName.type			= 'text';
		this.elmInputName.maxLength		= 255;
		this.elmInputName.value			= (objDocument) ? objDocument.objDocumentContent.name : '';
		this.elmInputsTDName.appendChild(this.elmInputName);
		
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
		this.elmInputDescription.name			= "Document_Edit_Description";
		this.elmInputDescription.type			= 'text';
		this.elmInputDescription.maxLength		= 1024;
		this.elmInputDescription.value			= (objDocument && objDocument.objDocumentContent.description) ? objDocument.objDocumentContent.description : '';
		this.elmInputsTDDescription.appendChild(this.elmInputDescription);
		
		// FILE
		if (strDocumentNature === 'DOCUMENT_NATURE_FILE')
		{
			this.elmInputsTRFile				= document.createElement('tr');
			this.elmInputsTableBody.appendChild(this.elmInputsTRFile);
			
			this.elmInputsTHFile				= document.createElement('th');
			this.elmInputsTHFile.className		= "label";
			this.elmInputsTHFile.innerHTML		= "File :";
			this.elmInputsTRFile.appendChild(this.elmInputsTHFile);
			
			this.elmInputsTDFile				= document.createElement('td');
			this.elmInputsTDFile.className		= "input";
			this.elmInputsTRFile.appendChild(this.elmInputsTDFile);
			
			if (objDocument)
			{
				this.elmInputsTHFile.style.verticalAlign	= "top";
				this.elmInputsTDFile.style.verticalAlign	= "top";
				
				this.elmInputsDIVFileReplace		= document.createElement('div');
				this.elmInputsTDFile.appendChild(this.elmInputsDIVFileReplace);
				
				this.elmInputFileReplaceNo			= document.createElement('input');
				this.elmInputFileReplaceNo.checked	= true;
				this.elmInputFileReplaceNo.type		= 'radio';
				this.elmInputFileReplaceNo.id		= 'Document_Edit_File_Replace_No';
				this.elmInputFileReplaceNo.name		= 'Document_Edit_File_Replace';
				this.elmInputFileReplaceNo.value	= 'false';
				this.elmInputsDIVFileReplace.appendChild(this.elmInputFileReplaceNo);
				
				this.elmLabelFileReplaceNo				= document.createElement('label');
				this.elmLabelFileReplaceNo.setAttribute('for', this.elmInputFileReplaceNo.id);
				this.elmLabelFileReplaceNo.innerHTML	= "Keep the existing Document Content";
				this.elmInputsDIVFileReplace.appendChild(this.elmLabelFileReplaceNo);
				
				this.elmInputsDIVFileReplace.appendChild(document.createElement('br'));
				
				this.elmInputFileReplaceYes			= document.createElement('input');
				this.elmInputFileReplaceYes.type	= 'radio';
				this.elmInputFileReplaceYes.id		= 'Document_Edit_File_Replace_Yes';
				this.elmInputFileReplaceYes.name	= 'Document_Edit_File_Replace';
				this.elmInputFileReplaceYes.value	= 'true';
				this.elmInputsDIVFileReplace.appendChild(this.elmInputFileReplaceYes);
				
				this.elmLabelFileReplaceYes			= document.createElement('label');
				this.elmLabelFileReplaceYes.setAttribute('for', this.elmInputFileReplaceYes.id); 
				this.elmLabelFileReplaceYes.innerHTML	= "Replace the Document Content";
				this.elmInputsDIVFileReplace.appendChild(this.elmLabelFileReplaceYes);
			}
			
			this.elmInputsDIVFile				= document.createElement('div');
			this.elmInputsTDFile.appendChild(this.elmInputsDIVFile);
			
			this.elmInputFile					= document.createElement('input');
			this.elmInputFile.name				= "Document_Edit_File";
			this.elmInputFile.type				= 'file';
			this.elmInputsDIVFile.appendChild(this.elmInputFile);
			
			this._updateFileUpload();
		}
		
		// SYSTEM
		if (Flex.Document.Explorer.objUser.bolGOD && !objDocument)
		{
			this.elmInputsTRSystem				= document.createElement('tr');
			this.elmInputsTableBody.appendChild(this.elmInputsTRSystem);
			
			this.elmInputsTHSystem				= document.createElement('th');
			this.elmInputsTHSystem.className	= "label";
			this.elmInputsTHSystem.innerHTML	= "&nbsp;";
			this.elmInputsTRSystem.appendChild(this.elmInputsTHSystem);
			
			this.elmInputsTDSystem				= document.createElement('td');
			this.elmInputsTDSystem.className		= "input";
			this.elmInputsTRSystem.appendChild(this.elmInputsTDSystem);
			
			this.elmInputSystem			= document.createElement('input');
			this.elmInputSystem.id		= "Document_Edit_System";
			this.elmInputSystem.name	= "Document_Edit_System";
			this.elmInputSystem.type	= 'checkbox';
			this.elmInputSystem.value	= strDocumentNature;
			this.elmInputsTDSystem.appendChild(this.elmInputSystem);
			
			this.elmLabelSystem				= document.createElement('label');
			this.elmLabelSystem.setAttribute('for', this.elmInputSystem.id); 
			this.elmLabelSystem.innerHTML	= "This is a System "+this.strFriendlyName;
			this.elmInputsDIVFileReplace.appendChild(this.elmLabelSystem);
		}
		
		// BUTTONS
		this.elmButtonsDIV					= document.createElement('div');
		this.elmButtonsDIV.style.textAlign	= 'center';
		this.elmForm.appendChild(this.elmButtonsDIV);
		
		this.elmSubmit				= document.createElement('input');
		this.elmSubmit.name			= "Document_Edit_Submit";
		this.elmSubmit.type			= "button";
		this.elmSubmit.value		= "Save";
		this.elmButtonsDIV.appendChild(this.elmSubmit);
		
		this.elmCancel				= document.createElement('input');
		this.elmCancel.name			= "Document_Edit_Cancel";
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
			arrErrors.push("[!] Please enter a Name for the "+this.strFriendlyNature);
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
		Vixen.Popup.ShowPageLoadingSplash("Saving "+this.strFriendlyNature+"...", null, null, null, 1);
		
		// Perform AJAX query
		if (jQuery.json.jsonIframeFormSubmit(this.elmForm, this._submitResponse.bind(this)))
		{
			this.elmForm.submit();
			return true;
		}
		else
		{
			return false;
		}
	},
	
	_submitResponse	: function(objResponse)
	{
		Vixen.Popup.ClosePageLoadingSplash();
		if (objResponse.Success)
		{
			$Alert("The "+this.strFriendlyNature+" '"+this.elmInputName.value.replace(/(^\s+|\s+$)/g, '')+"' has been successfully saved.", null, null, null, "Save Successful", this._close.bind(this, null, true));
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
			var strPopupId	= 'Flex_Document_Edit_Cancel_'+(Math.round(Math.random()*100));
			Vixen.Popup.YesNoCancel("Are you sure you want to cancel and revert all changes?", this._close.bind(this, null, true), Vixen.Popup.Close.bind(Vixen.Popup, strPopupId), null, null, strPopupId, "Revert Changes");
		}
		else
		{
			// Do nothing
		}
	},
	
	_updateFileUpload	: function()
	{
		if (this.elmInputsDIVFileReplace != undefined)
		{
			if (this.elmInputFileReplaceYes.checked)
			{
				//this.elmInputsDIVFile.style.display	= 'block';
				this.elmInputFile.disabled			= false;
			}
			else
			{
				//this.elmInputsDIVFile.style.display	= 'none';
				this.elmInputFile.disabled			= true;
			}
		}
		else
		{
			//this.elmInputsDIVFile.style.display	= 'block';
			this.elmInputFile.disabled			= false;
		}
	},
	
	_registerEventHandlers	: function()
	{
		if (this.elmInputsDIVFileReplace != undefined)
		{
			this.elmInputFileReplaceNo.addEventListener('change', this._updateFileUpload.bindAsEventListener(this), false);
			this.elmInputFileReplaceYes.addEventListener('change', this._updateFileUpload.bindAsEventListener(this), false);
		}
		
		this.elmSubmit.addEventListener('click', this._submit.bindAsEventListener(this), false);
		this.elmCancel.addEventListener('click', this._close.bindAsEventListener(this), false);
	},
	
	_unregisterEventHandlers	: function()
	{
		if (this.elmInputsDIVFileReplace != undefined)
		{
			this.elmInputFileReplaceNo.removeEventListener('change', this._updateFileUpload.bindAsEventListener(this), false);
			this.elmInputFileReplaceYes.removeEventListener('change', this._updateFileUpload.bindAsEventListener(this), false);
		}

		this.elmSubmit.removeEventListener('click', this._submit.bindAsEventListener(this), false);
		this.elmCancel.removeEventListener('click', this._close.bindAsEventListener(this), false);
	}
});