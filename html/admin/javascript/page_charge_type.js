
var Page_Charge_Type = Class.create(
{
	initialize	: function(oContainerDiv, iChargeModel)
	{
		// Get the JSON Handler method using the iChargeModel
		var sMethod			= '';
		var sChargeModel	= '';
		
		switch(iChargeModel)
		{
			case $CONSTANT.CHARGE_MODEL_CHARGE:
				sMethod			= 'getChargeTypes';
				sChargeModel	= 'Charge';
				break;
			case $CONSTANT.CHARGE_MODEL_ADJUSTMENT:
				Reflex_Popup.alert('Deprecated Functionality, please inform YBS.', {sTitle: 'Error'});
				
				sMethod			= 'getAdjustmentTypes';
				sChargeModel	= 'Adjustment';
				break;
			default:
				sMethod		= 'getAllTypes';
				sChargeModel	= 'Charge';
		}
	
		this._iChargeModel	= iChargeModel;
		this._sChargeModel	= sChargeModel;
		
		// Create DataSet & pagination object
		this.oDataset		= 	new Dataset_Ajax(
									Dataset_Ajax.CACHE_MODE_NO_CACHING, 
									{sObject: 'Charge_Type', sMethod: sMethod}
								);
		this.oPagination	= 	new Pagination(
									this._updateTable.bind(this),
									Page_Charge_Type.MAX_RECORDS_PER_PAGE, 
									this.oDataset
								);
		
		// Create the page HTML
		var sButtonPathBase	= '../admin/img/template/resultset_';
		this.oContentDiv 	= $T.div(
								$T.table({class: 'reflex highlight-rows'},
										$T.caption(
											$T.div({class: 'caption_bar'},						
												$T.div({class: 'caption_title'},
													'No records'
												),
												$T.div({class: 'caption_options'},
													$T.div(
														$T.button(
															$T.img({src: sButtonPathBase + 'first.png'})
														),
														$T.button(
															$T.img({src: sButtonPathBase + 'previous.png'})
														),
														$T.button(
															$T.img({src: sButtonPathBase + 'next.png'})
														),
														$T.button(
															$T.img({src: sButtonPathBase + 'last.png'})
														)
													)
												)
											)
										),
										$T.thead(
											$T.tr(
												$T.th('Code'),
												$T.th('Description'),
												$T.th({colspan: '3'},
													'Amount ($ inc GST)'
												),
												$T.th('Visibility'),
												$T.th('Added By'),
												$T.th('Status'),
												$T.th('Actions')
											)
										),
										$T.colgroup(
											$T.col(),
											$T.col(),
											$T.col(),
											$T.col(),
											$T.col()
										),
										$T.tbody({class: 'alternating'}
											// ...
										),
										$T.tfoot( 
											$T.tr(
												$T.th({colspan: '9'},
													$T.button(
														$T.img({src: Page_Charge_Type.ADD_IMAGE_SOURCE, alt: '', title: 'Add Charge Type'}),
														$T.span('Add ' + this._sChargeModel + ' Type')
													)
												)
											)
										)
									),
									$T.div({class: 'footer-pagination'},
										$T.button(
											$T.img({src: sButtonPathBase + 'first.png'})
										),
										$T.button(
											$T.img({src: sButtonPathBase + 'previous.png'})
										),
										$T.button(
											$T.img({src: sButtonPathBase + 'next.png'})
										),
										$T.button(
											$T.img({src: sButtonPathBase + 'last.png'})
										)
									)
								);
		
		// Bind events to the pagination buttons
		var aTopPageButtons		= this.oContentDiv.select('table > caption div.caption_options button');
		var aBottomPageButtons 	= this.oContentDiv.select('div.footer-pagination button');
		// First
		aTopPageButtons[0].observe('click', this.oPagination.firstPage.bind(this.oPagination));
		aBottomPageButtons[0].observe('click', this.oPagination.firstPage.bind(this.oPagination));
		//Previous		
		aTopPageButtons[1].observe('click', this.oPagination.previousPage.bind(this.oPagination));
		aBottomPageButtons[1].observe('click', this.oPagination.previousPage.bind(this.oPagination));
		// Next
		aTopPageButtons[2].observe('click', this.oPagination.nextPage.bind(this.oPagination));
		aBottomPageButtons[2].observe('click', this.oPagination.nextPage.bind(this.oPagination));
		// Last
		aTopPageButtons[3].observe('click', this.oPagination.lastPage.bind(this.oPagination));
		aBottomPageButtons[3].observe('click', this.oPagination.lastPage.bind(this.oPagination));
		
		// Setup pagination button object
		this.oPaginationButtons = {
			oTop	: {
				oFirstPage		: aTopPageButtons[0],
				oPreviousPage	: aTopPageButtons[1],
				oNextPage		: aTopPageButtons[2],
				oLastPage		: aTopPageButtons[3]
			},
			oBottom	: {
				oFirstPage		: aBottomPageButtons[0],
				oPreviousPage	: aBottomPageButtons[1],
				oNextPage		: aBottomPageButtons[2],
				oLastPage		: aBottomPageButtons[3]
			},
		};		
		
		// Bind 'add' button event, making it create a popup which calls back to load the last page of data on completion
		var oAddButton = this.oContentDiv.select('tfoot button').first();
		oAddButton.observe('click', this._showAddPopup.bind(this));
		
		// Attach content and get data
		oContainerDiv.appendChild(this.oContentDiv);
		this.oPagination.getCurrentPage();
	},
	
	_showAddPopup	: function()
	{
		new Popup_Charge_Type(this.oPagination.lastPage.bind(this.oPagination, true), this._iChargeModel);
	},
	
	_updateTable	: function(oResultSet)
	{
		var oTBody = this.oContentDiv.select('table > tbody').first();
		
		// Remove all existing rows
		while (oTBody.firstChild)
		{
			var oArchiveButton = oTBody.firstChild.select('img').first();
			
			if (oArchiveButton)
			{
				oArchiveButton.stopObserving();
			}
			
			//oTBody.removeChild(oTBody.firstChild);
			oTBody.firstChild.remove();
		}
		
		// Add the new records
		var oCaptionTitle = this.oContentDiv.select('table > caption > div.caption_bar > div.caption_title').first();
		
		// Check if any results came back
		if (!oResultSet || oResultSet.intTotalResults == 0 || oResultSet.arrResultSet.length == 0)
		{
			// No records
			oCaptionTitle.innerHTML = 'No Records';
			
			oTBody.appendChild( 
								$T.tr(
									$T.td({colspan: this.oContentDiv.select('table > thead > tr').first().childNodes.length},
										'There are no records to display'
									)
								)
							);
		}
		else
		{
			// Update Page ? of ?
			var iCurrentPage		= oResultSet.intCurrentPage + 1;
			oCaptionTitle.innerHTML	= 'Page '+ iCurrentPage +' of ' + oResultSet.intPageCount;
			
			// Add the rows
			var aData 			= jQuery.json.arrayAsObject(oResultSet.arrResultSet);
			var bAlternateRow	= true;
			
			for(var iId in aData)
			{
				bAlternateRow = !bAlternateRow;
				oTBody.appendChild(this._createChargeTypeRow(aData[iId], bAlternateRow));
			}
			
			this._updatePagination();
		}
		
		// Close the loading popup
		if (this.oLoadingOverlay)
		{
			this.oLoadingOverlay.hide();
			delete this.oLoadingOverlay;
		}
	},
	
	_createChargeTypeRow	: function(oData, bAlternateRow)
	{
		if (oData.Id != null)
		{
			// Add CSS to the nature cell
			var sNatureTDClass = '';
			
			switch (oData.Nature)
			{
				case 'DR':
					sNatureTDClass = 'charge-nature-debit';
					break;
				case 'CR':
					sNatureTDClass = 'charge-nature-credit';
					break;
			}
			
			// Add a row with the charge types details, alternating class applied
			var	oTR	=	$T.tr(
							$T.td(oData.ChargeType),
							$T.td(oData.Description),
							$T.td({class: 'charge-amount-number'},
								parseFloat(oData.Amount).toFixed(2)
							),
							$T.td({class: sNatureTDClass},
								oData.Nature
							),
							$T.td({class: 'charge-amount-fixation'},
								oData.Fixed ? '(Fixed)' : ''
							),
							$T.td(oData.charge_type_visibility_name),
							$T.td(oData.automatic_only_label),
							$T.td(oData.archived_label),
							$T.td({class: 'charge-archive pointer'}
								// Place holder for archive button
							)
						);
			
			// If NOT a 'system only' charge, allow it to be archived
			if (!oData.automatic_only && !oData.Archived)
			{
				var oLastTD = oTR.select('td:last-child' ).first();
				
				// Add click event to the 'archive' button
				var oArchiveButton = $T.img({src: Page_Charge_Type.ARCHIVE_IMAGE_SOURCE, alt: 'Archive', title: 'Archive'});
				oArchiveButton.observe('click', this._archive.bind(this, oData.Id, false));
				
				oLastTD.appendChild(oArchiveButton);
			}
			
			return oTR;
		}
	},
	
	_updatePagination : function(iPageCount)
	{
		// Update the 'disabled' state of each pagination button
		this.oPaginationButtons.oTop.oFirstPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oFirstPage.disabled 	= true;
		this.oPaginationButtons.oTop.oPreviousPage.disabled		= true;
		this.oPaginationButtons.oBottom.oPreviousPage.disabled	= true;
		this.oPaginationButtons.oTop.oNextPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oNextPage.disabled 		= true;
		this.oPaginationButtons.oTop.oLastPage.disabled 		= true;
		this.oPaginationButtons.oBottom.oLastPage.disabled 		= true;
		
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
				this.oPaginationButtons.oBottom.oFirstPage.disabled		= false;
				this.oPaginationButtons.oTop.oPreviousPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oPreviousPage.disabled 	= false;
			}
			if (this.oPagination.intCurrentPage < (iPageCount - 1) && iPageCount)
			{
				// Enable the next and last buttons
				this.oPaginationButtons.oTop.oNextPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oNextPage.disabled 	= false;
				this.oPaginationButtons.oTop.oLastPage.disabled 	= false;
				this.oPaginationButtons.oBottom.oLastPage.disabled 	= false;
			}
		}
	},
	
	_archive	: function(iId, bConfirmed)
	{
		// Show yes, no, cancel
		if (!bConfirmed)
		{
			Reflex_Popup.yesNoCancel(	$T.div(
											$T.p('Archiving this Charge Type will make it unavailable for use.'),
											$T.p('Are you sure you want to archive it?')
										),
										{
											sTitle			: 'Archive Confirmation', 
											sYesLabel		: 'Yes, Archive', 
											sNoLabel		: 'No, do not Archive', 
											fnOnYes			: this._archive.bind(this, iId, true, false)
										});
			return;
		}
		
		this.oLoadingOverlay	= new Reflex_Popup.Loading('Archiving...');
		this.oLoadingOverlay.display();
		
		// Archive confirmed do the AJAX request
		var fnArchive = jQuery.json.jsonFunction(
							this._archiveComplete.bind(this), 
							this._archiveFailed.bind(this), 
							'Charge_Type', 
							'archive'
						);
		fnArchive(iId);
	},
	
	_archiveComplete	: function(oResponse)
	{
		if (oResponse.Success)
		{
			// Handler for getPageCount
			var fnPageCountCallback	= function(iPageCount)
			{
				if (this.oPagination.intCurrentPage > (iPageCount - 1))
				{
					this.oPagination.lastPage(true);
				}
				else
				{
					this.oPagination.getCurrentPage();
				}
			}
			
			// Refresh the current page, or move to the last, if this page is empty
			this.oPagination.getPageCount(fnPageCountCallback.bind(this), true);
		}
		else
		{
			// Hide loading & show error popup
			this.oLoadingOverlay.hide();
			delete this.oLoadingOverlay;
			
			Reflex_Popup.alert((oResponse.Message ? oResponse.Message : ''), {sTitle: 'Error'});
		}
	},
	
	_archiveFailed	: function(oResponse) {
		jQuery.json.errorPopup(oResponse);
		
		// Close the loading popup
		if (this.oLoadingOverlay) {
			this.oLoadingOverlay.hide();
			delete this.oLoadingOverlay;
		}
	}
});

Page_Charge_Type.MAX_RECORDS_PER_PAGE	= 25;
Page_Charge_Type.ARCHIVE_IMAGE_SOURCE	= '../admin/img/template/delete.png';
Page_Charge_Type.ADD_IMAGE_SOURCE		= '../admin/img/template/new.png';
