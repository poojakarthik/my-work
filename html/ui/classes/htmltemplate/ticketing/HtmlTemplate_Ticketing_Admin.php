<?php

class HtmlTemplate_Ticketing_Admin extends FlexHtmlTemplate
{

	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		//$this->LoadJavascript('reflex_popup');
		//$this->LoadJavascript('ticketing_contact');
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

		$config = $this->mxdDataToRender['config'];

		?>

		<form id="view_ticketing_admin" name="view_ticketing_admin" method="POST">
			<table id="ticketing" name="ticketing" class="reflex">
				<caption>
					<div id="caption_bar" name="caption_bar">
					<div id="caption_title" name="caption_title">
						System Settings
					</div>
					<div id="caption_options" name="caption_options">
						<a href="<?=Flex::getUrlBase()?>/reflex.php/Ticketing/Admin/Edit" >Edit</a>
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
						<td class="title">Source Directory: </td>
						<td><?=htmlspecialchars($config->getSourceDirectory(FALSE))?></td>
					</tr>
					<tr class="alt">
						<td class="title">Archive Directory: </td>
						<td><?=htmlspecialchars($config->getBackupDirectory(FALSE))?></td>
					</tr>
					<tr class="alt">
						<td class="title">Junk Mail Directory: </td>
						<td><?=htmlspecialchars($config->getJunkDirectory(FALSE))?></td>
					</tr>
				</tbody>
			</table>
		</form>

		<?php

		$this->render_customer_groups();
	}

	private function render_edit($requestedAction="Edit")
	{
		if ($this->mxdDataToRender['error']) 
		{
			?>
		<div class="message error"><?=$this->mxdDataToRender['error']?></div><?php
		}

		$config = $this->mxdDataToRender['config'];
		$invalidValues = $this->mxdDataToRender['invalid_values'];

		$cancel = Flex::getUrlBase() . '/reflex.php/Ticketing/Admin/View';

		?>

		<form id="edit_ticketing_admin" name="edit_ticketing_admin" method="POST">
			<input type="hidden" name="save" value="1" />
			<table id="ticketing" name="ticketing" class="reflex">
				<caption>
					<div id="caption_bar" name="caption_bar">
					<div id="caption_title" name="caption_title">
						System Settings
					</div>
					<div id="caption_options" name="caption_options">
						<a href="<?=Flex::getUrlBase()?>/reflex.php/Ticketing/Admin/View" >View</a>
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
						<td class="title">Source Directory: </td>
						<td><?php
							$invalid = array_key_exists('sourcePath', $invalidValues) ? 'invalid' : '';
							$value = $config->getSourceDirectory(FALSE);
							$value = $value ? htmlspecialchars($value) : '';
							?><input type="text" id="subject" name="sourcePath" class="<?=$invalid?>" size="50" value="<?=$value?>" />
						</td>
					</tr>
					<tr class="alt">
						<td class="title">Archive Directory: </td>
						<td><?php
							$invalid = array_key_exists('backupPath', $invalidValues) ? 'invalid' : '';
							$value = $config->getBackupDirectory(FALSE);
							$value = $value ? htmlspecialchars($value) : '';
							?><input type="text" id="subject" name="backupPath" class="<?=$invalid?>" size="50" value="<?=$value?>" />
						</td>
					</tr>
					<tr class="alt">
						<td class="title">Junk Mail Directory: </td>
						<td><?php
							$invalid = array_key_exists('junkPath', $invalidValues) ? 'invalid' : '';
							$value = $config->getJunkDirectory(FALSE);
							$value = $value ? htmlspecialchars($value) : '';
							?><input type="text" id="subject" name="junkPath" class="<?=$invalid?>" size="50" value="<?=$value?>" />
						</td>
					</tr>
				</tbody>
			</table>
		</form>

		<?php

		$this->render_customer_groups();

	}

	private function render_customer_groups()
	{
		$customerGroups = Customer_Group::listAll();

		?>
		<br/>
		<table class="reflex">
			<caption>
				<div id="caption_bar" name="caption_bar">
				<div id="caption_title" name="caption_title">
					Customer Groups
				</div>
				<div id="caption_options" name="caption_options">
				</div>
				</div>
			</caption>
			<thead class="header">
				<tr>
					<th>Name</th>
					<th style="width: 10%;">Action</th>
				</tr>
			</thead>
			<tfoot class="footer">
				<tr>
					<th colspan="2">&nbsp;</th>
				</tr>
			</tfoot>
			<tbody>
		<?php
			$alt = FALSE;
			foreach($customerGroups as $customerGroup)
			{
				$altClass = $alt ? ' class="alt"' : '';
				$alt = !$alt;
				$link = Flex::getUrlBase() . 'reflex.php/Ticketing/GroupAdmin/' . $customerGroup->id . '/View';
		?>
				<tr<?=$altClass?>>
					<td><?=htmlspecialchars($customerGroup->name)?></td>
					<td><a href="<?=$link?>">View</a></td>

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
