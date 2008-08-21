<?php

// Framework
require_once("../../flex.require.php");

$selDestination	= new StatementSelect("Destination", "Description", "Context = 1 AND ((<Exact> = 1 AND Description = <Description>) OR (Description LIKE <Description>))");

$strInFile		= "/home/rdavis/m2_country_code_conversion.csv";
$strOutFile		= "/home/rdavis/m2_country_code_conversion_updated.csv";

$resInputFile	= fopen($strInFile, 'r');
$resOutputFile	= fopen($strOutFile, 'w');

// Parse the Input File
while ($arrLine = fgetcsv($resInputFile))
{
	if (substr($arrLine[0], 0, 1) !== '/')
	{
		// Non-Data Row
		continue;
	}
	
	// Check for an exact match
	if ($selDestination->Execute(Array('Description' => $arrLine[1], 'Exact' => TRUE)))
	{
		// Found an exact match
		$arrDestination	= $selDestination->Fetch();
		$arrLine[]		= $arrDestination['Description'];
	}
	elseif ($selDestination->Error())
	{
		// DB Error
		throw new Exception($selDestination->Error());
	}
	else
	{
		// Check for a close match
		if ($selDestination->Execute(Array('Description' => '%'.$arrLine[1].'%', 'Exact' => FALSE)))
		{
			// Found one/many partial matches
			while ($arrDestination = $selDestination->Fetch())
			{
				$arrLine[]	= $arrDestination['Description'];
			}
		}
		elseif ($selDestination->Error())
		{
			// DB Error
			throw new Exception($selDestination->Error());
		}
		else
		{
			// Couldn't find any matches
			// Nothing to do
		}
	}
	
	// Write the modified line to the Output File
	fwrite($resOutputFile, implode(',', $arrLine)."\n");
}

// Cleanup
fclose($resInputFile);
fclose($resOutputFile);
?>