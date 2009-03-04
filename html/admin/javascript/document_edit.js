// Class: Document_Edit
// Handles the creation/editing of Flex Documents
var Document_Edit	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function(strDocumentNature, objDocument)
	{
		if ((new Array('DOCUMENT_NATURE_FILE', 'DOCUMENT_NATURE_FOLDER')).indexOf(strDocumentNature) == -1)
		{
			throw "'"+strDocumentNature+"' is not a valid Document Nature";
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
		this.elmEncapsulator.innerHTML		= "<span>OHAI</span>";
		
		this.pupEdit.setContent(this.elmEncapsulator);
		this.pupEdit.display();
		alert("DONE");
	},
});