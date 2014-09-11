//4158-4467
FW.Package.create('SP.Sale.Note', {

	requires: 'FW.GUIComponent.TextareaGroup',
	extends: 'FW.GUIComponent',

	initialize: function(obj)
	{
		if (obj == null)
		{
			this.object = {
				id					: null,
				sale_id				: null,
				created_dealer_id	: null,
				created_timestamp	: null,
				content				: null
			};
		}
		else
		{
			this.object = obj;
		}

		this.sDetailsContainerId	= 'sale-note_'+(this.object.id ? this.object.id : SP.Sale.Note.getNewNoteId())+'_';

		this.elementGroups = {};
	},

	getSaleElementId	: function()
	{
		return this.sDetailsContainerId;
	},

	buildGUI: function()
	{
		var oSetTable		= SP.Sale.Note.getNoteTable(),
			oSetTableRow	= document.createElement('tr'),
			oSetTableCell	= document.createElement('td');

		oSetTable.appendChild(oSetTableRow);
		oSetTableRow.appendChild(oSetTableCell);

		var	oDetailsTable			= document.createElement('table');
		oDetailsTable.id			= this.getSaleElementId();
		oDetailsTable.addClassName('sale-note');
		oSetTableCell.appendChild(oDetailsTable);

		if (this.object.id)
		{
			// Existing Saved Notes are not editable
			oDetailsTable.addClassName('read-only');
		}

		var	oHeaderRow		= document.createElement('tr'),
			oSummaryCell	= document.createElement('td'),
			oButtonCell		= document.createElement('td');
		oDetailsTable.appendChild(oHeaderRow);
		oHeaderRow.appendChild(oSummaryCell);
		oHeaderRow.appendChild(oButtonCell);

		oButtonCell.style.textAlign	= 'right';

		if (!this.object.id)
		{
			// New Note
			var	oButtonDelete				= document.createElement('button');
			oButtonDelete.innerHTML			= 'Delete';
			oButtonDelete.style.marginRight	= '0.25em';

			oButtonDelete.addClassName('sale-item-delete');
			oButtonDelete.addClassName('data-entry');

			oButtonCell.appendChild(oButtonDelete);

			oButtonDelete.observe('click', SP.Sale.Note.deleteNote.curry(this));
		}
		var	oButtonCollapse	= document.createElement('button');
		oButtonCollapse.addClassName('sale-item-collapse');
		oButtonCollapse.innerHTML	= 'Collapse';
		oButtonCollapse.observe('click', this.toggleExpanded.bind(this));
		oButtonCell.appendChild(oButtonCollapse);

		var	oBodyRow	= document.createElement('tr'),
			oBodyCell	= document.createElement('td');
		this.setWorkingTable(document.createElement('table'));
		oDetailsTable.appendChild(oBodyRow);
		oBodyRow.appendChild(oBodyCell);
		oBodyCell.appendChild(this.getWorkingTable());

		oBodyCell.colSpan	= 2;

		// Seems like a waste when there's only one field... :'(
		this.addElementGroup('content',new FW.GUIComponent.TextareaGroup(this.getContent(), true),'Content');
		

		for (var i = 0, aTextAreas = oBodyRow.select('textarea'), j = aTextAreas.length; i < j; i++)
		{
			aTextAreas[i].setStyle({width: '90%', height: '10em'});
			aTextAreas[i].observe('change', this.updateSummaryContent.bind(this));
			aTextAreas[i].observe('keyup', this.updateSummaryContent.bind(this));
		}

		oHeaderRow.addClassName('sale-item-header');
		oBodyRow.addClassName('sale-item-body');

		oSummaryCell.id					= this.getSaleElementId()+'-summary';
		oSummaryCell.style.whiteSpace	= 'nowrap';
		this.updateSummaryContent();

		this.expand();
	},

	updateFromGUI: function($super)
	{
		var bUpdated = $super();
		if (bUpdated)
		{
			this.updateSummaryContent();
		}
		return bUpdated;
	},

	toggleExpanded	: function()
	{
		if ($ID(this.getSaleElementId()).select('.sale-item-body').first().visible())
		{
			this.collapse();
		}
		else
		{
			this.expand();
		}
	},

	expand	: function()
	{
		$ID(this.getSaleElementId()).select('.sale-item-body').first().show();
		$ID(this.getSaleElementId()).select('.sale-item-collapse').first().innerHTML	= 'Collapse';
	},

	collapse	: function()
	{
		$ID(this.getSaleElementId()).select('.sale-item-body').first().hide();
		$ID(this.getSaleElementId()).select('.sale-item-collapse').first().innerHTML	= 'Expand';
	},

	showValidationTip: function()
	{
		return false;
	},

	updateSummaryContent	: function()
	{
		var	oSummaryCell	= $ID(this.getSaleElementId()+'-summary');

		var	sSummary	= "";

		if (!this.object.id)
		{
			sSummary	= "[ New Unsaved Note ]";
		}
		else
		{
			var	oNoteDate	= Date.parseDate(this.object.created_timestamp, 'Y-m-d H:i:s');
			sSummary		= oNoteDate.dateFormat("d/m/Y h:i:sa")+" by "+this.getCreatedDealerName();
		}
		sSummary	+= " &ndash; "+this.getContentSummarised().escapeHTML();

		oSummaryCell.innerHTML	= sSummary;
	},

	setContent: function(value)
	{
		this.object.content = value;
	},

	getContent: function()
	{
		return this.object.content;
	},

	getContentUnsaved	: function()
	{
		return this.elementGroups.content.getValue();
	},

	getContentSummarised	: function()
	{
		// Only want to return a short summary of the Note Content -- so get the first line
		return String(this.getContentUnsaved().strip().split("\n", 1).first());
	},

	getCreatedDealerId: function()
	{
		return this.object.created_dealer_id;
	},

	getCreatedDealerName	: function()
	{
		return (!this.object.id) ? '[ New Unsaved Note ]' : this.object.dealer_name;
	},

	getCreatedTimestamp: function()
	{
		return (!this.object.id) ? '[ New Unsaved Note ]' : this.object.created_timestamp;
	}
}, false);

FW.Package.extend(SP.Sale.Note,
{
	iNewNoteId	: 0,
	oSaleNotes	: {},

	getNewNoteId	: function()
	{
		SP.Sale.Note.iNewNoteId--;
		return SP.Sale.Note.iNewNoteId;
	},

	registerNote	: function(oSaleNote)
	{
		SP.Sale.Note.registerNotes([oSaleNote]);
		oSaleNote.buildGUI();
	},

	registerNotes	: function(aSaleNotes)
	{
		for (var i = 0, j = aSaleNotes.length; i < j; i++)
		{
			var	oSaleNote	= aSaleNotes[i];
			SP.Sale.Note.oSaleNotes[oSaleNote.getSaleElementId()]	= oSaleNote;
			//oSaleNote.buildGUI();
		}

		SP.Sale.getInstance().object.notes	= SP.Sale.Note.getNotesDataAsArray();
	},

	buildGUI: function()
	{

		for (var key in SP.Sale.Note.oSaleNotes)
		{

			SP.Sale.Note.oSaleNotes[key].buildGUI();

		}

	},


	deleteNote	: function(oSaleNote)
	{
		$ID(oSaleNote.getSaleElementId()).remove();
		delete	SP.Sale.Note.oSaleNotes[oSaleNote.getSaleElementId()];

		SP.Sale.getInstance().object.notes	= SP.Sale.Note.getNotesDataAsArray();
	},

	getNoteTable	: function()
	{
		return $ID('sale-notes-table');
	},

	toggleExpandedAll	: function()
	{
		switch ($ID('sale-notes-collapse-all').innerHTML.split(' ', 1).first().toLowerCase())
		{
			case 'collapse':
				SP.Sale.Note.collapseAll();
				break;

			case 'expand':
			default:
				SP.Sale.Note.expandAll();
				break;
		}
	},

	collapseAll	: function()
	{
		$ID('sale-notes-collapse-all').innerHTML	= 'Expand All';
		for (var i in SP.Sale.Note.oSaleNotes)
		{
			SP.Sale.Note.oSaleNotes[i].collapse();
		}
	},

	expandAll	: function()
	{
		$ID('sale-notes-collapse-all').innerHTML	= 'Collapse All';
		for (var i in SP.Sale.Note.oSaleNotes)
		{
			SP.Sale.Note.oSaleNotes[i].expand();
		}
	},

	getNotesAsArray	: function()
	{
		var	aAsArray	= [];
		for (var sElementId in SP.Sale.Note.oSaleNotes)
		{
			aAsArray.push(SP.Sale.Note.oSaleNotes[sElementId]);
		}
		return aAsArray;
	},

	getNotesDataAsArray	: function()
	{
		var	aDataArray	= [];
		for (var sElementId in SP.Sale.Note.oSaleNotes)
		{
			aDataArray.push(SP.Sale.Note.oSaleNotes[sElementId].object);
		}
		return aDataArray;
	}
}, true);
