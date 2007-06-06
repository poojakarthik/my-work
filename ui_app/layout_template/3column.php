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
		$this->RenderBreadCrumbMenu();
		$this->RenderContextMenu();

		//var_dump($this->Page->arrObjects);
		$this->RenderColumn(COLUMN_ONE);
		
		?>
		
		  </td>
		</tr>
        </tbody>
	  </table>
	</div>
		<?php
		// this echo will be replaced by a page-end template
		echo "</body>\n</html>";
		
?>
