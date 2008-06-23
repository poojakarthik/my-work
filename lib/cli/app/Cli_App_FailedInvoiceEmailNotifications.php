<?php


class Cli_App_FailedInvoiceEmailNotifications extends Cli
{
	const SWITCH_POSTFIX_LOG = "f";

	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$arrArgs = $this->getValidatedArguments();

			// Include the application... 
			$this->requireOnce('flex.require.php');

			// !!!HACK HACK HACK!!!
			// This is always for telcoblue, but it will need changing for other customer groups!
			$accountLink = 'https://telcoblue.yellowbilling.com.au/management/flex.php/Account/Overview/?Account.Id=';
			$this->log("WARNING: This script assumes an interface URL of 'https://telcoblue.yellowbilling.com.au/...'.\n         This must be changed before using for new customer groups.", TRUE);

			$strIntro = 'The email addresses for the following accounts were recently found to be invalid. Any recent attempts to send emails to those addresses will have failed. Please check the details are entered correctly for each account.';
			$strFooter = "Regards,<br/>\n<br/>\nThe Team at Yellow Billing";

			$pathToLog = $arrArgs[self::SWITCH_POSTFIX_LOG];

			// Need to parse the log file here to extract the rejected email addresses
			$log = file_get_contents($pathToLog);
			if (!$log)
			{
				$this->log("Log file '$pathToLog' is empty.");
				return 0;
			}

			$matches = array();
			preg_match_all("/.+ said: [45]{1,1}[0-9]{2,2} .*\<([^\>]+)\>/", $log, $matches);
			@$arrEmailAddresses = array_unique($matches[1]);
			if (!$arrEmailAddresses || !count($arrEmailAddresses))
			{
				$this->log('No bounced messages detected.');
				return 0;
			}
			$this->log(count($arrEmailAddresses) . ' bounced email address detected.');

			// Escape the email address strings to prevent sql injection & prevent errors - this is a bit hacky!
			$func = function_exists('mysql_escape_string') ? 'mysql_escape_string' : (function_exists('mysqli_escape_string') ? 'mysqli_escape_string' : 'pg_escape_string');
			array_walk($arrEmailAddresses, $func);

			// Build the 'Email IN (...)' part of the SQL queries
			$strSqlEmailIn = "Email in ('" . implode("', '", $arrEmailAddresses) . "')";
			$strSqlEmailIn = str_replace('noemail@', ' i-g-n-o-r-e ', $strSqlEmailIn);
			$strSqlEmailIn = str_replace('no email', ' i-g-n-o-r-e ', $strSqlEmailIn);

			// Find the affected customer groups
			$sel = array('CG.Id', 'CG.OutboundEmail');
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
				$this->log('No customer groups were related.');
				return 0;
			}
			else
			{
				$this->log($intNrCustGroups . ' customer groups were related.');
			}

			$sel = array('TR' => "Concat('<tr><td>', Account.Id, '</td>\t<td>', Contact.FirstName, ' ', Contact.LastName, '</td>\t<td>', Contact.Email, '</td>\t<td>',
					   '<a href=''$accountLink', Account.Id, '''>https://telcoblue.yellowbilling.com.au/management/flex.php/Account/Overview/?Account.Id=', Account.Id, '</a></td></tr>\n')");
			$selAccounts = new StatementSelect("Account, Contact", $sel, "Contact.AccountGroup = Account.AccountGroup AND Account.CustomerGroup = <CustomerGroup> AND $strSqlEmailIn");

			$exitCode = 0;

			// For each customer group
			foreach($affectedCustomerGroups as $arrCustomerGroup)
			{
				$intCustomerGroup = $arrCustomerGroup['Id'];
				$strNoticationEmail = $arrCustomerGroup['OutboundEmail'];

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
				foreach ($arrRows as $idx => $email) $arrRows[$idx] = $email['TR'];

				// Complete building the html body
				$html = '<html>' .
						"<head><style content='text/css'>body { background-color: #fff; font-family: Arial, Verdana, Sans-Serif !important };th { font-weight: bold; }</style></head>" .
						"<body>$strIntro\n\n<table><tr><th>Account</th>\t<th>Contact</th>\t<th>Email</th>\t<th>Link to account in Flex</th></tr>\n" .
						implode("\n", $arrRows) . "</table>\n<br/>\n<br/>$strFooter" . $arrCustomerGroup[1] . "</body></html>";

				// Build the text body
				// Strip out the style
				$text = preg_replace("/\<style.*\/style\>/s", '', $html);
				// Strip all other tags
				$text = strip_tags($text);

				// Send email to cust group email address
				$email = new Mail_mimePart('' , array('content_type' => 'multipart/related; type="multipart/alternative"'));
				$body = $email->addSubPart('' , array('content_type' => 'multipart/alternative'));
				$body->addSubPart($text, array('content_type' => 'text/plain'));
				$body->addSubPart($html, array('content_type' => 'text/html'));
				//$mimMime->_parts = array(0=>$body);
				//$mimMime->addBcc("root@bne-beprod-01.yellowbilling.com.au");
				$email = $email->encode();

				$strHeaders	= $email['headers'];
				//$strHeaders['Bcc'] = 'ybs-admin@yellowbilling.com.au';
				$strNoticationEmail = 'holiver@yellowbilling.com.au';
				$strBody	= $email['body'];
				$emlMail 	= &Mail::factory('mail');
				if (!$emlMail->send($strNoticationEmail, $strHeaders, $strBody))
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
