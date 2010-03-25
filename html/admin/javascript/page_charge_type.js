
var Page_Charge_Type = Class.create(
{
	initialize	: function(oContainerDiv, iMaxRecordsPerPage)
	{
		this.oDataset		= new Dataset_Ajax(Dataset_Ajax.CACHE_MODE_NO_CACHING, {strObject: 'Charge_Type', strMethod: 'getChargeTypes'});
		this.oPagination	= new Pagination(this._updateTable.bind(this), Page_Charge_Type.MAX_RECORDS_PER_PAGE, this.oDataset);
		
		// Create the page html
		var sButtonPathBase = '../admin/img/template/resultset_';
		
		oContainerDiv.appendChild(
			$T.div(
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
							$T.th('Amount ($ inc GST)'),
							$T.th('Nature'),
							$T.th('Actions')
						)
					),
					$T.tbody(
						// ...	
					),
					$T.tfoot(
						// ...
					)
				)
			)
		);
		
		this.oPagination.getCurrentPage();
	},
	
	_updateTable	: function(oResultSet)
	{
		debugger;
	}
});

Page_Charge_Type.MAX_RECORDS_PER_PAGE	= 25;
