<?=system ("clear");?>

	=====================================================================================================
	WELCOME TO THE ETECH DATA PARSER (version 1.0)
	=====================================================================================================
	
<?php
	
	set_time_limit (0);
	
	// Record the start time of the script
	$startTime = microtime (TRUE);
	
	$MonthAbbr = Array (
		"Jan"	=> "1",
		"Feb"	=> "2",
		"Mar"	=> "3",
		"Apr"	=> "4",
		"May"	=> "5",
		"Jun"	=> "6",
		"Jul"	=> "7",
		"Aug"	=> "8",
		"Sep"	=> "9",
		"Oct"	=> "10",
		"Nov"	=> "11",
		"Dec"	=> "12"
	);
	
	// connect
	mysql_connect ("10.11.12.13", "bash", "bash");
	mysql_select_db ("vixen");
	
	$sql = "TRUNCATE TABLE Account";
	$query = mysql_query ($sql);
	
	$sql = "TRUNCATE TABLE AccountGroup";
	$query = mysql_query ($sql);
	
	$sql = "TRUNCATE TABLE Contact";
	$query = mysql_query ($sql);
	
	$sql = "TRUNCATE TABLE CreditCard";
	$query = mysql_query ($sql);
	
	$sql = "TRUNCATE TABLE Service";
	$query = mysql_query ($sql);
	
	// How many scrapes do we have?
	$sql = "SELECT count(*) AS records FROM ScrapeAccount ";
	$query = mysql_query ($sql);
	
	$row = mysql_fetch_assoc ($query);
	$records = $row ['records'];
	
	// Loop through each Scrape
	for ($i=0; $i < $records; ++$i)
	{
	
		// Get the information about the scrape
		$sql = "SELECT CustomerId, DataSerialized FROM ScrapeAccount ";
		$sql .= "LIMIT " . $i . ", 1";
		$query = mysql_query ($sql);
		$row = mysql_fetch_assoc ($query);
		
		$Customer = unserialize ($row ['DataSerialized']);
		
		if ($Customer ['customer_group'] == "TelcoBlue")
		{
			$Customer ['customer_group'] = 1;
		}
		else if ($Customer ['customer_group'] == "VoiceTalk")
		{
			$Customer ['customer_group'] = 2;
		}
		else if ($Customer ['customer_group'] == "Imagine")
		{
			$Customer ['customer_group'] = 3;
		}
		
		if ($Customer ['cc_type'] == "Visa")
		{
			$Customer ['cc_type'] = 1;
		}
		else if ($Customer ['cc_type'] == "Mastercard")
		{
			$Customer ['cc_type'] = 2;
		}
		else if ($Customer ['cc_type'] == "Bankcard")
		{
			$Customer ['cc_type'] = 3;
		}
		else if ($Customer ['cc_type'] == "Amex")
		{
			$Customer ['cc_type'] = 4;
		}
		else if ($Customer ['cc_type'] == "Diners")
		{
			$Customer ['cc_type'] = 5;
		}
		else
		{
			$Customer ['cc_type'] = null;
		}
		
		$sql = "INSERT INTO AccountGroup (Id, Archived) ";
		$sql .= "VALUES ('" . mysql_escape_string ($row ['CustomerId']) . "', " . ($Customer ['archived'] ? "TRUE" : "FALSE") . ")";
		$insQuery = mysql_query ($sql);
			
		if (!$insQuery)
		{
			echo "\n\n" . $sql . "\n\n" . mysql_error (); exit;
		}
		
		if ($Customer ['cc_type'] !== null)
		{
			$sql = "INSERT INTO CreditCard ";
			$sql .= "(AccountGroup, CardType, Name, CardNumber, ExpMonth, ExpYear, CVV) ";
			$sql .= "VALUES (";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['cc_type']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['cc_name']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['cc_num']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['cc_exp_m']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['cc_exp_y']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['cc_cvv']) . "'";
			$sql .= ")";
			$insQuery = mysql_query ($sql);
			
			if (!$insQuery)
			{
				echo "\n\n" . $sql . "\n\n" . mysql_error (); exit;
			}
			
			$Customer ['cc_id'] = mysql_insert_id ();
			
			unset ($insQuery);
		}
		
		$Customer ['abn'] = (strlen (preg_replace ("/\D/", "", $Customer ['abn_acn'])) == 11) ? $Customer ['abn_acn'] : "";
		$Customer ['acn'] = (strlen (preg_replace ("/\D/", "", $Customer ['abn_acn'])) == 9) ? $Customer ['abn_acn'] : "";
		
		$sql = "INSERT INTO Account ";
		$sql .= "(Id, BusinessName, TradingName, ABN, ACN, ";
		$sql .= "Address1, Address2, Suburb, Postcode, State, Country, ";
		$sql .= "CustomerGroup, CreditCard, AccountGroup, Archived) ";
		$sql .= "VALUES (";
			$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
			$sql .= "'" . mysql_escape_string ($Customer ['businessname']) . "', ";
			$sql .= "'" . mysql_escape_string ($Customer ['tradingname']) . "', ";
			$sql .= "'" . mysql_escape_string ($Customer ['abn']) . "', ";
			$sql .= "'" . mysql_escape_string ($Customer ['acn']) . "', ";
			$sql .= "'" . mysql_escape_string ($Customer ['address1']) . "', ";
			$sql .= "'" . mysql_escape_string ($Customer ['address2']) . "', ";
			$sql .= "'" . mysql_escape_string (strtoupper ($Customer ['suburb'])) . "', ";
			$sql .= "'" . mysql_escape_string ($Customer ['postcode']) . "', ";
			$sql .= "'" . mysql_escape_string (strtoupper ($Customer ['state'])) . "', ";
			$sql .= "'AU', ";
			$sql .= "'" . mysql_escape_string ($Customer ['customer_group']) . "', ";
			$sql .= "" . (isset ($Customer ['cc_id']) ? "'" . mysql_escape_string ($Customer ['cc_id']) . "'" : "NULL") . ", ";
			$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
			$sql .= ($Customer ['archived'] ? "TRUE" : "FALSE");
		$sql .= ")";
		$insQuery = mysql_query ($sql);
		
		if (!$insQuery)
		{
			echo "\n\n" . $sql . "\n\n" . mysql_error (); exit;
		}
		
		unset ($insQuery);
		
		if (
		($Customer ['admin_email'] == "" && $Customer ['billing_email'] == "" && $Customer ['billing_email_2'] == "") ||
		$Customer ['admin_email'] != ""
		) {
			$sql = "INSERT INTO Contact ";
			$sql .= "(AccountGroup, Title, FirstName, LastName, DOB, ";
			$sql .= "JobTitle, Email, Account, CustomerContact, Phone, Mobile, Fax, UserName, PassWord, Archived) ";
			$sql .= "VALUES (";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['title']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['firstname']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['lastname']) . "', ";
				$sql .= "'" . sprintf ("%04d", intval ($Customer ['dob_year'])) . "-" . sprintf ("%02d", ($Customer ['dob_month'] != "") ? intval ($MonthAbbr [trim ($Customer ['dob_month'])]) : "0") . "-" . sprintf ("%02d", intval ($Customer ['dob_day'])) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['position']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['admin_email']) . "', ";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
				$sql .= "1, ";
				$sql .= "'" . mysql_escape_string ($Customer ['phone']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['mobile']) . "', ";
				$sql .= "'" . mysql_escape_string ($Customer ['fax']) . "', ";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
				$sql .= "SHA1('" . mysql_escape_string ("password") . "'), ";
				$sql .= ($Customer ['archived'] ? "TRUE" : "FALSE");
			$sql .= ")";
			$insQuery = mysql_query ($sql);
			
			if (!$insQuery)
			{
				echo "\n\n" . $sql . "\n\n" . mysql_error (); exit;
			}
			
			unset ($insQuery);
		}
		
		if ($Customer ['billing_email'] == "" && $Customer ['admin_email'] != $Customer ['billing_email']) {
			$sql = "INSERT INTO Contact ";
			$sql .= "(AccountGroup, FirstName, JobTitle, Email, Account, CustomerContact, UserName, PassWord, Archived) ";
			$sql .= "VALUES (";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
				$sql .= "'Anonymous Billing Contact', ";
				$sql .= "'Billing Contact', ";
				$sql .= "'" . mysql_escape_string ($Customer ['billing_email']) . "', ";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
				$sql .= "0, ";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "-1', ";
				$sql .= "SHA1('" . mysql_escape_string ("password") . "'), ";
				$sql .= ($Customer ['archived'] ? "TRUE" : "FALSE");
			$sql .= ")";
			$insQuery = mysql_query ($sql);
			
			if (!$insQuery)
			{
				echo "\n\n" . $sql . "\n\n" . mysql_error (); exit;
			}
			
			unset ($insQuery);
		}
		
		if ($Customer ['billing_email_2'] == "" && $Customer ['admin_email'] != $Customer ['billing_email_2'] && $Customer ['billing_email'] != $Customer ['billing_email_2']) {
			$sql = "INSERT INTO Contact ";
			$sql .= "(AccountGroup, FirstName, JobTitle, Email, Account, CustomerContact, UserName, Password, Archived) ";
			$sql .= "VALUES (";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
				$sql .= "'Anonymous Billing Contact', ";
				$sql .= "'Billing Contact', ";
				$sql .= "'" . mysql_escape_string ($Customer ['billing_email']) . "', ";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
				$sql .= "0, ";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "-1', ";
				$sql .= "SHA1('" . mysql_escape_string ("password") . "'), ";
				$sql .= ($Customer ['archived'] ? "TRUE" : "FALSE");
			$sql .= ")";
			$insQuery = mysql_query ($sql);
			
			if (!$insQuery)
			{
				echo "\n\n" . $sql . "\n\n" . mysql_error (); exit;
			}
			
			unset ($insQuery);
		}
		
		$Indials = Array ();
		
		foreach ($Customer ['sn'] as $sn_id => $_SN)
		{
			$Indials [$_SN ['AreaCode'] . substr ($_SN ['Number'], 0, strlen ($_SN ['Number']) - 2)] = 
			isset ($Indials [$_SN ['AreaCode'] . substr ($_SN ['Number'], 0, strlen ($_SN ['Number']) - 2)]) ?
			$Indials [$_SN ['AreaCode'] . substr ($_SN ['Number'], 0, strlen ($_SN ['Number']) - 2)] + 1 : 1;
		}
		
		foreach ($Indials AS $IndialRange => $IndialNumbers)
		{
			if ($IndialNumbers < 90)
			{
				unset ($Indials [$IndialRange]);
			}
			else
			{
				foreach ($Customer ['sn'] as $sn_id => $_SN)
				{
					if ($IndialRange == $_SN ['AreaCode'] . substr ($_SN ['Number'], 0, strlen ($_SN ['Number']) - 2))
					{
						unset ($Customer ['sn'][$sn_id]);
					}
				}
			}
		}
		
		foreach ($Indials as $IndialRange => $IndialNumbers)
		{
			$sql = "INSERT INTO Service ";
			$sql .= "(FNN, ServiceType, Indial100, AccountGroup, Account) ";
			$sql .= "VALUES (";
				$sql .= "'" . mysql_escape_string ($IndialRange . "00") . "', ";
				$sql .= "0, ";
				$sql .= "1, ";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "' ";
			$sql .= ")";
			$insQuery = mysql_query ($sql);
			
			if (!$insQuery)
			{
				echo "\n\n" . $sql . "\n\n" . mysql_error (); exit;
			}
			
			unset ($insQuery);
		}
		
		foreach ($Customer ['sn'] as $sn_id => $_SN)
		{
			$sql = "INSERT INTO Service ";
			$sql .= "(EtechId, FNN, ServiceType, Indial100, AccountGroup, Account) ";
			$sql .= "VALUES (";
				$sql .= "'" . mysql_escape_string ($_SN ['Id']) . "', ";
				$sql .= "'" . mysql_escape_string ($_SN ['AreaCode'] . $_SN ['Number']) . "', ";
				$sql .= "0, ";
				$sql .= "0, ";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "', ";
				$sql .= "'" . mysql_escape_string ($row ['CustomerId']) . "'";
			$sql .= ")";
			$insQuery = mysql_query ($sql);
			
			if (!$insQuery)
			{
				echo "\n\n" . $sql . "\n\n" . mysql_error (); exit;
			}
			
			unset ($insQuery);
		}
		
		?>
		
+	<?=sprintf ("%06d", $i + 1)?>		<?=$row ['CustomerId']?>	NORMALISED AND DIVERTED<?php
		
		unset ($Customer);
		unset ($Indials);
		
		mysql_free_result ($query);
	}
	
?>
	
	
	
	=====================================================================================================
	ETECH DATA PARSER PROCESSED <?=$records?> RECORDS IN <?=microtime (TRUE) - $startTime?> SECONDS.
	=====================================================================================================
	<?="\n"?>
