
var Page_Recurring_Charge_Type = Class.create(
{
	initialize	: function(oContainerDiv, iMaxRecordsPerPage)
	{
		// Create DataSet & pagination object
		this.oDataset		= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, {strObject: 'Recurring_Charge_Type', strMethod: 'getAll'});
		this.oPagination	= new Pagination(this._updateTable.bind(this), Page_Recurring_Charge_Type.MAX_RECORDS_PER_PAGE, this.oDataset);
		
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
												$T.th({colspan: '2'},
													'Charged'
												),
												$T.th('Minimum Charge ($ inc GST)'),
												$T.th('Cancellation Fee ($ inc GST)'),
												$T.th('Status'),
												$T.th('Actions')
											)
										),
										$T.colgroup(
											$T.col(),
											$T.col(),
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
												$T.th({colspan: '11'},
													$T.button(
														$T.img({src: Page_Recurring_Charge_Type.ADD_IMAGE_SOURCE, alt: '', title: 'Add Adjustment Type'}),
														$T.span('Add Recurring Adjustment Type')
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
		new Popup_Recurring_Charge_Type(this.oPagination.lastPage.bind(this.oPagination));
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
				oTBody.appendChild(this._createTableRow(aData[iId], bAlternateRow));
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
	
	_createTableRow	: function(oData, bAlternateRow)
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
								parseFloat(oData.RecursionCharge).toFixed(2)
							),
							$T.td({class: sNatureTDClass},
								oData.Nature
							),
							$T.td({class: 'charge-amount-fixation'},
								oData.Fixed ? '(Fixed)' : ''
							),
							$T.td(
								{class: 'recurring-charge-charged'},
								$T.div(oData.recursion),
								$T.div(oData.recursion_detail)
							),
							$T.td(oData.Continuable ? '(Continuing)' : ''),
							$T.td({class: 'recurring-charge-min-charge'},
								parseFloat(oData.MinCharge).toFixed(2)
							),
							$T.td({class: 'recurring-charge-cancel-fee'},
								parseFloat(oData.CancellationFee).toFixed(2)
							),
							$T.td(oData.archived_label),
							$T.td({class: 'charge-archive'}
								// Place holder for archive button
							)
						);
			
			// If NOT already archived allow it to be archived
			if (!oData.Archived)
			{
				var oLastTD = oTR.select('td:last-child' ).first();
				
				// Add click event to the 'archive' button
				var oArchiveButton = $T.img({src: Page_Recurring_Charge_Type.ARCHIVE_IMAGE_SOURCE, alt: 'Archive', title: 'Archive'});
				oArchiveButton.observe('click', this._archive.bind(this, oData.Id, false));
				
				oLastTD.appendChild(oArchiveButton);
			}
			
			// Add CSS to the row depending on the nature
			switch (oData.Nature)
			{
				case 'DR':
					oTR.addClassName('charge-nature-debit');
					break;
				case 'CR':
					oTR.addClassName('charge-nature-credit');
					break;
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
											$T.p('Archiving this Recurring Adjustment Type will make it unavailable for use.'),
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
		
		var fnArchiveComplete = function()
		{
			this.oPagination.getCurrentPage();
		}
		
		var oArchivingPopup = new Reflex_Popup.Loading('Archiving...');
		oArchivingPopup.display();
		this.oLoadingOverlay = oArchivingPopup;
		
		// Archive confirmed do the AJAX request
		var fnArchive = jQuery.json.jsonFunction(fnArchiveComplete.bind(this, oArchivingPopup), this._archiveFailed.bind(this), 'Recurring_Charge_Type', 'archive');
		fnArchive(iId);
	},
	
	_archiveFailed	: function(oResponse)
	{
		Reflex_Popup.alert('There was an error accessing the database' + (oResponse.ErrorMessage ? ' (' + oResponse.ErrorMessage + ')' : ''), {sTitle: 'Database Error'});
		
		// Close the loading popup
		if (this.oLoadingOverlay)
		{
			this.oLoadingOverlay.hide();
			delete this.oLoadingOverlay;
		}
	}
});

Page_Recurring_Charge_Type.MAX_RECORDS_PER_PAGE	= 25;
Page_Recurring_Charge_Type.ARCHIVE_IMAGE_SOURCE	= '../admin/img/template/delete.png';
Page_Recurring_Charge_Type.ADD_IMAGE_SOURCE		= '../admin/img/template/new.png';
