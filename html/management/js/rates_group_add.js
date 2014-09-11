// PickList script- By Sean Geraty (http://www.freewebs.com/sean_geraty/)
// Visit JavaScript Kit (http://www.javascriptkit.com) for this JavaScript and 100s more
// Please keep this notice intact

// Control flags for list selection and sort sequence
// Sequence is on option value (first 2 chars - can be stripped off in form processing)
// It is assumed that the select list is in sort sequence initially
var singleSelect = true;  // Allows an item to be selected once only
var sortSelect = true;  // Only effective if above flag set to true
var sortPick = true;  // Will order the picklist in sort sequence

// Initialise - invoked on load
function initIt() {
  var selectList = document.getElementById("AvailableRates");
  var selectOptions = selectList.options;
  var selectIndex = selectList.selectedIndex;
  var pickList = document.getElementById("SelectedRates");
  var pickOptions = pickList.options;
  if (!(selectIndex > -1)) {
    selectOptions[0].selected = true;  // Set first selected on load
    selectOptions[0].defaultSelected = true;  // In case of reset/reload
  }
}

// Adds a selected item into the picklist
function addIt() {
  var selectList = document.getElementById("AvailableRates");
  var selectIndex = selectList.selectedIndex;
  var selectOptions = selectList.options;
  var pickList = document.getElementById("SelectedRates");
  var pickOptions = pickList.options;
  var pickOLength = pickOptions.length;
  // An item must be selected
  while (selectIndex > -1) {
    pickOptions[pickOLength] = new Option(selectList[selectIndex].text);
    pickOptions[pickOLength].value = selectList[selectIndex].value;
    // If single selection, remove the item from the select list
    if (singleSelect) {
      selectOptions[selectIndex] = null;
    }
    if (sortPick) {
      var tempText;
      var tempValue;
      // Sort the pick list
      while (pickOLength > 0 && pickOptions[pickOLength].value < pickOptions[pickOLength-1].value) {
        tempText = pickOptions[pickOLength-1].text;
        tempValue = pickOptions[pickOLength-1].value;
        pickOptions[pickOLength-1].text = pickOptions[pickOLength].text;
        pickOptions[pickOLength-1].value = pickOptions[pickOLength].value;
        pickOptions[pickOLength].text = tempText;
        pickOptions[pickOLength].value = tempValue;
        pickOLength = pickOLength - 1;
      }
    }
    selectIndex = selectList.selectedIndex;
    pickOLength = pickOptions.length;
  }
}

// Deletes an item from the picklist
function delIt() {
  var selectList = document.getElementById("AvailableRates");
  var selectOptions = selectList.options;
  var selectOLength = selectOptions.length;
  var pickList = document.getElementById("SelectedRates");
  var pickIndex = pickList.selectedIndex;
  var pickOptions = pickList.options;
  while (pickIndex > -1) {
    // If single selection, replace the item in the select list
    if (singleSelect) {
      selectOptions[selectOLength] = new Option(pickList[pickIndex].text);
      selectOptions[selectOLength].value = pickList[pickIndex].value;
    }
    pickOptions[pickIndex] = null;
    if (singleSelect && sortSelect) {
      var tempText;
      var tempValue;
      // Re-sort the select list
      while (selectOLength > 0 && selectOptions[selectOLength].value < selectOptions[selectOLength-1].value) {
        tempText = selectOptions[selectOLength-1].text;
        tempValue = selectOptions[selectOLength-1].value;
        selectOptions[selectOLength-1].text = selectOptions[selectOLength].text;
        selectOptions[selectOLength-1].value = selectOptions[selectOLength].value;
        selectOptions[selectOLength].text = tempText;
        selectOptions[selectOLength].value = tempValue;
        selectOLength = selectOLength - 1;
      }
    }
    pickIndex = pickList.selectedIndex;
    selectOLength = selectOptions.length;
  }
}

// Selection - invoked on submit
function selIt() {
  var pickList = document.getElementById("SelectedRates");
  var pickOptions = pickList.options;
  var pickOLength = pickOptions.length;
  if (pickOLength < 1) {
    alert("You must select at least one Rate Group\nPlease select Rate Groups using the [>>] button");
    return false;
  }
  for (var i = 0; i < pickOLength; i++) {
    pickOptions[i].selected = true;
  }
  return true;
}




// v - created by Bash
function showSelectedRatesTable ()
{
	newForm = document.createElement ("FORM");
	newForm.method = "POST";
	newForm.action = "rates_group_summary.php";
	newForm.style.display = "none";
	newForm = document.body.appendChild (newForm);
	
	for (var i=0; i < document.getElementById("SelectedRates").options.length; ++i)
	{
		formElement = document.getElementById("SelectedRates").options [i];
		
		newFormElement = document.createElement ("INPUT");
		newFormElement.type = "hidden";
		newFormElement.name = "SelectedRates[]";
		newFormElement.value = formElement.value;
		newFormElement = newForm.appendChild (newFormElement);
	}
	
	var newWindow = window.open (
		"",
		"newWindow",
		"width=1000, height=500, scrollbars=yes"
	);
	
	newForm.target = "newWindow";
	newForm.submit ();
}

window.onload = initIt;
