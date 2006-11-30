<?php
	
	class Rates extends Search
	{
		
		function __construct ()
		{
			parent::__construct ('Rates', 'Rate', 'Rate');
		}
		
		public function UnarchivedNameExists ($strName)
		{
			$selRate = new StatementSelect (
				"Rate", 
				"count(*) AS Length", 
				"Name = <Name> AND Archived = 0"
			);
			
			$selRate->Execute (Array ("Name" => $strName));
			$arrLength = $selRate->Fetch ();
			
			return $arrLength ['Length'] <> 0;
		}
		
		public function Add ($arrRate)
		{
			$arrRate ['Archived']		= 0;
			$arrRate ['StartTime']		= $arrRate ['StartTime'] . ":59";
			$arrRate ['EndTime']		= $arrRate ['EndTime'] . ":59";
			
			$insRate = new StatementInsert ("Rate");
			return $insRate->Execute ($arrRate);
		}
	}
	
?>
