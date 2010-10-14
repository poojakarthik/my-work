
var Component_Email_Template_History = Class.create(
{

	initialize	: function(oContainerDiv, iTemplateId, sTemplateName, sCustomerGroup)
	{
		
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
			var fnRequest     = jQuery.json.jsonFunction(this._buildGUI.bind(this), this._buildGUI.bind(this), 'Customer_Group', 'getEmailTemplateHistory');
			fnRequest(this._iTemplateId);
		
		
		}
		else
		{
			
			if (!oResponse.Success)
			{
				Reflex_Popup.alert($T.div($T.span("There was an error, the email template history could not be retrieved: "),$T.textarea( oResponse.message)), {sTitle: 'Email Template Save Error', iWidth: 50});
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
					$T.li('Show Cancelled Versions')			
				);
				
				oDetailsSection.addToHeaderOptions(
					$T.li(this._oShowCancelled.getElement())			
				);
				
				oDetailsSection.addToHeaderOptions(
					 $T.li($T.img({src: 'img/template/new.png' , style:'cursor:pointer', title: 'Create a new Version' }).observe('click', this._editPopupNew.bind(this,this._iTemplateId)))
				
				);
				
				oDetailsSection.setTitleText('Email Template History - ' + this._sTemplateName);
				
				//This is the data table that is the main body of the section
				this._oTable = new Email_Template_Table({class: 'alternating'}, {}, {class: 'reflex highlight-rows'});
				this._oTable.addHeaderField(this._createFieldHeader('Description', false));
				this._oTable.addHeaderField(this._createFieldHeader('Effective Date', false, true));
				this._oTable.addHeaderField(this._createFieldHeader('Created', false));
				this._oTable.addHeaderField(this._createFieldHeader(''));
				
				//an array of table rows for template versions that were never used, they were cancelled before their effective date			
				this._aCancelledVersions = [];
				
				for (var i=0;i<this._aData.length;i++)
				{
					var oRow = this._aData[i];				
					var tr = this._createTableRow(oRow);
					//tr.style.display!='none'?body.appendChild(tr):null;	
					tr.style.display!='none'?this._oTable.appendRow(tr):null;						
				}
				debugger;
				if (this._oTable.rowCount() == 0)
				{
					var tr = document.createElement('tr');
					var td = document.createElement('td');
					var span = document.createElement('span');
					span.className = 'no-versions';
					span.innerHTML = this._aCancelledVersions.length==0?'There are no versions for this template':'There are only cancelled versions for this template. Check \'Show Cancelled Versions\' to display them';
					td.appendChild(span);
					tr.appendChild(td);
					//body.appendChild(tr);
					this._oTable.appendRow(tr);
				}		
				
				
				for (var i=0;i<this._aCancelledVersions.length;i++)
				{
					//body.appendChild(this._aCancelledVersions[i]);	
					this._oTable.appendRow(this._aCancelledVersions[i]);					
				}				

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
	
	_createTableRow	: function(oRow)
	{
		
		var keys = Object.keys(oRow);
		var tr = document.createElement('tr');
		if (oRow.effective_datetime>oRow.end_datetime)
		{			
			tr.style.display = 'none';
			this._aCancelledVersions.push(tr);
			tr.title='This template was never used';		
		}
		
		
			
		var bCancelled = tr.style.display == 'none'?true:false;	
		var td = this._createTableCell(oRow.description, bCancelled);//document.createElement('td');
		tr.appendChild(this._createTableCell(oRow.description, bCancelled));
		tr.appendChild(this._createTableCell(this._formatDate(oRow.effective_datetime), bCancelled));
		//tr.appendChild(this._createTableCell(this._formatDate(oRow.end_datetime), bCancelled));
		//tr.appendChild(this._createTableCell(this._formatDate(oRow.created_timestamp), bCancelled));
		
		//the created cell is special
		var td = document.createElement('td');
		var oContent = document.createElement('span');
		bCancelled?oContent.className = 'line-through':null;					
		oContent.innerHTML = new Date(Date.$parseDate(oRow.created_timestamp,'Y-m-d H:i:s').getTime()).$format('j F Y');	
		
		var oCreatedBy = document.createElement('div');
		oCreatedBy.innerHTML = 'by ' + oRow.employee;
		oCreatedBy.className = 'email-template-history-createdby';
		td.appendChild(oContent);
			td.appendChild(oCreatedBy);
				tr.appendChild(td);	
		
		var oCreateNewVersion				= $T.img({src: 'img/template/new.png' , style:'cursor:pointer', title: 'create a new version based on this version' }, $T.span('New')).observe('click', this._editPopup.bind(this,oRow.template_version_id,oRow.name, oRow.customergroup, this._iTemplateId));
		 
		td = document.createElement('td');
		td.appendChild(oCreateNewVersion);
		tr.appendChild(td);
			
		return tr;	
	},
	
	_formatDate: function(sDate)
	{
		return new Date(Date.$parseDate(sDate,'Y-m-d').getTime()).$format('j F Y');	
	},
	
	_createTableCell: function (mCellContent, bCancelled)
	{
		var td = document.createElement('td');
		var oContent = document.createElement('span');
		bCancelled?oContent.className = 'line-through':null;					
		oContent.innerHTML = mCellContent;
		td.appendChild(oContent);
		return td;
	},
	
	_showCancelledChanged	: function()
	{	
		for (var i=0;i<this._aCancelledVersions.length;i++)
		{
			this._oShowCancelled.getElementValue()?this._aCancelledVersions[i].style.display = '':this._aCancelledVersions[i].style.display = 'none';			
		}
		
	},
	
	_editPopup		: function(template_version_id, name, customergroup, iTemplateId)
	{
		new Popup_Email_Text_Editor(template_version_id ,  name , customergroup, this._refresh.bind(this), iTemplateId);	
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
	