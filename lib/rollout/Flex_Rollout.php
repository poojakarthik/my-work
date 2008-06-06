<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'flex.require.php');
require_once('Flex_Rollout_Version.php');

class Flex_Rollout
{

	public function updateFromVersion($intVersion=NULL)
	{
		// Using the default connection, find the current (maximum) version number
		$strTables = 'DATABASE_VERSION';
		$arrColumns = array( 'VERSION' 	=> 'max(VERSION)');
		$strWhere = NULL;
		$arrWhere = Array();
		$selVersion = new StatementSelect($strTables, $arrColumns, $strWhere);
		$mixResult = $selVersion->Execute($arrWhere);

		// If we couldn't get the connection or result, bail out before we do any damage!
		if ($mixResult === FALSE || !$mixResult)
		{
			throw new Exception("Rollout was unable to determine the current database version prior to starting.");
		}

		// Get the version number from the results
		$arrVersion = $selVersion->Fetch();
		$intVersion = intval($arrVersion['VERSION']);

		// Get the available versions after the current version
		$arrVersions = self::_getVersionsAfter($intVersion);
		$versions = array_keys($arrVersions);

		// Rollouts can use any of the configured database connections,
		// so we need to start a transaction on each of them.
		$arrConnectionNames = array_keys($GLOBALS['**arrDatabase']);
		$nrConnections = count($arrConnectionNames);
		$arrConnections = array();

		$index = 0;
		$nrVersions = count($versions);

		// If there are no rollouts, end here
		if (!$nrVersions)
		{
			return '';
		}

		// Begin a database transaction for each connection
		for ($i = 0; $i < $nrConnections; $i++)
		{
			try
			{
				$step = 'connect to';
				$arrConnections[$i] = DataAccess::getDataAccess($arrConnectionNames[$i]);
				$step = 'start transaction on';
				$arrConnections[$i]->TransactionStart();
			}
			catch (Exception $e)
			{
				$dbName = $arrConnectionNames[$i];
				throw new Exception("Rollout failed to $step database ''$dbName' prior to starting: " . $e->getMessage());
			}
		}

		$errors = array();

		try
		{
			// Roll out each change in order
			for($index = 0; $index < $nrVersions; $index++)
			{
				$arrVersions[$versions[$index]]->rollout();
			}
			$index--;

			// Using the default database connection, update the database version number
			$arrValues = array(
				'VERSION' => max($versions),
				'ROLLED_OUT_DATE' => date('Y-m-d H:i:s')
			);
			$insVersion = new StatementInsert($strTables, NULL);
			$mxdResult = $insVersion->Execute($arrValues);
			if ($mxdResult === FALSE)
			{
				throw new Exception('Failed to update the database version number to ' . max($versions));
			}
		}
		catch (Exception $exception)
		{
			$errors[] = "ERROR: " . $exception->getMessage();

			// Need to rollback the db changes for each connection
			for ($i = 0; $i < $nrConnections; $i++)
			{
				try
				{
					$arrConnections[$i]->TransactionRollback();
				}
				catch (Exception $e)
				{
					$errors[] = "ERROR: Rollout failed to rollback changes to db " . $arrConnectionNames[$i] . ": " . $e->getMessage();
				}
			}

			// For each update applied do a rollback, in reverse order
			for(; $index >= 0; $index--)
			{
				try
				{
					$arrVersions[$versions[$index]]->rollback();
				}
				catch (Exception $e)
				{
					// This really shouldn't happen!
					$errors[] = "WARNING: Failed to rollback version " . $versions[$index] . " (non-database changes only)";
				}
			}

			throw new Exception(implode("\n", $errors));
		}

		// Commit the database changes
		for ($i = 0; $i < $nrConnections; $i++)
		{
			try
			{
				$arrConnections[$i]->TransactionCommit();
			}
			catch (Exception $e)
			{
				$errors[] = "ERROR: Rollout failed to commit changes to db " . $arrConnectionNames[$i] . ": " . $e->getMessage();
			}
		}

		// Invoke the commit function for each rollout, in the same sequence
		for($index = 0; $index < $nrVersions; $index++)
		{
			try
			{
				$arrVersions[$versions[$index]]->commit();
			}
			catch (Exception $e)
			{
				// This really shouldn't happen!
				$errors[] = "WARNING: Failed to commit version " . $versions[$index] . " (non-database changes only)";
			}
		}

		$errors = implode("\n", $errors);

		if ($errors)
		{
			throw new Exception($errors);
		}

		return $errors;
	}


	private static function _getVersionsAfter($intAfter=NULL)
	{
		if ($intAfter === NULL || !is_int($intAfter))
		{
			$intAfter = -1;
		}

		$pattern = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'version' . DIRECTORY_SEPARATOR . 'Flex_Rollout_Version_*.php';
		$arrVersions = array();
		preg_match_all("/_([0-9]+)\.php$/", implode("\n", glob($pattern)), $arrVersions);
		$arrNewVersions = array();
		foreach($arrVersions[1] as $strVersion)
		{
			$intVersion = intval($strVersion);
			if ($intVersion <= $intAfter)
			{
				continue;
			}

			$className = "Flex_Rollout_Version_$strVersion";
			
			$arrNewVersions[$intVersion] = Flex_Rollout_Version::getInstance($className);
		}

		if (!ksort($arrNewVersions))
		{
			throw new Exception('Unable to load and order version updates.');
		}

		return $arrNewVersions;
	}
	
}

?>
