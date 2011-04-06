
var Component_Correspondence_Template_Additional_Columns = Class.create(
{
	initialize : function(bRenderMode)
	{
		this._bRenderMode = bRenderMode;
		this._buildUI();
	},
	
	// Public
	
	getElement : function()
	{
		return this._oElement;
	},
	
	getColumns : function()
	{
		var aRows 		= this._oTBody.childNodes;
		var aColumns 	= [];
		if (this._bRenderMode)
		{
			for (var i = 0; i < aRows.length; i++)
			{
				aColumns.push(
					{
						name			: aRows[i].select('input').first().value,
						description		: aRows[i].select('input').last().value,
						column_index	: i + 1
					}
				);
			}
		}
		return aColumns;
	},
	
	setColumns : function(aColumns)
	{
		// Clear table
		this._oTBody.innerHTML = '';
		
		// Put the columns in order
		aColumns 			= $A(aColumns);
		var hRowsInOrder	= {};
		for (var i = 0; i < aColumns.length; i++)
		{
			var oColumn	= aColumns[i];
			var iIndex	= oColumn.column_index - 1;
			if (!hRowsInOrder[iIndex])
			{
				hRowsInOrder[iIndex] = [];
			}
			hRowsInOrder[iIndex].push(oColumn);
		}
		
		for (var iIndex in hRowsInOrder)
		{
			for (var i = 0; i < hRowsInOrder[iIndex].length; i++)
			{
				var oColumn = hRowsInOrder[iIndex][i];
				this._addColumnRow(oColumn.name, oColumn.description);
			}
		}
	},
	
	// Protected
	
	_buildUI : function()
	{		
		this._oElement =	$T.div({class: 'component-correspondence-template-additional-columns'},	
								$T.table(
									$T.thead(
										$T.tr(
											$T.td('Name'),
											$T.td('Description'),
											$T.td()
										)
									)
								),
								$T.div({class: 'component-correspondence-template-additional-columns-columnlist'},	
									$T.table(
										$T.tbody()
									)
								),
								this._bRenderMode ? $T.table(
									$T.tfoot(
										$T.tr({class: 'component-correspondence-template-additional-columns-add-column-row'},
											$T.td({class: 'component-correspondence-template-additional-columns-column-name'},
												$T.input({type: 'text'})	
											),
											$T.td({class: 'component-correspondence-template-additional-columns-column-description'},
												$T.input({type: 'text'})	
											),
											$T.td(
												$T.button({class: 'icon-button'},
													$T.img({src: '../admin/img/template/new.png'}),
													$T.span('Add Column')
												).observe('click', this._addColumn.bind(this))
											)
										)
									)
								) : null
							);
		this._oAddRow		= this._oElement.select('.component-correspondence-template-additional-columns-add-column-row').first();
		this._oTBody 		= this._oElement.select('tbody').first();
		this._oAddInputs	= this._oElement.select('.component-correspondence-template-additional-columns-add-column-row > td > input');
	},
	
	_addColumn : function()
	{
		var sName 					= this._oAddInputs[0].value;
		var sDescription			= this._oAddInputs[1].value;
		this._oAddInputs[0].value	= '';
		this._oAddInputs[1].value	= '';
		this._addColumnRow(sName, sDescription);
	},
	
	_addColumnRow : function(sName, sDescription)
	{
		var oTR =	$T.tr(
						$T.td({class: 'component-correspondence-template-additional-columns-column-name'},
							this._bRenderMode ? $T.input({type: 'text', value: sName}) : $T.span(sName)
						),
						$T.td({class: 'component-correspondence-template-additional-columns-column-description'},
							this._bRenderMode ? $T.input({type: 'text', value: sDescription}) : $T.span(sDescription)
						),
						this._bRenderMode ? $T.td({class: 'component-correspondence-template-additional-columns-column-move'},
							$T.img({src: '../admin/img/template/icon_moveup.png', alt: 'Move Up', title: 'Move Up'}).observe('click', this._moveColumnUp.bind(this)),
							$T.img({src: '../admin/img/template/icon_movedown.png', alt: 'Move Down', title: 'Move Down'}).observe('click', this._moveColumnDown.bind(this))
						) : null
					);
		this._oTBody.appendChild(oTR);
	},
	
	_moveColumnUp : function(oEvent)
	{
		var oTR = oEvent.target.up().up();
		if (oTR.previousSibling)
		{
			this._oTBody.insertBefore(oTR, oTR.previousSibling);
		}
	},
	
	_moveColumnDown : function(oEvent)
	{
		var oTR = oEvent.target.up().up();
		if (oTR.nextSibling)
		{
			if (oTR.nextSibling.nextSibling)
			{
				this._oTBody.insertBefore(oTR, oTR.nextSibling.nextSibling);
			}
			else
			{
				this._oTBody.appendChild(oTR);
			}
		}
	}
});
