<?php

class DO_Sales_SaleVoiceRecording extends DO_Sales_Base_SaleVoiceRecording
{
	const MAX_FILE_SIZE = 5000000;
	protected static $_bolIncludeRecording = TRUE;
	
	// This overrides the base method ommitting the "recording" property if self::$_bolIncludeRecording is set to FALSE, because this is rarely needed, and can be quite large
	public static function getDataSourcePropertyMappings()
	{
		$arrMapping = DO_Sales_Base_SaleVoiceRecording::getDataSourcePropertyMappings();
		if (self::$_bolIncludeRecording)
		{
			return $arrMapping;
		}
		else
		{
			// Remove the 'recording' field
			unset($arrMapping['recording']);
			return $arrMapping;
		}
	}
	
	// This is used to declare whether or not to include the recording property when dealing with objects representing records of the sale_voice_recording table
	// By default the sale_voice_recording.recording property is included
	public static function setIncludeRecording($bolIncludeRecording)
	{
		self::$_bolIncludeRecording = $bolIncludeRecording;
	}
	
	// Returns the number of recordings attached to a sale
	public static function countForSale($doSale)
	{
		return self::countFor(array("saleId"=>$doSale->id));
	}
	
	// Returns an assoc array of the file types that can be used for voice recordings
	// the key will be the file extension, and the value will be the mime type associated with that extension
	public static function getAcceptedFileTypes()
	{
		return array(	"wav"	=> "audio/x-wav",
						"mp3"	=> "audio/mpeg",
						"ogg"	=> "application/ogg");
	}
	
}

?>