<?php
// HTMLObjectGroup AccountDetails

class AccountDetails
{
	// Class AccountDetails
	function Render()
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
				<?php
					// Dbo()->Object->Property->Render(CONSTANT_ELEMENT_TYPE|$strElementType??, [$bolRequired], [$strContext]);
					// Dbo()->Object->Property->Render(ELEMENT_INPUT, TRUE);
					// Dbo()->Object->Property->Render('Input', TRUE);					
					dboRender('Input',TRUE);
				?>	
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
