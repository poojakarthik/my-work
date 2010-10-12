
var Popup_Email_Templates	= Class.create(Reflex_Popup, 
{
	initialize	: function($super)
	{
		$super(53);
		
		this.oDataSet		= 	new Dataset_Ajax(
									Dataset_Ajax.CACHE_MODE_NO_CACHING, 
									Popup_Email_Templates.DATA_SET_DEFINITION
								);
		this.oPagination	= 	new Pagination(
									this._updateTable.bind(this), 
									Popup_Email_Templates.MAX_RECORDS_PER_PAGE, 
									this.oDataSet
								);

		this._hBatchState	= {};
		
		
		 this._oFilter	= new Filter(this.oDataSet, this.oPagination);
		
		
		this._oLoadingElement	= 	$T.div({class: 'loading'},
										'Loading...'
									);
		
		var sButtonPathBase		= '../admin/img/template/resultset_';
		var oSection			= new Section(true);
		oSection.setTitleContent(
			$T.span(
				$T.span('Templates '),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		
		oSection.setContent(
			$T.table({class: 'reflex'},
				$T.tbody({class: 'alternating'}
					// ...
				)
			)
		);
		
		oSection.setFooterContent(
			$T.div(
				this._oLoadingElement,
				$T.div({class: 'pagination'},
					$T.button({class: 'pagination-button'},
						$T.img({src: sButtonPathBase + 'first.png'})
					),
					$T.button({class: 'pagination-button'},
						$T.img({src: sButtonPathBase + 'previous.png'})
					),
					$T.button({class: 'pagination-button'},
						$T.img({src: sButtonPathBase + 'next.png'})
					),
					$T.button({class: 'pagination-button'},
						$T.img({src: sButtonPathBase + 'last.png'})
					)
				)
			)
		);
		
		this._oContentDiv 	= 	$T.div({class: 'popup-email-templates'/*'popup-correspondence-ledger'*/},
									oSection.getElement()
								);
		
		// Bind events to the pagination buttons
		var aTopPageButtons		= this._oContentDiv.select('.pagination button.pagination-button');
		aTopPageButtons[0].observe('click', this._changePage.bind(this, 'firstPage'));
		aTopPageButtons[1].observe('click', this._changePage.bind(this, 'previousPage'));
		aTopPageButtons[2].observe('click', this._changePage.bind(this, 'nextPage'));
		aTopPageButtons[3].observe('click', this._changePage.bind(this, 'lastPage'));
		
		// Setup pagination button object
		this.oPaginationButtons = {
			oTop	: {
				oFirstPage		: aTopPageButtons[0],
				oPreviousPage	: aTopPageButtons[1],
				oNextPage		: aTopPageButtons[2],
				oLastPage		: aTopPageButtons[3]
			}
		};
		
		// Attach content and get data
		this.setTitle('Email Templates');
		this.addCloseButton();
		this.setContent(this._oContentDiv);
		this.display();
		
		this._oLoadingPopup	= new Reflex_Popup.Loading();
		this._oLoadingPopup.display();
		
		// Send the initial filter parameters to dataset ajax 
		
		this._oFilter.refreshData(true);
		this.oPagination.getCurrentPage();
	},
	
	display	: function($super)
	{
		$super();
		this.container.style.top = '150px';
	},
	
	_changePage	: function(sAction)
	{
		this._oLoadingElement.show();
		this.oPagination[sAction]();
	},
	
	_updateTable	: function(oResultSet)
	{
		
		var oTBody = this._oContentDiv.select('table > tbody').first();
		
		// Remove all existing rows
		while (oTBody.firstChild)
		{
			// Remove event handlers from the action buttons
			var aActionButtons = oTBody.firstChild.select('img');
			for (var i = 0; i < aActionButtons.length; i++)
			{
				aActionButtons[i].stopObserving();
			}
			
			// Remove the row
			oTBody.firstChild.remove();
		}
		
		// Add the new records
		var oPageInfo	= this._oContentDiv.select('span.pagination-info').first();
		
		// Check if any results came back
		if (!oResultSet || oResultSet.intTotalResults == 0 || oResultSet.arrResultSet.length == 0)
		{
			// No records
			oTBody.appendChild(
				$T.tr(
					$T.td({class: 'no-rows'},
						'There are no records to display'
					)
				)
			);
		}
		else
		{
			// Update Page ? of ?
			var iCurrentPage	= oResultSet.intCurrentPage + 1;
			oPageInfo.innerHTML	= '(Page '+ iCurrentPage +' of ' + oResultSet.intPageCount + ')';
			
			// Add the rows
			var aData	= jQuery.json.arrayAsObject(oResultSet.arrResultSet);
			
			for(var i in aData)
			{
				
				oTBody.appendChild(this._createTableRow(aData[i]));
			}
		}
		
		this._updatePagination();
		
		// Hide the loading div
		this._oLoadingElement.hide();
		
		if (this._oLoadingPopup)
		{
			this._oLoadingPopup.hide();
			delete this._oLoadingPopup;
		}
	},
	
	_createTableRow	: function(oTemplate)
	{
		
		if (oTemplate.id != null)
		{
			// Create a tbody with rows listing the correspondence_run(s) in the batch
			var oCustomerGroupTBody	= $T.tbody({class: 'alternating'});
			var oCustomerGroup		= null;
			if (oTemplate.customerGroupInstances.length)
			{
				for (var i = 0; i < oTemplate.customerGroupInstances.length; i++)
				{
					oCustomerGroup	= oTemplate.customerGroupInstances[i];
					oCustomerGroupTBody.appendChild(
						$T.tr(
							
							$T.td(oCustomerGroup.external_name),
							$T.td({class: 'actions'},
								$T.img({src: '../admin/img/template/edit.png', alt: 'Edit Template Text', title: 'Edit Template Text'}
								).observe('click', this._doEdit.bind(this, oTemplate.name, oCustomerGroup.external_name, oCustomerGroup.id))
							)
							)
						
					);
				}
			}
			else
			{
				// There are no runs
				oCustomerGroupTBody.appendChild(
					$T.tr(
						$T.td({class: 'no-rows', colspan: 3},
							'This template is not defined for any Customer Group'
						)
					)
				);
			}
			
			// THead for run table
			var oTHead	= 	$T.thead(
								$T.th('id'),
								$T.th('Customer Group'),
								$T.th('')
								
							);
			
			
			
			// Caption for batch information
			var oCaption	= 	$T.caption({class: 'template-row'},
									$T.img({src: '../admin/img/template/menu_open_right.png'}),
									$T.span(oTemplate.name ),
									$T.span({class: 'run-count'},
										 'Defined for ' + oTemplate.customerGroupInstances.length + ' Customer Groups'
									)
								);
			oCaption.observe('click', this._toggleBatch.bind(this, oTemplate.id));
			
			// Create the batch row
			var	oTR	=	$T.tr(
							$T.td({class: 'template-table'},
								$T.table({class: 'reflex highlight-rows'},
									oCaption
									//oTHead
								),
								$T.div({class: 'template-table-container'},
									$T.table({class: 'reflex highlight-rows'},
										oCustomerGroupTBody
									)
								)
							)
						);
			
			this._hBatchState[oTemplate.id]	= {oTHead: oTHead, oTBody: oCustomerGroupTBody, oCaption: oCaption, bVisible: false};
			oTHead.hide();
			oCustomerGroupTBody.hide();
			
			return oTR;
		}
		else
		{
			// Invalid, return empty row
			return $T.tr();
		}
	},
	
	_doEdit	: function(sTemplateName, customerGroupName,iTemplateId) 
	{
		var fnRequest     = jQuery.json.jsonFunction(this._getTemplateDetailsSuccess.bind(this, sTemplateName, customerGroupName), Popup_Email_Templates._ajaxError.bind(this), 'Email_Text_Editor', 'getTemplateDetails');
		fnRequest(iTemplateId);		
	},
	
	_getTemplateDetailsSuccess: function (sTemplateName, customerGroupName, oResponse)
	{
		
		new Popup_Email_Text_Editor(oResponse.aTemplateDetails, sTemplateName, customerGroupName, this._unhide.bind(this));
		this.hide();	
	},
	
	_unhide: function()
	{
		this.display();
	},
	
	_updatePagination : function(iPageCount)
	{
		// Update the 'disabled' state of each pagination button
		this.oPaginationButtons.oTop.oFirstPage.disabled 		= true;
		this.oPaginationButtons.oTop.oPreviousPage.disabled		= true;
		this.oPaginationButtons.oTop.oNextPage.disabled 		= true;
		this.oPaginationButtons.oTop.oLastPage.disabled 		= true;
		
		if (iPageCount == undefined)
		{
			// Get the page count
			this.oPagination.getPageCount(this._updatePagination.bind(this));
		}
		else
		{
			if (this.oPagination.intCurrentPage != Pagination.PAGE_FIRST)
			{
				// Enable the first and previous buttons
				this.oPaginationButtons.oTop.oFirstPage.disabled 		= false;
				this.oPaginationButtons.oTop.oPreviousPage.disabled 	= false;
			}
			if (this.oPagination.intCurrentPage < (iPageCount - 1) && iPageCount)
			{
				// Enable the next and last buttons
				this.oPaginationButtons.oTop.oNextPage.disabled 	= false;
				this.oPaginationButtons.oTop.oLastPage.disabled 	= false;
			}
		}
	},
	
	_toggleBatch	: function(iBatchId)
	{
		// Hide all others
		for (var iId in this._hBatchState)
		{
			if (iId != iBatchId)
			{
				this._hBatchState[iId].oCaption.removeClassName('open');
				this._hBatchState[iId].oTHead.hide();
				this._hBatchState[iId].oTBody.hide();
				this._hBatchState[iId].bVisible	= false;
			}
		}
		
		// Toggle the current
		if (this._hBatchState[iBatchId].bVisible)
		{
			this._hBatchState[iBatchId].oCaption.removeClassName('open');
			this._hBatchState[iBatchId].oTHead.hide();
			this._hBatchState[iBatchId].oTBody.hide();
		}
		else
		{
			this._hBatchState[iBatchId].oCaption.addClassName('open');
			this._hBatchState[iBatchId].oTHead.show();
			this._hBatchState[iBatchId].oTBody.show();
		}
		this._hBatchState[iBatchId].bVisible	= !this._hBatchState[iBatchId].bVisible;
	},
	
	_showRunDetails	: function(iRunId)
	{
		new Popup_Correspondence_Run(iRunId);
	},
	
	_filterResults	: function()
	{
		var sMin	= this._oMinDateTime.getElementValue();
		var sMax	= this._oMaxDateTime.getElementValue();
		this._oFilter.setFilterValue('batch_datetime', sMin, sMax);
		this._oLoadingElement.show();
		this._oFilter.refreshData();
	}
});

// Static

Object.extend(Popup_Email_Templates, 
{
	MAX_RECORDS_PER_PAGE	: 10,
	DATA_SET_DEFINITION		: {sObject: 'Email_Text_Editor', sMethod: 'getTemplates'},
	
	_ajaxError	: function(oResponse)
	{
		var oConfig	= {sTitle: 'Error'};
		
		if (oResponse.Message)
		{
			Reflex_Popup.alert(oResponse.Message, oConfig);
		}
		else if (oResponse.ERROR)
		{
			Reflex_Popup.alert(oResponse.ERROR, oConfig);
		}
	},
});
