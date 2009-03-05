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
		
		this.strDocumentNature	= strDocumentNature;
		this.strMode			= (objDocument) ? 'Edit' : 'New';
		
		this.pupEdit	= new Reflex_Popup(35);
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
		this.elmInputsTDDescription.appendChild(this.elmInputDescription);
		
		// FILE
		if (strDocumentNature === 'DOCUMENT_NATURE_FILE')
		{
			if (objDocument)
			{
				this.elmInputsTRFileReplace			= document.createElement('tr');
				this.elmFileTableBody.appendChild(this.elmInputsTRFileReplace);
				
				this.elmInputsTDFileReplace				= document.createElement('td');
				this.elmInputsTDFileReplace.className	= "label";
				this.elmInputsTDFileReplace.innerHTML	= "Do you want to replace the existing Document Content?";
				this.elmInputsTRFileReplace.appendChild(this.elmInputsTDFileReplace);
				
				this.elmInputFileReplaceNo			= document.createElement('input');
				this.elmInputFileReplaceNo.checked	= true;
				this.elmInputFileReplaceNo.type		= 'radio';
				this.elmInputFileReplaceNo.id		= 'Document_Edit_File_Replace_No';
				this.elmInputFileReplaceNo.name		= 'Document_Edit_File_Replace';
				this.elmInputFileReplaceNo.value	= 'false';
				this.elmInputsTDFileReplace.appendChild(this.elmInputFileReplaceNo);
				
				this.elmLabelFileReplaceNo				= document.createElement('label');
				this.elmLabelFileReplaceNo.setAttribute('for', this.elmInputFileReplaceNo.id);
				this.elmLabelFileReplaceNo.innerHTML	= "Keep the existing Document Content";
				this.elmInputsTDFileReplace.appendChild(this.elmLabelFileReplaceNo);
				
				this.elmInputFileReplaceYes			= document.createElement('input');
				this.elmInputFileReplaceYes.type	= 'radio';
				this.elmInputFileReplaceYes.id		= 'Document_Edit_File_Replace_Yes';
				this.elmInputFileReplaceYes.name	= 'Document_Edit_File_Replace';
				this.elmInputFileReplaceYes.value	= 'true';
				this.elmInputsTDFileReplace.appendChild(this.elmInputFileReplaceYes);
				
				this.elmLabelFileReplaceYes			= document.createElement('label');
				this.elmLabelFileReplaceYes.setAttribute('for', this.elmInputFileReplaceYes.id); 
				this.elmLabelFileReplaceNo.innerHTML	= "Replace the Document Content";
				this.elmInputsTDFileReplace.appendChild(this.elmLabelFileReplaceYes);
			}
			
			this.elmInputsTRFile				= document.createElement('tr');
			this.elmInputsTableBody.appendChild(this.elmInputsTRFile);
			
			this.elmInputsTHFile				= document.createElement('th');
			this.elmInputsTHFile.className		= "label";
			this.elmInputsTHFile.innerHTML		= "File :";
			this.elmInputsTRFile.appendChild(this.elmInputsTHFile);
			
			this.elmInputsTDFile				= document.createElement('td');
			this.elmInputsTDFile.className		= "input";
			this.elmInputsTRFile.appendChild(this.elmInputsTDFile);
			
			this.elmInputFile					= document.createElement('input');
			this.elmInputFile.name				= "Document_Edit_File";
			this.elmInputFile.type				= 'file';
			this.elmInputsTDFile.appendChild(this.elmInputFile);
			
			this._updateFileUpload();
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
	},
	
	_updateFileUpload	: function()
	{
		if (this.elmInputsTDFileReplace != undefined)
		{
			if (this.elmForm.Document_Edit_File_Replace.value == 'true')
			{
				this.elmInputsTRFile.style.display	= 'table-row';
				this.elmInputFile.disabled			= false;
			}
			else
			{
				this.elmInputsTRFile.style.display	= 'none';
				this.elmInputFile.disabled			= true;
			}
		}
		else
		{
			this.elmInputsTRFile.style.display	= 'table-row';
			this.elmInputFile.disabled			= false;
		}
	}
});