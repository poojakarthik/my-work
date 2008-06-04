<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'flex.require.php');
require_once('Flex_Rollout_Version.php');

class Flex_Rollout
{

	public function updateFromVersion($intVersion=NULL)
	{
		$arrVersions = self::_getVersionsAfter($intVersion=NULL);

		// Begin a database transaction
		$GLOBALS['dbaDatabase']->TransactionStart();

		// Get the versions
		$versions = array_keys($arrVersions);

		$index = 0;
		$nrVersions = count($versions);

		$errors = array();

		try
		{
			for($index = 0; $index < $nrVersions; $index++)
			{
				$arrVersions[$versions[$index]]->rollout();
			}
		}
		catch (Exception $exception)
		{
			$errors[] = "ERROR: Failed to rollout version $version";

			// Need to rollback the db changes
			$GLOBALS['dbaDatabase']->TransactionRollback();

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

			return implode("\n", $errors);
		}

		// Commit the database changes
		$GLOBALS['dbaDatabase']->TransactionCommit();

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

		return implode("\n", $errors);
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
