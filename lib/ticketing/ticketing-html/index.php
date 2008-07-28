<html>
	<head>
		<title>Ticketing Mockup</title>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<link rel="stylesheet" type="text/css" href="css/ticketing.css" />
		<link rel="stylesheet" type="text/css" href="css/menu.css" />
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
				</div>
			</div>
			<div id="nav" name="nav">
				<ul>
					<li><a href="#">Home</a></li>
					<li><a href="#">Customers</a>
						<ul>
							<li><a href="#">Add Customer</a>
								<ul>
									<li><a href="#">Find a Customer</a></li>
									<li><a href="#">Find a Customer</a></li>
									<li><a href="#">Find a Customer</a></li>
									<li><a href="#">Find a Customer</a></li>
									<li><a href="#">Find a Customer</a></li>
									<li><a href="#">Find a Customer</a></li>
									<li><a href="#">Find a Customer</a>
										<ul>
											<li><a href="#">Find a Customer</a></li>
										</ul>
									</li>
								</ul>
							</li>
							<li><a href="#">Find a Customer</a></li>
							<li><a href="#">Recent Customers</a></li>
							<li><a href="#">Recent Customers</a></li>
							<li><a href="#">Recent Customers</a></li>
							<li><a href="#">Recent Customers</a></li>
							<li><a href="#">Recent Customers</a></li>
							<li><a href="#">Recent Customers</a></li>
						</ul>
					</li>
					<li><a href="#">Plans</a></li>
					<li><a href="#">Admin Console</a></li>
					<li><a href="#">Reports</a></li>
					<li><a href="#">Tickets</a></li>
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
