<?php

class Application_Handler_Console extends Application_Handler
{
	// View all the Customer Statuses in a tabulated format
	public function View($subPath)
	{
		// Build Context Menu
		ContextMenu()->Console->BugList();
		
		try
		{
			// Build the Daily Message
			try
			{
				$objMessage = Employee_Message::getForTime(GetCurrentISODateTime());
				if ($objMessage !== NULL)
				{
					$arrMessage = array("Message"	=> $objMessage->message,
										"Timestamp"	=> $objMessage->effectiveOn
										);
				}
				else
				{
					// There is no message
					$arrMessage = NULL;
				}
			}
			catch (Exception $e)
			{
				// Suppress the normal form of error reporting, by displaying the error as the message of the day
				$arrMessage = array("Message"	=> "The Daily Message functionality is currently broken.  Please notify your system administrators.\n". $e->getMessage(),
									"Timestamp"	=> GetCurrentISODateTime()
									);
			}
			
			$arrDetailsToRender = array();
			$arrDetailsToRender['DailyMessage'] = $arrMessage;
	
			$this->LoadPage('console', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
}

?>
