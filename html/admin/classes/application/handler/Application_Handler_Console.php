<?php

class Application_Handler_Console extends Application_Handler
{
	// View all the Customer Statuses in a tabulated format
	public function View($subPath)
	{
		$bolIsGOD	= AuthenticatedUser()->UserHasPerm(PERMISSION_GOD);
		
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
			
			// Get the Events for the next couple of days
			try
			{
				// Get the Events for Today and Tomorrow
				$strToday			= GetCurrentISODateTime();
				$intToday			= strtotime($strToday);
				$intDayOfTheWeek	= (int)date('w', $intToday);
				$intTomomorrow		= strtotime("+1 day", $intToday);
				
				$arrUpcomingEvents	= array();
				
				$arrUpcomingEvents[date('Y-m-d', $intToday)]		= Calendar_Event::getForDate($intToday);
				$arrUpcomingEvents[date('Y-m-d', $intTomomorrow)]	= Calendar_Event::getForDate($intTomomorrow);
				
				// If today is Friday, list events for Saturday AND Sunday
				if ($intDayOfTheWeek == 5)
				{
					$intSunday										= strtotime("+2 day", $intToday);
					$arrUpcomingEvents[date('Y-m-d', $intSunday)]	= Calendar_Event::getForDate($intSunday);
				}
			}
			catch (Exception $eException)
			{
				throw new Exception("There was an error retrieving the Calendar Events for the upcoming days.  Please notify YBS of this error.\n". ($bolIsGOD ? $eException->getMessage() : ''));
			}
			
			$arrDetailsToRender = array();
			$arrDetailsToRender['DailyMessage']		= $arrMessage;
			$arrDetailsToRender['UpcomingEvents']	= $arrUpcomingEvents;
	
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
