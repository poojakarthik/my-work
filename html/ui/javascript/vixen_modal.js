Vixen.Popup._Close = Vixen.Popup.Close;
Vixen.Popup.Close = function(mixId) 
{
	if (mixId != undefined && typeof mixId == "string" && mixId == "CloseFlexModalWindow")
	{
		window.parent.FlexModalContent.remove();
	}
	else
	{
		return Vixen.Popup._Close(mixId);
	}
}
