<?php

// Note: Suppress errors whilst loading application as there may well be some if the 
// database model files have not yet been generated.
$_SESSION = array();
// Load Flex.php
require_once(dirname(__FILE__) . "/../../../lib/classes/Flex.php");
Flex::load();

class Cli_App_Voice_Message_HACK extends Cli
{
	public function run()
	{
		try
		{
			self::runVT();
			self::runTB();
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());
		}
	}
	
	public function runVT()
	{
		self::doForCG(2, "This is an urgent message from Tel coe blue   your telephone provider. According to our records your account remains unpaid. Suspension of your telephone services will commence within the next 24 hours. To avoid disruption please pay your Tel coe blue  account immediately and forward the remittance advice. This is an urgent message from Tel coe blue   your telephone provider. According to our records your account remains unpaid. Suspension of your telephone services will commence within the next 24 hours. To avoid disruption please pay your Tel coe blue account immediately and forward the remittance advice", FILES_BASE_PATH . "/voicetalk.csv");
	}
	
	public function runTB()
	{
		self::doForCG(1, "This is an urgent message from Voicetalk   your telephone provider. According to our records your account remains unpaid. Suspension of your telephone services will commence within the next 24 hours. To avoid disruption please pay your Voicetalk   account immediately and forward the remittance advice. This is an urgent message from Voicetalk   your telephone provider. According to our records your account remains unpaid. Suspension of your telephone services will commence within the next 24 hours. To avoid disruption please pay your Voicetalk   account immediately and forward the remittance advice", FILES_BASE_PATH . "/telco_blue.csv");
	}
	
	public function doForCG($cg, $message, $name)
	{
		$this->log("Processing customer group $cg.");
		$intEffectiveTime = mktime(0,0,0,date('m'),(date('d')+2),date('Y'));
		$this->log("Effective date: " . date("Y-m-d H:i:s", $intEffectiveTime));
		$contacts = $this->listBarrableAccounts($intEffectiveTime, $cg);
		$this->log("Found " . count($contacts) . " accounts for customer group $cg.");
		
		$f = fopen($name, 'w+');
		$fx = null;
		$first = true;

		foreach ($contacts as $contact)
		{
			$accountId = self::getName($contact["AccountId"]);
			//$contactName = self::getName(trim($contact["FirstName"]));
			//$amount = Flex::framework()->GetOverdueBalance($contact["AccountId"]);

			$nr = preg_replace("/[^0-9]+/", '', $contact["Phone"]);
			
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


//			$amountx = explode('.', $amount);
//			if (count($amountx) > 1)
//			{
//				$amount = $amountx[0] . ' and ';
//				$cent = $amountx[1] . '0000000000000';
//				$cent = substr($cent, 0, 2);
//				$amount .= $cent . ' cents';
//			}
			
			$output = $nr . ',' . $message;
			if ($crap)
			{
				$output = $accountId . ',' . $output;
			}
			//$output = str_replace('[name]', $contactName, $output);
			//$output = str_replace('[amount]', $amount, $output);
			if ($crap && !$fx)
			{
				$fx = fopen($name.'.bad_phone_numbers', 'w+');
			}
			fwrite($crap ? $fx : $f, "\r\n".$output);
		}
		fclose($f);
		if ($fx)
		{
			fclose($fx);
		}
		
		$contents = explode("\r\n", trim(file_get_contents($name)));
		sort($contents);
		$f = fopen($name, 'w+');
		fwrite($f, implode("\r\n", $contents) . "\r\n");
	}
	
	public static function getName($firstName)
	{
		return $firstName ? $firstName : 'customer';
	}

	public function listBarrableAccounts($intEffectiveTime, $customerGroupId)
	{
		if ($customerGroupId == 1)
		{
			$strSQL = "
SELECT Contact.Phone AS Phone, Account.Id AS AccountId
FROM Account, Contact
WHERE Account.PrimaryContact = Contact.Id
  AND
Account.Id IN (1000006772, 1000007642, 1000154916, 1000154941, 1000155463, 1000155632, 1000155675, 1000155822, 1000156499, 1000156636, 1000156665, 1000156678, 1000156925, 1000156968, 1000157199, 1000157217, 1000158014, 1000158253, 1000158489, 1000158560, 1000158874, 1000159402, 1000159686, 1000159814, 1000159881, 1000160234, 1000160402, 1000160786, 1000160968, 1000162325, 1000162403, 1000162534, 1000162700, 1000162863, 1000163492, 1000163526, 1000163709, 1000163843, 1000164557, 1000164568, 1000165207, 1000166607, 1000166656, 1000167208, 1000168035, 1000168869, 1000168875, 1000169057, 1000169272)
";
		}
		else if ($customerGroupId == 2)
		{
			$strSQL = "
SELECT Contact.Phone AS Phone, Account.Id AS AccountId
FROM Account, Contact
WHERE Account.PrimaryContact = Contact.Id
  AND
Account.Id IN (1000006120, 1000006261, 1000008396, 1000008470, 1000159961, 1000160142, 1000160589, 1000160630, 1000160766, 1000161372, 1000161382, 1000161801, 1000163079, 1000163191, 1000163226, 1000163279, 1000163333, 1000163632, 1000163683, 1000163766, 1000163847, 1000163881, 1000163889, 1000163891, 1000164015, 1000164099, 1000164151, 1000164205, 1000164305, 1000164532, 1000164574, 1000164655, 1000164658, 1000164699, 1000164768, 1000164792, 1000164951, 1000164970, 1000165191, 1000165192, 1000165235, 1000165243, 1000165379, 1000165384, 1000165387, 1000165427, 1000165469, 1000165550, 1000165559, 1000165611, 1000165627, 1000165657, 1000165683, 1000165724, 1000165741, 1000165786, 1000165789, 1000165836, 1000165840, 1000165878, 1000165922, 1000165971, 1000165976, 1000165990, 1000165998, 1000166033, 1000166096, 1000166110, 1000166124, 1000166139, 1000166149, 1000166174, 1000166207, 1000166228, 1000166230, 1000166255, 1000166260, 1000166290, 1000166316, 1000166317, 1000166338, 1000166357, 1000166440, 1000166476, 1000166479, 1000166504, 1000166525, 1000166537, 1000166552, 1000166574, 1000166583, 1000166585, 1000166611, 1000166613, 1000166624, 1000166672, 1000166686, 1000166700, 1000166707, 1000166722, 1000166728, 1000166735, 1000166778, 1000166818, 1000166822, 1000166900, 1000166908, 1000166910, 1000166920, 1000166939, 1000166976, 1000167012, 1000167040, 1000167046, 1000167054, 1000167063, 1000167065, 1000167083, 1000167088, 1000167154, 1000167158, 1000167161, 1000167168, 1000167203, 1000167209, 1000167241, 1000167253, 1000167266, 1000167295, 1000167309, 1000167319, 1000167328, 1000167353, 1000167378, 1000167408, 1000167444, 1000167446, 1000167454, 1000167457, 1000167478, 1000167506, 1000167553, 1000167557, 1000167566, 1000167583, 1000167627, 1000167634, 1000167641, 1000167642, 1000167654, 1000167656, 1000167670, 1000167700, 1000167779, 1000167780, 1000167824, 1000167832, 1000167870, 1000167875, 1000167876, 1000167923, 1000167936, 1000167942, 1000167961, 1000167964, 1000167989, 1000168020, 1000168023, 1000168029, 1000168061, 1000168073, 1000168088, 1000168155, 1000168177, 1000168217, 1000168238, 1000168249, 1000168256, 1000168266, 1000168279, 1000168307, 1000168334, 1000168337, 1000168364, 1000168369, 1000168377, 1000168390, 1000168402, 1000168405, 1000168436, 1000168439, 1000168496, 1000168514, 1000168515, 1000168522, 1000168526, 1000168527, 1000168585, 1000168598, 1000168601, 1000168666, 1000168688, 1000168696, 1000168731, 1000168737, 1000168738, 1000168747, 1000168786, 1000168808, 1000168809, 1000168819, 1000168836, 1000168854, 1000168856, 1000168877, 1000168879, 1000168882, 1000168957, 1000168985, 1000169004, 1000169007, 1000169026, 1000169030, 1000169032, 1000169034, 1000169050, 1000169052, 1000169075, 1000169076, 1000169103, 1000169112, 1000169147, 1000169159, 1000169172, 1000169180, 1000169193, 1000169215, 1000169242, 1000169244, 1000169299, 1000169336, 1000169337, 1000169338, 1000169345, 1000169358, 1000169366, 1000169377, 1000169414, 1000169424, 1000169473, 1000169488)
";
		}
		
		$db = Data_Source::get();
		$res = $db->query($strSQL);
		if (PEAR::isError($res))
		{
			$this->log("\n\n$strSQL\n\n");
			throw new Exception("Failed to load contact details for barring: " . $res->getMessage());
		}
		return $res->fetchAll(MDB2_FETCHMODE_ASSOC);
	}
}



?>
