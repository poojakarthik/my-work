
var Popup_Correspondence_Run_Batch_Ledger	= Class.create(Reflex_Popup, 
{
	initialize	: function($super)
	{
		$super(53);
		
		this.oDataSet		= 	new Dataset_Ajax(
									Dataset_Ajax.CACHE_MODE_NO_CACHING, 
									Popup_Correspondence_Run_Batch_Ledger.DATA_SET_DEFINITION
								);
		this.oPagination	= 	new Pagination(
									this._updateTable.bind(this), 
									Popup_Correspondence_Run_Batch_Ledger.MAX_RECORDS_PER_PAGE, 
									this.oDataSet
								);

		this._hBatchState	= {};
		
		// Create filter object
		this._oFilter	= new Filter(this.oDataSet, this.oPagination);
		this._oFilter.addFilter('batch_datetime', {iType: Filter.FILTER_TYPE_RANGE});
				
		var oDateTimeConfig	= Popup_Correspondence_Run_Batch_Ledger.FIELD_CONFIG.batch_datetime;
		var oMinDateTime	= Control_Field.factory(oDateTimeConfig.sType, oDateTimeConfig.oConfig);
		oMinDateTime.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oMinDateTime.disableValidationStyling();
		this._oMinDateTime	= oMinDateTime;
		
		var oMaxDateTime	= Control_Field.factory(oDateTimeConfig.sType, oDateTimeConfig.oConfig);
		oMaxDateTime.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oMaxDateTime.disableValidationStyling();
		this._oMaxDateTime	= oMaxDateTime;
		
		this._oLoadingElement	= 	$T.div({class: 'loading'},
										'Loading...'
									);
		
		var sButtonPathBase		= '../admin/img/template/resultset_';
		var oSection			= new Section(true);
		oSection.setTitleContent(
			$T.span(
				$T.span('Batches '),
				$T.span({class: 'pagination-info'},
					''
				)
			)
		);
		
		oSection.addToHeaderOptions(
			$T.li('From: ', oMinDateTime.getElement())
		);
		
		oSection.addToHeaderOptions(
				$T.li('To: ', oMaxDateTime.getElement())
		);
		
		oSection.addToHeaderOptions(
			$T.li(
				$T.button({class: 'icon-button'},
					'Search'	
				).observe('click', this._filterResults.bind(this))
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
		
		this._oContentDiv 	= 	$T.div({class: 'popup-correspondence-ledger'},
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
		this.setTitle('Correspondence Run Batch Ledger');
		this.addCloseButton();
		this.setContent(this._oContentDiv);
		this.display();
		
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
	},
	
	_createTableRow	: function(oBatch)
	{
		if (oBatch.id != null)
		{
			// Create a tbody with rows listing the correspondence_run(s) in the batch
			var oRunsTBody	= $T.tbody({class: 'alternating'});
			var oRun		= null;
			if (oBatch.aCorrespondenceRuns.length)
			{
				// There are runs
				for (var i = 0; i < oBatch.aCorrespondenceRuns.length; i++)
				{
					oRun	= oBatch.aCorrespondenceRuns[i];
					oRunsTBody.appendChild(
						$T.tr(
							$T.td(oRun.id),
							$T.td(Date.$parseDate(oRun.created, 'Y-m-d H:i:s').$format('d/m/y g:i A')),
							$T.td(oRun.correspondence.length + ' Items'),							
							$T.td({class: 'actions'},
								$T.img({src: '../admin/img/template/magnifier.png', alt: 'View Run Details', title: 'View Run Details'}
								).observe('click', this._showRunDetails.bind(this, oRun.id))
							)
						)
					);
				}
			}
			else
			{
				// There are no runs
				oRunsTBody.appendChild(
					$T.tr(
						$T.td({class: 'no-rows', colspan: 3},
							'There are no Correspondence Runs in this batch.'
						)
					)
				);
			}
			
			// THead for run table
			var oTHead	= 	$T.thead(
								$T.th('ID'),
								$T.th('Created'),
								$T.th('Correspondence'),
								$T.th('')
							);
			
			// Format batch date time
			var sFormattedDateTime	= Date.$parseDate(oBatch.batch_datetime, 'Y-m-d H:i:s').$format('d/m/y g:i A');
			
			// Caption for batch information
			var oCaption	= 	$T.caption({class: 'batch-row'},
									$T.img({src: '../admin/img/template/menu_open_right.png'}),
									$T.span('Batch ' + oBatch.id + ' sent on ' + sFormattedDateTime),
									$T.span({class: 'run-count'},
										oBatch.aCorrespondenceRuns.length + ' Correspondence Runs'
									)
								);
			oCaption.observe('click', this._toggleBatch.bind(this, oBatch.id));
			
			// Create the batch row
			var	oTR	=	$T.tr(
							$T.td(
								$T.table({class: 'reflex highlight-rows'},
									oCaption,
									oTHead,
									oRunsTBody
								)
							)
						);
			
			this._hBatchState[oBatch.id]	= {oTHead: oTHead, oTBody: oRunsTBody, oCaption: oCaption, bVisible: false};
			oTHead.hide();
			oRunsTBody.hide();
			
			return oTR;
		}
		else
		{
			// Invalid, return empty row
			return $T.tr();
		}
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

Object.extend(Popup_Correspondence_Run_Batch_Ledger, 
{
	MAX_RECORDS_PER_PAGE	: 10,
	DATA_SET_DEFINITION		: {sObject: 'Correspondence_Run', sMethod: 'getAllBatches'},
	FIELD_CONFIG	:
	{
		batch_datetime	:
		{
			sType	: 'date-picker',
			oConfig	:
			{
				sLabel		: 'Batch Date & Time', 
				sDateFormat	: 'Y-m-d H:i:s', 
				bTimePicker	: true,
				iYearStart	: 2010,
				iYearEnd	: new Date().getFullYear() + 1,
				mMandatory	: true,
				mEditable	: true,
				mVisible	: true
			}
		}
	}
});
