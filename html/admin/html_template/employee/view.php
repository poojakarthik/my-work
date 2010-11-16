<?php
//----------------------------------------------------------------------------//
// HtmlTemplateEmployeeView
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateEmployeeView
 *
 * A specific HTML Template object
 *
 * An Employee Details HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateEmployeeView
 * @extends	HtmlTemplate
 */
class HtmlTemplateEmployeeView extends HtmlTemplate
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
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		$this->LoadJavascript("table_sort");
		$this->LoadJavascript("employee_view");
		$this->LoadJavascript("reflex_anchor");
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
		$strArchivedValue = '';
		if (array_key_exists('Archived', $_POST) && $_POST['Archived'])
		{
			$strArchivedValue = 'checked';
		}

		echo "<!-- START HtmlTemplateEmployeeView -->\n";
		echo "<div id='EmployeeViewDiv'>\n";
		echo "<div style='margin: 0px; width: 500px;'>\n";
		
		$this->_RenderButtonBar($strArchivedValue);

		echo "<div id='EmployeeTableDiv'>";
		$this->_RenderTable();
		echo "</div>";
		
		$this->_RenderButtonBar($strArchivedValue);
		
		// End narrow table
		echo "</div>\n";
		echo "</div>\n";
		echo "<!-- END HtmlTemplateEmployeeView -->\n";
		
		// Javascript that sets up anchor change listeners, for the 'page view' links. It also defines a callback
		// for when the edit/add employee popup is finished
		echo "
		<script type='text/javascript'>
			function refreshTable()
			{
				var oTable		= document.getElementById('EmployeeTable');
				var sUrl		= window.location.toString().split('#')[0];
				window.location	= sUrl;
				//window.location	= sUrl + '?table_page=' + oTable.pageDisplay + '#Page/' + oTable.pageDisplay + '/View/';
			}
			
			function anchorCallback(iPage)
			{
				Vixen.TableSort.showTablePage('EmployeeTable', iPage);
			}
			
			window.addEventListener('load', function()
			{
				var oAnchor	= Reflex_Anchor.getInstance();
				var oTable	= document.getElementById('EmployeeTable');
				var iPages 	= Math.ceil((oTable.rows.length - 1) / oTable.pageSize);
				
				for (var i = 1; i <= iPages; i++)
				{
					oAnchor.registerCallback('Page/' + i + '/View/', anchorCallback.curry(i), true);
				}
			}, false);
		</script>
		";
	}
	
	
	function _RenderTable()
	{
		Table()->EmployeeTable->SetHeader("Given Name", "Surname", "Username", "Status", "Actions");
		Table()->EmployeeTable->SetWidth("25%", "25%", "25%", "15%", "10%");
		Table()->EmployeeTable->SetAlignment("Left", "Left", "Left", "Left", "Left");
		Table()->EmployeeTable->SetSortable(TRUE);
		Table()->EmployeeTable->SetSortFields("FirstName", "LastName", "UserName", "Archived", null);
		Table()->EmployeeTable->SetPageSize(24);
		
		$iAuthedUserId	= Flex::getUserId();
		
		foreach (DBL()->Employee as $dboEmployee)
		{
			$sViewHref 	= Href()->EditEmployee($dboEmployee->Id->Value, $dboEmployee->UserName->Value, 'refreshTable');
			$sActions 	= "<img onclick='$sViewHref' title='View Employee' src='img/template/user_edit.png'></img>";
			
			// Removed until permissions release. rmctainsh 20100429
			/*if (AuthenticatedUser()->UserHasPerm(PERMISSION_GOD))
			{
				$sPermissionsHref	= Href()->ManageEmployeePermissions($dboEmployee->Id->Value);
				$sActions			.= "&nbsp;<img onclick=\"$sPermissionsHref\" src='../admin/img/template/operation.png'/>";
			}*/
			
			$sArchivedLabel	= "Active";
			
			if ($dboEmployee->Archived->Value == 1)
			{
				$sArchivedLabel	= "Archived";
			}
			
			Table()->EmployeeTable->AddRow(	$dboEmployee->FirstName->AsValue(),
											$dboEmployee->LastName->AsValue(),
											$dboEmployee->UserName->AsValue(),
											$sArchivedLabel,
											$sActions);
		}

		Table()->EmployeeTable->Render();
	}
	
	
	//------------------------------------------------------------------------//
	// _RenderButtonBar
	//------------------------------------------------------------------------//
	/**
	 * _RenderButtonBar()
	 *
	 * Render the button bar
	 *
	 * Only renders one form per page. Other checkboxes invoke submit on the one form
	 *
	 * @param	str	$strArchivedValue "1" if list includes archived employees, "0" otherwise
	 *
	 * @method
	 */
	private function _RenderButtonBar($strArchivedValue)
	{
		static $formRendered, $strEditEmployee;
		if (!isset($formRendered))
		{
			$formRendered 	= FALSE;
			$strEditEmployee	= Href()->EditEmployee(false, false, 'refreshTable');
		}

		$bolCanCreateEmployees 	= AuthenticatedUser()->UserHasPerm(PERMISSION_PROPER_ADMIN);

		$strUpdateOtherCheckBox	= "";
		$strCheckBoxID 			= "";
		if (!$formRendered)
		{
			$this->FormStart('EmployeeList', 'Employee', 'EmployeeList');
			$strCheckBoxID 			= "id='chbArchived'";
			$strUpdateOtherCheckBox	= "try { var cb = document.getElementById(\"chbArchivedFooter\"); if (cb.checked != this.checked) cb.checked = this.checked; } catch(e){}";
		}
		else
		{
			$strCheckBoxID 			= "id='chbArchivedFooter'";
			$strUpdateOtherCheckBox = "document.getElementById(\"chbArchived\").checked = this.checked;";
		}

		echo "<div class='ButtonContainer' style='width: 100%; position: relative;'>\n";
		echo "<input type='checkbox' $strCheckBoxID name='Archived' value=1 $strArchivedValue onClick='$strUpdateOtherCheckBox EmployeeView.Update();'>Show Archived Employees</input>";

		if ($bolCanCreateEmployees)
		{
			echo "<div style='position: absolute; right: 0px; top: 3px;'>";
			$this->Button("Add Employee", $strEditEmployee);
			echo "</div>\n";
		}
		
		echo "</div>\n";
		
		if (!$formRendered)
		{
			$this->FormEnd();
			$formRendered = TRUE;
		}
	}
}

?>
