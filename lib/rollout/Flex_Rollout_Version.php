<?php

abstract class Flex_Rollout_Version
{
	const NEW_SYSTEM_CUTOVER = 60;

	public function getInstance($className)
	{
		require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'version' . DIRECTORY_SEPARATOR . $className . '.php';
		return new $className();
	}

	protected final function __construct()
	{
	}

	/**
	 * This function must be implemented by the subclass.
	 * The subclass should make any changes necessary, including non-db changes,
	 * in a manner which it can rollback if needed.
	 * If necessary, it can create temporary file resources to ensure that a rolloback 
	 * is possible. 
	 * If the database update completes successfully, the commit() function will be invoked.
	 * If the database update fails, the rollback() function will be invoked.
	 * The rollback() and commit() functions should clear up any temporary resources created
	 * by the rollout() function. Note however that all changes to the database will be
	 * committed or rolled-back automatically.
	 */
	public abstract function rollout();

	/**
	 * This function need only be implemented by the subclass if it 
	 * has non-db changes to rollback, such as removing temp file resources.
	 * Database changes are rolled back automatically.
	 */
	public function rollback()
	{
		// Default implementation does nothing
	}

	/**
	 * This function need only be implemented by the subclass if it 
	 * has non-db changes to commit, such as removing temp file resources.
	 * Database changes are committed automatically.
	 */
	public function commit()
	{
		// Default implementation does nothing
	}

	protected function outputMessage($strMessage)
	{
		if ($fh = fopen('php://stdout','w'))
		{
			fwrite($fh, $strMessage);
			fclose($fh);
		}
	}

	/**
	 * This function can be invoked by the subclass to interact with a user at the command line.
	 */
	protected function getUserResponse($strPrompt)
	{
		set_time_limit(0);
		if ($fh = fopen('php://stdout','w'))
		{
			fwrite($fh, $strPrompt . " ");
			fclose($fh);
		}
		if ($fh = fopen('php://stdin','rb'))
		{
			$strResponse = fread($fh,1024);
			fclose($fh);
		}
		set_time_limit(600);
		return trim($strResponse);
	}

	/**
	 * This function can be invoked by the subclass to interact with a user at the command line
	 * and wait for an integer response.
	 */
	public function getUserResponseInteger($message)
	{
		$ok = TRUE;
		do
		{
			$msg = "\n".$message;
			if (!$ok)
			{
				$msg = "\nInvalid response. Please enter an integer value." . $msg;
			}
			$response = $this->getUserResponse($msg);
			$ok = FALSE;
		} while(!is_numeric($response));
		return intval($response);
	}

	/**
	 * This function can be invoked by the subclass to interact with a user at the command line
	 * and wait for a decimal response.
	 */
	public function getUserResponseDecimal($message)
	{
		$ok = TRUE;
		do
		{
			$msg = "\n".$message;
			if (!$ok)
			{
				$msg = "\nInvalid response. Please enter a decimal value." . $msg;
			}
			$response = $this->getUserResponse($msg);
			$ok = FALSE;
		} while(!is_numeric($response));
		return floatval($response);
	}

	/**
	 * This function can be invoked by the subclass to interact with a user at the command line
	 * and wait for a yes (TRUE)/no (FALSE) response.
	 */
	public function getUserResponseYesNo($message)
	{
		$ok = TRUE;
		do
		{
			$msg = "\n".$message . " (enter 'y' for yes or 'n' for no)";
			if (!$ok)
			{
				$msg = "\nInvalid response. Please enter 'y' for yes or 'n' for no." . $msg;
			}
			$response = strtolower(trim($this->getUserResponse($msg)));
			$ok = FALSE;
		} while(!$response || ($response[0] != 'y' && $response[0] != 'n'));
		return $response[0] == 'y';
	}
}
?>
