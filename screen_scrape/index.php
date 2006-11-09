<html>
	<head>
		<title>Puller</title>
	</head>
	<body>
		<form method="post" action="index.php">
			<table border="0" cellpadding="10" cellspacing="0">
				<tr>
					<td>URL:</td>
					<td>
						<input type="text" name="Url" size="100" />
					</td>
				</tr>
				<tr>
					<td>Method:</td>
					<td>
						<select name="Method">
							<option>POST</option>
							<option>GET</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>Submit:</td>
					<td>
						<input type="submit" value="Pull URL" />
					</td>
				</tr>
			</table>
			
			<table border="1" cellpadding="3" cellspacing="0">
				<tr>
					<th>Param:</td>
					<th>Name:</th>
					<th>Value:</th>
				</tr>
				</tr>
				<?php for ($i=1; $i <= 5; ++$i) { ?>
					<tr>
						<td>Parameter <?=$i?></td>
						<td><input type="text" name="name[<?=$i?>]" /></td>
						<td><input type="text" name="value[<?=$i?>]" /></td>
					</tr>
				<?php } ?>
			</table>
		</form>
		
		<?php
			
			if (isset ($_POST ['Url']))
			{
				echo "<h1>Response:</h1>";
				
				require_once ("functions/connection.php");
				
				$Params = Array ();
				
				for ($i=1; $i <= 5; ++$i)
				{
					if (isset ($_POST ['name'][$i]) && !empty ($_POST ['name'][$i]) && isset ($_POST ['value'][$i]) && !empty ($_POST ['value'][$i]))
					{
						$Params [$_POST ['name'][$i]] = $_POST ['value'][$i];
					}
				}
				
				$Response = Connection_Transmit (
					$_POST ['Method'],
					$_POST ['Url'],
					$Params
				);
				
				echo "<pre>";
				echo htmlentities ($Response);
				echo "</pre>";
			}
			
		?>
	</body>
</html>
