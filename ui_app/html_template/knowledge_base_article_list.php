<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// HtmlTemplateKnowledgeBaseArticleList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateKnowledgeBaseArticleList
 *
 * HTML Template object for the Knowledge Base Article List
 *
 * HTML Template object for the Knowledge Base Article List
 *
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateKnowledgeBaseArticleList
 * @extends	HtmlTemplate
 */
class HtmlTemplateKnowledgeBaseArticleList extends HtmlTemplate
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
		
		// Load all java script specific to the page here
		$this->LoadJavascript("highlight");
		$this->LoadJavascript("retractable");
		
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
		echo "<div class='WideColumn'>\n";
		
		if ($this->_intContext == HTML_CONTEXT_RELATED_ARTICLES)
		{
			echo "<h2 class='Article'>Related Articles</h2>\n";
		}
		
		// display all articles in a table
		if (DBL()->KnowledgeBase->RecordCount())
		{
			Table()->Articles->SetHeader("Id", "Title", "Last Updated", "&nbsp;");
			Table()->Articles->SetAlignment("Left", "Left", "Left", "Center");
			Table()->Articles->SetWidth("10%", "75%", "10%", "5%");
			foreach (DBL()->KnowledgeBase as $dboArticle)
			{
				// Set up the link to view the article
				$strViewArticle = Href()->ViewKnowledgeBaseArticle($dboArticle->Id->Value);
				$strViewArticleLabel = "<span class='DefaultOutputSpan Default'><a href='$strViewArticle' style='color:blue; text-decoration: none;'><img src='img/template/article.png' title='View Article'></img></a></span>";
				
				// Set the date field
				if ($dboArticle->LastUpdated->Value)
				{
					// Use the LastUpdated date
					$intTimeStamp = strtotime($dboArticle->LastUpdated->Value);
				}
				else
				{
					// Use the CreatedOn date
					$intTimeStamp = strtotime($dboArticle->CreatedOn->Value);
				}
				$strDate = date("d/m/Y", $intTimeStamp);
				$strDate = "<span class='DefaultOutputSpan Default'>$strDate</span>";
				
				Table()->Articles->AddRow($dboArticle->ArticleId->AsValue(), $dboArticle->Title->AsValue(), $strDate, $strViewArticleLabel);
				
				// Set up the Abstract
				if (!trim($dboArticle->Abstract->Value))
				{
					// The abstract is an empty string
					$strAbstract = $dboArticle->Abstract->AsArbitrary("This article does not have an abstract");
				}
				else
				{
					// There is a valid abstract
					$strAbstract = $dboArticle->Abstract->AsValue();
				}
				
				// Add the abstract as the drop down detail of the row
				Table()->Articles->SetDetail($strAbstract);
			}
			
			Table()->Articles->Render();
		}
		echo "</div>\n";
	}
}

?>
