function checkDateInput ()
{
	// Check to see if any field has been selected, and if so,
	// that ALL have been selected
	
	var arrType = Array('CreatedOn', 'ClosedOn');
	var arrWhen = Array('Start', 'End');
	var arrPeriod = Array('Day','Month','Year');
	var inputBox;
	var bolInvalid = false;
	var weight = 0;
	
	for (var strType in arrType)
	{
		weight = 0;
		for (var strWhen in arrWhen)
		{
			for (var strPeriod in arrPeriod)
			{
				// for each input box in the line, add its selected index to the weight
				// and refesh the display
				inputBox = document.getElementById(arrType[strType] + arrWhen[strWhen] + arrPeriod[strPeriod]);
				weight += inputBox.selectedIndex;
				inputBox.className = 'select';
			}			
		}
		
		if (weight != 0)
		{
			for (var strWhen in arrWhen)
			{
				for (var strPeriod in arrPeriod)
				{
					// if there is even one input selected, show whether each input is valid or not
					inputBox = document.getElementById(arrType[strType] + arrWhen[strWhen] + arrPeriod[strPeriod]);
					if (inputBox.selectedIndex == 0)
					{
						inputBox.className = 'select-invalid';
						bolInvalid = true;
					}
					else
					{
						inputBox.className = 'select';
					}
				}
			}
		}
	}

	return !bolInvalid;
}
