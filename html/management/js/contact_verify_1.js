
// Usage:
// <script language="javascript" src="js/contact_verify_1.js"></script>
// <form onsubmit="return contactList1 (this)">

function contactList1 (form)
{
	var count = 0;

	if (form.elements ["ui-Account"].value != "")			count += 1;
	if (form.elements ["ui-ABN"].value != "")				count += 1;
	if (form.elements ["ui-ACN"].value != "")				count += 1;
	if (form.elements ["ui-Invoice"].value != "")			count += 1;
	if (form.elements ["ui-FNN"].value != "")				count += 1;
	if (form.elements ["ui-BusinessName"].value != "")		count += 1;
	if (form.elements ["ui-Contact-First"].value != "")		count += 0.5;
	if (form.elements ["ui-Contact-Last"].value != "")		count += 0.5;

	if (count != 1)
	{
		alert (
				"You did not fill in this form correctly.  Please try again."
			  );

		return false;
	}

	return true;
}

