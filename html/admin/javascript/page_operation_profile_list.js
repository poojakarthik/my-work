
var Page_Operation_Profile_List = Class.create(
{
	initialize	: function(oContainerDiv, iMaxRecordsPerPage)
	{
		// Create DataSet & pagination object
		this.oDataset		= 	new Dataset_Ajax(
									Dataset_Ajax.CACHE_MODE_NO_CACHING, 
									{sObject: 'Operation_Profile', sMethod: 'getAll'}
								);
		this.oPagination	= new Pagination(this._updateTable.bind(this), Page_Operation_Profile_List.MAX_RECORDS_PER_PAGE, this.oDataset);
		
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
												$T.th('Name'),
												$T.th('Description'),
												$T.th('Status'),
												$T.th('Actions')
											)
										),
										$T.tbody({class: 'alternating'}
											// ...
										),
										$T.tfoot( 
											$T.tr(
												$T.th({colspan: '11'},
													$T.button({class: 'icon-button'},
														$T.img({src: Page_Operation_Profile_List.ADD_IMAGE_SOURCE, alt: '', title: 'Add Permission Profile'}),
														$T.span('Add Permission Profile')
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
		oAddButton.observe('click', this._addProfile.bind(this));
		
		// Attach content and get data
		oContainerDiv.appendChild(this.oContentDiv);
		this.oPagination.getCurrentPage();
	},
	
	_updateTable	: function(oResultSet)
	{
		var oTBody = this.oContentDiv.select('table > tbody').first();
		
		// Remove all existing rows
		while (oTBody.firstChild)
		{
			// Remove event handlers from the action buttons
			var oEditButton = oTBody.firstChild.select('img').first();
			
			if (oEditButton)
			{
				oEditButton.stopObserving();
			}
			
			// Remove the row
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
			
			for(var i in aData)
			{
				bAlternateRow = !bAlternateRow;
				oTBody.appendChild(this._createTableRow(aData[i], bAlternateRow));
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
		if (oData.id != null)
		{
			var	oTR	=	$T.tr(
							$T.td(oData.name),
							$T.td(oData.description),
							$T.td(oData.status_label),
							$T.td({class: 'operation-profile-list-action'},
								$T.img({src: Page_Operation_Profile_List.EDIT_IMAGE_SOURCE, alt: 'Edit', title: 'Edit'})
							)
						);
			
			// Add click event to the 'edit' button
			var oEditButton = oTR.select('td.operation-profile-list-action > img').first();
			oEditButton.observe('click', this._editProfile.bind(this, oData.id));
			
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
	
	_addProfile	: function()
	{
		this._editProfile();
	},
	
	_editProfile	: function(iProfileId)
	{
		new Popup_Operation_Profile_Edit(
			(iProfileId ? false : true), 
			iProfileId, 
			this._profileSaved.bind(this, iProfileId)
		);
	},
	
	_profileSaved	: function(iProfileId)
	{
		this.oPagination.getCurrentPage();
	}
});

Page_Operation_Profile_List.MAX_RECORDS_PER_PAGE	= 15;
Page_Operation_Profile_List.EDIT_IMAGE_SOURCE		= '../admin/img/template/group_key.png';
Page_Operation_Profile_List.ADD_IMAGE_SOURCE		= '../admin/img/template/new.png';
