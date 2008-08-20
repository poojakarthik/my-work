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
$this->RenderFlexHeader(TRUE, TRUE, TRUE);

		//var_dump($this->Page->arrObjects);
		//$this->RenderColumn(COLUMN_ONE);
		
?>
<div id='PageBody'>
	<div id='PageTitle' name='PageTitle'>
		<h1> <?php echo $this->_strPageName; ?></h1>
	</div>
	<div id='Container_Columns_1_And_2' style='width:100%;height:auto'>
		<div id='Column1' style='width:49%;height:auto;float:left'>
			<?php $this->RenderColumn(COLUMN_ONE); ?>
		</div>
		<div id='Column2' style='width:49%;height:auto;float:right;'>
			<?php $this->RenderColumn(COLUMN_TWO); ?>
		</div>
	</div>
	<div id='Column3' style='width:100%;clear:both'>
		<?php $this->RenderColumn(COLUMN_THREE); ?>
	</div>
</div>
<?php
		
		$this->RenderFooter();
		
?>
