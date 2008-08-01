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
				// It is a precondition that DBO()->ConstantGroup points to the ConstantGroup that you want to render 
				if (DBO()->ConfigConstantGroup->Special->Value)
				{
					// The ConstantGroup is part of the $GLOBALS array.
					// It's values must be unique within the group, and all the same datatype
					$this->RenderGlobalArrayGroup(DBO()->ConfigConstantGroup);
				}
				else
				{
					// The ConstantGroup is just a normal group of constants
					$this->RenderGroup(DBO()->ConfigConstantGroup);
				}
				echo "<div class='SmallSeperator'></div>\n";
				break;
			default:
				echo "ERROR: HtmlTemplateConfigConstantList rendered in known context: ". $this->_intContext;
				break;
		}
	}	

	//------------------------------------------------------------------------//
	// RenderGlobalArrayGroup
	//------------------------------------------------------------------------//
	/**
	 * RenderGlobalArrayGroup()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderGlobalArrayGroup($dboConstantGroup)
	{
		$strDataType	= GetConstantDescription($dboConstantGroup->Type->Value, "DataType");
		$strConstGroup	= $dboConstantGroup->Name->Value;
		$strTableName	= str_replace(" ", "", $strConstGroup);
		$strDescription = htmlspecialchars($dboConstantGroup->Description->Value, ENT_QUOTES);
		echo "<h2 class='ConstantGroup' title='$strDescription'>{$strConstGroup}s ($strDataType)</h2>\n"; 
		
		// Build the link to the "Add Constant" popup
		$strAddConstantLink = "&nbsp;";
		if ($dboConstantGroup->Extendable->Value || AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			$strAddConstant		= Href()->AddConfigConstant($dboConstantGroup->Id->Value);
			$strAddConstantLink	= "<a href='$strAddConstant' title='Add New Constant'><img src='img/template/new.png'></img></a>\n";
		}
		
		// Set up the header information for the table
		Table()->$strTableName->SetHeader("Name", "Description", "Value", "&nbsp", $strAddConstantLink);
		Table()->$strTableName->SetWidth("30%", "54%", "10%", "3%", "3%");
		Table()->$strTableName->SetAlignment("Left", "Left", "Right", "Right", "Right");
		
		// Retrieve the constants  (They should be ordered by value)
		if ($dboConstantGroup->Type->Value == DATA_TYPE_INTEGER)
		{
			// The ConstantGroup is a collection of integers.  These values are stored in the database as strings.
			// Therefore they have to be type-cast to integers so that they can be sorted properly
			$arrColumns = Array("Id"=>"Id", "Name"=>"Name", "Description"=>"Description", "Editable"=>"Editable", "Deletable"=>"Deletable", "Value"=> "CONVERT(Value, SIGNED)");
		}
		else
		{
			// It is assumed the ConstantGroup is a collection of strings.  Having a constant group of floats can't be done
			// and having a constant group of booleans is just silly
			$arrColumns = Array("Id", "Name", "Description", "Editable"=>"Editable", "Deletable"=>"Deletable", "Value");
		}
		
		$selConstants = new StatementSelect("ConfigConstant", $arrColumns, "ConstantGroup = <ConstantGroup>", "Value ASC");
		$selConstants->Execute(Array("ConstantGroup"=> $dboConstantGroup->Id->Value));
		$arrConstants = $selConstants->FetchAll();
		
		foreach ($arrConstants as $arrConstant)
		{
			// Convert html special chars
			$strDescription	= htmlspecialchars($arrConstant['Description'], ENT_QUOTES);
			$strValue		= htmlspecialchars($arrConstant['Value'], ENT_QUOTES);
			
			// Build the edit button
			$strEditConstantLink = "&nbsp;";
			if ($arrConstant['Editable'] || AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
			{
				$strEditConstantHref = Href()->EditConfigConstant($arrConstant['Id']);
				$strEditConstantLink = "<a href='$strEditConstantHref' title='Edit'<img src='img/template/edit.png'></img></a>";
			}
			
			// Build the delete button
			// If the Constant isn't editable, then it can't be deleted either
			$strDeleteConstantLink = "&nbsp;";
			if (($arrConstant['Editable'] && $arrConstant['Deletable']) || AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
			{
				$strDeleteConstantJs	= "javascript: Vixen.ConfigConstantsManagement.DeleteConstant({$arrConstant['Id']}, \"{$arrConstant['Name']}\")";
				$strDeleteConstantLink	= "<a href='$strDeleteConstantJs' title='Delete'<img src='img/template/delete.png'></img></a>";
			}

			// Remove the underscores from the name, so that it can be split over more than 1 line
			$strName = str_replace("_", " ", $arrConstant['Name']);
			
			Table()->$strTableName->AddRow($strName, 
											$strDescription,
											$strValue,
											$strEditConstantLink, $strDeleteConstantLink);
		}
			
		// Check if the table is empty
		if (Table()->$strTableName->RowCount() == 0)
		{
			// There are no Constants to stick in this table
			Table()->$strTableName->AddRow("<span>No constants to display</span>");
			Table()->$strTableName->SetRowAlignment("left");
			Table()->$strTableName->SetRowColumnSpan(5);
		}
				
		Table()->$strTableName->Render();
	}
	
	//------------------------------------------------------------------------//
	// RenderGroup
	//------------------------------------------------------------------------//
	/**
	 * RenderGroup()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function RenderGroup($dboConstantGroup)
	{
		// Render a specific ConstantGroup
		$strConstGroup	= $dboConstantGroup->Name->Value;
		$strTableName	= str_replace(" ", "", $strConstGroup);
		$strDescription = htmlspecialchars($dboConstantGroup->Description->Value, ENT_QUOTES);
		echo "<h2 class='ConstantGroup' title='$strDescription'>$strConstGroup<h2>\n";
		
		// Build the link to the "Add Constant" popup
		$strAddConstantLink = "&nbsp;";
		if ($dboConstantGroup->Extendable->Value || AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
		{
			$strAddConstant		= Href()->AddConfigConstant($dboConstantGroup->Id->Value);
			$strAddConstantLink	= "<a href='$strAddConstant' title='Add New Constant'><img src='img/template/new.png'></img></a>\n";
		}
		
		// Set up the header information for the table
		Table()->$strTableName->SetHeader("Name", "Description", "Type", "Value", "&nbsp;", $strAddConstantLink);
		Table()->$strTableName->SetWidth("30%", "30%", "7%", "27%", "3%", "3%");
		Table()->$strTableName->SetAlignment("Left", "Left", "Left", "Left", "Right", "Right");
		
		// Retrieve the constants (They should be ordered by Name)
		$selConstants = new StatementSelect("ConfigConstant", "*", "ConstantGroup = <ConstantGroup>", "Name ASC");
		$selConstants->Execute(Array("ConstantGroup"=>$dboConstantGroup->Id->Value));
		$arrConstants = $selConstants->FetchAll();
		
		foreach ($arrConstants as $arrConstant)
		{
			// Build the edit button
			$strEditConstantLink = "&nbsp;";
			if ($arrConstant['Editable'] || AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
			{
				$strEditConstantHref = Href()->EditConfigConstant($arrConstant['Id']);
				$strEditConstantLink = "<a href='$strEditConstantHref' title='Edit'<img src='img/template/edit.png'></img></a>";
			}
			
			// Build the delete button
			// If the Constant isn't editable, then it can't be deleted either
			$strDeleteConstantLink = "&nbsp;";
			if (($arrConstant['Editable'] && $arrConstant['Deletable']) || AuthenticatedUser()->UserHasPerm(USER_PERMISSION_GOD))
			{
				$strDeleteConstantJs	= "javascript: Vixen.ConfigConstantsManagement.DeleteConstant({$arrConstant['Id']}, \"{$arrConstant['Name']}\")";
				$strDeleteConstantLink	= "<a href='$strDeleteConstantJs' title='Delete'<img src='img/template/delete.png'></img></a>";
			}
			
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

			// Remove the underscores from the name, so that it can be split over more than 1 line
			$strName = str_replace("_", " ", $arrConstant['Name']);
			
			Table()->$strTableName->AddRow($strName, 
											htmlspecialchars($arrConstant['Description'], ENT_QUOTES),
											GetConstantDescription($arrConstant['Type'], "DataType"),
											$strValue,
											$strEditConstantLink, $strDeleteConstantLink);
		}
		
		
		// Check if the table is empty
		if (Table()->$strTableName->RowCount() == 0)
		{
			// There are no Constants to stick in this table
			Table()->$strTableName->AddRow("<span>No constants to display</span>");
			Table()->$strTableName->SetRowAlignment("left");
			Table()->$strTableName->SetRowColumnSpan(6);
		}

		Table()->$strTableName->Render();
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
			
			if ($dboConstantGroup->Special->Value)
			{
				// The ConstantGroup is part of the $GLOBALS array.
				// It's values must be unique within the group, and all the same datatype
				$this->RenderGlobalArrayGroup($dboConstantGroup);
			}
			else
			{
				// The ConstantGroup is just a normal group of constants
				$this->RenderGroup($dboConstantGroup);
			}
			
			echo "<div class='SmallSeperator'></div>\n";
			
			echo "</div>\n";  //ContainerDiv_ConstantGroup_
		}
		
		// Initialise the javascript object
		echo "<script type='text/javascript'>Vixen.ConfigConstantsManagement.Initialise('$strContainerDivPrefix')</script>";
	}
	
}

?>
