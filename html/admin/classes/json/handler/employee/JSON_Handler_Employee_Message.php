<?php

class JSON_Handler_Employee_Message extends JSON_Handler
{

	// Get the daily message
	public function getLastestMessageForConstant($sMessageTypeConstant=null) {
		try {
			$oQuery = Query::run("
							SELECT		id,
										created_on,
										effective_on,
										message,
										employee_message_type_id

							FROM		employee_message AS em1

							WHERE		NOW( ) > em1.effective_on
										AND em1.employee_message_type_id = (
											SELECT id FROM employee_message_type WHERE const_name = '{$sMessageTypeConstant}'
										)

							ORDER BY	em1.created_on DESC,
										em1.effective_on DESC
							LIMIT 1;");
			
			$aRecord = $oQuery->fetch_assoc();
			return (isset($aRecord)) ? $aRecord : null;
		}
		catch (Exception $oException) {
			// Suppress the normal form of error reporting, by displaying the error as the message of the day
			$aData = array(
						"message"	=> "The Daily Message functionality is currently broken.  Please notify your system administrators.\n" . $oException->getMessage()
					);
			return $aData;
		}
	}

	// used to save a message
	// set $intId to null to save a new message
	public function save($intId=NULL, $strMessage, $strEffectiveOn=NULL, $strMessageTypeConstant)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		try
		{
			
			// INPROGRESS
			// Get message type id for constant
			// Validate $intMessageTypeId
			$intMessageTypeId = constant($strMessageTypeConstant);
			if (!isset($intMessageTypeId)) {
				throw new Exception("Message type not defined");
			}
			// INPROGRESS

			// Check that the message is not empty
			$strMessage = trim($strMessage);
			if (strlen($strMessage) == 0)
			{
				throw new Exception("Message is not defined");
			}
			
			// Use current timestamp if effectiveon is not specified
			if ($strEffectiveOn === NULL)
			{
				$strEffectiveOn = GetCurrentISODateTime();
			}
			
			// Save the message
			TransactionStart();
			if ($intId === NULL)
			{
				// This is a new message
				Employee_Message::declareMessage($strMessage, $strEffectiveOn, $intMessageTypeId);
			}
			else
			{
				// This is an existing message
				//TODO! This scenario isn't currently handled
			}
			
			// If no exceptions were thrown, then everything worked
			TransactionCommit();
			
			return array(	"Success"	=> TRUE
						);
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}


	//------------------------------------------------------------------------//
	// buildPopup
	//------------------------------------------------------------------------//
	/**
	 * buildPopup()
	 *
	 * Handles ajax request from client, to build the EmployeeMessage popup
	 * 
	 * Handles ajax request from client, to build the EmployeeMessage popup
	 *
	 * @param	int			$intId				message id, optional, defaults to NULL, signifying a new message
	 * @return	array		["Success"]				TRUE if search was executed successfully, else FALSE
	 * 						["PopupContent"]		content for the popup
	 * 						["Message"]				object defining the message (assoc array), or NULL
	 * 						["ErrorMessage"]		Declares what went wrong (only defined when Success == FALSE)
	 * @method
	 */
	public function buildPopup($intId=NULL)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		try
		{
			$objMessage = ($intId !== NULL)? Employee_Message::getForId($intId) : NULL;
			
			$strPopupContent = $this->_buildPopupContent();
			
			return array(	"Success"		=> TRUE,
							"Message"		=> ($objMessage !== NULL)? $objMessage->toArray() : NULL,
							"PopupContent"	=> $strPopupContent
						);
		}
		catch (Exception $e)
		{
			return array(	"Success"		=> FALSE,
							"ErrorMessage"	=> $e->getMessage()
						);
		}
	}
	
	
	private function _buildPopupContent()
	{
		$strHtml = "
<div id='PopupPageBody'>
	<form id='EmployeeMessagePopup_Form' name='EmployeeMessagePopup_Form'>
		<div class='GroupedContent'>
			<table class='form-data'>
				<tr>
					<td class='title' style='width:20%'>Message</td>
					<td>
						<textarea id='EmployeeMessagePopup_Message' name='EmployeeMessagePopup_Message' wrap='on' style='overflow:auto;width:100%;height:20em;font-family:Courier New, monospace;font-size:1em;border: solid 1px #D1D1D1'></textarea>
					</td>
				</tr>
			</table>
		</div>
	</form>
	<div style='padding-top:3px;height:auto:width:100%'>
		<div style='float:right'>
			<input type='button' id='EmployeeMessagePopup_SaveButton' name='EmployeeMessagePopup_SaveButton' value='Save' onclick='FlexEmployeeMessage.saveNewMessage()' style='margin-left:3px'></input>
			<input type='button' value='Close' onclick='Vixen.Popup.Close(this)' style='margin-left:3px'></input>
		</div>
		<div style='clear:both;float:none'></div>
	</div>
</div>
";
		return $strHtml;
		
	}
}

?>
