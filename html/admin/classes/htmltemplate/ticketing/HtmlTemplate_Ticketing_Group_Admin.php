<?php

class HtmlTemplate_Ticketing_Group_Admin extends FlexHtmlTemplate
{

	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		//$this->LoadJavascript('reflex_popup');
		//$this->LoadJavascript('ticketing_admin');
	}

	public function Render()
	{
		$renderer = strtolower('render_'.str_replace('-', '', $this->mxdDataToRender['action']));
		if (method_exists($this, $renderer))
		{
			$this->{$renderer}();
		}
	}

	private function render_save()
	{
		$this->render_view("The configuration has been saved.");
	}

	private function render_error()
	{
		$errorMessage = $this->mxdDataToRender['error'];
		$this->render_view($errorMessage, TRUE);
	}

	private function render_view($message=NULL, $bolIsError=FALSE)
	{
		if ($message)
		{
			?>
		<div class="message<?=($bolIsError ? " error" : "")?>"><?=$message?></div><?php
		}

		$customerGroup = $this->mxdDataToRender['customer_group'];
		$customerGroupConfig = $this->mxdDataToRender['customer_group_config'];
		$customerGroupEmails = Ticketing_Customer_Group_Email::listForCustomerGroup($customerGroup);

		$email = $customerGroupConfig->emailReceiptAcknowledgement;
		$email = $email ? str_replace(array("\n", "\t", "   ", "  "), array('<br/>', '&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp; &nbsp;', '&nbsp; '), htmlspecialchars($email)) : '[Not set]';

		?>

		<form id="view_ticketing_group_admin" name="view_ticketing_group_admin" method="POST">
			<table id="ticketing" name="ticketing" class="reflex">
				<caption>
					<div id="caption_bar" class="caption_bar">
					<div id="caption_title" class="caption_title">
						Customer Group Settings: <?=$customerGroup->name?>
					</div>
					<div id="caption_options" class="caption_options">
						<a href="<?=Flex::getUrlBase()?>reflex.php/Ticketing/GroupAdmin/<?=$customerGroup->id?>/Edit" >Edit</a>
					</div>
					</div>
				</caption>
				<thead>
					<tr>
						<th colspan="2">&nbsp;</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="2">
							&nbsp;
						</th>
					</tr>
				</tfoot>
				<tbody>
					<tr class="alt">
						<td class="title">Acknowledge Email Receipts: </td>
						<td><?=htmlspecialchars($customerGroupConfig->acknowledgeEmailReceipts() ? 'Yes' : 'No')?></td>
					</tr>
					<tr class="alt">
						<td class="title">Acknowledgement Email: </td>
						<td><?=$email?></td>
					</tr>
				</tbody>
			</table>
		</form>

		<?php

		$this->render_customer_group_emails(FALSE, $customerGroupEmails, $customerGroupConfig);
	}

	private function render_edit()
	{
		if ($this->mxdDataToRender['error']) 
		{
			?>
		<div class="message error"><?=$this->mxdDataToRender['error']?></div><?php
		}

		$customerGroup = $this->mxdDataToRender['customer_group'];
		$customerGroupConfig = $this->mxdDataToRender['customer_group_config'];
		$customerGroupEmails = Ticketing_Customer_Group_Email::listForCustomerGroup($customerGroup);

		$email = $customerGroupConfig->emailReceiptAcknowledgement ? $customerGroupConfig->emailReceiptAcknowledgement : '';

		$invalidValues = $this->mxdDataToRender['invalid_values'];

		$cancel = Flex::getUrlBase() . '/reflex.php/Ticketing/GroupAdmin/' . $customerGroup->id . '/View';

		?>

		<form id="view_ticketing_group_admin" name="view_ticketing_group_admin" method="POST">
			<input type="hidden" name="save" value="1" />
			<table id="ticketing" name="ticketing" class="reflex">
				<caption>
					<div id="caption_bar" class="caption_bar">
					<div id="caption_title" class="caption_title">
						Ccstomer Group Settings: <?=$customerGroup->name?>
					</div>
					<div id="caption_options" class="caption_options">
						<a href="<?=Flex::getUrlBase()?>/reflex.php/Ticketing/GroupAdmin/<?=$customerGroup->id?>/View" >View</a>
					</div>
					</div>
				</caption>
				<thead>
					<tr>
						<th colspan="2">&nbsp;</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="2">
							<input type="button" class="reflex-button" value="Cancel" onclick="document.location='<?=$cancel?>'" />
							<input type="button" class="reflex-button" value="Save" onclick="this.form.submit()" />
						</th>
					</tr>
				</tfoot>
				<tbody>
					<tr class="alt">
						<td class="title">Acknowledge Email Receipts: </td>
						<td><?php
							$invalid = array_key_exists('acknowledgeEmailReceipts', $invalidValues) ? 'invalid' : '';
							$strSelectedYes = $customerGroupConfig->acknowledgeEmailReceipts() ? ' checked' : '';
							$strSelectedNo = $strSelectedYes ? '' : ' checked';
							?>
							<label for="acknowledgeEmailReceiptsTrue">Yes</label><input type="radio" id="acknowledgeEmailReceiptsTrue" name="acknowledgeEmailReceipts" value="1" class="<?=$invalid?>" <?=$strSelectedYes?>/>
							<label for="acknowledgeEmailReceiptsFalse">No</label><input type="radio" id="acknowledgeEmailReceiptsFalse" name="acknowledgeEmailReceipts" value="0" class="<?=$invalid?>" <?=$strSelectedNo?>/>
					</tr>
					<tr class="alt">
						<td class="title">Acknowledgement Email: <div style="font-weight: normal;"><br/><u>Special entries</u><br/><br/><em>[CUSTOMER_GROUP_NAME]</em> will be replaced by the customer group name.<br/><br/><em>[TICKET_ID]</em> will be replaced by the ticket number in the format 'T12345Z'.</div></td>
						<td><textarea id="emailReceiptAcknowledgement" name="emailReceiptAcknowledgement" class="<?=$invalid?>" style="position: relative; width: 100%; height: 16em;"><?=htmlspecialchars($email)?></textarea></td>
					</tr>
				</tbody>
			</table>

		<?php

		$this->render_customer_group_emails(TRUE, $customerGroupEmails, $customerGroupConfig);

		?>
		</form>
		<?php

	}

	private function render_customer_group_emails($bolEdit, $customerGroupEmails, Ticketing_Customer_Group_Config $customerGroupConfig)
	{
		$options = $bolEdit ? '' : '<a href="#" onclick="addEmail();return false;">Add</a>';

		if (!$bolEdit)
		{
			?>

<script type="text/JavaScript">
<!--

	var customerGroupId = <?=$customerGroupConfig->customerGroupId?>;
	var undoLast = null;

	function addEmail()
	{
		undoCurrentAction();

		var tr = createNewRow();

		makeRowEditable(null, tr, cancelAddEmail);
	}

	function deleteEmail(id)
	{
		undoCurrentAction();

		$deleteEmail(id);
	}

	function deletedEmail(id)
	{
		var deletedRow = $ID('email_' + id);
		if (deletedRow) deletedRow.parentNode.removeChild(deletedRow);
		rejigRows(true);
	}

	function undoCurrentAction()
	{
		if (typeof undoLast == 'function')
		{
			undoLast();
		}
		undoLast = null;
	}

	function editEmail(id)
	{
		undoCurrentAction();

		var editRow = $ID('email_' + id);
		if (!editRow) return false;

		makeRowEditable(id, editRow, cancelEditEmail);
	}

	function makeRowEditable(id, tr, cancelFunction)
	{
		var input = null, label = null;

		// Create the input for the email address
		tr.cells[1].setAttribute('oldInnerHtml', tr.cells[1].innerHTML);
		input = document.createElement('input');
		input.value = tr.cells[1].textContent;
		input.id = 'emailAddress';
		tr.cells[1].innerHTML = '';
		tr.cells[1].appendChild(input);

		// Create the input for the name
		tr.cells[2].setAttribute('oldInnerHtml', tr.cells[2].innerHTML);
		input = document.createElement('input');
		input.value = tr.cells[2].textContent;
		input.id = 'emailName';
		tr.cells[2].innerHTML = '';
		tr.cells[2].appendChild(input);

		// Create the radio buttons for acknowledge emails
		tr.cells[3].setAttribute('oldInnerHtml', tr.cells[3].innerHTML);
		var autoReply = tr.cells[3].textContent == 'Yes';
		tr.cells[3].innerHTML = '';
		label = document.createElement('label');
		label.appendChild(document.createTextNode('Yes'));
		input = document.createElement('input');
		input.type = 'radio';
		input.name = 'autoReply';
		input.value = '1';
		label.for = input.id = 'autoReplyYes';
		input.checked = autoReply;
		tr.cells[3].appendChild(label);
		tr.cells[3].appendChild(input);
		
		label = document.createElement('label');
		label.appendChild(document.createTextNode('No'));
		input = document.createElement('input');
		input.type = 'radio';
		input.name = 'autoReply';
		input.value = '0';
		label.for = input.id = 'autoReplyNo';
		input.checked = !autoReply;
		tr.cells[3].appendChild(label);
		tr.cells[3].appendChild(input);

		// Create new action links
		tr.cells[4].setAttribute('oldInnerHtml', tr.cells[4].innerHTML);
		tr.cells[4].innerHTML = '';

		var cancel = function () { this.cancelFunction(this.id); }
		cancel = cancel.bind({id: id, cancelFunction: cancelFunction});
		var save = function () { saveEmail(this.id); }
		save = save.bind({tr: tr, id: id});
		var link = null;

		link = document.createElement('input');
		link.type = 'button';
		link.className = 'reflex-button';
		link.value = "Save";
		Event.observe(link, 'click', save)
		tr.cells[4].appendChild(link);

		link = document.createElement('input');
		link.type = 'button';
		link.className = 'reflex-button';
		link.value = "Cancel";
		Event.observe(link, 'click', cancel)
		tr.cells[4].appendChild(link);

		undoLast = cancel;
	}

	function cancelEditEmail(id)
	{
		var editRow = $ID('email_' + id);
		if (!editRow) return;
		editRow.cells[1].innerHTML = editRow.cells[1].getAttribute('oldInnerHtml');
		editRow.cells[2].innerHTML = editRow.cells[2].getAttribute('oldInnerHtml');
		editRow.cells[3].innerHTML = editRow.cells[3].getAttribute('oldInnerHtml');
		editRow.cells[4].innerHTML = editRow.cells[4].getAttribute('oldInnerHtml');
	}

	function cancelAddEmail()
	{
		var tr = $ID('email_new');
		if (!tr) return;
		tr.parentNode.removeChild(tr);
		rejigRows(false);
	}

	function saveEmail(id)
	{
		// Need to submit the values via ajax to get them saved (Note: id may be null!!)
		var email = $ID('emailAddress').value;
		var name = $ID('emailName').value;
		var autoReply = $ID('autoReplyYes').checked;
		$saveEmail(id, customerGroupId, email, name, autoReply);
	}

	function savedEmail(savedDetails)
	{
		if (savedDetails['INVALID'])
		{
			$Alert(savedDetails['INVALID']);
			return false;
		}

		// Make sure that all cells (except that in col 0) are populated, including links
		var tr = null;
		var id = savedDetails['id'];

		if (savedDetails['new'])
		{
			tr = $ID('email_new');
			if (!tr)
			{
				tr = createNewRow();
			}
			tr.id = 'email_' + id;
		}
		else
		{
			tr = $ID('email_' + id);
		}

		tr.cells[1].innerHTML = '';
		tr.cells[1].appendChild(document.createTextNode(savedDetails['email']));

		tr.cells[2].innerHTML = '';
		tr.cells[2].appendChild(document.createTextNode(savedDetails['name']));

		tr.cells[3].innerHTML = '';
		tr.cells[3].appendChild(document.createTextNode(savedDetails['autoReply'] ? 'Yes' : 'No'));

		if (savedDetails['new'])
		{
			tr.cells[4].innerHTML = '';
			var link = null;
	
			link = document.createElement('a');
			link.href = '#';
			link.appendChild(document.createTextNode('Edit'));
			link.setAttribute('onclick', "editEmail(" + id + "); return false;");
			tr.cells[4].appendChild(link);

			tr.cells[4].appendChild(document.createTextNode('\u00a0\u00a0\u00a0'));

			link = document.createElement('a');
			link.href = '#';
			link.appendChild(document.createTextNode('Delete'));
			link.setAttribute('onclick', "deleteEmail(" + id + "); return false;");
			tr.cells[4].appendChild(link);
		}
		else
		{
			tr.cells[4].innerHTML = tr.cells[4].getAttribute('oldInnerHtml');
		}

		rejigRows(true);

		undoLast = null;
	}

	function createNewRow()
	{
		var tr = $ID('group-emails-list').tBodies[0].insertRow(-1);
		tr.id = 'email_new';
		var td = null;
		td = tr.insertCell(-1);
		td.appendChild(document.createTextNode('No'));
		td = tr.insertCell(-1);
		td = tr.insertCell(-1);
		td = tr.insertCell(-1);
		td.appendChild(document.createTextNode('No'));
		td = tr.insertCell(-1);
		rejigRows(true);
		return tr;
	}

	function rejigRows(redoLinks)
	{
		var tbody = $ID('group-emails-list').tBodies[0];
		for (var i = 0; i < tbody.rows.length; i++)
		{
			var tr = tbody.rows[i];
			tr.className = (i % 2) == 1 ? 'alt' : '';

			if (redoLinks)
			{
				
			}
		}
	}

	function onTicketingGroupAdminLoad()
	{
		var remoteClass = 'Ticketing';

		$deleteEmail = jQuery.json.jsonFunction(deletedEmail, null, remoteClass, 'deleteGroupEmail');
		$saveEmail = jQuery.json.jsonFunction(savedEmail, null, remoteClass, 'saveGroupEmail');
	}

	Event.observe(window, 'load', onTicketingGroupAdminLoad, false);

//-->
</script>

			<?php
		}

		?>
		<br/>
		<table class="reflex" id="group-emails-list">
			<caption>
				<div id="caption_bar" class="caption_bar">
					<div id="caption_title" class="caption_title">
						Email Addresses
					</div>
					<div id="caption_options" class="caption_options">
						<?=$options?>
					</div>
				</div>
			</caption>
			<thead class="header">
				<tr>
					<th>Default Outgoing Address</th>
					<th>Email Address</th>
					<th>Name</th>
					<th>Acknowledge Emails</th>
					<th style="width: 10%;"><?=$bolEdit ? '' : 'Action'?></th>
				</tr>
			</thead>
			<tfoot class="footer">
				<tr>
					<th colspan="5">&nbsp;</th>
				</tr>
			</tfoot>
			<tbody>
		<?php
			$nrEmails = count($customerGroupEmails);
			if ($nrEmails)
			{
				$alt = FALSE;
				foreach($customerGroupEmails as $customerGroupEmail)
				{
					$altClass = $alt ? ' class="alt"' : '';
					$alt = !$alt;
					$default = ($nrEmails == 1) ? TRUE : $customerGroupEmail->id == $customerGroupConfig->defaultEmailId;
					$defaultLabel = $bolEdit ? ('<input type="radio" name="defaultEmailId" value="' . $customerGroupEmail->id . '"' . ($default ? ' checked' : '') . ' />') : ($default ? 'Yes' : '');
					$link = '';//Flex::getUrlBase() . 'reflex.php/Ticketing/GroupAdmin/' . $customerGroupEmails->id . '/View';
					$id = $customerGroupEmail->id;
		?>
				<tr<?=$altClass?> id="email_<?=$id?>">
					<td><?=$defaultLabel?></td>
					<td><?=htmlspecialchars($customerGroupEmail->email)?></td>
					<td><?=htmlspecialchars($customerGroupEmail->name)?></td>
					<td><?=htmlspecialchars($customerGroupEmail->autoReply() ? 'Yes' : 'No')?></td>
					<td><?php 
						if ($bolEdit) 
						{ 
							echo '&nbsp;'; 
						}
						else 
						{
							$link = "editEmail($id); return false;";
							echo '<a href="#" onclick="' . $link . '">Edit</a>';
							if (!$default)
							{
								$link = "deleteEmail($id); return false;";
								echo '&nbsp;&nbsp;&nbsp;<a href="#" onclick="' . $link . '">Delete</a>';
							}
						}
						?></td>
				</tr>
		<?php
				}
			}
			else
			{
		?>
				<tr id="no_emails_message">
					<td colspan="5">There are no email addresses for this Customer Group. You must configure at least one.</td>
				</tr>
		<?php
			}
		?>
			</tbody>
		</table>

		<?php
	}
}

?>
