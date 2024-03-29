// Class: Document_Explorer
// Handles the Flex Document Explorer
var Document_Explorer = Class.create
({
	// Function: initialize()
	// Prototype constructor
	initialize: function () {
		this.pupExplorer = new Reflex_Popup(70);
		this.pupExplorer.setTitle('Document Explorer');
		this.pupExplorer.addCloseButton();
		this.pupExplorer.setIcon("../admin/img/template/folder_explore.png");
		
		this.arrSelected = [];
		this.intLastSelected = null;
		this.arrChildren = [];
		this.objDocument = null;
		this.objUser = null;
		
		// Popup Contents
		this.elmEncapsulator = document.createElement('div');
		this.elmEncapsulator.style.margin = "0.5em";
		
		this.elmBreadcrumbDIV = document.createElement('div');
		this.elmBreadcrumbDIV.className = "document-explorer-address";
		this.elmEncapsulator.appendChild(this.elmBreadcrumbDIV);
		
		this.elmDocumentExplorerDIV = document.createElement('div');
		this.elmDocumentExplorerDIV.className = "document-explorer";
		this.elmEncapsulator.appendChild(this.elmDocumentExplorerDIV);
		
		this.elmHeaderTable = document.createElement('table');
		this.elmHeaderTable.className = "reflex document-explorer";
		this.elmDocumentExplorerDIV.appendChild(this.elmHeaderTable);
		
		this.elmHeaderTHEAD = document.createElement('thead');
		this.elmHeaderTable.appendChild(this.elmHeaderTHEAD);
		
		this.elmHeaderTitlesRow = document.createElement('tr');
		this.elmHeaderTHEAD.appendChild(this.elmHeaderTitlesRow);
		
		this.elmHeaderTitleName = document.createElement('th');
		this.elmHeaderTitleName.className = "field-name";
		this.elmHeaderTitleName.innerHTML = "Name";
		this.elmHeaderTitlesRow.appendChild(this.elmHeaderTitleName);
		
		this.elmHeaderTitleSize = document.createElement('th');
		this.elmHeaderTitleSize.className = "field-size";
		this.elmHeaderTitleSize.innerHTML = "Size";
		this.elmHeaderTitlesRow.appendChild(this.elmHeaderTitleSize);
		
		this.elmHeaderTitleDate = document.createElement('th');
		this.elmHeaderTitleDate.className = "field-date";
		this.elmHeaderTitleDate.innerHTML = "Date Modified";
		this.elmHeaderTitlesRow.appendChild(this.elmHeaderTitleDate);
		
		this.elmHeaderTitleUser = document.createElement('th');
		this.elmHeaderTitleUser.className = "field-user";
		this.elmHeaderTitleUser.innerHTML = "Modified By";
		this.elmHeaderTitlesRow.appendChild(this.elmHeaderTitleUser);
		
		this.elmHeaderTitleActions = document.createElement('th');
		this.elmHeaderTitleActions.className = "field-actions";
		this.elmHeaderTitleActions.innerHTML = "";
		this.elmHeaderTitlesRow.appendChild(this.elmHeaderTitleActions);
		
		this.elmContentDIV = document.createElement('div');
		this.elmContentDIV.className = "document-explorer-list";
		this.elmDocumentExplorerDIV.appendChild(this.elmContentDIV);
		//this.elmContentDIV.addEventListener('click', this.recordClick.bind(this, null), false);
		
		this.elmContentTable = document.createElement('table');
		this.elmContentTable.className = "reflex document-explorer";
		this.elmContentDIV.appendChild(this.elmContentTable);
		
		this.elmContentTableBody = document.createElement('tbody');
		this.elmContentTable.appendChild(this.elmContentTableBody);
		
		this.elmFooterTable = document.createElement('table');
		this.elmFooterTable.className = "reflex document-explorer";
		this.elmDocumentExplorerDIV.appendChild(this.elmFooterTable);
		
		this.elmFooterTFOOT = document.createElement('tfoot');
		this.elmFooterTable.appendChild(this.elmFooterTFOOT);
		
		this.elmFooterActionsSelectedRow = document.createElement('tr');
		this.elmFooterTFOOT.appendChild(this.elmFooterActionsSelectedRow);
		
		this.elmFooterActionsSelectedCell = document.createElement('th');
		this.elmFooterActionsSelectedCell.style.textAlign = 'right';
		this.elmFooterActionsSelectedCell.innerHTML = '&nbsp;';
		this.elmFooterActionsSelectedRow.appendChild(this.elmFooterActionsSelectedCell);
		
		/*this.elmFooterActionsGeneralRow = document.createElement('tr');
		this.elmFooterTHEAD.appendChild(this.elmFooterActionsGeneralRow);
		this.elmHeaderTHEAD.insertBefore(this.elmFooterActionsGeneralRow, this.elmHeaderTitlesRow);*/
		
		this.elmFooterActionsGeneralCell = document.createElement('th');
		this.elmFooterActionsGeneralCell.colSpan = 5;
		this.elmFooterActionsGeneralCell.style.textAlign = 'left';
		this.elmFooterActionsGeneralCell.innerHTML = '&nbsp;';
		//this.elmFooterActionsGeneralRow.appendChild(this.elmFooterActionsGeneralCell);
		this.elmFooterActionsSelectedRow.insertBefore(this.elmFooterActionsGeneralCell, this.elmFooterActionsSelectedCell);
		
		this.elmStatusDIV = document.createElement('div');
		this.elmStatusDIV.className = "document-explorer-status";
		this.elmEncapsulator.appendChild(this.elmStatusDIV);
		
		this.pupExplorer.setContent(this.elmEncapsulator);
	},
	
	update: function (intDocumentId) {
		this.elmContentTableBody.innerHTML = " <tr>\n" +
			" <td colspan='5' style='text-align:center;'><em>Loading</em>&nbsp;<img style='width: 16px; height: 16px;' src='../admin/img/template/loading.gif' /></td>\n" +
			" </tr>\n";
		this.pupExplorer.display();
		
		this.elmEncapsulator.style.cursor = "wait";
		
		// Retrieve the children data for this Document
		var fncJsonFunc = jQuery.json.jsonFunction(Flex.Document.Explorer._render.bind(this), null, 'Document', 'getDirectoryListing');
		fncJsonFunc(intDocumentId);
	},
	
	refresh: function () {
		this.update(this.objDocument ? this.objDocument.intId : null);
	},
	
	_render: function(objResponse) {
		this.elmEncapsulator.style.cursor = "";
		
		// Unregister any outstanding Event Handlers
		this._unregisterEventHandlers();

		if (objResponse.Success) {
			var strIcon;
			// Build Breadcrumb Menu
			this.elmBreadcrumbDIV.innerHTML = '';
			for (var i = 0; i < objResponse.objDocument.arrPath.length; i++) {
				strIcon = '';
				if (i !== 0) {
					this.elmBreadcrumbDIV.innerHTML += '<div class="document-explorer-address-separator"><img src="../admin/img/template/menu_open_right.png" /></div>';
					strIcon = "<img class='document-explorer-icon' src='../admin/img/template/folder.png' />";
				} else {
					strIcon = "<img class='document-explorer-icon' src='../admin/img/template/home.png' />";
				}
				
				var strOnClick = (i < objResponse.objDocument.arrPath.length - 1) ? "onclick='Flex.Document.Explorer.update("+objResponse.objDocument.arrPath[i].document_id+");'" : '';
				this.elmBreadcrumbDIV.innerHTML += "<div class='document-explorer-address-node' "+strOnClick+">"+strIcon+"<span class='node-label'>"+objResponse.objDocument.arrPath[i].friendly_name+"</span></div>";
			}

			// Build Document Listing
			this.elmContentTableBody.innerHTML = '';
			if (objResponse.objDocument.arrChildren.length) {
				// Have children, render each entry
				var	strLink,
					strFriendlyName;
				for (i = 0; i < objResponse.objDocument.arrChildren.length; i++) {
					var objChild = objResponse.objDocument.arrChildren[i];
					
					strIcon = '<img class="document-explorer-icon" src="../admin/img/template/file.png" />';
					strLink = 'window.location.href="../admin/reflex.php/File/Document/'+objChild.id+'"';
					strFriendlyName = objChild.friendly_name;
					if (objChild.nature == 'DOCUMENT_NATURE_FOLDER') {
						// Folder
						strIcon = '<img title="Folder" class="document-explorer-icon" src="../admin/img/template/folder.png" />';
						strLink = 'Flex.Document.Explorer.update('+objChild.id+');';
					} else {
						// File
						if (objChild.has_icon) {
							strIcon = '<img title="File" class="document-explorer-icon" src="../admin/reflex.php/File/Image/FileTypeIcon/'+objChild.file_type_id+'/16x16" />';
						}
						strFriendlyName += '.' + objChild.extension;
					}
					
					if (objChild.system) {
						var strType = (objChild.nature == 'DOCUMENT_NATURE_FOLDER') ? 'Folder' : 'File';
						strIcon += '<img title="'+strType+'" class="document-explorer-icon-overlay" src="../admin/img/template/system_object.png" />';
					}
					
					var elmTR = document.createElement('tr');
					elmTR.setAttribute('valign', 'top');
					elmTR.setAttribute('onclick', 'return true;');
					
					var elmTDName = document.createElement('td');
					elmTDName.className = 'field-name';
					elmTDName.title = (objChild.description ? objChild.description : objChild.friendly_name);
					elmTDName.innerHTML = strIcon+"<span class='record-label'>"+strFriendlyName+"</span>";
					elmTR.appendChild(elmTDName);
					
					var elmTDSize = document.createElement('td');
					elmTDSize.className = 'field-size';
					elmTDSize.innerHTML = (objChild.nature == 'DOCUMENT_NATURE_FILE' ? Flex.Document.byteRound(objChild.file_size, 1) : '');
					elmTR.appendChild(elmTDSize);
					
					var elmTDDate = document.createElement('td');
					elmTDDate.className = 'field-date';
					elmTDDate.innerHTML = objChild.date_modified;
					elmTR.appendChild(elmTDDate);
					
					var elmTDUser = document.createElement('td');
					elmTDUser.className = 'field-user';
					elmTDUser.innerHTML = objChild.modified_by;
					elmTR.appendChild(elmTDUser);
					
					var elmTDActions = document.createElement('td');
					elmTDActions.className = 'field-actions';
					elmTDActions.innerHTML = '';
					elmTR.appendChild(elmTDActions);
					
					objResponse.objDocument.arrChildren[i].elmTR = elmTR;
					this.elmContentTableBody.appendChild(objResponse.objDocument.arrChildren[i].elmTR);
					/*
					objResponse.objDocument.arrChildren[i].elmTR.innerHTML = " <td class='field-name' title='"+(objChild.description ? objChild.description : objChild.friendly_name)+"'>"+strIcon+"<span class='record-label'>"+strFriendlyName+"</span></td>\n" +
																				" <td class='field-size'>"+(objChild.nature == 'DOCUMENT_NATURE_FILE' ? objChild.file_size+' KB' : '')+"</td>\n" +
																				" <td class='field-date'>"+objChild.date_modified+"</td>\n" +
																				" <td class='field-user'>"+objChild.modified_by+"</td>\n" +
																				" <td class='field-actions'></td>\n";
					*/
				}
			} else {
				// No children
				this.elmContentTableBody.innerHTML = " <tr>\n" +
														" <td colspan='5' style='text-align:center;'><em>There are no Documents in this Folder.</em></td>\n" +
														" </tr>\n";
			}
			
			// General Actions Bar
			var strNewFolder = "<span onclick='Flex.Document.Explorer.renderEditPopup({Success:true, nature:\"DOCUMENT_NATURE_FOLDER\"})'><img class='icon' src='../admin/img/template/folder.png' /><img class='icon overlay' src='../admin/img/template/overlay_add.png' />&nbsp;New Folder</span>";
			var strNewDocument = "<span onclick='Flex.Document.Explorer.renderEditPopup({Success:true, nature:\"DOCUMENT_NATURE_FILE\"})'><img class='icon' src='../admin/img/template/file.png' /><img class='icon overlay' src='../admin/img/template/overlay_add.png' />&nbsp;New Document</span>";
			
			if (objResponse.objDocument.editable) {
				this.elmFooterActionsGeneralCell.className = '';
				this.elmFooterActionsGeneralCell.innerHTML = strNewFolder + '&nbsp;|&nbsp;' + strNewDocument;
			} else {
				this.elmFooterActionsGeneralCell.className = 'notice-normal';
				this.elmFooterActionsGeneralCell.innerHTML = 'This Folder is Read-Only';
			}
			
			this.objUser = objResponse.objEmployee;
			this.objDocument = objResponse.objDocument;
			this.arrChildren = objResponse.objDocument.arrChildren;
			this.arrSelected = [];

			this._updateStatusBar();
			this._updateActionBar();
			
			this._registerEventHandlers();
			//this.pupExplorer.recentre();
		} else if (objResponse.Success == null) {			
			this.pupExplorer.hide();
			jQuery.json.errorPopup(objResponse);
			return false;
		} else {
			this.pupExplorer.hide();
			jQuery.json.errorPopup(objResponse);
			return false;
		}
	},
	
	recordClick : function(intDocumentIndex, eEvent) {
		//alert(eEvent);
		//alert(intDocumentIndex);
		if (eEvent.ctrlKey) {
			// Select/deselect this Record in addition to the currently Selected Records
			var intExistingIndex = this.arrSelected.indexOf(intDocumentIndex);
			if (intExistingIndex >= 0) {
				// Already exists -- remove
				this.arrSelected.splice(intExistingIndex, 1);
			} else {
				// Doesn't exist -- add
				this.arrSelected.push(intDocumentIndex);
			}
		} else if (eEvent.shiftKey) {
			// Select everything between the last clicked Record and this Record
			var i = (this.intLastSelected > intDocumentIndex) ? intDocumentIndex : this.intLastSelected;
			var j = (this.intLastSelected > intDocumentIndex) ? this.intLastSelected : intDocumentIndex;
			
			this.arrSelected = [];
			for (i; i <= j; i++) {
				this.arrSelected.push(i);
			}
		} else {
			// Shift focus to the clicked Record
			this.arrSelected = [];
			
			if (intDocumentIndex != null) {
				this.arrSelected.push(intDocumentIndex);
				this.intLastSelected = intDocumentIndex;
				//alert("SELECT'D");
			} else {
				//alert("PURGE'D");
			}
		}
		
		this._updateRecordClasses();
		this._updateStatusBar();
		this._updateActionBar();
	},
	
	recordDoubleClick : function(intDocumentIndex, eEvent) {
		var objChild = this.arrChildren[intDocumentIndex];
		if (objChild.nature == 'DOCUMENT_NATURE_FOLDER') {
			// Explore the folder
			Flex.Document.Explorer.update(objChild.id);
		} else {
			// Download the file
			window.location.href = '../admin/reflex.php/File/Document/'+objChild.id;
		}
	},
	
	_registerEventHandlers : function() {
		for (var i = 0; i < this.arrChildren.length; i++) {
			this.arrChildren[i].elmTR.addEventListener('click', Flex.Document.Explorer.recordClick.bind(this, i), false);
			this.arrChildren[i].elmTR.addEventListener('dblclick', Flex.Document.Explorer.recordDoubleClick.bind(this, i), false);
		}
	},
	
	_unregisterEventHandlers : function() {
		for (var i = 0; i < this.arrChildren.length; i++) {
			this.arrChildren[i].elmTR.removeEventListener('click', Flex.Document.Explorer.recordClick.bind(this, i), false);
			this.arrChildren[i].elmTR.removeEventListener('dblclick', Flex.Document.Explorer.recordDoubleClick.bind(this, i), false);
		}
	},
	
	_updateRecordClasses : function() {
		// Update the TR Classes
		for (var i = 0; i < this.arrChildren.length; i++) {
			if (this.arrSelected.indexOf(i) >= 0) {
				//alert("Index "+i+" is selected");
				this.arrChildren[i].elmTR.addClassName('selected');
			} else {
				//alert("Index "+i+" is unselected");
				this.arrChildren[i].elmTR.removeClassName('selected');
			}
		}
		//alert("CLASS'D");
	},
	
	_updateStatusBar : function() {
		var strIcon = '';
		var strDetails = '';
		
		if (this.arrSelected.length > 1) {
			// Many Selected
			var fltTotalFileSize = 0;
			for (var i = 0; i < this.arrSelected.length; i++) {
				fltTotalFileSize += this.arrChildren[this.arrSelected[i]].file_size;
			}
			
			strDetails = "<span class='name'>"+this.arrSelected.length+" items selected</span><br />\n" +
							"<span>Total File Size: "+Flex.Document.byteRound(fltTotalFileSize, 2)+"</span><br />\n" +
							"";
		} else if (this.arrSelected.length == 1) {
			objChild = this.arrChildren[this.arrSelected[0]];
			
			// Single Selected
			var strFriendlyName = objChild.friendly_name;
			var strType;
			if (objChild.nature == 'DOCUMENT_NATURE_FOLDER') {
				// Folder
				strIcon = '<img title="Folder" class="document-explorer-icon-large" src="../admin/img/template/folder_64x64.png" />';
				strType = "Folder";
			} else {
				// File
				if (objChild.has_icon_large) {
					strIcon = '<img title="File" class="document-explorer-icon-large" src="../admin/reflex.php/File/Image/FileTypeIcon/'+objChild.file_type_id+'/64x64" />';
				}
				strFriendlyName += '.' + objChild.extension;
				strType = objChild.file_type+" File ("+objChild.mime+")";
			}
			
			if (objChild.system) {
				strType = "System "+strType;
				strIcon += '<img title="'+strType+'" class="document-explorer-icon-large-overlay" src="../admin/img/template/system_object_large.png" />';
			}
			
			strDetails = "<span class='name'>"+strFriendlyName+"</span><br />\n" +
							"<span class='description'>"+(objChild.description ? objChild.description : objChild.friendly_name)+"</span><br />\n" +
							"<span>"+strType+"</span><br />\n" +
							"<span>Last Modified:&nbsp;"+objChild.date_modified+" by "+objChild.modified_by+"</span><br />\n" +
							(objChild.nature == 'DOCUMENT_NATURE_FOLDER' ? '' : "<span>Size:&nbsp;"+Flex.Document.byteRound(objChild.file_size, 2)+"</span><br />\n") +
							"";
		} else {
			// Nothing Selected
			strDetails = "<span class='name'>"+this.objDocument.strFriendlyName+"</span><br />\n" +
							"<span class='description'>"+(this.objDocument.strDescription ? this.objDocument.strDescription : this.objDocument.strFriendlyName)+"</span><br />\n";
		}
		
		this.elmStatusDIV.innerHTML = strDetails;
	},
	
	_updateActionBar : function() {
		var arrActions = [];
		
		if (this.arrSelected.length > 1) {
			// Many Documents Selected
			var fltTotalFileSize = 0;
			var arrTypeTotals = [];
			for (var i = 0; i < this.arrSelected.length; i++) {
				var strNature = this.arrChildren[this.arrSelected[i]].nature;
				if (arrTypeTotals.indexOf(strNature) == -1) {
					arrTypeTotals.push(strNature);
				}
			}
			
			if (arrTypeTotals.length > 1) {
				// More than one document nature selected -- no actions
			} else {
				// Only one Document Nature selected -- allow multi-actions
				if (this.arrChildren[this.arrSelected[0]].nature == 'DOCUMENT_NATURE_FILE') {
					// EMAIL
					arrActions.push("<span onclick='"+this._generateEmailCall()+"'><img class='icon' src='../admin/img/template/email.png' />&nbsp;Email</span>");
				}
				
				//arrActions.push("<span onclick='alert(\"Delete some docs!\")'><img class='icon' src='../admin/img/template/delete.png' />&nbsp;Delete</span>");
			}
		} else if (this.arrSelected.length == 1) {
			// Single Document Selected
			objChild = this.arrChildren[this.arrSelected[0]];
			
			if (objChild.nature == 'DOCUMENT_NATURE_FILE') {
				arrActions.push("<span onclick='"+this._generateEmailCall()+"'><img class='icon' src='../admin/img/template/email.png' />&nbsp;Email</span>");
			}
			
			if (objChild.editable) {
				var strEditCall = "(jQuery.json.jsonFunction(Flex.Document.Explorer.renderEditPopup.bind(Flex.Document.Explorer), null, \"Document\", \"getDetails\"))("+objChild.id+")";
				var strDeleteCall = "Flex.Document.Explorer._deleteDocument("+objChild.id+");";
				
				
				arrActions.push("<span onclick='"+strEditCall+"'><img class='icon' src='../admin/img/template/file.png' />&nbsp;Edit</span>");
				arrActions.push("<span onclick='"+strDeleteCall+"'><img class='icon' src='../admin/img/template/delete.png' />&nbsp;Delete</span>");
			}
		} else {
			// Nothing selected -- no actions
		}
		
		this.elmFooterActionsSelectedCell.innerHTML = (arrActions.length ? "With Selected: "+arrActions.join('&nbsp;|&nbsp;') : '');
	},
	
	_generateEmailCall : function() {
		var arrDocuments = [];
		for (var i = 0; i < this.arrSelected.length; i++) {
			var objChild = this.arrChildren[this.arrSelected[i]];
			objEmailDocument = {
				id : objChild.id,
				strFileName: objChild.name+'.'+objChild.extension,
				file_type_id: objChild.file_type_id,
				intFileSizeKB: Math.round(objChild.file_size / 1024)
			};
			arrDocuments.push(objEmailDocument);
		}
		
		var arrFrom = [{
			name : this.objUser.firstName+' '+this.objUser.lastName,
			address : this.objUser.email
		}];
		
		//var strDocuments = "new Array("+arrDocuments.toSource().substring(1, arrDocuments.toSource().length-1)+")";
		var strDocuments = JSON.stringify(arrDocuments);
		var strDescription = "Document"+((arrDocuments.length > 1) ? 's' : '');
		//var strFrom = "new Array("+arrFrom.toSource().substring(1, arrFrom.toSource().length-1)+")";
		var strFrom = JSON.stringify(arrFrom);
		var strSubject = "";
		var strContent = "";
		var strTo = 'null';
		var intAccountId = 'null';
		
		return "Flex.Document.emailDocument("+strDocuments+", \""+strDescription+"\", "+strFrom+", \""+strSubject+"\", \""+strContent+"\", "+strTo+", "+intAccountId+");";
	},
	
	_deleteDocument : function(eEvent, bolConfirmed) {
		var objChild = this.arrChildren[this.arrSelected[0]];
		if (bolConfirmed) {
			// Confirmed
			(jQuery.json.jsonFunction(Flex.Document.Explorer._responseRefresh.bind(Flex.Document.Explorer), null, "Document", "delete"))(objChild.id);
		} else if (bolConfirmed == null) {
			// Prompt
			var strPopupId = 'Flex_Document_Delete_Cancel_'+(Math.round(Math.random()*100));
			
			var strMessage = "Are you sure you want to delete '"+objChild.friendly_name+"'?";
			if (objChild.nature === 'DOCUMENT_NATURE_FOLDER') {
				strMessage += "<br />WARNING: This will also delete all Documents and Folders contained within this Folder!";
			}
			
			Vixen.Popup.YesNoCancel(strMessage, this._deleteDocument.bind(this, null, true), Vixen.Popup.Close.bind(Vixen.Popup, strPopupId), null, null, strPopupId, "Delete Confirmation");
		} else {
			// Do nothing
		}
	},
	
	_responseRefresh : function(oResponse) {
		if (oResponse.Success) {
			this.refresh();
			if (oResponse.Message) {
				$Alert(oResponse.Message);
			}
			return true;
		} else if (oResponse.Success == null) {
			jQuery.json.errorPopup(oResponse);
			return false;
		} else {
			jQuery.json.errorPopup(oResponse);
			return false;
		}
	},
	
	renderEditPopup : function(oResponse) {
		if (oResponse.Success) {
			JsAutoLoader.loadScript("javascript/document_edit.js", (function(intParentDocumentId, strNature, objDocument){return new Document_Edit(intParentDocumentId, strNature, objDocument);}).curry(this.objDocument.intId, oResponse.nature, oResponse.objDocument));
		} else if (oResponse.Success == null) {
			jQuery.json.errorPopup(oResponse);
			return false;
		} else {
			jQuery.json.errorPopup(oResponse);
			return false;
		}
	}
});

Flex.Document.Explorer = (typeof Flex.Document.Explorer === 'undefined') ? new Document_Explorer() : Flex.Document.Explorer;