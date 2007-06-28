<?php
//----------------------------------------------------------------------------//
// HtmlTemplateEmployeeDetails
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateEmployeeDetails
 *
 * A specific HTML Template object
 *
 * An Employee Details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateEmployeeDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplateEmployeeEdit extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strId = $strId;
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		$this->LoadJavascript("permissions");
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{
		switch ($this->_intContext)
		{
			/*case HTML_CONTEXT_SEANS_DETAIL:
				$this->_RenderSeansDetail();
				break;
			case HTML_CONTEXT_LEDGER_DETAIL:
				$this->_RenderLedgerDetail();
				break;
			case HTML_CONTEXT_FULL_DETAIL:
		
				$this->_RenderFullDetail();
				break;*/
			default:
				$this->_RenderFullDetail();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderFullDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderFullDetail()
	 *
	 * Render this HTML Template with full detail
	 *
	 * Render this HTML Template with full detail
	 *
	 * @method
	 */
	private function _RenderFullDetail()
	{
		/*echo "<h2 class='Employees'>Employees</h2>";
		
		if ($_GET['Archived'])
		{
			$strArchivedValue = 'checked';
		}
		echo "<form name='theform' action='view_employees.php' method='get'><input type='checkbox' name='Archived' value=1 $strArchivedValue onClick='document.theform.submit();'>Show Archived Employees</input></form>";
		echo "<div class='NarrowTable'>\n";
		Table()->EmployeeTable->SetHeader("FirstName", "LastName", "UserName","Status","View Employee");
		foreach (DBL()->Employee as $dboEmployee)
		{
			$strEditHref = Href()->EditEmployee($dboEmployee->Id->Value);
			$strEditLabel = "<span class='DefaultOutputSpan Default'><a href='$strEditHref'>Edit Employee</a></span>";	
			Table()->EmployeeTable->AddRow(  $dboEmployee->FirstName->AsValue(),
												$dboEmployee->LastName->AsValue(), 
												$dboEmployee->UserName->AsValue(),
												$dboEmployee->Archived->AsValue(),
												$strEditLabel);
		}
		Table()->EmployeeTable->Render();
		echo "</div>\n";*/
		
		echo "<h2 class='Employee'> Edit Employee</h2>";
		$this->FormStart('Employee', 'Employee', 'Edit');
		
		DBO()->Employee->FirstName->RenderInput();
		DBO()->Employee->LastName->RenderInput();
		DBO()->Employee->Email->RenderInput();
		DBO()->Employee->Extension->RenderInput();
		DBO()->Employee->Phone->RenderInput();
		DBO()->Employee->Mobile->RenderInput();
		DBO()->Employee->Password->RenderInput();
		DBO()->Employee->Archived->RenderInput();	
		
		echo "<p><h2 class='Permissions'> Permissions</h2>
              
			  <div class='Narrow-Form'>
			  
			  	<input type='hidden' name='Id' value='27' />
				Select multiple Permissions by holding the CTRL key while you click options from
				either of the lists.
					<div class='SmallSeperator'></div>
						<table border='0' cellpadding='3' cellspacing='0'>
							<tr>
								<th>Available Permissions :</th>
								<th></th>
								<th>Selected Permissions :</th>
							</tr>
							<tr>
								<td>
									<select id='AvailablePermissions' name='AvailablePermissions[]' size='8' class='SmallSelection' multiple='multiple'></select>
								</td>
								<td>
									<div>
										<input type='button' value='&#xBB;' onclick='addIt()' />
									</div>
									<div class='Seperator'></div>
									<div>
										<input type='button' value='&#xAB;' onclick='delIt ()' />
									</div>
								</td>
								<td>
									<select id='SelectedPermissions' name='SelectedPermissions[]' size='8' class='SmallSelection' multiple='multiple'>
										<option value='16'>Accounts</option>
										<option value='2'>Admin</option>
										<option value='4'>Operator</option>
										<option value='1'>Public</option>
										<option value='8'>Sales</option>
									</select>
								</td>
							</tr>
						</table>
					</div>
				</div>
              <div class='Seperator'></div>";
			  
			  //echo "<a href='Javascript:alert(document.getElementById(\"anid\").elements[1]);'>links</a>";
			  //echo "<a href='Javascript:if(document.anid.elements[0].toString().indexOf(\"Select\")==-1){alert(\"is a select box\");}'>link</a>";
			  //echo "<a href='Javascript:alert(document.getElementById(\"anid"\).elements[0])'>link1</a>";
			  //alert(document.getElementById(\"anid\").elements[0])'>LINK</a>";
			  //echo "<script type='text/javascript'>document.innerHTML;</script>";
			  //echo "<a href='Javascript:this.document.forms[0].elements[0].name;' target='_blank'>asdf</a>";
			  //echo "<a href='Javascript:this.document.innerHTML;' target='_new'>adsg</a>";
			  $this->AjaxSubmit('Save', 'Save', HTML_MODE);
			  $this->FormEnd();
	}

	//------------------------------------------------------------------------//
	// _RenderLedgerDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderLedgerDetail()
	 *
	 * Render this HTML Template with ledger detail
	 *
	 * Render this HTML Template with ledger detail
	 *
	 * @method
	 */
	/*private function _RenderLedgerDetail()
	{
		echo "<h2 class='Account'>Account Details</h2>\n";
		echo "<div class='NarrowContent'>\n";
		//echo "<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
		//echo "<tr>\n";
		//echo "<td>\n";
		DBO()->Account->Id->RenderOutput();
		DBO()->Account->BusinessName->RenderOutput();
		DBO()->Account->Balance->RenderOutput();
		DBO()->Account->Overdue->RenderOutput();
		DBO()->Account->TotalUnbilledAdjustments->RenderOutput();
		//echo "</td>\n";
		//echo "<td>\n";
		DBO()->Account->DisableDDR->RenderInput();
		DBO()->Account->DisableLatePayment->RenderInput();
		echo "<div class='Right'>\n";
		echo "   <input type='submit' class='input-submit' value='Apply Changes' />\n";
		echo "</div>\n";
		
		//echo "</td>\n";
		//echo "</tr>\n";
		//echo "</table>\n";
		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}
	
	//------------------------------------------------------------------------//
	// _RenderSeansDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderSeansDetail()
	 *
	 * Render this HTML Template with Sean's detail
	 *
	 * Render this HTML Template with Sean's detail
	 *
	 * @method
	 */
	/*private function _RenderSeansDetail()
	{
		?>
			<h2 class='Account'>Account Details</h2>
			<div class='Narrow-Form'>
			<table border='0' cellpadding='3' cellspacing='0'>
			<tr>
				<?php DBO()->Account->Id->RenderOutput(); ?>
			</tr>
			<tr>
					<?php DBO()->Account->BusinessName->RenderOutput(); ?>
			</tr>
			<tr>
					<?php DBO()->Account->TradingName->RenderOutput(); ?>
			</tr>
			<tr>
					<?php DBO()->Account->ABN->RenderOutput();?>
			</tr>
			<tr>
					<?php DBO()->Account->BillingType->RenderOutput();?>
			</tr>
			<tr><td>
				<select name='Mode'>
					<option value="modal">Modal</option>
					<option value="modeless">Modeless</option>
					<option value="autohide">Autohide</option>
				</td>
				<td>
				<select name='Size'>
					<option value='small'>Small</option>
					<option value='medium'>Medium</option>
					<option value='large'>Large</option>
				</select>
				</td></tr><tr>
				<td>
					Popup Id: </td><td><input type='text' name='PopupId' value='MyLogin'></input>
				</td>
				</tr>
			</select>
			<tr>
				<input type='button' value='Popup-Centre' onclick='Vixen.Popup.Create(PopupContent.value, PopupId.value, Size.value, "centre", Mode.value)'></input>
				<input type='button' value='Popup-Cursor' onclick='Vixen.Popup.Create(PopupContent.value, PopupId.value, Size.value, event, Mode.value)'></input>
				<input type='button' value='Popup-Target' onclick='Vixen.Popup.Create(PopupContent.value, PopupId.value, Size.value, this, Mode.value)'></input>
			</tr>
		</table>
		</div>
		<div class='Seperator'></div>
		HTML for popup:
				<textarea name='PopupContent' cols=59></textarea>
		<div class='Seperator'></div>
		<?php
		//var_dump($_POST);
		//HTML is OK here, to define structures which enclose these objects
	}*/
}

?>
