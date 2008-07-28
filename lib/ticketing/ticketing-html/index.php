<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta name="generator" content="Flex" />
		<title>Ticketing Mockup</title>
		<link rel="stylesheet" type="text/css" media="screen" href="css/style.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="css/ticketing.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="css/menu.css" />
	</head>
	<body>
		<div id="header" name="header">
			<div id="logo" name="logo">
				<div id="blurb" name="blurb">Flex Customer Management System</div>
			</div>
			<div id="person_search" name="person_search">
				<div id="person" name="person">
					Logged in as: XXXX
					| <a href="#">Preferences</a>
					| <a href="#">Logout</a>
				</div>
				<div id="search_bar" name="search_bar">
					Search: 
					<input type="text" id="search_string" name="search_string" />
					<select name="category" id="category">
						<option>Tickets</option>
						<option>Customers</option>
						<option>Services</option>
					</select>
					<input type="submit" id="Search" name="Search" value="Search" />
				</div>
			</div>

			<div id="nav" name="nav">
				<ul>
					<li><a href="console.php">Home</a></li>
					<li><a href="#">Customers</a>
						<ul>
							<li><a href="account_add.php">Add Customer</a>
							<li><a href="contact_verify.php">Find a Customer</a></li>
							<li><a href="#" onclick="return ModalDisplay ('#modalContent-recentCustomers')">Recent Customers</a></li>
						</ul>
					</li>
					<li><a href="rates_plan_list.php">Plans</a></li>
					<li><a href="console_admin.php">Admin Console</a>
						<ul>
							<li><a href="account_list.php">Advanced Account Search</a></li>
							<li><a href="contact_list.php">Advanced Contact Search</a></li>
							<li><a href="service_list.php">Advanced Service Search</a></li>
							<li><a href="charges_approve.php">Approve Unbilled Adjustments</a></li>
							<li><a href="charges_charge_list.php">Single Adjustment Types</a></li>
							<li><a href="charges_recurringcharge_list.php">Single Adjustment Types</a></li>
							<li><a href="payment_download.php">Payment Download</a></li>
							<li><a href="flex.php/Misc/MoveDelinquentCDRs/">Move Delinquent CDRs</a></li>
							<li><a href="employee_list.php">Manage Employees</a></li>
							<li><a href="flex.php/InvoiceRunEvents/Manage/">Invoice Run Events</a></li>
							<li><a href="flex.php/Config/SystemSettingsMenu/">System Settings Menu</a></li>
						</ul>
					</li>
					<li><a href="datareport_list.php">Reports</a></li>
					<li><a href="#">Tickets</a>
						<ul>
							<li><a href="#">Create Ticket</a></li>
						</ul>
					</li>
				</ul>
			</div>

		</div>
		<div id="content" name="content">
		<table id="ticketing" name="ticketing">
			<caption>
				Owner: 
				<select id="x" name="x">
					<option id="a" name="a">all</option>
					<option id="a" name="a">Lee Jell</option>
					<option id="a" name="a">Cane McKinnon</option>
				</select>
				Category: 
				<select id="y" name="y">
					<option id="a" name="a">all</option>
					<option id="a" name="a">Adds Moves Changes</option>
					<option id="a" name="a">Faults</option>
				</select>
				Status: 
				<select id="z" name="z">
					<option id="a" name="a">all</option>
					<option id="a" name="a">Active</option>
					<option id="a" name="a">Assigned</option>
				</select>
			</caption>
			<thead>
				<tr>
					<th>ID</th>
					<th>Subject</th>
					<th>Last Actioned</th>
					<th>Received</th>
					<th>Owner</th>
					<th>Category</th>
					<th>Status</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan=8 align=right> Total Tickets: XXXX</th>
				</tr>
			</tfoot>
			<tbody>

<?

for ($i = 1; $i <= 24; $i++) {
	if ($i % 2) {
		$tr_alt =  "alt";
	} else {
		$tr_alt = "";
	}
?>

				<tr class="<?=$tr_alt?>">
					<td><?=$i?></td>
					<td>a</td>
					<td>a</td>
					<td>a</td>
					<td>a</td>
					<td>a</td>
					<td>a</td>
					<td>a</td>
				</tr>
<? 

}

?>
			</tbody>
		</table>
		<div>
	</body>
</html>
