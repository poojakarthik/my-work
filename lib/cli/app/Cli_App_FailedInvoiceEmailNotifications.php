<?php


class Cli_App_FailedInvoiceEmailNotifications extends Cli
{
	const SWITCH_POSTFIX_LOG	= "f";

	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			// Include the application... 
			$this->requireOnce('flex.require.php');

			$accountLink = '/admin/flex.php/Account/Overview/?Account.Id=';

			$strIntro = 'The email addresses for the following accounts were recently found to be invalid. Any recent attempts to send emails to those addresses will have failed. Please check the details are entered correctly for each account.';
			$strFooter = "Regards,<br/>\n<br/>\nThe Team at Yellow Billing";

			$pathToLog = $arrArgs[self::SWITCH_POSTFIX_LOG];
			
			// Need to parse the log file here to extract the rejected email addresses
			$sPathWithWrapper	= (strtolower(array_pop(explode('.', $pathToLog))) === 'gz') ? 'compress.zlib://'.$pathToLog : $pathToLog;
			
			if (strpos($sPathWithWrapper, 'compress.zlib://') === 0)
			{
				$this->log("File '$pathToLog' is ZLIB compressed -- opening with wrapper.");
			}
			
			$log = file_get_contents($sPathWithWrapper);
			if (!$log)
			{
				$this->log("Log file '$pathToLog' is empty.");
				return 0;
			}
			
			$matches = array();
			preg_match_all("/ to=\<([^\>\n\r]+)\>[^\n\r]+status=bounced/", $log, $matches);
			@$arrEmailAddresses = array_unique($matches[1]);
			if (!$arrEmailAddresses || !count($arrEmailAddresses))
			{
				$this->log('No bounced messages detected.');
				return 0;
			}
			$this->log(count($arrEmailAddresses) . ' different failed email address detected.');

			// Escape the email address strings to prevent sql injection & prevent errors - this is a bit hacky!
			//$func = function_exists('mysql_escape_string') ? 'mysql_escape_string' : (function_exists('mysqli_escape_string') ? 'mysqli_escape_string' : 'pg_escape_string');
			//array_walk($arrEmailAddresses, $func);
			foreach ($arrEmailAddresses as &$strEmailAddress)
			{
				//$strEmailAddress	= call_user_func($func, $strEmailAddress);
				$strEmailAddress	= DataAccess::getDataAccess()->refMysqliConnection->real_escape_string($strEmailAddress);
			}
			unset($strEmailAddress);

			// Build the 'Email IN (...)' part of the SQL queries
			$strSqlEmailIn = "Email in ('" . implode("', '", $arrEmailAddresses) . "')";
			$strSqlEmailIn = str_replace('noemail@', ' i-g-n-o-r-e ', $strSqlEmailIn);
			$strSqlEmailIn = str_replace('no email', ' i-g-n-o-r-e ', $strSqlEmailIn);

			// Find the affected customer groups
			$sel = array('CG.Id', 'CG.outbound_email');
			$selCustGroups = new StatementSelect('CustomerGroup CG', $sel, "CG.Id IN (
					SELECT distinct(CustomerGroup.Id)
					  FROM CustomerGroup, Account, Contact
					 WHERE Contact.AccountGroup = Account.AccountGroup
					   AND CustomerGroup.Id = Account.CustomerGroup
					   AND $strSqlEmailIn)");
			$intNrCustGroups = $selCustGroups->Execute(NULL);
			if ($intNrCustGroups === FALSE)
			{
				throw new Exception('ERROR: Failed to retrieve customer groups.');
			}
			$affectedCustomerGroups = $selCustGroups->FetchAll();

			if (!$intNrCustGroups)
			{
				$this->log('None of the failed emails related to the customer groups.');
				return 0;
			}
			else
			{
				$this->log('The failed emails were related to ' . $intNrCustGroups . ' customer groups.');
			}

			$sel = array(	"account_id"				=> "Account.Id",
							"contact_first_name"		=> "Contact.FirstName",
							"contact_last_name"			=> "Contact.LastName",
							"contact_email"				=> "Contact.Email",
							"customer_group_flex_url"	=> "CustomerGroup.flex_url"
						);
			$selAccounts = new StatementSelect("Account, Contact, CustomerGroup", $sel, "Contact.AccountGroup = Account.AccountGroup AND Account.CustomerGroup = <CustomerGroup> AND CustomerGroup.Id = Account.CustomerGroup AND $strSqlEmailIn");

			$exitCode = 0;

			// For each customer group
			foreach($affectedCustomerGroups as $arrCustomerGroup)
			{
				$intCustomerGroup = $arrCustomerGroup['Id'];
				$strNoticationEmail = $arrCustomerGroup['outbound_email'];

				// Retrieve the result rows for the relevant email addresses
				$where = array('CustomerGroup' => $intCustomerGroup);
				$intNrAccounts = $selAccounts->Execute($where);
				if ($intNrAccounts === FALSE)
				{
					$this->log("ERROR: Failed to retrieve accounts for customer group $intCustomerGroup.", TRUE);
					$exitCode++;
					continue;
				}

				$arrRows = $selAccounts->FetchAll();
				foreach ($arrRows as $idx => $email) 
				{
					$arrRows[$idx] = "
<tr>
	<td>{$email['account_id']}</td>
	<td>{$email['contact_first_name']} {$email['contact_last_name']}</td>
	<td>{$email['contact_email']}</td>
	<td>
		<a href='{$email['customer_group_flex_url']}{$accountLink}{$email['account_id']}'>{$email['customer_group_flex_url']}{$accountLink}{$email['account_id']}</a>
	</td>
</tr>";
				}

				// Complete building the html body
				$html = '<html>' .
						"<head><style content='text/css'>body { background-color: #fff; font-family: Arial, Verdana, Sans-Serif !important };th { font-weight: bold; }</style></head>" .
						"<body>$strIntro\n\n<table><tr><th>Account</th>\t<th>Contact</th>\t<th>Email</th>\t<th>Link to account in Flex</th></tr>\n" .
						implode("\n", $arrRows) . "</table>\n<br/>\n<br/>$strFooter" . $arrCustomerGroup[1] . "</body></html>";

				if (!Email_Notification::sendEmailNotification('FAILED_EMAIL_REPORT', $intCustomerGroup, NULL, 'Recent Email Failures: ' . date('Y-m-d H:i:s'), $html, NULL, NULL, TRUE))
				{
					$this->log("ERROR: Failed to send email to $strNoticationEmail for customer group $intCustomerGroup.", TRUE);
					$exitCode++;
					continue;
				}
			}

			$this->log("Finished.");
			return $exitCode;

		}
		catch(Exception $exception)
		{
			$this->showUsage('ERROR: ' . $exception->getMessage());
			return 1;
		}
	}

	function getCommandLineArguments()
	{
		$commandLineArguments = array(
			self::SWITCH_POSTFIX_LOG => array(
				self::ARG_LABEL 		=> "POSTFIX_LOG_FILE",
				self::ARG_REQUIRED 	=> TRUE,
				self::ARG_DESCRIPTION => "is the full path to the Postfix log (or error file)",
				self::ARG_DEFAULT 	=> NULL,
				self::ARG_VALIDATION 	=> 'Cli::_validFile("%1$s", TRUE)'
			),

		);
		return $commandLineArguments;
	}

}


?>
