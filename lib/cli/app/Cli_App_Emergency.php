<?php

// Note: Suppress errors whilst loading application as there may well be some if the 
// database model files have not yet been generated.
$_SESSION = array();
// Load Flex.php
require_once(dirname(__FILE__) . "/../../../lib/classes/Flex.php");
Flex::load();

class Cli_App_Emergency extends Cli
{
	public function run()
	{
		self::runVT();
		self::runTB();
	}
	
	public function runVT()
	{
		self::doForCG(2, "Hello [name].  Your Voicetalk account has been affected due to non payment   of the amount \$[amount].  If you have already paid  please e mail your receipt to  contact @ Voice talk dot com dot a  u .   Otherwise please pay your account as soon as possible by calling 1300 65 35 88 .  And e mail your receipt to contact @ Voice talk dot com dot a   u to have your services promptly restored.", dirname(__FILE__) . "/../../../../flex/voicetalk.csv");
	}
	
	public function runTB()
	{
		self::doForCG(1, "Hello [name].  Your Tell coe blue account has been affected due to non payment   of the amount \$[amount].  If you have already paid  please e mail your receipt to  contact @ tell coe blue dot com dot a  u .    Otherwise please pay your account as soon as possible by calling 1300 79 71 14 .  And e mail your receipt to contact @ tell coe blue dot com dot a   u to have your services promptly restored.", dirname(__FILE__) . "/../../../../flex/telco_blue.csv");
	}
	
	public function doForCG($cg, $message, $name)
	{
		$db = Data_Source::get();
		$res = $db->query(
"select Account.Id as AccountId, Title, FirstName, LastName, Phone from Account, Contact 
 where Account.PrimaryContact = Contact.Id and Account.automatic_barring_status = 2 and Account.CustomerGroup = $cg and automatic_barring_datetime > '2008-09-22 00:00:00' 
 and Account.Id NOT IN (SELECT Account FROM ProvisioningRequest WHERE Type = 903 AND RequestedOn > '2008-09-23 00:00:00')");
		$contacts = $res->fetchAll();
		
		$f = fopen($name, 'w+');
		$fx = fopen($name.'.crap', 'w+');
		$first = true;
		
		foreach ($contacts as $contact)
		{
			$contactName = self::getName(trim($contact[1]), trim($contact[2]), trim($contact[3]));
			$amount = Flex::framework()->GetOverdueBalance($contact[0]);

			$nr = preg_replace("/[^0-9]+/", '', $contact[4]);
			
			$l = strlen($nr);
			
			$crap = false;
			
			if ($l > 10 || $l < 9)
			{
				$crap = true;
			}
			if ($l == 9)
			{
				$nr = '0' . $nr;
				$l = 10;
			}
			if ($nr[0] != '0')
			{
				$crap = true;
			}
			if (!$crap && $nr[1] == '4')
			{
				$crap = true;
			}


			$amountx = explode('.', $amount);
			if (count($amountx) > 1)
			{
				$amount = $amountx[0] . ' and ';
				$cent = $amountx[1] . '0000000000000';
				$cent = substr($cent, 0, 2);
				$amount .= $cent . ' cents';
			}
			
			$output = $nr . ',' . $message;
			$output = str_replace('[name]', $contactName, $output);
			$output = str_replace('[amount]', $amount, $output);
			fwrite($crap ? $fx : $f, "\r\n".$output);
		}
		fclose($f);
		fclose($fx);
		
		$contents = explode("\r\n", trim(file_get_contents($name)));
		sort($contents);
		$f = fopen($name, 'w+');
		fwrite($f, implode("\r\n", $contents) . "\r\n");
	}
	
	public static function getName($title, $firstName, $surname)
	{
		return $firstName ? $firstName : 'customer';
		/*
		return (($title && !$firstName && $surname) ? $title . ' ' : '') . // Output a title if we have a title and surname but no christian name 
				($firstName ? $firstName : '') . // Output a cristian name if we have one
				($firstName && $surname ? ' ' : '') . // If we have both christian and surname, put a space between them
				($surname ? $surname : '') . // If we have a surname, output it
				(!$firstName && !$surname ? 'customer' : ''); // If we have neither christian name nor surname, output 'customer'
		*/
	}
}




?>
