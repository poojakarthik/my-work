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
		
		this.pupEdit	= new Reflex_Popup(50);
		this.pupEdit.setTitle(this.strMode+' Document');
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
		this.elmForm.addEventListener('submit', this._submit.bind(this), false);
		
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
		
		this.elmInputName			= document.createElement('input');
		this.elmInputName.name		= "Document_Edit_Name";
		this.elmInputName.type		= 'text';
		this.elmInputName.maxLength	= 255;
		this.elmInputsDIV.appendChild(this.elmInputName);
		
		this.elmInputDescription			= document.createElement('input');
		this.elmInputDescription.name		= "Document_Edit_Description";
		this.elmInputDescription.type		= 'text';
		this.elmInputDescription.maxLength	= 1024;
		this.elmInputsDIV.appendChild(this.elmInputDescription);
		
		if (strDocumentNature === 'DOCUMENT_NATURE_FILE')
		{
			this.elmInputFile			= document.createElement('input');
			this.elmInputFile.name		= "Document_Edit_File";
			this.elmInputFile.type		= 'file';
			this.elmInputsDIV.appendChild(this.elmInputFile);
		}
		
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
		this.elmCancel.addEventListener('click', this._cancel.bind(this), false);
		this.elmButtonsDIV.appendChild(this.elmCancel);
		
		this.pupEdit.setContent(this.elmEncapsulator);
		this.pupEdit.display();
	},
	
	_submit	: function()
	{
		alert("SUBMIT'D");
		return false;
	},
	
	_cancel	: function(bolConfirmed)
	{
		alert(bolConfirmed);
		if (bolConfirmed)
		{
			// Confirmed
			this.elmForm.removeEventListener('submit', this._submit.bind(this), false);
			this.elmCancel.removeEventListener('click', this._cancel.bind(this), false);
			this.pupEdit.hide();
		}
		else if (bolConfirmed == undefined)
		{
			// Prompt
			var strPopupId	= 'Flex_Document_Edit_Cancel';
			Vixen.Popup.YesNoCancel("Are you sure you want to cancel and revert all changes?", this._cancel.bind(this, true), Vixen.Popup.Close.curry(strPopupId), null, null, strPopupId, "Revert Changes");
		}
		else
		{
			// Do nothing
		}
	}
});