<?php


// Logic for the Ticketing Summary Report
class Ticketing_Summary_Report
{
	protected static $arrBoundaryConditions = array();
	protected static $arrReport = array();

	protected function __construct($arrBoundaryConditions)
	{
		self::$arrBoundaryConditions	= $arrBoundaryConditions;
		self::$arrReport				= array();
	}

	// Throws an exception if any of the boundary conditions are invalid
	public static function SetBoundaryConditions($arrOwners, $arrCategories, $arrStatusTypes, $arrStatuses, $strEarliestTime=NULL, $strLatestTime=NULL)
	{
		// Process the owners declared
		if (!is_array($arrOwners) || count($arrOwners) == 0)
		{
			throw new Exception("At least one owner must be specified");
		}
		$intIndex = array_search("all", $arrOwners);
		$bolAllOwners = FALSE;
		if ($intIndex !== FALSE)
		{
			// The "all" owner option has been declared
			$bolAllOwners = TRUE;
			$arrOwners = array_splice($arrOwners, $intIndex, 1);
		}
		
		// Process the categories declared
		if (!is_array($arrCategories) || count($arrCategories) == 0)
		{
			throw new Exception("At least one category must be specified");
		}
		$intIndex = array_search("all", $arrCategories);
		$bolAllCategories = FALSE;
		if ($intIndex !== FALSE)
		{
			// The "all" category option has been declared
			$bolAllCategories = TRUE;
			$arrCategories = array_splice($arrCategories, $intIndex, 1);
		}
		
		// Process the Statuses and StatusTypes declared
		if ((!is_array($arrStatuses) || count($arrStatuses) == 0) && (!is_array($arrStatusTypes) || count($arrStatusTypes) == 0))
		{
			throw new Exception("At least one status or status type must be specified");
		}
		
		// Assume EarliestTime and EndTime are valid ISO DateTime strings or NULL
		
		self::$arrBoundaryConditions = array(
												"arrOwners"			=> $arrOwners,
												"bolAllOwners"		=> $bolAllOwners,
												"arrCategories"		=> $arrCategories,
												"bolAllCategories"	=> $bolAllCategories,
												"arrStatusTypes"	=> $arrStatusTypes,
												"arrStatuses"		=> $arrStatuses,
												"strEarliestTime"	=> $strEarliestTime,
												"strLatestTime"		=> $strLatestTime
											);
		return TRUE;
	}

	public static function BuildReport($user)
	{
		
	}

}

?>
