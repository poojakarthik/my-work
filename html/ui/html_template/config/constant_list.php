<?php
//----------------------------------------------------------------------------//
// HtmlTemplateConfigConstantList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateConfigConstantList
 *
 * Lists all constants in a constant group (or all those that don't belong to a constant group)
 *
 * Lists all constants in a constant group (or all those that don't belong to a constant group)
 * It can also list all of the constant groups (as tables), including a table listing 
 * all constants that don't belong to a group
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateConfigConstantList
 * @extends	HtmlTemplate
 */
class HtmlTemplateConfigConstantList extends HtmlTemplate
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
	 * @param	string	$_strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("config_constants_management");
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
		switch($this->_intContext)
		{
			case HTML_CONTEXT_ALL:
				$this->RenderAllGroups();
				break;
			case HTML_CONTEXT_SINGLE:
				// It is a precondition that DBO()->ConstantGroup points to the ConstantGroup that you want to 
				// render. Else it will render the miscellaneous ConstantGroup
				if (DBO()->ConfigConstantGroup->Id->Value)
				{
					$this->RenderConstantGroup(DBO()->ConfigConstantGroup);
				}
				else
				{
					$this->RenderMiscGroup();
				}
				echo "<div class='SmallSeperator'></div>\n";
				break;
			default:
				echo "ERROR: HtmlTemplateConfigConstantList rendered in known context: ". $this->_intContext;
				break;
		}
	}	

	//------------------------------------------------------------------------//
	// RenderConstantGroup
	//------------------------------------------------------------------------//
	/**
	 * RenderConstantGroup()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderConstantGroup($dboConstantGroup)
	{
		$strDataType	= GetConstantDescription($dboConstantGroup->Type->Value, "DataType");
		$strConstGroup	= $dboConstantGroup->Name->Value;
		echo "<h2 class='ConstantGroup'>{$strConstGroup}s ($strDataType)</h2>\n"; 
		
		// Build the link to the "Add Constant" popup
		$strAddConstant		= Href()->AddConfigConstant($dboConstantGroup->Id->Value);
		$strAddConstantLink	= "<a href='$strAddConstant' title='Add New Constant'><img src='img/template/new.png'></img></a>\n";
		
		// Set up the header information for the table
		Table()->$strConstGroup->SetHeader("Name", "Description", "Value", $strAddConstantLink);
		Table()->$strConstGroup->SetWidth("30%", "50%", "10%", "10%");
		Table()->$strConstGroup->SetAlignment("Left", "Left", "Right", "Right");
		
		// Retrieve the constants  (They should be ordered by value)
		if ($dboConstantGroup->Type->Value == DATA_TYPE_INTEGER)
		{
			// The ConstantGroup is a collection of integers.  These values are stored in the database as strings.
			// Therefore they have to be type-cast to integers so that they can be sorted properly
			$arrColumns = Array("Id"=>"Id", "Name"=>"Name", "Description"=>"Description", "Value"=> "CONVERT(Value, SIGNED)");
		}
		else
		{
			// It is assumed the ConstantGroup is a collection of strings.  Having a constant group of floats can't be done
			// and having a constant group of booleans is just silly
			$arrColumns = Array("Id", "Name", "Description", "Value");
		}
		
		$selConstants = new StatementSelect("ConfigConstant", $arrColumns, "ConstantGroup = <ConstantGroup>", "Value ASC");
		$selConstants->Execute(Array("ConstantGroup"=> $dboConstantGroup->Id->Value));
		$arrConstants = $selConstants->FetchAll();
		
		foreach ($arrConstants as $arrConstant)
		{
			$arrConstant['ConstantGroupName'] = $arrConstantGroup->Name->Value;
			
			// Convert html special chars
			$strValue = htmlspecialchars($arrConstant['Value'], ENT_QUOTES);
			
			// Build the edit button
			$strEditConstantHref = Href()->EditConfigConstant($arrConstant['Id']);
			$strEditConstantLink = "<a href='$strEditConstantHref' title='Edit'<img src='img/template/edit.png'></img></a>";
			
			// Build the delete button
			$objConstant = Json()->encode($arrConstant);
			$strDeleteConstantJs = "javascript: Vixen.ConfigConstantsManagement.DeleteConstant($objConstant)";
			$strDeleteConstantLink = "<a href='$strDeleteConstantJs' title='Delete'<img src='img/template/delete.png'></img></a>";

			Table()->$strConstGroup->AddRow($arrConstant['Name'], 
											$arrConstant['Description'],
											$strValue,
											$strEditConstantLink ."&nbsp;".	$strDeleteConstantLink);
		}
			
		// Check if the table is empty
		if (Table()->$strConstGroup->RowCount() == 0)
		{
			// There are no Constants to stick in this table
			Table()->$strConstGroup->AddRow("<span>No constants to display</span>");
			Table()->$strConstGroup->SetRowAlignment("left");
			Table()->$strConstGroup->SetRowColumnSpan(4);
		}
				
		Table()->$strConstGroup->Render();
	}
	
	//------------------------------------------------------------------------//
	// RenderMiscGroup
	//------------------------------------------------------------------------//
	/**
	 * RenderMiscGroup()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderMiscGroup()
	{
		echo "<h2 class='ConstantGroup'>Miscellaneous Constants</h2>\n"; 
		
		// Build the link to the "Add Constant" popup
		$strAddConstant		= Href()->AddConfigConstant();
		$strAddConstantLink	= "<a href='$strAddConstant' title='Add New Constant'><img src='img/template/new.png'></img></a>\n";
		
		// Set up the header information for the table
		Table()->MiscConstants->SetHeader("Name", "Description", "Type", "Value", $strAddConstantLink);
		Table()->MiscConstants->SetWidth("30%", "30%", "7%", "27%", "6%");
		Table()->MiscConstants->SetAlignment("Left", "Left", "Left", "Left", "Right");
		
		// Retrieve the constants (They should be ordered by Name)
		$selConstants = new StatementSelect("ConfigConstant", "*", "ConstantGroup IS NULL", "Name ASC");
		$selConstants->Execute();
		$arrConstants = $selConstants->FetchAll();
		
		foreach ($arrConstants as $arrConstant)
		{
			// Build the edit button
			$strEditConstantHref = Href()->EditConfigConstant($arrConstant['Id']);
			$strEditConstantLink = "<a href='$strEditConstantHref' title='Edit'<img src='img/template/edit.png'></img></a>";
			
			// Build the delete button
			$objConstant			= Json()->encode($arrConstant);
			$strDeleteConstantJs	= "javascript: Vixen.ConfigConstantsManagement.DeleteConstant($objConstant)";
			$strDeleteConstantLink	= "<a href='$strDeleteConstantJs' title='Delete'<img src='img/template/delete.png'></img></a>";

			// Process the value
			if ($arrConstant['Value'] === NULL)
			{
				$strValue = "&lt;NULL&gt;";
			}
			else
			{
				switch ($arrConstant['Type'])
				{
					case DATA_TYPE_BOOLEAN:
						$strValue = ($arrConstant['Value']) ? "TRUE" : "FALSE";
						break;
					default:
						$strValue = htmlspecialchars($arrConstant['Value'], ENT_QUOTES);
						break;
				}
			}

			Table()->MiscConstants->AddRow(	$arrConstant['Name'], 
											$arrConstant['Description'],
											GetConstantDescription($arrConstant['Type'], "DataType"),
											$strValue,
											$strEditConstantLink ."&nbsp;".	$strDeleteConstantLink);
		}
		
		
		// Check if the table is empty
		if (Table()->MiscConstants->RowCount() == 0)
		{
			// There are no Constants to stick in this table
			Table()->MiscConstants->AddRow("<span>No constants to display</span>");
			Table()->MiscConstants->SetRowAlignment("left");
			Table()->MiscConstants->SetRowColumnSpan(5);
		}

		Table()->MiscConstants->Render();
	}


	//------------------------------------------------------------------------//
	// RenderAllGroups
	//------------------------------------------------------------------------//
	/**
	 * RenderAllGroups()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderAllGroups()
	{
		$strContainerDivPrefix = "ContainerDiv_ConstantGroup_";
		// Render each ConstantGroup
		foreach (DBL()->ConfigConstantGroup as $dboConstantGroup)
		{
			// Build the container div
			echo "<div id='{$strContainerDivPrefix}{$dboConstantGroup->Id->Value}'>\n";
			
			$this->RenderConstantGroup($dboConstantGroup);
			echo "<div class='SmallSeperator'></div>\n";
			
			echo "</div>\n";  //ContainerDiv_ConstantGroup_
		}
		
		// Render the miscellaneous group
		echo "<div id='{$strContainerDivPrefix}Misc'>\n";
		$this->RenderMiscGroup(NULL);
		echo "<div class='SmallSeperator'></div>\n";
		echo "</div>\n";
		
		// Initialise the javascript object
		echo "<script type='text/javascript'>Vixen.ConfigConstantsManagement.Initialise('$strContainerDivPrefix')</script>";
	}
	
}

?>
