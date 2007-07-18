<?php
//----------------------------------------------------------------------------//
// HtmlTemplatePlanRates
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePlanRates
 *
 * A specific HTML Template object
 *
 * An Plan HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountDetails
 * @extends	HtmlTemplate
 */
class HtmlTemplatePlanRates extends HtmlTemplate
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
		
		$this->LoadJavascript("dhtml");
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
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
		echo "<div  style='overflow:scroll; height:500px'>\n";
		
		Table()->RateTable->SetHeader("Id", "Description", "Name", "RateGroup");
		Table()->RateTable->SetWidth("10%", "30%", "30%", "30%");
		Table()->RateTable->SetAlignment("Left", "Left", "Left", "Left");
		
		$arrTables = Array();
		foreach (DBL()->RateList as $dboRate)
		{
			$strTableName = str_replace(Array(" ", "-"), "", $dboRate->Name->Value);
			//echo $strTableName;
			$strTableLabel = $dboRate->RateGroup->Value . " (" . $dboRate->Name->Value . ")";
			
			if (!in_array($strTableName, $arrTables))
			{
				$arrTables[] = $strTableName;
			}

			Table()->$strTableName->SetHeader($strTableLabel);
			Table()->$strTableName->SetAlignment("Left");
			Table()->$strTableName->AddRow($dboRate->Description->Value);
			//Table()->RateTable->AddRow($dboRate->Id->Value, $dboRate->Description->Value, $dboRate->Name->Value, $dboRate->RateGroup->Value);
		}

		foreach ($arrTables as $strTable)
		{
			
			//echo "<br>";
			Table()->$strTable->RowHighlighting = TRUE;
			Table()->$strTable->Render();
			Table()->$strTable->Info();

		}
		//DBL()->RateList->ShowInfo();
	}


}

?>
