
var Component_Email_Template_History = Class.create(
{

	initialize	: function(oContainerDiv, iTemplateId, sTemplateName, sCustomerGroup)
	{
		
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._iTemplateId 		= iTemplateId;
		this._sTemplateName 	= sTemplateName;
		this._oContainerDiv 	= oContainerDiv;
		this._sCustomerGroup 	= sCustomerGroup;
		this._buildGUI();
	},
	
	_refresh: function()
	{
		this._buildGUI();
	
	},
	
	
	_buildGUI		: function(oResponse)
	{
		
		if (typeof(oResponse) == 'undefined')
		{
			//get the data
			var fnRequest     = jQuery.json.jsonFunction(this._buildGUI.bind(this), Popup_Email_Text_Editor.errorCallback.bind(this), 'Customer_Group', 'getEmailTemplateHistory');
			fnRequest(this._iTemplateId);
		
		
		}
		else
		{
			
			if (!oResponse.Success)
			{
				Popup_Email_Text_Editor.serverErrorMessage.bind(this,oResponse.message, 'Email Template History Error')();				
			}
			else
			{
			
				this._aData = oResponse.aResults;				
				
				//the section represents the actual gui elements of the component
				var oDetailsSection	= new Section(true, 'version-details');
				//First, the added header options
				this._oShowCancelled = Control_Field.factory('checkbox', {
																			sLabel		: 'Show Cancelled Versions',
																			mMandatory	: true,
																			mEditable	: true,
																			mVisible	: true,
																			bDisableValidationStyling	: true
																		});
				
				this._oShowCancelled.addOnChangeCallback(this._showCancelledChanged.bind(this));				
				
			
				oDetailsSection.addToHeaderOptions(
					this._oShowCancelled.getElement()			
				);
				
				
				oDetailsSection.addToHeaderOptions(
					'Show Cancelled Versions'			
				);
				

				
				oDetailsSection.addToHeaderOptions(
					// $T.li($T.img({src: Popup_Email_Text_Editor.ADD_IMAGE_SOURCE , style:'cursor:pointer', title: 'Create a new Version', class:'add-icon' }).observe('click', this._editPopupNew.bind(this,this._iTemplateId)))
				
										$T.button({class: 'icon-button',  title: 'Create a New Version'},
							$T.img({src: Popup_Email_Text_Editor.ADD_IMAGE_SOURCE, alt: '', title: 'Create a New Version'}),
							$T.span('New Version')
							
						).observe('click', this._editPopupNew.bind(this,this._iTemplateId))
				
				
				);
				
				oDetailsSection.setTitleText('Email Template History - ' + this._sTemplateName);
				
				//This is the data table that is the main body of the section
				this._oTable = new Email_Template_Table({class: 'alternating'}, {}, {class: 'reflex highlight-rows'});
				this._oTable.addHeaderField(this._createFieldHeader(''));
				this._oTable.addHeaderField(this._createFieldHeader('Description', false));
				this._oTable.addHeaderField(this._createFieldHeader('Effective Date', false, true));
				this._oTable.addHeaderField(this._createFieldHeader('Created', false));
				this._oTable.addHeaderField(this._createFieldHeader(''));
				
				this._aTableRows = [];
				for (var i=0;i<this._aData.length;i++)
				{								
					this._aTableRows.push(this._createTableRow(this._aData[i]));
				}
				
				if (this._aTableRows.length == 0)
				{
					var tr = document.createElement('tr');
					tr.appendChild(document.createElement('td'));
					var td = document.createElement('td');
					var span = document.createElement('span');
					span.className = 'no-versions';
					span.innerHTML = 'There are no versions for this template';
					td.appendChild(span);
					
					tr.appendChild(td);
					tr.cancelled = false;					
					this._aTableRows.push(tr);
					
				}
				this._refreshTable(false);

				oDetailsSection.setContent(this._oTable.getElement());
			
				if (this._oContainerDiv.select('div.section').first()!=null)
				{
					this._oContainerDiv.replaceChild(oDetailsSection.getElement(),this._oContainerDiv.select('div.section').first());
				}
				else
				{
					this._oContainerDiv.appendChild(oDetailsSection.getElement());
				}
				
			}		
		}
	
	},
	
	_refreshTable: function (bCancelledVersions)
	{
		this._oTable.truncate();
		for(var i=0;i<this._aTableRows.length;i++)
		{		
			this._aTableRows[i].cancelled==false||bCancelledVersions?this._oTable.appendRow(this._aTableRows[i]):null;		
		}	
	},
	
	_createTableRow	: function(oRow)
	{
		
		var keys = Object.keys(oRow);
		var tr = document.createElement('tr');
		if (oRow.effective_datetime>oRow.end_datetime)
		{			
			tr.cancelled = true;
			tr.title='This template was never used';		
		}
		else
		{
			tr.cancelled = false;
		
		}
		var sNow = new Date().$format(Popup_Email_Save_Confirm.FIELD_CONFIG.changeDate.oConfig.sDateFormat);
		var bCurrentVersion;
		oRow.effective_datetime<=sNow&&oRow.end_datetime>sNow?bCurrentVersion = true:bCurrentVersion=false;
		bCurrentVersion?tr.appendChild($T.td({class:'current-version'},$T.img({src: Popup_Email_Text_Editor.SAVE_IMAGE_SOURCE , title: 'This is the Current Version' }))):tr.appendChild($T.td({class:'current-version'}));
		
	
		tr.appendChild(this._createTableCell(oRow.description, tr.cancelled, bCurrentVersion));
		tr.appendChild(this._createTableCell(this._formatDate(oRow.effective_datetime), tr.cancelled, bCurrentVersion));
				
		//the 'created' cell is special
		var td = document.createElement('td');
		bCurrentVersion?td.className = 'current-version-text':null;
		var oContent = document.createElement('span');
		tr.cancelled?oContent.className = 'line-through':null;					
		oContent.innerHTML = new Date(Date.$parseDate(oRow.created_timestamp,'Y-m-d H:i:s').getTime()).$format('j F Y');	
		
		var oCreatedBy = document.createElement('div');
		oCreatedBy.innerHTML = 'by ' + oRow.employee;
		oCreatedBy.className = 'email-template-history-createdby';
		td.appendChild(oContent);
			td.appendChild(oCreatedBy);
				tr.appendChild(td);	
		
		var oCreateNewVersion				= $T.img({src: 'img/template/new.png' , style:'cursor:pointer', title: 'create a new version based on this version' }, $T.span('New')).observe('click', this._editPopup.bind(this,oRow.template_version_id,oRow.name, oRow.customergroup, this._iTemplateId, false));
		 var oView =  $T.img({src: Popup_Email_Text_Editor.PREVIEW_IMAGE_SOURCE , style:'cursor:pointer; padding-left: 5px;', title: 'View' }, $T.span('View')).observe('click', this._editPopup.bind(this,oRow.template_version_id,oRow.name, oRow.customergroup, this._iTemplateId, true));
																																											
		td = document.createElement('td');
		
		td.appendChild(oCreateNewVersion);
		td.appendChild(oView);
		tr.appendChild(td);
			
		return tr;	
	},
	
	_formatDate: function(sDate)
	{
		return new Date(Date.$parseDate(sDate,'Y-m-d').getTime()).$format('j F Y');	
	},
	
	_createTableCell: function (mCellContent, bCancelled, bCurrent)
	{
		var td = document.createElement('td');
		var oContent = document.createElement('span');
		bCancelled?oContent.className = 'line-through':null;
		bCurrent?td.className = 'current-version-text':null;
		oContent.innerHTML = mCellContent;
		td.appendChild(oContent);
		return td;
	},
	
	_showCancelledChanged	: function()
	{	
		// for (var i=0;i<this._aCancelledVersions.length;i++)
		// {
			// this._oShowCancelled.getElementValue()?this._aCancelledVersions[i].style.display = '':this._aCancelledVersions[i].style.display = 'none';			
		// }
		bShowCancelled = this._oShowCancelled.getElementValue()?true:false;
		this._refreshTable(bShowCancelled);
		
	},
	
	_editPopup		: function(template_version_id, name, customergroup, iTemplateId, bReadOnly)
	{
		new Popup_Email_Text_Editor(template_version_id ,  name , customergroup, this._refresh.bind(this), iTemplateId,bReadOnly);	
	},
	
	_editPopupNew: function (iTemplateId)
	{
		new Popup_Email_Text_Editor(null ,  this._sTemplateName , this._sCustomerGroup, this._refresh.bind(this), iTemplateId);	
	
	},
	
	_createFieldHeader	: function(sLabel, sSortField, bMultiLine)
	{
		var oTH			= 	$T.th({class: 'followup-list-all-header' + (bMultiLine ? '-multiline' : '')},
								
								$T.span(sLabel)
							);
				
		return oTH;
	},
});	

Component_Email_Template_History.COLUMNCOUNT = 4;
	