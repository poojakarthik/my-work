<?php
class Application_Handler_File extends Application_Handler
{
	protected function _render($arrDetailsToRender, $strDownloadFileName=null)
	{
		$strContent			= $arrDetailsToRender['raw_data'];
		$strMimeContentType	= $arrDetailsToRender['mime_content_type'];
		
		// Set headers
		header("Content-type: {$strMimeContentType}");
		if ($strDownloadFileName && trim($strDownloadFileName))
		{
			header("Content-Disposition: attachment; filename=\"{$strDownloadFileName}\"");
		}
		
		// Render the output
		echo $strContent;
		exit;
	}
	
	// Shows a history of Proposed Dialling Lists and their associated data
	public function Image($arrSubPath)
	{
		// Get raw data for an image and return it to the browser
		try
		{
			// Get method name from Sub Path
			if (!isset($arrSubPath[0]))
			{
				throw new Exception("No valid subpath field 0 (Function): ".print_r($arrSubPath, true));
			}
			$strMethod	= "_image{$arrSubPath[0]}";
			
			$arrDetailsToRender	= $this->{$strMethod}($arrSubPath);
			if ($arrDetailsToRender)
			{
				$this->_render($arrDetailsToRender, false);
			}
			else
			{
				throw new Exception("Image Not Found");
			}
		}
		catch (Exception $eException)
		{
			// Force a 404
			header("HTTP/1.0 404 Not Found");
			exit;
			/*
			$arrDetailsToRender['Message']		= "An error occured";
			$arrDetailsToRender['ErrorMessage']	= $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
			*/
		}
	}
	
	protected function _imageFileTypeIcon($arrSubPath)
	{
		if (!isset($arrSubPath[1]))
		{
			throw new Exception("No valid subpath field 1 (File Type Id): ".print_r($arrSubPath, true));
		}
		if (!isset($arrSubPath[2]))
		{
			throw new Exception("No valid subpath field 2 (Icon Dimensions): ".print_r($arrSubPath, true));
		}
		
		$objFileType	= new File_Type(array('id'=>(int)$arrSubPath[1]), true);
		
		$strField		= "icon_{$arrSubPath[2]}";
		$strIconData	= $objFileType->{$strField};
		
		return array('raw_data'=>$strIconData, 'mime_content_type'=>'image/png');
	}
}
?>