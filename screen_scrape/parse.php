<?=system ("clear");?>

	=====================================================================================================
	WELCOME TO THE ETECH DATA PARSER (version 1.0)
	=====================================================================================================
	
<?php
	
	set_time_limit (0);
	
	// Record the start time of the script
	$startTime = microtime (TRUE);
	
	// connect
	mysql_connect ("10.11.12.13", "bash", "bash");
	mysql_select_db ("bash");
	
	// How many scrapes do we have?
	$sql = "SELECT count(*) AS records FROM Scrapes ";
	$query = mysql_query ($sql);
	
	$row = mysql_fetch_assoc ($query);
	$records = $row ['records'];
	
	// set up DOM Document
	$_DOMDoc = new DOMDocument ("1.0", "utf-8");
	
	// Loop through each Scrape
	for ($i=0; $i < $records; ++$i)
	{
	
		// Get the information about the scrape
		$sql = "SELECT * FROM Scrapes ";
		$sql .= "LIMIT " . $i . ", 1";
		$query = mysql_query ($sql);
		$row = mysql_fetch_assoc ($query);
		mysql_free_result($query);
		
		// Put the info into a DOM Object
		@$_DOMDoc->LoadHTML ($row ['ScrapeResponse']);		// Is silent because ETECH has no idea about W3C XHTML
		
		// Read the DOMDocument as a DOMXPath so we can perform operations on it
		unset($_DOMXPath);
		$_DOMXPath = new DOMXPath ($_DOMDoc);
		
		$Customer = Array (
			"operator"				=> "",
			"sales_day"				=> "",
			"sales_month"			=> "",
			"sales_year"			=> "",
			
			"cust_ref"				=> "",
			"title"					=> "",
			"firstname"				=> "",
			"lastname"				=> "",
			"dob_day"				=> "",
			"dob_month"				=> "",
			"dob_year"				=> "",
			
			"businessname"			=> "",
			"tradingname"			=> "",
			"abn_acn"				=> "",
			"position"				=> "",
			
			"address1"				=> "",
			"address2"				=> "",
			"suburb"				=> "",
			"state"					=> "",
			"postcode"				=> "",
			
			"admin_email"			=> "",
			"billing_email"			=> "",
			"billing_email_2"		=> "",
			
			"cycle"					=> "",
			"bill_type"				=> "",
			
			"customer_group"		=> "",
			
			"phone"					=> "",
			"mobile"				=> "",
			"fax"					=> "",
			
			"cc_type"				=> "",
			"cc_name"				=> "",
			"cc_num"				=> "",
			"cc_exp_m"				=> "",
			"cc_exp_y"				=> "",
			"cc_cvv"				=> "",
			
			"localrate"				=> "",
			"natrate"				=> "",
			"mobrate"				=> "",
			"intrate"				=> "",
			"service_equip_rate"	=> "",
			
			"mobileunitel"			=> "",
			"mobiletelstra"			=> "",
			"mobileother"			=> "",
			"mobilenational"		=> "",
			"mobile1800"			=> "",
			"mobilevoicemail"		=> "",
			"mobilediverted"		=> "",
			"mobilesms"				=> "",
			"mobilemms"				=> "",
			"mobiledata"			=> "",
			"mobileinternational"	=> "",
			
			"inbfee"				=> "",
			"inv_type"				=> "",
			"sn"					=> Array ()
		);
		
		
		/////////////////////////////////////
		// DIRTY STUFF
		/////////////////////////////////////
		
		
		/////////////////////////////////////
		// <INPUT ... >
		
		$_INPUTS = $_DOMXPath->Query ("//input");
		
		$rowIndex = 0;
		foreach ($_DOMXPath->Query ("//input") AS $item)
		{
			++$rowIndex;
			$_DOMSubDoc = new DOMDocument ();
			$_DOMSubDoc->appendChild (
				$_DOMSubDoc->importNode (
					$item, true
				)
			);
			
			$_DOMSubXPath = new DOMXPath ($_DOMSubDoc);
			
			if (isset ($Customer [$item->getAttribute ("name")]))
			{
				if (strtolower ($item->getAttribute ("type")) == "text")
				{
					$Customer [$item->getAttribute ("name")] = $item->getAttribute ("value");
				}
				else if (strtolower ($item->getAttribute ("type")) == "radio")
				{
					if ($_DOMSubXPath->Query ("/input[@checked]")->length <> 0)
					{
						$Customer [$item->getAttribute ("name")] = $item->getAttribute ("value");
					}
				}
			}
			else if (preg_match ("/^sn\[(\d+)\]$/", $item->getAttribute ("name"), $_MATCHES))
			{
				$Customer ['sn'][$_MATCHES [1]]['Number'] = $item->getAttribute ("value");
			}
			else if (preg_match ("/^snac\[(\d+)\]$/", $item->getAttribute ("name"), $_MATCHES))
			{
				$Customer ['sn'][$_MATCHES [1]]['AreaCode'] = $item->getAttribute ("value");
			}
		}
		
		/////////////////////////////////////
		// <SELECT ... >
		
		$_SELECTS = $_DOMXPath->Query ("//select");
		
		$rowIndex = 0;
		foreach ($_DOMXPath->Query ("//select") AS $item)
		{
			++$rowIndex;
			$_DOMSubDoc = new DOMDocument ();
			$_DOMSubDoc->appendChild (
				$_DOMSubDoc->importNode (
					$item, TRUE
				)
			);
			
			$_DOMSubXPath = new DOMXPath ($_DOMSubDoc);
			
			if (isset ($Customer [$item->getAttribute ("name")]))
			{
				foreach ($_DOMSubXPath->Query ("//option[@selected]") AS $selectedItem)
				{
					if ($selectedItem->nodeValue <> "--" && $selectedItem->nodeValue <> "-")
					{
						$Customer [$item->getAttribute ("name")] = $selectedItem->nodeValue;
					}
				}
			}
		}
		
		$sql = "UPDATE Scrapes SET ";
		$sql .= "ParseResponse='" . mysql_real_escape_string (serialize ($Customer)) . "' ";
		$sql .= "WHERE CustomerId='" . mysql_real_escape_string ($row ['CustomerId']) . "'";
		$updQuery = mysql_query ($sql);
		
		?>
		
+	<?=sprintf ("%06d", $i + 1)?>		<?=$row ['CustomerId']?>	PARSED AND NORMALISED<?php
	}
	
?>
	
	
	
	=====================================================================================================
	ETECH DATA PARSER PROCESSED <?=$records?> RECORDS IN <?=microtime (TRUE) - $startTime?> SECONDS.
	=====================================================================================================
	<?="\n"?>
