<?php

// template class
class aphplix_template
{
	function aphplix_template($menu ='', $template_dir = "templates/")
	{
		// set the template location
		$this->template_dir = $template_dir;
		
		// get a list off available templates
		$this->templates['default'] = 'default.tmpl.php';
		
		// no template is loaded
		$this->template = FALSE;
		
		// menu data
		$this->menu_data = $menu;
	}
	
	function _valid_template($template)
	{
		if (!$this->templates[$template])
		{
			// page not found
			return 'default';
		}
		else
		{
			return $template;
		}
	}
	
	function set_page($page)
	{
		$this->page_name = $page;
	}
	
	function set_menu($menu)
	{
		$this->menu_data = $menu;
	}
	
	function set_content($content)
	{
		$this->content = $content;
	}
	
	function set_meta($meta)
	{
		if (is_array($meta))
		{
			$this->meta = $meta;
		}
	}
	
	function load($template)
	{
		if ($this->template)
		{
			return TRUE;
		}
		
		// check this is a valid template
		$template = $this->_valid_template($template);
		
		// load the template
		require($this->template_dir.$this->templates[$template]);
		if (!class_exists('aphplix_template_def'))
		{
			return FALSE;
		}
		$this->template = new aphplix_template_def();
		
		return TRUE;
	}
	
	function build()
	{
		if ($this->template)
		{
			// replace contents
			$this->replace();
			
			// setup the template
			$this->template->set($this->page_name, $this->content, $this->meta, $this->menu_data);
			
			// build the menu
			$this->template->menu();
			
			// build the page
			$this->output_buffer[] = $this->template->head($this->meta);
			$this->output_buffer[] = $this->template->page($page_name, $page_content, $menu);
			$this->output_buffer[] = $this->template->foot($this->meta);
			return TRUE;
		}
		return FALSE;
	}
	
	function replace()
	{
		if (is_array($this->template->replacements))
		{
			foreach ($this->template->replacements as $key=>$value)
			{
				if (!empty($key))
				{
					$search[] = $key;
					$replace[] = $value;
				}
			}
			if (is_array($search))
			{
				$this->content = str_replace($search, $replace, $this->content);
			}
		}
	}
	
	function render()
	{
		return $this->flush();
	}
	
	function clean()
	{
		unset($this->output_buffer);
	}
	
	function clean_last()
	{
		return array_pop($this->output_buffer);
	}
	
	function flush()
	{
		if (is_array($this->output_buffer))
		{
			foreach ($this->output_buffer as $key=>$value)
			{
				echo $value;
			}
		}
		unset($this->output_buffer);
	}
}
?>
