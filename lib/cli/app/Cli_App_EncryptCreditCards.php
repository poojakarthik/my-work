<?php

class Cli_App_EncryptCreditCards extends Cli
{

	function run()
	{
		try
		{
			if (!function_exists('mcrypt_get_iv_size'))
			{
				throw new Exception("mcrypt library is not installed.");
			}

			if (!defined('MCRYPT_RIJNDAEL_256'))
			{
				throw new Exception("MCRYPT_RIJNDAEL_256 is not available.");
			}

			if (!defined('MCRYPT_MODE_CFB'))
			{
				throw new Exception("MCRYPT_MODE_CFB is not available.");
			}
			$this->log("\nRequired libraries appear to be loaded and ready.");

			// Include the application... 
			$this->requireOnce("flex.require.php");

			// Get the key for this customer
			if (!array_key_exists('**arrCustomerConfig', $GLOBALS) || !array_key_exists('Key', $GLOBALS['**arrCustomerConfig']))
			{
				throw new Exception("Encryption key has not been configurred in customer.cfg.php (\$GLOBALS['**arrCustomerConfig']['Key']).");
			}

			// Test that encryption & decryption are working
			//$before = '1234 5678 9012 3456';
			$before = '081';
			$encrypted = Encrypt($before);
			$after = Decrypt($encrypted);
			if ($before !== $after)
			{
				throw new Exception("Pre-run test failed: $before > $encrypted > $after");
			}
			$this->log("\nPre-run test passed.");

			// Read in the credit card details from the database
			$strTables = "CreditCard";

			$arrColumns = Array( "Id" 			=> "Id",
			 					 "CardNumber" 	=> "CardNumber",
			 					 "CVV" 			=> "CVV");

			$strWhere = NULL;

			$arrWhere = Array();

			$selCreditCards = new StatementSelect($strTables, $arrColumns, $strWhere);

			$mixResult = $selCreditCards->Execute($arrWhere);

			if ($mixResult === FALSE)
			{
				throw new Exception("An error occurred when fetching credit card details.");
			}
			$this->log("\nFound $mixResult card details.");

			$arrRecordSet = $selCreditCards->FetchAll();
			$this->log("\nFetched all card details.\nRunning pre-update encryption test for each.\nTesting ");

			$maxLen = 0;
			$maxLenCVV = 0;
			$strLen = 0;
			for ($i = 0; $i < $mixResult; $i++)
			{
				$this->log(str_repeat(chr(8), $strLen), FALSE, TRUE);
				$this->log($i + 1, FALSE, TRUE);
				$strLen = strlen($i + 1);

				$before = $arrRecordSet[$i]['CardNumber'];
				$beforeCVV = $arrRecordSet[$i]['CVV'];
				$encrypted = Encrypt($arrRecordSet[$i]['CardNumber']);
				$encryptedCVV = Encrypt($arrRecordSet[$i]['CVV']);
				$maxLen = max($maxLen, strlen($encrypted));
				$maxLenCVV = max($maxLenCVV, strlen($encryptedCVV));
				$arrRecordSet[$i]['EncryptedCardNumber'] = $encrypted;
				$arrRecordSet[$i]['EncryptedCVV'] = $encryptedCVV;
				$after = Decrypt($encrypted);
				$afterCVV = Decrypt($encryptedCVV);
				if (   ( (($before != '') || ($after != '')) && ($before !== $after) )
					|| ( (($beforeCVV != '') || ($afterCVV != '')) && ($beforeCVV !== $afterCVV) ) )
				{
					throw new Exception("Pre-update test failed: $before > $encrypted > $after && $beforeCVV > $encryptedCVV > $afterCVV for Id " . $arrRecordSet[$i]['Id']);
				}
			}
			$this->log("\nPre-update encryption test passed.");

			$arrColumnsBoth = Array( "CardNumber" => "CardNumber", "CVV" => "CVV");
			$arrColumnsCVV = Array( "CVV" => "CVV");
			$arrColumnsCardNumber = Array( "CardNumber" => "CardNumber");
			$updCreditCard = new StatementUpdate($strTables, "Id = <Id>", $arrColumns, 1);

			$this->log("\nUpdating database...");
			
			$GLOBALS['dbaDatabase']->TransactionStart();
			
			for ($i = 0; $i < $mixResult; $i++)
			{
				if ($arrRecordSet[$i]['CardNumber'] === "" && ($arrRecordSet[$i]['CVV'] === "" || $arrRecordSet[$i]['CVV'] === NULL))
				{
					$this->log("\nSkipping blank record for id " . $arrRecordSet[$i]['Id'] . ".");
					continue;
				}
				if ($arrRecordSet[$i]['CardNumber'] === "")
				{
					$arrCols = $arrColumnsCVV;
					$arrData = array('CVV' => $arrRecordSet[$i]['EncryptedCVV']);
					$arrWhere = array('Id' => $arrRecordSet[$i]['Id']);
				}
				else if ($arrRecordSet[$i]['CVV'] === "" || $arrRecordSet[$i]['CVV'] === NULL)
				{
					$arrCols = $arrColumnsCardNumber;
					$arrData = array('CardNumber' => $arrRecordSet[$i]['EncryptedCardNumber']);
					$arrWhere = array('Id' => $arrRecordSet[$i]['Id']);
				}
				else
				{
					$arrCols = $arrColumnsBoth;
					$arrData = array('CardNumber' => $arrRecordSet[$i]['EncryptedCardNumber'],
									 'CVV' => $arrRecordSet[$i]['EncryptedCVV']);
					$arrWhere = array('Id' => $arrRecordSet[$i]['Id']);
				}
				$arrWhere = array('Id' => $arrRecordSet[$i]['Id']);
				$this->log("\nEncrypting " . $arrRecordSet[$i]['CardNumber'] . ", " . $arrRecordSet[$i]['Encrypted'] . " for id " . $arrRecordSet[$i]['Id']);
				$intNrUpdates = $updCreditCard->Execute($arrData, $arrWhere);
				if ($intNrUpdates !== 1)
				{
					throw new Exception("Failed to update database for id " . $arrRecordSet[$i]['Id'] 
					. ". Original CardNumber = '" . $arrRecordSet[$i]['CardNumber'] . "' (" . strlen($arrRecordSet[$i]['CardNumber']) . ") > '" . $arrRecordSet[$i]['EncryptedCardNumber'] . "' (" . strlen($arrRecordSet[$i]['EncryptedCardNumber']) . "); "
					. ". Original CVV = '" . $arrRecordSet[$i]['CVV'] . "' (" . strlen($arrRecordSet[$i]['CVV']) . ") > '" . $arrRecordSet[$i]['EncryptedCVV'] . "' (" . strlen($arrRecordSet[$i]['EncryptedCVV']) . ")");
				}
			}

			$this->log("\nPreparing for post-update test.");
			$newMixResult = $selCreditCards->Execute($arrWhere);

			if ($newMixResult === FALSE)
			{
				throw new Exception("An error occurred when fetching updated credit card details.");
			}
			$this->log("\nFound $newMixResult updated card details.");
			if ($newMixResult !== $mixResult)
			{
				throw new Exception("Number of updated records ($newMixResult) does not match original number of records ($mixResult)!");
			}

			$arrUpdatedRecordSet = $selCreditCards->FetchAll();
			$this->log("\nFetched all updated card details.\nRunning post-update encryption test for each.\nTesting ");
			$strLen = 0;
			for ($i = 0; $i < $mixResult; $i++)
			{
				$this->log(str_repeat(chr(8), $strLen), FALSE, TRUE);
				$this->log($i + 1, FALSE, TRUE);
				$strLen = strlen($i + 1);

				$before = $arrRecordSet[$i]['CardNumber'];
				$beforeCVV = $arrRecordSet[$i]['CVV'];
				$encrypted = $arrUpdatedRecordSet[$i]['CardNumber'];
				$encryptedCVV = $arrUpdatedRecordSet[$i]['CVV'];
				$after = Decrypt($encrypted);
				$afterCVV = Decrypt($encryptedCVV);
				if (   ( (($before != '') || ($after != '')) && ($before !== $after) )
					|| ( (($beforeCVV != '') || ($afterCVV != '')) && ($beforeCVV !== $afterCVV) ) )
				{
					throw new Exception("Post-update test failed: $before (" . strlen($before) . ") > $encrypted > $after (" . strlen($after) . ") && $beforeCVV (" . strlen($beforeCVV) . ") > $encryptedCVV > $afterCVV (" . strlen($afterCVV) . ") for Id " . $arrRecordSet[$i]['Id']);
				}
			}
			$this->log("\nPost-update encryption test passed.");

			$GLOBALS['dbaDatabase']->TransactionCommit();

			// Must have worked! Exit with 'OK' code 0
			$this->log("\nCompleted successfully (max length of encrypted data is $maxLen & $maxLenCVV characters).\n");
			return 0;
		}
		catch(Exception $exception)
		{
			$GLOBALS['dbaDatabase']->TransactionRollback();
			$this->showUsage('ERROR: ' . $exception->getMessage());
			return 1;
		}
	}

}

?>
