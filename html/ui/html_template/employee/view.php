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
		if ($_POST['Archived'])
		{
			$strArchivedValue = 'checked';
		}

		Table()->EmployeeTable->SetHeader("Given Name", "Surname", "Username", "Status", "Actions");
		Table()->EmployeeTable->SetWidth("25%", "25%", "25%", "15%", "10%");
		Table()->EmployeeTable->SetAlignment("Left", "Left", "Left", "Left", "Left");
		Table()->EmployeeTable->SetSortable(TRUE);
		foreach (DBL()->Employee as $dboEmployee)
		{
			$strViewHref = Href()->EditEmployee($dboEmployee->Id->Value, $dboEmployee->UserName->Value);
			
			$strView = "<a href='$strViewHref' title='View Employee'><img src='img/template/view.png'></img></a>";

			//$strEditLabel = "<span class='DefaultOutputSpan Default'><a href='$strEditHref'>Edit Employee</a></span>";	
			
			$strArchivedLabel = "Active";
			if ($dboEmployee->Archived->Value == 1)
			{
				$strArchivedLabel = "Archived";
			}
			
			Table()->EmployeeTable->AddRow(  $dboEmployee->FirstName->AsValue(),
												$dboEmployee->LastName->AsValue(), 
												$dboEmployee->UserName->AsValue(),
												$strArchivedLabel,
												$strView);
		}
		

		echo "<!-- START HtmlTemplateEmployeeView -->\n";
		echo "<div id='EmployeeViewDiv'>\n";
		echo "<h2 class='Employees'>Employees</h2>";
		echo "<div style='margin: 0px; width: 500px;'>\n";
		
		$this->_RenderButtonBar($strArchivedValue);

		Table()->EmployeeTable->Render();
		
		$this->_RenderButtonBar($strArchivedValue);
		
		// End narrow table
		echo "</div>\n";
		echo "</div>\n";
		echo "<!-- END HtmlTemplateEmployeeView -->\n";
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
		static $formRendered, $strAddEmployee;
		if (!isset($formRendered))
		{
			$formRendered = FALSE;
			$strAddEmployee = Href()->AddEmployee();
		}

		$strUpdateTopCheckBox = "";
		$strCheckBoxID = "";
		if (!$formRendered)
		{
			$this->FormStart('Employee', 'Employee', 'EmployeeList');
			$strCheckBoxID = "id='chbArchived'";
		}
		else
		{
			$strUpdateTopCheckBox = "document.getElementById(\"chbArchived\").checked = this.checked;";
		}

		echo "<div class='ButtonContainer' style='width: 100%; position: relative;'>\n";
		echo "<input type='checkbox' $strCheckBoxID name='Archived' value=1 $strArchivedValue onClick='$strUpdateTopCheckBox document.getElementById(\"VixenForm_Employee\").submit();'>Show Archived Employees</input>";

		if (AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN))
		{
			echo "<div style='position: absolute; right: 0px; top: 3px;'>";
			$this->Button("Add Employee", "window.location='$strAddEmployee'");
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
