<?
$basepath = dirname($_SERVER['SCRIPT_NAME']) . "/";
$args = explode('/', $_SERVER['PATH_INFO']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta name="generator" content="Flex" />
		<title>Ticketing Mockup</title>
		<link rel="stylesheet" type="text/css" media="screen" href="<?=$basepath?>css/style.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?=$basepath?>css/reflex.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?=$basepath?>css/menu.css" />
	</head>
	<body>
		<div id="header" name="header">
			<div id="logo" name="logo">
				<div id="blurb" name="blurb">Flex Customer Management System</div>
			</div>
			<div id="person_search" name="person_search">
				<div id="person" name="person">
					Logged in as: XXXX
					| <a href="<?=$basepath?>#">Preferences</a>
					| <a href="<?=$basepath?>#">Logout</a>
				</div>
				<div id="search_bar" name="search_bar">
					Search: 
					<input type="text" id="search_string" name="search_string" />
					<select name="category" id="category">
						<option>Tickets</option>
					</select>
					<input type="submit" id="Search" name="Search" value="Search" />
				</div>
			</div>

			<div id="nav" name="nav">
				<ul>
					<li><a href="<?=$basepath?>console.php">Home</a></li>
					<li class="dropdown"><a href="#">Customers</a>
						<ul>
							<li><a href="<?=$basepath?>account_add.php">Add Customer</a>
							<li><a href="<?=$basepath?>contact_verify.php">Find a Customer</a></li>
							<li><a href="#" onclick="return ModalDisplay ('#modalContent-recentCustomers')">Recent Customers</a></li>
						</ul>
					</li>
					<li><a href="<?=$basepath?>rates_plan_list.php">Plans</a></li>
					<li class="dropdown"><a href="<?=$basepath?>console_admin.php">Admin Console</a>
						<ul>
							<li><a href="<?=$basepath?>account_list.php">Advanced Account Search</a></li>
							<li><a href="<?=$basepath?>contact_list.php">Advanced Contact Search</a></li>
							<li><a href="<?=$basepath?>service_list.php">Advanced Service Search</a></li>
							<li><a href="<?=$basepath?>charges_approve.php">Approve Unbilled Adjustments</a></li>
							<li><a href="<?=$basepath?>charges_charge_list.php">Single Adjustment Types</a></li>
							<li><a href="<?=$basepath?>charges_recurringcharge_list.php">Single Adjustment Types</a></li>
							<li><a href="<?=$basepath?>payment_download.php">Payment Download</a></li>
							<li><a href="<?=$basepath?>flex.php/Misc/MoveDelinquentCDRs/">Move Delinquent CDRs</a></li>
							<li><a href="<?=$basepath?>employee_list.php">Manage Employees</a></li>
							<li><a href="<?=$basepath?>flex.php/InvoiceRunEvents/Manage/">Invoice Run Events</a></li>
							<li><a href="<?=$basepath?>flex.php/Config/SystemSettingsMenu/">System Settings Menu</a></li>
						</ul>
					</li>
					<li><a href="<?=$basepath?>datareport_list.php">Reports</a></li>
					<li class="dropdown"><a href="#">Tickets</a>
						<ul>
							<li><a href="<?=$basepath?>ticket/new">Create Ticket</a></li>
							<li><a href="<?=$basepath?>ticket/view/all">View All Tickets</a></li>
							<li><a href="<?=$basepath?>ticket/view/mine">View My Tickets</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
		<div id="content" name="content">
