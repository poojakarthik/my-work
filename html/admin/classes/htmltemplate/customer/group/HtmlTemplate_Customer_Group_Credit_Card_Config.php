<?php

class HtmlTemplate_Customer_Group_Credit_Card_Config extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		$renderer = strtolower('render_'.str_replace('-', '', $this->mxdDataToRender['action']));
		if (method_exists($this, $renderer))
		{
			$this->{$renderer}();
		}
	}

	private function render_delete()
	{
		$this->no_config("The configuration has been deleted.", FALSE);
	}

	private function no_config($message="No cofiguration found.", $bolIsError=TRUE)
	{
		$customerGroup = $this->mxdDataToRender['customerGroup'];
		?>
		<div class="message<?=$bolIsError ? " error" : ""?>"><?=htmlspecialchars($message)?></div>
		<form id="view_credit_card_config" name="view_credit_card_config" method="POST">
			<table id="credit_card_config" name="credit_card_config" class="reflex">
				<caption>
					<div id="caption_bar" name="caption_bar">
						<div id="caption_title" name="caption_title">
							Secure Pay Configuration
						</div>
						<div id="caption_options" name="caption_options">
							<a href="<?=Href()->ViewCustomerGroupCreditCardConfig($customerGroup->id, 'Create')?>" >Enter</a>
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
						<td colspan="2" class="title">[ Not configurred ]</td>
					</tr>
				</tbody>
			</table>
		</form>
		<?php
	}

	private function render_create()
	{
		return $this->render_edit('Create');
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

		$config = $this->mxdDataToRender['config'];
		$customerGroup = $this->mxdDataToRender['customerGroup'];

		$edit = '<a href="' . Href()->ViewCustomerGroupCreditCardConfig($config->customerGroupId, 'Edit') . '" >Edit</a>';
		$delete = $config->isSaved() ? (' | <a href="' . Href()->ViewCustomerGroupCreditCardConfig($config->customerGroupId, 'Delete') . '" >Delete</a>') : '';

		$confirmationText = str_replace(array("\n", "\t", "   ", "  "), array("<br/>\n", "&nbsp;&nbsp; &nbsp;", "&nbsp; &nbsp;", "&nbsp;&nbsp;"), htmlspecialchars($config->confirmationText));
		$confirmationEmail = str_replace(array("\n", "\t", "   ", "  "), array("<br/>\n", "&nbsp;&nbsp; &nbsp;", "&nbsp; &nbsp;", "&nbsp;&nbsp;"), htmlspecialchars($config->confirmationEmail));
		$directDebitText = str_replace(array("\n", "\t", "   ", "  "), array("<br/>\n", "&nbsp;&nbsp; &nbsp;", "&nbsp; &nbsp;", "&nbsp;&nbsp;"), htmlspecialchars($config->directDebitText));
		$directDebitDisclaimer = str_replace(array("\n", "\t", "   ", "  "), array("<br/>\n", "&nbsp;&nbsp; &nbsp;", "&nbsp; &nbsp;", "&nbsp;&nbsp;"), htmlspecialchars($config->directDebitDisclaimer));
		$directDebitEmail = str_replace(array("\n", "\t", "   ", "  "), array("<br/>\n", "&nbsp;&nbsp; &nbsp;", "&nbsp; &nbsp;", "&nbsp;&nbsp;"), htmlspecialchars($config->directDebitEmail));

		?>

		<form id="view_credit_card_config" name="view_credit_card_config" method="POST">
			<table id="credit_card_config" name="credit_card_config" class="reflex">
				<caption>

					<div id="caption_bar" name="caption_bar">
						<div id="caption_title" name="caption_title">
							Secure Pay Configuration
						</div>
						<div id="caption_options" name="caption_options">
							<?=$edit?><?=$delete?>
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
						<td class="title">Merchant ID: </td>
						<td><?=htmlspecialchars($config->merchantId)?></td>
					</tr>

					<tr class="alt">
						<td class="title">Password: </td>
						<td><?=htmlspecialchars($config->password)?></td>
					</tr>
					<tr class="alt">
						<td class="title">Payment Confirmation Message: </td>
						<td><?=$confirmationText?></td>

					</tr>
					<tr class="alt">
						<td class="title">Payment Confirmation Email: </td>
						<td><?=$confirmationEmail?></td>
					</tr>
					<tr class="alt">
						<td class="title">Direct Debit Disclaimer/Terms: </td>
						<td><?=$directDebitDisclaimer?></td>
					</tr>
					<tr class="alt">
						<td class="title">Direct Debit Confirmation Message: </td>
						<td><?=$directDebitText?></td>
					</tr>
					<tr class="alt">
						<td class="title">Direct Debit Confirmation Email: </td>
						<td><?=$directDebitEmail?></td>
					</tr>
				</tbody>
			</table>
		</form>

		<?php

	}

	private function render_save()
	{
		$message = "The configuration has been saved.";
		$this->render_view($message);
	}


	private function render_edit($requestedAction="Edit")
	{
		$message = array_key_exists('error', $this->mxdDataToRender) ? $this->mxdDataToRender['error'] : '';

		if ($message)
		{
			?>
		<div class="message error"><?=$message?></div><?php
		}

		$invalidValues = $this->mxdDataToRender['invalid_values'];

		$config = $this->mxdDataToRender['config'];
		$customerGroup = $this->mxdDataToRender['customerGroup'];

		$editing = $config->isSaved();

		$save = Href()->ViewCustomerGroupCreditCardConfig($config->customerGroupId, 'Save');
		$cancel = $editing ? Href()->ViewCustomerGroupCreditCardConfig($config->customerGroupId, 'View')
						   : Href()->ViewCustomerGroup($config->customerGroupId, $customerGroup->name);
		$actions = $editing ? ('<a href="'.Href()->ViewCustomerGroupCreditCardConfig($config->customerGroupId, 'View').'" >View</a>') 
							: '';

		$actions .= $config->isSaved() ? (' | <a href="' . Href()->ViewCustomerGroupCreditCardConfig($config->customerGroupId, 'Delete') . '" >Delete</a>') : '';

		$title = 'Secure Pay Configuration';

		?>

		<form id="edit_credit_card_config" name="edit_credit_card_config" method="POST" action="<?=$save?>">
			<table id="credit_card_config" name="credit_card_config" class="reflex">
				<caption>
					<div id="caption_bar" name="caption_bar">

						<div id="caption_title" name="caption_title">
							<?=$title?>
						</div>
						<div id="caption_options" name="caption_options">
							<?=$actions?>
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
						<td class="title">Merchant ID: </td>
						<td><input type="text" id="merchantId" name="merchantId" class="<?=array_key_exists("merchantId", $invalidValues) ? 'invalid' : ''?>" size="50" value="<?=htmlspecialchars($config->merchantId)?>" />
						</td>

					</tr>
					<tr class="alt">
						<td class="title">Password: </td>
						<td><input type="text" id="password" name="password" class="<?=array_key_exists("password", $invalidValues) ? 'invalid' : ''?>" size="50" value="<?=htmlspecialchars($config->password)?>" />
						</td>
					</tr>
					<tr class="alt">
						<td class="title">Payment Confirmation Message: </td>

						<td><textarea id="confirmationText" name="confirmationText" class="<?=array_key_exists("confirmationText", $invalidValues) ? 'invalid' : ''?>" style="position: relative; width: 100%; height: 16em;"><?=htmlspecialchars($config->confirmationText)?></textarea>
						</td>
					</tr>
					<tr class="alt">
						<td class="title">Payment Confirmation Email: </td>
						<td><textarea id="confirmationEmail" name="confirmationEmail" class="<?=array_key_exists("confirmationEmail", $invalidValues) ? 'invalid' : ''?>" style="position: relative; width: 100%; height: 16em;"><?=htmlspecialchars($config->confirmationEmail)?></textarea>
						</td>

					</tr>
					<tr class="alt">
						<td class="title">Direct Debit Disclaimer/Terms: </td>
						<td><textarea id="directDebitDisclaimer" name="directDebitDisclaimer" class="<?=array_key_exists("directDebitDisclaimer", $invalidValues) ? 'invalid' : ''?>" style="position: relative; width: 100%; height: 16em;"><?=htmlspecialchars($config->directDebitDisclaimer)?></textarea>
						</td>
					</tr>
					<tr class="alt">
						<td class="title">Payment &amp; Direct Debit Confirmation Message: </td>

						<td><textarea id="directDebitText" name="directDebitText" class="<?=array_key_exists("directDebitText", $invalidValues) ? 'invalid' : ''?>" style="position: relative; width: 100%; height: 16em;"><?=htmlspecialchars($config->directDebitText)?></textarea>
						</td>
					</tr>
					<tr class="alt">
						<td class="title">Payment &amp; Direct Debit Confirmation Email: </td>
						<td><textarea id="directDebitEmail" name="directDebitEmail" class="<?=array_key_exists("directDebitEmail", $invalidValues) ? 'invalid' : ''?>" style="position: relative; width: 100%; height: 16em;"><?=htmlspecialchars($config->directDebitEmail)?></textarea>

						</td>
					</tr>
				</tbody>
			</table>
		</form>
		<br/>
		<br/>

		<?php

	}
}

?>
