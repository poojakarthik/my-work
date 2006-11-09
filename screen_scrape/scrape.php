<?=system ("clear");?>

	=====================================================================================================
	WELCOME TO THE ETECH SCREEN SCRAPER (version 1.0)
	=====================================================================================================
	
	<?php
		
		set_time_limit (0);
		
		// Record the start time of the script
		$startTime = microtime (TRUE);
		
		// Keep a list of all the people we're pulling
		$customerList = Array ();
		
		//----------------------------------------------------------------------------------------------
		
		// Method:
		// 1.	Read in the list of Customers.
		//	(Which is a text file with a new customer on each line)
		//
		// 2.	Spoof an Etech Login
		//
		// 3.	Connect to a DB
		//
		// 4.	Pull the whole page (spider and cache) to the database
		//
		// 5.	Disconnect and logout from all.
		
		//----------------------------------------------------------------------------------------------
		
		require_once ("functions/connection.php");
		
		// STEP 1 - Read Customers
		$customerFp = fopen ("customers_short.csv", "r");
		
		while (!FEOF ($customerFp))
		{
			$customerLine = fgets ($customerFp);
			
			if ($customerLine <> "")
			{
				$customerExplode = explode (",", $customerLine);
				$customerList [] = $customerExplode [0];
			}
		}
		
		fclose ($customerFp);
		?>
		
>	CUSTOMER CSV FILE HAS BEEN READ AND STORED IN ARRAY<?php
		flush ();
		
		// STEP 2 - Spoof a Login
		Connection_Login ();
		?>
		
>	LOGIN SCRIPT HAS BEEN CALLED<?php
		flush ();
		
		// STEP 3 - Connect to the DB
		mysql_connect ("10.11.12.13", "bash", "bash");
		mysql_select_db ("bash");
		
		?>
		
>	DATABASE CONNECTION HAS BEEN ESTABISHED<?php
		flush ();
		
		// STEP 4 - Pull Each Page
		
		foreach ($customerList AS $customerID)
		{
			$indivStart = microtime (TRUE);
			
			$Response = Connection_Transmit (
				"GET",
				"https://sp.teleconsole.com.au/sp/customers/editdetails.php?customer_id=" . $customerID
			);
			
			$indivLength = microtime (TRUE) - $indivStart;
			
			if (!stristr ($Response, "<td height=\"21\" colspan=\"3\" class=\"bodytext\">Edit customer details</td>"))
			{
				?>
				
!	<?=$customerID?> (<?=substr ($indivLength, 0, 8)?>) PAGE INCORRECTLY DOWNLOADED<?php

				system ("printf \"\\a\"");
			}
			else
			{
				$sql = "INSERT INTO Scrapes (CustomerID, ScrapeResponse) ";
				$sql .= "VALUES(";
					$sql .= "'" . mysql_real_escape_string ($customerID) . "', ";
					$sql .= "'" . mysql_real_escape_string ($Response) . "'";
				$sql .= ")";
				$MySQLResponse = mysql_query ($sql);
				
				if ($MySQLResponse !== FALSE)
				{
					?>
					
+	<?=$customerID?> (<?=substr ($indivLength, 0, 8)?>) SCRAPED AND INSERTED<?php
				}
				else
				{
					?>
					
!	<?=$customerID?> (<?=substr ($indivLength, 0, 8)?>) ERROR INSERTING INTO DATABASE<?php
					system ("printf \"\\a\"");
				}
			}
			
			flush ();
		}
		
		// STEP 5 - Close
		mysql_close ();
		?>
		
>	DATABASE CONNECTION CLOSED<?php
		
		Connection_Logout ();
		
		?>
		
>	LOGOUT SCRIPT HAS BEEN CALLED<?php
		flush ();
	?>
	
	
	
	=====================================================================================================
	ETECH SCREEN SCRAPER PULLED <?=count ($customerList)?> RECORDS IN <?=microtime (TRUE) - $startTime?> SECONDS.
	=====================================================================================================
	<?="\n"?>
