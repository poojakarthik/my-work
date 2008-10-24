<?php

class HtmlTemplate_Contract_ManageBreached extends FlexHtmlTemplate
{
	protected	$_arrColumns	= Array(
											'Id'		=> Array(),
											'Account'	=> Array
															(
																'bolShowTitle'	=> TRUE,
																'bolSortable'	=> TRUE
															),
											'Service'	=> Array
															(
																'bolShowTitle'	=> TRUE,
																'bolSortable'	=> TRUE
															),
											'Rate Plan'	=> Array
															(
																'bolShowTitle'	=> TRUE,
																'bolSortable'	=> TRUE
															),
											'Contract Started'	=> Array
															(
																'bolShowTitle'	=> TRUE,
																'bolSortable'	=> TRUE
															),
											'Contract Breached'	=> Array
															(
																'bolShowTitle'	=> TRUE,
																'bolSortable'	=> TRUE
															),
											'Breach Nature'	=> Array
															(
																'bolShowTitle'	=> TRUE,
																'bolSortable'	=> TRUE
															),
											'Min Monthly'	=> Array
															(
																'bolShowTitle'	=> TRUE,
																'bolSortable'	=> TRUE
															),
											'Months Left'	=> Array
															(
																'bolShowTitle'	=> TRUE,
																'bolSortable'	=> TRUE
															),
											'Payout'	=> Array
															(
																'bolShowTitle'	=> TRUE,
																'bolSortable'	=> TRUE
															),
											'Exit Fee'	=> Array
															(
																'bolShowTitle'	=> TRUE,
																'bolSortable'	=> TRUE
															),
											'Actions'	=> Array()
										);
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript("contract_manage_expired");
	}

	public function Render()
	{
		// Pagination
		$arrPaginationHTML	= Array();
		if ($this->mxdDataToRender['Pagination']['intPrevious'])
		{
			$arrPaginationHTML[]	= "<a href='../admin/reflex.php/Contract/ManageBreached/?offset=0'>First</a>";
			$arrPaginationHTML[]	= "<a href='../admin/reflex.php/Contract/ManageBreached/?offset={$this->mxdDataToRender['Pagination']['intPrevious']}'>Previous</a>";
		}
		if ($this->mxdDataToRender['Pagination']['intNext'])
		{
			$arrPaginationHTML[]	= "<a href='../admin/reflex.php/Contract/ManageBreached/?offset={$this->mxdDataToRender['Pagination']['intNext']}'>Next</a>";
			$arrPaginationHTML[]	= "<a href='../admin/reflex.php/Contract/ManageBreached/?offset={$this->mxdDataToRender['Pagination']['intLast']}'>Last</a>";
		}
		$strPaginationHTML	= implode('&nbsp;|&nbsp;', $arrPaginationHTML);
		
		// Render Table Head & Form HTML
		echo "
<form method='GET' action='../admin/reflex.php/Contract/ManageBreached'>
	<table id='contracts' name='contracts' class='reflex'>

		<caption>
			<div id='caption_bar' name='caption_bar'>
				<div id='caption_title' name='caption_title'>
					Displaying {$this->mxdDataToRender['Pagination']['intStart']} to {$this->mxdDataToRender['Pagination']['intEnd']} of {$this->mxdDataToRender['Pagination']['intTotal']} Breached Contracts
				</div>

				<div id='caption_options' name='caption_options'>
					Select: <a onclick='javascript:ManageContracts.selectAll()' >All</a> | <a onclick='javascript:ManageContracts.selectNone()' >None</a>&nbsp;&nbsp;With Selected: <a onclick='javascript:ManageContracts.confirm(\"apply\")' >Apply</a> | <a onclick='javascript:ManageContracts.confirm(\"waive\")' >Waive</a>
				</div>
			</div>
			
		</caption>
		<thead>
			<tr>
";
		
		// Render TH's
		echo $this->_buildTH('Id'					, FALSE	, FALSE)."\n";
		echo $this->_buildTH('Account'				, TRUE	, TRUE)."\n";
		echo $this->_buildTH('Service'				, TRUE	, TRUE)."\n";
		echo $this->_buildTH('Rate Plan'			, TRUE	, TRUE)."\n";
		echo $this->_buildTH('Contract Started'		, TRUE	, TRUE)."\n";
		echo $this->_buildTH('Contract Breached'	, TRUE	, TRUE)."\n";
		echo $this->_buildTH('Breach Nature'		, TRUE	, TRUE)."\n";
		echo $this->_buildTH('Min Monthly'			, TRUE	, TRUE)."\n";
		echo $this->_buildTH('Months Left'			, TRUE	, TRUE)."\n";
		echo $this->_buildTH('Payout'				, TRUE	, TRUE)."\n";
		echo $this->_buildTH('Exit Fee'				, TRUE	, TRUE)."\n";
		echo $this->_buildTH('Actions'				, FALSE	, FALSE)."\n";
		
		// Render Table Footer
		echo "
			</tr>
		</thead>
		<tfoot>

			<tr>
				<th colspan='6' align=left>Select: <a onclick='javascript:ManageContracts.selectAll()' >All</a> | <a onclick='javascript:ManageContracts.selectNone()' >None</a>&nbsp;&nbsp;With Selected: <a onclick='javascript:ManageContracts.confirm(\"apply\")' >Apply</a> | <a onclick='javascript:ManageContracts.confirm(\"waive\")' >Waive</a></th>
				<th colspan='6' align=right>{$strPaginationHTML}</th>
			</tr>
		</tfoot>
		<tbody>";
		
		// Render each contract
		$bolAlternate	= FALSE;
		foreach ($this->mxdDataToRender['Contracts'] as $arrContract)
		{
			$strRowClass	= ($bolAlternate) ? 'alt' : '';
			$bolAlternate	= !$bolAlternate;
			
			$strAccountName	= Account::getForId($arrContract['account'])->getName();
			
			echo "
			<tr class='{$strRowClass}' valign='top'>
				<td><input type='checkbox' /></td>
				<td><a onclick='#' >{$arrContract['account']}</a><br />{$strAccountName}</td>
				<td><a onclick='#' >{$arrContract['fnn']}</a></td>
				<td><a onclick='#' >{$arrContract['ratePlan']}</a><br />({$arrContract['contractTerm']} Months)</td>
				<td>{$arrContract['contractStarted']}</td>
				<td>{$arrContract['contractBreached']}<br />({$arrContract['contractInvoices']} Invoices)</td>
				<td>{$arrContract['breachNature']}</td>
				<td>\${$arrContract['minMonthly']}</td>
				<td>{{$arrContract['monthsLeft']}}</td>
				<td><input id='contract_payout_percentage_{$arrContract['id']}' type='text' size='1' onkeyup='javascript:ManageContracts.calculatePayout(\"{$arrContract['id']}\")' onchange='javascript:ManageContracts.calculatePayout(\"{$arrContract['id']}\")' value='{$arrContract['payout']}'/>% (\$<span id='contract_payout_charge_{$arrContract['id']}'>{$arrContract['payoutAmount']}</span>)</td>
				<td>\$<input id='contract_exit_fee_{$arrContract['id']}' type='text' size='2' value='{$arrContract['exitFee']}'/></td>
				
				<td nowrap='nowrap'><a onclick='javascript:ManageContracts.confirm(\"apply\", {$arrContract['id']})' ><img alt='Apply' src='img/template/tick.png'></a>&nbsp;<a onclick='javascript:ManageContracts.confirm(\"waive\", {$arrContract['id']})' ><img alt='Waive' src='img/template/delete.png'></a></td>
			</tr>";
		}
		
		// Close off the Table and Form
		echo "
		</tbody>
	</table>
</form>";
	}
	
	protected function _buildTH($strColName, $bolShowTitle, $bolSortable)
	{
		$strColId		= ucwords(strtolower($strColName));
		$strColId[0]	= strtolower($strColId[0]);
		
		$strHTML	= "<th";
		
		// Sorting
		if ($bolSortable)
		{
			switch ($this->mxdDataToRender['Sort'][$strColId])
			{
				case 'a':
					$strHTML .= " class='reflex-sorted-ascending' onclick='document.location = \"../admin/reflex.php/Contract/ManageBreached/?sort[\'{$strColId}\']=d\"'";
					break;
					
				case 'd':
					$strHTML .= " class='reflex-sorted-descending' onclick='document.location = \"../admin/reflex.php/Contract/ManageBreached/?sort[\'{$strColId}\']=a\"'";
					break;
				
				default:
					$strHTML .= " class='reflex-unsorted' onclick='document.location = \"../admin/reflex.php/Contract/ManageBreached/?sort[\'{$strColId}\']=a\"'";
					break;
			}
		}
		
		return $strHTML.(($bolShowTitle) ? ">{$strColName}</th>" : ">&nbsp;</th>");
	} 
}

?>