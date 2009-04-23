<?php

class HtmlTemplate_Console extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		$arrMessage = $this->mxdDataToRender['DailyMessage'];
		if ($arrMessage !== NULL)
		{
			// There is a daily message to display
			$strTimestamp	= date("M jS, Y H:i:s", strtotime($arrMessage['Timestamp']));
			$strMessage		= $arrMessage['Message'];
			
			$strDailyMessageSection = '
<div id="DailyMessageContainer">
	<table class="reflex">
		<thead>
			<th>Message of the day</th>
		</thead>
		<tfoot>
			<th>Last updated: ' . $strTimestamp . '</th>
		</tfoot>
		<tbody>
			<tr class="alt">
				<td>&nbsp;</td>
			</tr>
			<tr class="alt">
				<td>' . $strMessage . '</td>
			</tr>
			<tr class="alt">
				<td>&nbsp;</td>
			</tr>
		</tbody>
	</table>
</div>
';
		}
		else
		{
			$strDailyMessageSection = "";
		}
		
		echo "
$strDailyMessageSection
";
		
		// Display upcoming Calendar Events
		echo "
<br />
<div>
	<table class='reflex'>
		<thead>
			<th>Upcoming Events</th>
		</thead>"/*."
		<tfoot>
			<th>&nbsp;</th>
		</tfoot>"*/."
		<tbody>
			<tr class='alt'>
				<td>
					<div class='console-calendar'>";
		
		ksort($this->mxdDataToRender['UpcomingEvents']);
		foreach ($this->mxdDataToRender['UpcomingEvents'] as $strDate=>$arrCalendarEvents)
		{
			// Table Header & Footer
			echo "
						<div class='day'>
							<span class='date'>".date('l jS F', strtotime($strDate))."</span>
							<table class='reflex'>
								<thead>
									<tr>
										<th>
											Item for action
										</th>
										<th>
											Department Responsible
										</th>
									</tr>
								</thead>"/*."
								<tfoot>
									<tr>
										<th colspan='2'>
											&nbsp;
										</th>
									</tr>
								</tfoot>"*/."
								<tbody>";
			
			// Content
			if (count($arrCalendarEvents))
			{
				foreach ($arrCalendarEvents as $objCalendarEvent)
				{
					echo "
									<tr class='alt'>
										<td>
											{$objCalendarEvent->description}
										</td>
										<td>
											{$objCalendarEvent->department_responsible}
										</td>
									</tr>";
				}
			}
			else
			{
				echo "
									<tr class='alt'>
										<td colspan='2'>
											No actions required
										</td>
									</tr>";
			}
			
			// Close off Table
			echo	"
								</tbody>
							</table>
						</div>";
		}
		
		// Close off the calendar
		echo "
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>";
	}
}

?>
