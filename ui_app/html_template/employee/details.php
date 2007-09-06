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
class HtmlTemplateEmployeeDetails extends HtmlTemplate
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
		
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
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
		echo "<h2 class='Employees'>Employees</h2>";
		
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
		echo "</div>\n";
	}
}

?>
