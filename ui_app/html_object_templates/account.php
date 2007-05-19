<?php
// HTMLObjectGroup AccountDetails

class AccountDetails
{
	// Class AccountDetails
	function objRender()
	{
		//User HTML element Tempaltes

		
		/*
		$this->Dbo->Account->Id->dboRender('input');
		$this->Dbo->Account->BusinessName->dboRender('label');
		*/
		
		/*
		?> </table> <.... insert html here> 	<?php
		*/
		
		//var_dump($this);
		?>
		<table>
			<tr>
				<?php dboRender('Input',TRUE); ?>	
			</tr>
			<tr>
				<?php dboRender('Label',TRUE); ?>	
			</tr>
			<tr>
				<?php dboRender('Other',TRUE); ?>	
			</tr>
		</table>
		<?php
		
		//HTML is OK here, to define structures which enclose these objects
	}
}

?>
