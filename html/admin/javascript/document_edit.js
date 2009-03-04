// Class: Document_Edit
// Handles the creation/editing of Flex Documents
var Document_Edit	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function(strDocumentNature, objDocument)
	{
		alert(arguments.toSource());
		
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
		
		this.strDocumentNature	= strDocumentNature;
		this.strMode			= (objDocument) ? 'Edit' : 'New';
		
		this.pupEdit	= new Reflex_Popup(30);
		this.pupEdit.setTitle(this.strMode+' '+strFriendlyNature);
		this.pupEdit.setIcon("../admin/img/template/page_white_edit.png");
		
		this.objDocument		= (objDocument) ? objDocument : null;
		
		// Popup Contents
		this.elmEncapsulator				= document.createElement('div');
		this.elmEncapsulator.style.margin	= "0.5em";
		
		this.elmForm				= document.createElement('form');
		this.elmForm.method			= 'POST';
		this.elmForm.enctype		= 'multipart/form-data';
		this.elmForm.action			= '../admin/reflex.php/Document/Save';
		this.elmEncapsulator.appendChild(this.elmForm);
		this.elmForm.addEventListener('submit', this._submit.bindAsEventListener(this), false);
		
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
		this.elmInputsTHName.innerHTML	= "Name :";
		this.elmInputsTRName.appendChild(this.elmInputsTHName);
		
		this.elmInputsTDName			= document.createElement('th');
		this.elmInputsTRName.appendChild(this.elmInputsTDName);
		
		this.elmInputName				= document.createElement('input');
		this.elmInputName.name			= "Document_Edit_Name";
		this.elmInputName.type			= 'text';
		this.elmInputName.maxLength		= 255;
		this.elmInputsTDName.appendChild(this.elmInputName);
		
		// DESCRIPTION
		this.elmInputsTRDescription				= document.createElement('tr');
		this.elmInputsTableBody.appendChild(this.elmInputsTRDescription);
		
		this.elmInputsTHDescription				= document.createElement('th');
		this.elmInputsTHDescription.innerHTML	= "Description :";
		this.elmInputsTRDescription.appendChild(this.elmInputsTHDescription);
		
		this.elmInputsTDDescription				= document.createElement('th');
		this.elmInputsTRDescription.appendChild(this.elmInputsTDDescription);
		
		this.elmInputDescription				= document.createElement('input');
		this.elmInputDescription.name			= "Document_Edit_Description";
		this.elmInputDescription.type			= 'text';
		this.elmInputDescription.maxLength		= 1024;
		this.elmInputsTDDescription.appendChild(this.elmInputDescription);
		
		// FILE
		if (strDocumentNature === 'DOCUMENT_NATURE_FILE')
		{
			this.elmInputsTRFile				= document.createElement('tr');
			this.elmInputsTableBody.appendChild(this.elmInputsTRFile);
			
			this.elmInputsTHFile				= document.createElement('th');
			this.elmInputsTHFile.innerHTML		= "File :";
			this.elmInputsTRFile.appendChild(this.elmInputsTHFile);
			
			this.elmInputsTDFile				= document.createElement('th');
			this.elmInputsTRFile.appendChild(this.elmInputsTDFile);
			
			this.elmInputFile					= document.createElement('input');
			this.elmInputFile.name				= "Document_Edit_File";
			this.elmInputFile.type				= 'file';
			this.elmInputsTDFile.appendChild(this.elmInputFile);
		}
		
		// BUTTONS
		this.elmButtonsDIV					= document.createElement('div');
		this.elmButtonsDIV.style.textAlign	= 'center';
		this.elmForm.appendChild(this.elmButtonsDIV);
		
		this.elmSubmit				= document.createElement('input');
		this.elmSubmit.name			= "Document_Edit_Submit";
		this.elmSubmit.type			= "submit";
		this.elmSubmit.value		= "Save";
		this.elmButtonsDIV.appendChild(this.elmSubmit);
		
		this.elmCancel				= document.createElement('input');
		this.elmCancel.name			= "Document_Edit_Cancel";
		this.elmCancel.type			= "button";
		this.elmCancel.value		= "Cancel";
		this.elmCancel.addEventListener('click', this._cancel.bindAsEventListener(this), false);
		this.elmButtonsDIV.appendChild(this.elmCancel);
		
		this.pupEdit.setContent(this.elmEncapsulator);
		this.pupEdit.display();
	},
	
	_submit	: function()
	{
		alert("SUBMIT'D");
		return false;
	},
	
	_cancel	: function(eEvent, bolConfirmed)
	{
		if (bolConfirmed)
		{
			// Confirmed
			this.elmForm.removeEventListener('submit', this._submit.bindAsEventListener(this), false);
			this.elmCancel.removeEventListener('click', this._cancel.bindAsEventListener(this), false);
			this.pupEdit.hide();
		}
		else if (bolConfirmed == undefined)
		{
			// Prompt
			var strPopupId	= 'Flex_Document_Edit_Cancel_'+(Math.round(Math.random()*100));
			Vixen.Popup.YesNoCancel("Are you sure you want to cancel and revert all changes?", this._cancel.bind(this, null, true), Vixen.Popup.Close.bind(Vixen.Popup, strPopupId), null, null, strPopupId, "Revert Changes");
		}
		else
		{
			// Do nothing
		}
	}
});