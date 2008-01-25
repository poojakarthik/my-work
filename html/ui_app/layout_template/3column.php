<?php	
		
		// render page layout
			// set page title using $this->_strPageName
			// page layout renders objects in the columns
			/*
				// this code needs to live somewhere else
				// this is what the LAYOUT TEMPLATE will do
				render header bar
				render context menu
				render breadcrumbs
				echo start of column structure
				render objects in column 1
				echo part of column structure
				render objects in column 2
				echo end of column structure
				
				$this->RenderHeader();
				$this->RenderContextMenu();
				echo "<table><tr><td>";
				//foreach (object in column 1)
				{
					// render the object
				}
				echo "</td><td>";
				//foreach (object in column 2)
				{
					// render the object
				}
				echo "</td></tr></table>";
				
			*/
		
		// this echo will be replaced by a page template
		// and a header template
		
		$this->RenderHeader();
		$this->RenderVixenHeader();
		$this->RenderBreadCrumbMenu();
		$this->RenderContextMenu();

		//var_dump($this->Page->arrObjects);
		//$this->RenderColumn(COLUMN_ONE);
		
		?>
	<div id='PageBody'>
		<h1> <?php echo $this->_strPageName; ?></h1>
		<table width='100%' border='0'>
			<tr>
				<td width='49%' valign='top'>
					<?php $this->RenderColumn(COLUMN_ONE); ?>
				</td>
				<td width='2%'></td>
				<td width='49%' valign='top'>
					<?php $this->RenderColumn(COLUMN_TWO); ?>
				</td>
			</tr>
		</table>
		<table width='100%' border='0'>
			<tr>
				<td width='100%' valign='top'>
					<?php $this->RenderColumn(COLUMN_THREE); ?>
				</td>
			</tr>
		</table>
	</div>
		<?php
		
		$this->RenderFooter();
		
?>
