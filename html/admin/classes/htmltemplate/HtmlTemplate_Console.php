<?php

class HtmlTemplate_Console extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	// ----------------------------------------------------------------------------------- //
	// Console View
	// ----------------------------------------------------------------------------------- //
	public function Render() {
		?>
		<article class='flex-page'></article>
		<script>
			// Provide component once all dom content has been loaded.
			document.observe('DOMContentLoaded', 
				module.provide.bind(module, ["flex/component/page/dashboard"], function () {
					// Instantiate Component
					new require('flex/component/page/dashboard')({
						// Component.CONFIG
					});
				})
			);
		</script>
		<?
	}

	// Old
	/*
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
			<tr>
				<th>Message of the day</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Last updated: ' . $strTimestamp . '</th>
			</tr>
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
		if (Flex_Module::isActive(FLEX_MODULE_CALENDAR))
		{
			echo "
<br />
<div class='reflex-content'>
	<div class='header'>
		Upcoming Events
	</div>
	<div class='body'>
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
						<tr class='alt'>
							<th>
								Item for action
							</th>
							<th class='department-responsible'>
								Department Responsible
							</th>
						</tr>
					</thead>"."
					<tfoot>
						<tr>
							<th colspan='2'>
								
							</th>
						</tr>
					</tfoot>"."
					<tbody>";
				
				// Content
				if (count($arrCalendarEvents))
				{
					$bolAlt	= false;
					foreach ($arrCalendarEvents as $objCalendarEvent)
					{
						$bolAlt	= !$bolAlt;
						echo "
						<tr".($bolAlt ? " class='alt'" : '').">
							<td>
								".$objCalendarEvent->parseDescription()."
							</td>
							<td class='department-responsible'>
								{$objCalendarEvent->department_responsible}
							</td>
						</tr>";
					}
				}
				else
				{
					echo "
						<tr class='alt'>
							<td>
								No actions required
							</td>
							<td class='department-responsible'>
								&nbsp;
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
	</div>
</div>";
		}
	}
	*/
}

?>
