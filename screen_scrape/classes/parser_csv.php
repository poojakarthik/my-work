<?
	
	class Parser_CSV
	{
		
		private $_customerList;
		
		function __construct ($strFilename)
		{
			if (!file_exists ($strFilename))
			{
				throw new Exception (
					'The file you requested does not exist.'
				);
			}
			
			// STEP 1 - Read Customers
			$customerFp = fopen ($strFilename, "r");
			
			while (!FEOF ($customerFp))
			{
				$customerLine = fgets ($customerFp);
				
				if ($customerLine <> "")
				{
					$customerExplode = explode (",", $customerLine);
					$this->_customerList [] = $customerExplode [0];
				}
			}
			
			fclose ($customerFp);
		}
		
		public function CustomerList ()
		{
			return $this->_customerList;
		}
	}
	
?>
