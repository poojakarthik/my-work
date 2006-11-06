<?php

// template class
class aphplix_content
{
	function aphplix_content($pages = '', $content_dir = "content/")
	{
		// set the content location
		$this->content_dir = $content_dir;
		
		// get a list off available content
		$this->pages = $pages;
	}

	function _valid_page($page)
	{
		if (empty($page))
		{
			return 'index';
		}
		elseif (!$this->pages[$page])
		{
			// page not found
			return '404';
		}
		else
		{
			return $page;
		}
	}
	
	function menu($page)
	{
		unset($menu);
		// read in the page file
		include($this->content_dir.'menu.php');
		return $menu;
	}

	function load($page)
	{
		// check this is a valid page
		$page = $this->_valid_page($page);
		
		// clear the output buffer
		ob_clean();
		
		// clean meta data
		unset($meta);
		
		// read in the page file
		include($this->content_dir.$this->pages[$page]);
		
		// get the contents of the output buffer
		$content = ob_get_contents();
		
		// get meta data
		if (is_array($meta))
		{
			$content_array['meta'] = $meta;
			$content_array['content'] = $content;
			return $content_array;
		}
		
		// clean the output buffer
		ob_clean();
		
		return $content;
	}
}
?>
