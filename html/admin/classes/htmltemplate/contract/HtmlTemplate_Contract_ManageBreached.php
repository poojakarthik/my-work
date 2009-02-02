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
	protected	$_arrGETVariables	= Array();
	
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript("contract_manage_expired");
		
		BreadCrumb()->Employee_Console();
		//BreadCrumb()->Contracts();
		BreadCrumb()->SetCurrentPage("Manage Breached Contracts");
	}

	public function Render()
	{		
		// Init GET variables
		$this->_arrGETVariables['offset']	= $this->mxdDataToRender['Pagination']['intCurrent'];
		$this->_arrGETVariables['sort']		= $this->mxdDataToRender['Sort'];
		
		$strSort	= '';
		if (is_array($this->mxdDataToRender['Sort']))
		{
			$strDirection	= reset($this->mxdDataToRender['Sort']);
			$strSortColumn	= key($this->mxdDataToRender['Sort']);
			$strSort		= ($strSortColumn) ? '&sort["'.$strSortColumn.'"]='.$strDirection : '';
		}
		
		// Pagination
		$arrPaginationHTML	= Array();
		if ($this->mxdDataToRender['Pagination']['intCurrent'])
		{
			$arrPaginationHTML[]	= "<a href='../../admin/reflex.php/Contract/ManageBreached/?offset=0{$strSort}'>First</a>";
			$arrPaginationHTML[]	= "<a href='../../admin/reflex.php/Contract/ManageBreached/?offset={$this->mxdDataToRender['Pagination']['intPrevious']}{$strSort}'>Previous</a>";
		}
		else
		{
			$arrPaginationHTML[]	= "First";
			$arrPaginationHTML[]	= "Previous";
		}
		if ($this->mxdDataToRender['Pagination']['intCurrent'] < $this->mxdDataToRender['Pagination']['intNext'])
		{
			$arrPaginationHTML[]	= "<a href='../../admin/reflex.php/Contract/ManageBreached/?offset={$this->mxdDataToRender['Pagination']['intNext']}{$strSort}'>Next</a>";
			$arrPaginationHTML[]	= "<a href='../../admin/reflex.php/Contract/ManageBreached/?offset={$this->mxdDataToRender['Pagination']['intLast']}{$strSort}'>Last</a>";
		}
		else
		{
			$arrPaginationHTML[]	= "Next";
			$arrPaginationHTML[]	= "Last";
		}
		$strPaginationHTML	= implode('&nbsp;|&nbsp;', $arrPaginationHTML);
		
		$this->mxdDataToRender['Pagination']['intStart']	= min($this->mxdDataToRender['Pagination']['intStart'], $this->mxdDataToRender['Pagination']['intTotal']);
		$this->mxdDataToRender['Pagination']['intEnd']		= min($this->mxdDataToRender['Pagination']['intEnd'], $this->mxdDataToRender['Pagination']['intTotal']);
		
		// Render Table Head & Form HTML
		echo "
<form method='GET' action='../../admin/reflex.php/Contract/ManageBreached'>
	<table id='contracts' name='contracts' class='reflex'>

		<caption>
			<div id='caption_bar' name='caption_bar'>
				<div id='caption_title' name='caption_title'>
					Displaying {$this->mxdDataToRender['Pagination']['intStart']} to {$this->mxdDataToRender['Pagination']['intEnd']} of {$this->mxdDataToRender['Pagination']['intTotal']} Breached Contracts
				</div>

				<div id='caption_options' name='caption_options'>
					Select: <a onclick='javascript:Flex.Contract_ManageExpired.selectAll()' >All</a> | <a onclick='javascript:Flex.Contract_ManageExpired.selectNone()' >None</a>&nbsp;&nbsp;With Selected: <a onclick='javascript:Flex.Contract_ManageExpired.confirm(\"apply\")' >Apply</a> | <a onclick='javascript:Flex.Contract_ManageExpired.confirm(\"waive\")' >Waive</a>
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
				<th colspan='6' align=left>Select: <a onclick='javascript:Flex.Contract_ManageExpired.selectAll()' >All</a> | <a onclick='javascript:Flex.Contract_ManageExpired.selectNone()' >None</a>&nbsp;&nbsp;With Selected: <a onclick='javascript:Flex.Contract_ManageExpired.confirm(\"apply\")' >Apply</a> | <a onclick='javascript:Flex.Contract_ManageExpired.confirm(\"waive\")' >Waive</a></th>
				<th colspan='6' align=right>{$strPaginationHTML}</th>
			</tr>
		</tfoot>
		<tbody>";
		
		// Render each contract
		$bolAlternate	= FALSE;
		if (count($this->mxdDataToRender['Contracts']))
		{
			foreach ($this->mxdDataToRender['Contracts'] as $arrContract)
			{
				$strRowClass	= ($bolAlternate) ? 'alt' : '';
				$bolAlternate	= !$bolAlternate;
				
				$strAccountName	= Account::getForId($arrContract['account'])->getName();
				
				echo "
			<tr class='{$strRowClass}' valign='top'>
				<td><input type='checkbox' id='contract_checkbox_{$arrContract['id']}' value='{$arrContract['id']}' /></td>
				<td><a href='../../admin/flex.php/Account/Overview/?Account.Id={$arrContract['account']}' target='_blank' ><span id='contract_account_{$arrContract['id']}'>{$arrContract['account']}</span></a><br />{$strAccountName}</td>
				<td><input id='contract_service_id_{$arrContract['id']}' type='hidden' value='{$arrContract['serviceId']}'><a href='../../admin/flex.php/Service/View/?Service.Id={$arrContract['serviceId']}' target='_blank' ><span id='contract_fnn_{$arrContract['id']}'>{$arrContract['service']}</span></a></td>
				<td><a href='../../admin/flex.php/Service/ViewPlan/?Service.Id={$arrContract['serviceId']}' target='_blank' >{$arrContract['ratePlan']}</a><br /><span id='contract_term_{$arrContract['id']}'>({$arrContract['contractTerm']} Months)<span></td>
				<td>{$arrContract['contractStarted']}</td>
				<td>{$arrContract['contractBreached']}<br />({$arrContract['contractInvoices']} Invoices)</td>
				<td>{$arrContract['breachNature']}</td>
				<td>\$<span id='contract_min_monthly_{$arrContract['id']}'>{$arrContract['minMonthly']}</span></td>
				<td><span id='contract_months_left_{$arrContract['id']}'>{$arrContract['monthsLeft']}</span></td>
				<td><input id='contract_payout_percentage_{$arrContract['id']}' type='text' size='1' onkeyup='javascript:Flex.Contract_ManageExpired.calculatePayout(\"{$arrContract['id']}\")' onchange='javascript:Flex.Contract_ManageExpired.calculatePayout(\"{$arrContract['id']}\")' value='{$arrContract['payout']}'/>% (\$<span id='contract_payout_charge_{$arrContract['id']}'>{$arrContract['payoutAmount']}</span>)</td>
				<td nowrap='nowrap'>\$<input id='contract_exit_fee_{$arrContract['id']}' type='text' size='3' value='{$arrContract['exitFee']}'/></td>
				
				<td nowrap='nowrap'><a onclick='javascript:Flex.Contract_ManageExpired.confirm(\"apply\", {$arrContract['id']})' ><img alt='Apply' src='img/template/tick.png'></a>&nbsp;<a onclick='javascript:Flex.Contract_ManageExpired.confirm(\"waive\", {$arrContract['id']})' ><img alt='Waive' src='img/template/delete.png'></a></td>
			</tr>";
			}
		}
		else
		{
			// No contracts to display
			echo "
			<tr class='' valign='top'>
				<td colspan='12'>No Breached Contracts to display.</td>
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
		$strColId		= str_replace(' ', '', ucwords(strtolower($strColName)));
		$strColId[0]	= strtolower($strColId[0]);
		
		$strHTML	= "<th";
		
		// Sorting
		if ($bolSortable)
		{
			switch ($this->mxdDataToRender['Sort'][$strColId])
			{
				case 'a':
					$strHTML .= " class='reflex-sorted-ascending' onclick=\"document.location = '../../admin/reflex.php/Contract/ManageBreached/?sort[\'{$strColId}\']=d&offset={$this->mxdDataToRender['Pagination']['intCurrent']}'\"";
					break;
					
				case 'd':
					$strHTML .= " class='reflex-sorted-descending' onclick=\"document.location = '../../admin/reflex.php/Contract/ManageBreached/?sort[\'{$strColId}\']=a&offset={$this->mxdDataToRender['Pagination']['intCurrent']}'\"";
					break;
				
				default:
					$strHTML .= " class='reflex-unsorted' onclick=\"document.location = '../../admin/reflex.php/Contract/ManageBreached/?sort[\'{$strColId}\']=a&offset={$this->mxdDataToRender['Pagination']['intCurrent']}'\"";
					break;
			}
		}
		
		return $strHTML.(($bolShowTitle) ? ">{$strColName}</th>" : ">&nbsp;</th>");
	}
}

?>