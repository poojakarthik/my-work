<?php

class HtmlTemplate_Ticketing_Attachment_Types extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		$this->LoadJavascript('ticketing_contact');
	}

	public function Render()
	{
		

		$types = Ticketing_Attachment_Type::listAll();
		$noAttachmentTypes = count($types) ? FALSE : TRUE;

		?>
<script>
<!--
	undoLast = null;

	$deleteType = null;
	$saveType = null;

	blacklistTypes = [];<?php
	$statuses = Ticketing_Attachment_Blacklist_Status::listAll();
	$i = 0;
	foreach ($statuses as $status)
	{
		echo "\n\t\t\t\tblacklistTypes[$i] = { id: {$status->id}, name: '{$status->name}', className: '{$status->cssclass}' };";
		$i++;
	}
	?>;

	function createSelect(selectedClassName)
	{
		var select = document.createElement('select');
		select.id = "typeStatus";
		for (var i = 0, l = blacklistTypes.length; i < l; i++)
		{
			var option = document.createElement('option');
			select.appendChild(option);
			option.className = blacklistTypes[i].className;
			option.appendChild(document.createTextNode(blacklistTypes[i].name));
			option.value = blacklistTypes[i].id;
			option.selected = (selectedClassName == blacklistTypes[i].className);
		}
		return select;
	}

	function addType()
	{
		undoCurrentAction();

		var tr = createNewRow();

		makeRowEditable(null, tr, cancelAddType);
	}

	function undoCurrentAction()
	{
		if (typeof undoLast == 'function')
		{
			undoLast();
		}
		undoLast = null;
	}

	function editType(id)
	{
		undoCurrentAction();

		var editRow = $ID('type_' + id);
		if (!editRow) return false;

		makeRowEditable(id, editRow, cancelEditType);
	}

	function makeRowEditable(id, tr, cancelFunction)
	{
		var input = null, label = null;

		// Create the input for the extension
		tr.cells[0].setAttribute('oldInnerHtml', tr.cells[0].innerHTML);
		input = document.createElement('input');
		input.value = tr.cells[0].textContent;
		input.id = 'typeExtension';
		tr.cells[0].innerHTML = '';
		tr.cells[0].appendChild(input);

		// Create the input for the mime type
		tr.cells[1].setAttribute('oldInnerHtml', tr.cells[1].innerHTML);
		input = document.createElement('input');
		input.value = tr.cells[1].textContent;
		input.id = 'typeMime';
		tr.cells[1].innerHTML = '';
		tr.cells[1].appendChild(input);

		// Create the input for the name
		tr.cells[2].setAttribute('oldInnerHtml', tr.cells[2].innerHTML);
		tr.cells[2].innerHTML = '';
		var select = createSelect(tr.className);
		tr.cells[2].appendChild(select);

		// Create new action links
		tr.cells[3].setAttribute('oldInnerHtml', tr.cells[3].innerHTML);
		tr.cells[3].innerHTML = '';

		var cancel = function () { this.cancelFunction(this.id); }
		cancel = cancel.bind({id: id, cancelFunction: cancelFunction});
		var save = function () { saveType(this.id); }
		save = save.bind({tr: tr, id: id});
		var link = null;

		link = document.createElement('input');
		link.type = 'button';
		link.className = 'reflex-button';
		link.value = "Save";
		Event.observe(link, 'click', save)
		tr.cells[3].appendChild(link);

		link = document.createElement('input');
		link.type = 'button';
		link.className = 'reflex-button';
		link.value = "Cancel";
		Event.observe(link, 'click', cancel)
		tr.cells[3].appendChild(link);

		undoLast = cancel;
	}

	function cancelEditType(id)
	{
		var editRow = $ID('type_' + id);
		if (!editRow) return;
		editRow.cells[0].innerHTML = editRow.cells[0].getAttribute('oldInnerHtml');
		editRow.cells[1].innerHTML = editRow.cells[1].getAttribute('oldInnerHtml');
		editRow.cells[2].innerHTML = editRow.cells[2].getAttribute('oldInnerHtml');
		editRow.cells[3].innerHTML = editRow.cells[3].getAttribute('oldInnerHtml');
	}

	function cancelAddType()
	{
		var tr = $ID('type_new');
		if (!tr) return;
		tr.parentNode.removeChild(tr);
	}

	function saveType(id)
	{
		// Need to submit the values via ajax to get them saved (Note: id may be null!!)
		var extension = $ID('typeExtension').value;
		var mimeType = $ID('typeMime').value;
		var status = $ID('typeStatus').options[$ID('typeStatus').selectedIndex].value;
		$saveType(id, extension, mimeType, status);
	}

	function savedType(savedDetails)
	{
		if (savedDetails['INVALID'])
		{
			$Alert(savedDetails['INVALID']);
			return false;
		}

		// Make sure that all cells are populated, including links
		var tr = null;
		var id = savedDetails['id'];

		if (savedDetails['new'])
		{
			tr = $ID('type_new');
			if (!tr)
			{
				tr = createNewRow();
			}
			tr.id = 'type_' + id;
		}
		else
		{
			tr = $ID('type_' + id);
		}

		tr.className = savedDetails['cssName'];

		tr.cells[0].innerHTML = '';
		tr.cells[0].appendChild(document.createTextNode(savedDetails['extension']));

		tr.cells[1].innerHTML = '';
		tr.cells[1].appendChild(document.createTextNode(savedDetails['mimeType']));

		tr.cells[2].innerHTML = '';
		tr.cells[2].appendChild(document.createTextNode(savedDetails['statusName']));

		if (savedDetails['new'])
		{
			tr.cells[3].innerHTML = '';
			var link = null;
	
			link = document.createElement('a');
			link.href = '#';
			link.appendChild(document.createTextNode('Edit'));
			link.setAttribute('onclick', "editType(" + id + "); return false;");
			tr.cells[3].appendChild(link);
		}
		else
		{
			tr.cells[3].innerHTML = tr.cells[3].getAttribute('oldInnerHtml');
		}

		undoLast = null;
	}

	function createNewRow()
	{
		var tr = $ID('attachment-type-list').tBodies[0].insertRow(-1);
		tr.id = 'type_new';
		tr.className = 'ticketing-attachment-blacklist-status-black';
		var td = null;
		td = tr.insertCell(-1);
		td = tr.insertCell(-1);
		td = tr.insertCell(-1);
		td = tr.insertCell(-1);
		return tr;
	}

	function onTicketingAttachmentTypeLoad()
	{
		remoteClass = 'Ticketing';

		$saveType = jQuery.json.jsonFunction(savedType, null, remoteClass, 'saveTicketingAttachmentType');
	}

	Event.observe(window, 'load', onTicketingAttachmentTypeLoad, false);

//-->
</script>

<table class="reflex" id="attachment-type-list">
	<caption>
		<div id="caption_bar" name="caption_bar">
			<div id="caption_title" name="caption_title">
				Attachment Types
			</div>
			<div id="caption_options" name="caption_options">
				<a href="#" onclick="addType(); return false;">Add</a>
			</div>
		</div>
	</caption>
	<thead class="header">
		<tr>
			<th>File Extension</th>
			<th>Mime Type</th>
			<th>Blacklist Status</th>
			<th>Action</th>
		</tr>
	</thead>
	<tfoot class="footer">
		<tr>
			<th colspan="4">&nbsp;</th>
		</tr>
	</tfoot>
	<tbody>
<?php
		if ($noAttachmentTypes)
		{
?>
		<tr class="alt">
			<td colspan="4">[There are no attachment types]</td>
		</tr>
<?php
		}
		else
		{
			foreach ($types as $type)
			{
				$attachmentTypeExtension = $type->extension;
				$attachmentTypeMimeType = $type->mimeType;
				$blacklistStatus = $type->getBlacklistStatus();
				$blacklistStatusName = $blacklistStatus->name;
?>
		<tr class="<?=$blacklistStatus->cssClass?>" id="type_<?=$type->id?>">
			<td><?=$attachmentTypeExtension?></td>
			<td><?=$attachmentTypeMimeType?></td>
			<td><?=$blacklistStatusName?></td>
			<td>
				<a href="#" onclick="editType(<?=$type->id?>); return false;">Edit</a>
			</td>
		</tr>
<?php
			}
		}
?>
	</tbody>
</table>

		<?php
	}
}