// Class: Document_Explorer
// Handles the Flex Document Explorer
var Document_Explorer	= Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize	: function()
	{
		this.pupExplorer	= new Reflex_Popup(70);
		this.pupExplorer.setTitle('Document Explorer');
		this.pupExplorer.addCloseButton();
		this.pupExplorer.setIcon("../admin/img/template/account.png");
		
		this.arrSelected		= new Array();
		this.intLastSelected	= null;
		this.arrDocuments		= new Array();
		
		// Popup Contents
		this.elmEncapsulator				= document.createElement('div');
		this.elmEncapsulator.style.margin	= "0.5em";
		
		this.elmBreadcrumbDIV			= document.createElement('div');
		this.elmBreadcrumbDIV.className	= "document-explorer-address";
		this.elmEncapsulator.appendChild(this.elmBreadcrumbDIV);
		
		this.elmDocumentExplorerDIV				= document.createElement('div');
		this.elmDocumentExplorerDIV.className	= "document-explorer";
		this.elmEncapsulator.appendChild(this.elmDocumentExplorerDIV);
		
		this.elmHeaderTable				= document.createElement('table');
		this.elmHeaderTable.className	= "reflex document-explorer";
		this.elmHeaderTable.innerHTML	= 	"			<thead>\n" +
											"				<tr>\n" +
											"					<th class='field-name'>Name</th>\n" +
											"					<th class='field-size'>Size</th>" +
											"					<th class='field-date'>Date Modified</th>" +
											"					<th class='field-user'>Modified By</th>" +
											"					<th class='field-actions'></th>" +
											"				</tr>\n" +
											"			</thead>\n";
		this.elmDocumentExplorerDIV.appendChild(this.elmHeaderTable);
		
		this.elmContentDIV				= document.createElement('div');
		this.elmContentDIV.className	= "document-explorer-list";
		this.elmDocumentExplorerDIV.appendChild(this.elmContentDIV);
		
		this.elmContentTable			= document.createElement('table');
		this.elmContentTable.className	= "reflex document-explorer";
		this.elmContentDIV.appendChild(this.elmContentTable);
		
		this.elmContentTableBody			= document.createElement('tbody');
		this.elmContentTable.appendChild(this.elmContentTableBody);
		
		this.elmFooterTable				= document.createElement('table');
		this.elmFooterTable.className	= "reflex document-explorer";
		//this.elmDocumentExplorerDIV.appendChild(this.elmFooterTable);
		
		this.elmStatusDIV			= document.createElement('div');
		this.elmStatusDIV.className	= "document-explorer-status";
		this.elmEncapsulator.appendChild(this.elmStatusDIV);
		
		this.pupExplorer.setContent(this.elmEncapsulator);
	},
	
	update	: function(intDocumentId)
	{
		// Retrieve the children data for this Document
		var fncJsonFunc		= jQuery.json.jsonFunction(Flex.Document.Explorer._render.bind(this), null, 'Document', 'getDirectoryListing');
		fncJsonFunc(intDocumentId);
	},
	
	_render	: function(objResponse)
	{
		// Unregister any outstanding Event Handlers
		this._unregisterEventHandlers();

		if (objResponse.Success)
		{
			// Build Breadcrumb Menu
			this.elmBreadcrumbDIV.innerHTML	= '';
			for (var i = 0; i < objResponse.objDocument.arrPath.length; i++)
			{
				var strIcon	= '';
				if (i != 0)
				{
					this.elmBreadcrumbDIV.innerHTML	+= '<div class="document-explorer-address-separator"><img src="../admin/img/template/menu_open_right.png" /></div>';
					strIcon							= '../admin/img/template/folder.png';
				}
				else
				{
					strIcon							= '../admin/img/template/home.png';
				}
				
				var strOnClick	= (i < objResponse.objDocument.arrPath.length - 1) ? "onclick='Flex.Document.Explorer.update("+objResponse.objDocument.arrPath[i].document_id+");'" : '';
				this.elmBreadcrumbDIV.innerHTML	+= "<div class='document-explorer-address-node' "+strOnClick+"><img class='document-explorer-icon' src='"+strIcon+"' /><span class='node-label'>"+objResponse.objDocument.arrPath[i].friendly_name+"</span></div>";
			}

			// Build Document Listing
			this.elmContentTableBody.innerHTML	= '';
			if (objResponse.objDocument.arrChildren.length)
			{
				// Have children, render each entry
				for (var i = 0; i < objResponse.objDocument.arrChildren.length; i++)
				{
					var objChild	= objResponse.objDocument.arrChildren[i];
					
					var strIcon			= '<img class="document-explorer-icon" src="../admin/img/template/file.png" />';
					var strLink			= 'window.location.href="../admin/reflex.php/File/Document/'+objChild.id+'"';
					var strFriendlyName	= objChild.friendly_name;
					if (objChild.nature == 'DOCUMENT_NATURE_FOLDER')
					{
						// Folder
						strIcon	= '<img title="Folder" class="document-explorer-icon" src="../admin/img/template/folder.png" />';
						strLink	= 'Flex.Document.Explorer.update('+objChild.id+');';
					}
					else
					{
						// File
						if (objChild.has_icon)
						{
							strIcon	= '<img title="File" class="document-explorer-icon" src="../admin/reflex.php/File/Image/FileTypeIcon/'+objChild.file_type_id+'/16x16" />';
						}
						strFriendlyName	+= '.' + objChild.extension;
					}
					
					if (objChild.system)
					{
						var strType	= (objChild.nature == 'DOCUMENT_NATURE_FOLDER') ? 'Folder' : 'File';
						strIcon	+= '<img title="'+strType+'" class="document-explorer-icon-overlay" src="../admin/img/template/system_object.png" />';
					}
					
					var elmTR	= document.createElement('tr');
					elmTR.setAttribute('valign', 'top');
					elmTR.setAttribute('onclick', 'return true;');
					
					var elmTDName	= document.createElement('td');
					elmTDName.className	= 'field-name';
					elmTDName.title		= (objChild.description ? objChild.description : objChild.friendly_name);
					elmTDName.innerHTML	= strIcon+"<span class='record-label'>"+strFriendlyName+"</span>";
					elmTR.appendChild(elmTDName);
					
					var elmTDSize	= document.createElement('td');
					elmTDSize.className	= 'field-size';
					elmTDSize.innerHTML	= (objChild.nature == 'DOCUMENT_NATURE_FILE' ? objChild.file_size+' KB' : '');
					elmTR.appendChild(elmTDSize);
					
					var elmTDDate	= document.createElement('td');
					elmTDDate.className	= 'field-date';
					elmTDDate.innerHTML	= objChild.date_modified;
					elmTR.appendChild(elmTDDate);
					
					var elmTDUser	= document.createElement('td');
					elmTDUser.className	= 'field-user';
					elmTDUser.innerHTML	= objChild.date_modified;
					elmTR.appendChild(elmTDUser);
					
					var elmTDActions	= document.createElement('td');
					elmTDActions.className	= 'field-actions';
					elmTDActions.innerHTML	= '';
					elmTR.appendChild(elmTDActions);
					
					objResponse.objDocument.arrChildren[i].elmTR	= elmTR;
					this.elmContentTableBody.appendChild(objResponse.objDocument.arrChildren[i].elmTR);
					/*
					objResponse.objDocument.arrChildren[i].elmTR.innerHTML	=	"					<td class='field-name' title='"+(objChild.description ? objChild.description : objChild.friendly_name)+"'>"+strIcon+"<span class='record-label'>"+strFriendlyName+"</span></td>\n" +
																				"					<td class='field-size'>"+(objChild.nature == 'DOCUMENT_NATURE_FILE' ? objChild.file_size+' KB' : '')+"</td>\n" +
																				"					<td class='field-date'>"+objChild.date_modified+"</td>\n" +
																				"					<td class='field-user'>"+objChild.modified_by+"</td>\n" +
																				"					<td class='field-actions'></td>\n";
					*/
				}
			}
			else
			{
				// No children
				this.elmContentTableBody.innerHTML	=	"				<tr>\n" +
														"					<td colspan='5' style='text-align:center;'><em>There are no Documents in this Folder.</em></td>\n" +
														"				</tr>\n";
			}

			this.arrDocuments	= objResponse.objDocument.arrChildren;

			// Render the new Popup Contents
			var strHTML	= "\n" +
			"<div style='margin: 0.5em;'>\n" +
			"	<div class='document-explorer-address'>\n" +
			/*"		"+strBreadcrumbMenu+"\n" +*/
			"	</div>\n" +
			"	<div class='document-explorer'>\n" +
			"		<table class='reflex document-explorer'>\n" +
			"			<thead>\n" +
			"				<tr>\n" +
			"					<th class='field-name'>Name</th>\n" +
			"					<th class='field-size'>Size</th>" +
			"					<th class='field-date'>Date Modified</th>" +
			"					<th class='field-user'>Modified By</th>" +
			"					<th class='field-actions'></th>" +
			"				</tr>\n" +
			"			</thead>\n" +
			"		</table>\n" +
			"		<div class='document-explorer-list'>\n" +
			"			<table class='reflex document-explorer'>\n" +
			"				<tbody>\n" +
			/*strDocumentListing +*/
			"				</tbody>\n" +
			"			</table>\n" +
			"		</div>" +
			"	</div>\n" +
			"	<div class='document-explorer-status'>\n" +
			"		<span class='name'>"+objResponse.objDocument.strFriendlyName+"</span><br />\n" +
			"		<span class='description'>"+(objResponse.objDocument.strDescription ? objResponse.objDocument.strDescription : objResponse.objDocument.strFriendlyName)+"</span><br />\n" +
			/*"		<span>"+objResponse.objDocument.arrChildren.length+" object"+((objResponse.objDocument.arrChildren.length == 1) ? '' : 's')+"</span>\n" +*/
			"	</div>\n" +
			"</div>\n";
			
			this.elmStatusDIV.innerHTML	=	"		<span class='name'>"+objResponse.objDocument.strFriendlyName+"</span><br />\n" +
											"		<span class='description'>"+(objResponse.objDocument.strDescription ? objResponse.objDocument.strDescription : objResponse.objDocument.strFriendlyName)+"</span><br />\n";
											/*"		<span>"+objResponse.objDocument.arrChildren.length+" object"+((objResponse.objDocument.arrChildren.length == 1) ? '' : 's')+"</span>\n";*/

			this._registerEventHandlers();
			this.pupExplorer.display();
		}
		else if (objResponse.Success == undefined)
		{
			this.pupExplorer.hide();
			$Alert(objResponse.Message);
			return false;
		}
		else
		{
			this.pupExplorer.hide();
			$Alert(objResponse);
			return false;
		}
	},
	
	recordClick	: function(intDocumentIndex, eEvent)
	{
		//alert(eEvent);
		//alert(intDocumentIndex);
		if (eEvent.ctrlKey)
		{
			// Select/deselect this Record in addition to the currently Selected Records
			var intExistingIndex	= this.arrSelected.indexOf();
			if (intExistingIndex >= 0)
			{
				// Already exists -- remove
				this.arrSelected.splice(intExistingIndex, 1);
			}
			else
			{
				// Doesn't exist -- add
				this.arrSelected.push(intDocumentIndex);
			}
		}
		else if (eEvent.shiftKey)
		{
			// Select everything between the last clicked Record and this Record
			this.arrSelected		= new Array();
			for (var i = this.intLastSelected; i < intDocumentIndex; i++)
			{
				this.arrSelected.push(i);
			}
		}
		else
		{
			// Shift focus to the clicked Record
			this.arrSelected		= new Array(intDocumentIndex);
			this.intLastSelected	= intDocumentIndex;
		}
		
		alert("Update TR Classes ["+this.arrSelected+"]");
		
		// Update the TR Classes
		for (var i = 0; i < this.arrDocuments.length; i++)
		{
			if (this.arrSelected.indexOf(i) >= 0)
			{
				alert("Index "+i+" is selected");
				this.arrDocuments[i].elmTR.addClassName('selected');
			}
			else
			{
				alert("Index "+i+" is unselected");
				this.arrDocuments[i].elmTR.removeClassName('selected');
			}
		}
	},
	
	recordDoubleClick	: function(intDocumentIndex, eEvent)
	{
		var objChild	= this.arrDocuments[intDocumentIndex];
		if (objChild.nature == 'DOCUMENT_NATURE_FOLDER')
		{
			// Explore the folder
			Flex.Document.Explorer.update(objChild.id);
		}
		else
		{
			// Download the file
			window.location.href	= '../admin/reflex.php/File/Document/'+objChild.id;
		}
	},
	
	_registerEventHandlers	: function()
	{
		for (var i = 0; i < this.arrDocuments.length; i++)
		{
			this.arrDocuments[i].elmTR.addEventListener('click', Flex.Document.Explorer.recordClick.bind(this, i), false);
			this.arrDocuments[i].elmTR.addEventListener('dblclick', Flex.Document.Explorer.recordDoubleClick.bind(this, i), false);
		}
	},
	
	_unregisterEventHandlers	: function()
	{
		for (var i = 0; i < this.arrDocuments.length; i++)
		{
			this.arrDocuments[i].elmTR.removeEventListener('click', Flex.Document.Explorer.recordClick.bind(this, i), false);
			this.arrDocuments[i].elmTR.removeEventListener('dblclick', Flex.Document.Explorer.recordDoubleClick.bind(this, i), false);
		}
	}
});

Flex.Document.Explorer = (Flex.Document.Explorer == undefined) ? new Document_Explorer() : Flex.Document.Explorer;