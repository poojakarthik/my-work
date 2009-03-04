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
		
		this.elmInputName			= document.createElement('input');
		this.elmInputName.type		= 'text';
		this.elmInputName.maxLength	= 255;
		this.elmForm.appendChild(this.elmInputName);
		
		this.elmInputDescription			= document.createElement('input');
		this.elmInputDescription.type		= 'text';
		this.elmInputDescription.maxLength	= 1024;
		this.elmForm.appendChild(this.elmInputDescription);
		
		this.elmInputFile			= document.createElement('input');
		this.elmInputFile.type		= 'file';
		this.elmForm.appendChild(this.elmInputFile);
		
		this.elmSubmit				= document.createElement('submit');
		this.elmSubmit.name			= "Save";
		
		this.elmCancel				= document.createElement('cancel');
		this.elmCancel.name			= "Cancel";
		this.elmCancel.addEventListener('click', this._cancel.bind(this), false);
		
		this.pupEdit.setFooterButtons(new Array(this.elmSubmit, this.elmCancel), true);
		
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
		if (bolConfirmed)
		{
			// Confirmed
			this.elmForm.removeEventListener('submit', this._submit().bind(this), false);
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