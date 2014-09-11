<?php

/**
 * Version 0 (zero) of database update.
 * This version is reserved for use as an interactive installation/config wizard.
 * The functions on this class will only be invoked when running from the command line,
 * so standard command line reading can be used for interaction.
 */

class Flex_Rollout_Version_000000 extends Flex_Rollout_Version
{
	public function rollout()
	{
		/* Exmaple of getting user response:
		$user = $this->getUserResponse("Who are you?");
		echo "Hello $user!\n";
		*/
	}
}

?>
