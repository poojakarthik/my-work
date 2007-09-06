<?php
//----------------------------------------------------------------------------//
// <ClassName>
//----------------------------------------------------------------------------//
/**
 * <ClassName>
 *
 * A specific HTML Template object
 *
 * An <ClassDescription> HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	<ClassName>
 * @extends	HtmlTemplate
 */
class <ClassName> extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		
		//$this->LoadJavascript("dhtml");
		//$this->LoadJavascript("highlight");
		//$this->LoadJavascript("retractable");
	}
	
	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{
		switch ($this->_intContext)
		{
			case HTML_CONTEXT_LEDGER_DETAIL:
				$this->_RenderLedgerDetail();
				break;
			case HTML_CONTEXT_FULL_DETAIL:
				$this->_RenderFullDetail();
				break;
			default:
				$this->_RenderFullDetail();
				break;
		}
		
		// If there is only one context then stick the Rendering code in this method, instead of having a switch and multiple private methods
	}

	//------------------------------------------------------------------------//
	// _RenderFullDetail
	//------------------------------------------------------------------------//
	/**
	 * _RenderFullDetail()
	 *
	 * Render this HTML Template with full detail
	 *
	 * Render this HTML Template with full detail
	 *
	 * @method
	 */
	private function _RenderFullDetail()
	{
		echo "<h2 class='<ClassTitle>'><ClassDescription></h2>\n";
		?>
		
		<!--EXAMPLE:
		<div class='NarrowForm'>
			<table border='0' cellpadding='3' cellspacing='0'>
				<?php
				/*foreach (DBO()->Account AS $strProperty=>$objValue)
				{	
					echo "<tr>\n";
					$objValue->RenderOutput();
					echo "</tr>\n";
				}*/
				?>
			</table>
		</div>
		-->
		<div class='Seperator'></div>
		<?php

	}
}

?>
