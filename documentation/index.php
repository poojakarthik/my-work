<?
// start output buffering
ob_start();

// get the page name
$page = $_REQUEST['page'];

// read the config file
unset($menu);
unset($pages);
require('config.php');

// load the template manager
require('template.php');
$template = new aphplix_template($menu);

// load the content manager
require('content.php');
$content = new aphplix_content($pages);

// load content
$page_content = $content->load($page);

// load the template
$template->load('default');

// set page
$template->set_page($page);

// set menu
$template->set_menu($menu);

// set content
if (is_array($page_content))
{
	$template->set_content($page_content['content']);
	$template->set_meta($page_content['meta']);
}
else
{
	$template->set_content($page_content);
	// default meta data
	$meta['title'] = "Vixen";
	$template->set_meta($meta);
}

// build page
$template->build();

// end output buffering
ob_end_clean();

// render the page
$template->render();

// finished
exit();
?>
