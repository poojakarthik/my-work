<?php

class HtmlTemplate_Error extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
	}

	public function Render()
	{
		if (!is_array($this->mxdDataToRender))
		{
			// No details were sent
			$strMessage			= "An error has occurred";
			$strErrorMessage	= NULL;
		}
		else
		{
			// Details exist
			if (array_key_exists("Message", $this->mxdDataToRender))
			{
				$strMessage = htmlspecialchars($this->mxdDataToRender['Message']);
			}
			else
			{
				// No Message was defined
				$strMessage = "An error has occurred";
			}
			
			if (array_key_exists("ErrorMessage", $this->mxdDataToRender))
			{
				$strErrorMessage = htmlspecialchars($this->mxdDataToRender['ErrorMessage']);
			}
			else
			{
				// No error message was defined
				$strErrorMessage = NULL;
			}
		}
		
		echo "
<div class='message error'>$strMessage</div>
<div class='message error' style='white-space:pre'>$strErrorMessage</div>
";
	}
}
		
?>
