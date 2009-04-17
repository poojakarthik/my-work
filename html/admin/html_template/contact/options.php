<?php
//----------------------------------------------------------------------------//
// HtmlTemplateContactOptions
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateContactOptions
 *
 * A specific HTML Template object
 *
 * An Contact Options HTML Template object
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateContactOptions
 * @extends	HtmlTemplate
 */
class HtmlTemplateContactOptions extends HtmlTemplate
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
		echo "<h2 class='Options'>Contact Options</h2>\n";
		echo "<div class='NarrowForm'>\n";
		
		$strEditContactLink = Href()->EditContact(DBO()->Contact->Id->Value);
		echo "<li><a href='$strEditContactLink'>Edit Contact Details</a></li>\n";
		$strAddContactNoteLink = Href()->AddContactNote(DBO()->Contact->Id->Value);
		echo "<li><a href='$strAddContactNoteLink'>Add Contact Note</a></li>\n";
		$strViewContactNotesLink = Href()->ViewContactNotes(DBO()->Contact->Id->Value);
		echo "<li><a href='$strViewContactNotesLink'>View Contact Notes</a></li>\n";

		echo "</div>\n";
		echo "<div class='Seperator'></div>\n";
	}
}

?>
