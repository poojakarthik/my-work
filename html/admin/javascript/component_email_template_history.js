
var Component_Email_Template_History = Class.create(
{
	/*
	 * iEmployeeId & bEditMode are used to determine which actions can be performed on a follow-up.
	 * 
	 * If bEditMode is true, then all can be edited, reassigned and closed.
	 * 
	 * If bEditMode is false, then only those who belong to iEmployeeId can be closed or edited (not reassigned).
	 */
	initialize	: function(oContainerDiv, iTemplateId, sTemplateName)
	{
		
		this._iTemplateId = iTemplateId;
		this._sTemplateName = sTemplateName;
		this._oContainerDiv = oContainerDiv;
		
		this._buildGUI();
	},
	
	
	_buildGUI		: function(oResponse)
	{
		debugger;
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
				throw new Exception('error in data retrieval for template history');
			}
			
			this._aData = oResponse.aResults;
			
			
			
			
			var oDetailsSection	= new Section(true, 'invoice-details');
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
			
			
			oDetailsSection.setTitleText('Email Template History - ' + this._sTemplateName);
			
			var body = $T.tbody({class: 'alternating'});
			var table = 	$T.table({class: 'reflex highlight-rows'},
									$T.thead(
										// Column headings
										$T.tr(
											
											
											
											this._createFieldHeader(
												'Description', 
												false
											),
											this._createFieldHeader('Effective Date', false, true),
											this._createFieldHeader('End Date', false, true),
											this._createFieldHeader(
												'Created', 
												false
											),
											this._createFieldHeader('')
										)

									),
									body
									
								);
			
			
			

			
			
			
			
			debugger;
			
			this._aCancelledVersions = [];
			
			for (var i=0;i<this._aData.length;i++)
			{
				var oRow = this._aData[i];
				
				var tr = this._createTableRow(oRow);
				body.appendChild(tr);
			
			}

			oDetailsSection.setContent(table);
			//this._hideLoading();
			this._oContainerDiv.appendChild(oDetailsSection.getElement());
				
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
					tr.title='This template was never used'
				
				}
		
		
			
			var bCancelled = tr.style.display == 'none'?true:false;	
			var td = this._createTableCell(oRow.description, bCancelled);//document.createElement('td');
			tr.appendChild(this._createTableCell(oRow.description, bCancelled));
			tr.appendChild(this._createTableCell(this._formatDate(oRow.effective_datetime), bCancelled));
			tr.appendChild(this._createTableCell(this._formatDate(oRow.end_datetime), bCancelled));
			tr.appendChild(this._createTableCell(this._formatDate(oRow.created_timestamp), bCancelled));
			
					
		
		var oCreateNewVersion				= $T.img({src: 'img/template/new.png' , style:'cursor:pointer' }).observe('click', this._editPopup.bind(this,oRow.template_version_id,oRow.name));
		var td = document.createElement('td');
		td.appendChild(oCreateNewVersion);
		tr.appendChild(td);
		
		
		return tr;
	
	
	},
	
	_formatDate: function(sDate)
	{
		return new Date(Date.$parseDate(sDate,'Y-m-d H:i:s').getTime()).$format('d-m-Y');

	
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
	
	_editPopup		: function(template_version_id, name)
	{
		new Popup_Email_Text_Editor(template_version_id ,  name , 'whatever', 1);	
	},
	
	_showLoading	: function(bShow)
	{
		var oLoading	= this._oContentDiv.select('span.pagination-loading').first();
		if (bShow)
		{
			oLoading.show();
		}
		else
		{
			oLoading.hide();
		}
	},
	
	
	
	_createNoRecordsRow	: function(bOnLoad)
	{
		return $T.tr(
			$T.td({class: 'followup-list-all-norecords', colspan: 0},
				(bOnLoad ? 'Loading...' : 'There are no records to display')
			)
		);
	},
	
	
	
	_createFieldHeader	: function(sLabel, sSortField, bMultiLine)
	{
		//var oSortImg	= $T.img({class: 'followup-list-all-sort-' + (sSortField ? sSortField : '')});
		var oTH			= 	$T.th({class: 'followup-list-all-header' + (bMultiLine ? '-multiline' : '')},
								
								$T.span(sLabel)
							);
				
		return oTH;
	},
});	

Component_Email_Template_History.COLUMNCOUNT = 4;
	