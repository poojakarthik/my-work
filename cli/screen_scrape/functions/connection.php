<?php
	
	function Connection_Transmit ($method, $address, $params=Array ())
	{
		$ch = curl_init ();
		
		curl_setopt ($ch, CURLOPT_URL, $address);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_VERIFYHOST, FALSE);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt ($ch, CURLOPT_HEADER, FALSE);
		curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)");
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt ($ch, CURLOPT_COOKIEJAR, "/var/www/screen_scrape/cookies.txt");
		curl_setopt ($ch, CURLOPT_COOKIEFILE, "/var/www/screen_scrape/cookies.txt");
		
		$fp = fopen ("/var/www/screen_scrape/error.txt", "w");
		curl_setopt ($ch, CURLOPT_STDERR, $fp);
		
		if ($method == "POST")
		{
			$_Params = Array ();
			
			foreach ($params as $i => $j)
			{
				if (!empty ($i) && !empty ($j))
				{
					$_Params [] = $i . "=" . urlencode ($j);
				}
			}
			
			curl_setopt ($ch, CURLOPT_POSTFIELDS, implode ($_Params, "&"));
			curl_setopt ($ch, CURLOPT_POST, TRUE);
		}
		
		// grab URL and pass it to the browser
		$response = curl_exec($ch);

		// close CURL resource, and free up system resources
		curl_close($ch);
		fclose ($fp);
		
		chmod ("/var/www/screen_scrape/error.txt", 0777);
		
		return $response;
	}
	
	function Connection_Login ()
	{
		return Connection_Transmit
		(
			"POST", 
			"https://sp.teleconsole.com.au/sp/login.php", 
			Array (
				"username"		=> "ScottH",
				"email"			=> "scott@telcoblue.com.au",
				"pass"			=> "Scotth2x4",
				"saveLogin"		=> "y",
				"Submit"		=> "Login"
			)
		);
	}
	
	function Connection_Logout ()
	{
		return Connection_Transmit
		(
			"POST", 
			"https://sp.teleconsole.com.au/sp/includes/sessiondestroy.php"
		);
	}
	
?>
