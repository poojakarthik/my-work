<?php
	require_once('../framework/functions.php');
	LoadFramework();
	//------------------------------------------------------------------------//
	// Login
	//------------------------------------------------------------------------//
	/**
	 * Login()
	 *
	 * Attempts a Session Authentication
	 *
	 * Attempts to Authenticate the Session (Identified by UserName and PassWord)
	 * against an Employee
	 *
	 * @param	String		$strUserName		The UserName of the Attempted Authentication
	 * @param	String		$strPassWord		The PassWord of the Attempted Authentication
	 *
	 * @return	Boolean
	 *
	 * @method
	 */
		
	function Login ($strUserName, $strPassWord)
	{
		// Get the Id of the Employee (Identified by UserName and PassWord combination)
		$selSelectStatement = new StatementSelect (
			"Employee", 
			"Id", 
			"UserName = <UserName> AND PassWord = SHA1(<PassWord>) AND Archived = 0", 
			null, 
			"1"
		);
		
		$selSelectStatement->Execute(Array("UserName"=>$strUserName, "PassWord"=>$strPassWord));
		
		// If the employee could not be found, return false
		if ($selSelectStatement->Count () <> 1)
		{
			return false;
		}
		
		// If we reach this part of the Method, the session is authenticated.
		// Therefore, we have to store the Authentication
		$arrFetch = $selSelectStatement->Fetch ();
		$Id = $arrFetch ['Id'];
		
		
		
		// Generate a new session ID
		$SessionId = sha1(uniqid(rand(), true));
		
		// Updating information
		$Update = Array("SessionId" => $SessionId, "SessionExpire" => new MySQLFunction ("ADDTIME(NOW(),'00:20:00')"));
		
		// update the table
		$updUpdateStatement = new StatementUpdate("Employee", "UserName = <UserName> AND PassWord = SHA1(<PassWord>) AND Archived = 0", $Update);
		
		// If we successfully update the database table
		if ($updUpdateStatement->Execute($Update, Array("UserName" => $strUserName, "PassWord" => $strPassWord)) == 1)
		{
			setCookie ("Id", $Id, time () + (60 * 20), "/");
			setCookie ("SessionId", $SessionId, time () + (60 * 20), "/");
			
			return true;
		}
		
		return false;
	}
	
	
	if (isset($_POST['UserName']) && isset($_POST['PassWord']))
	{
		if (Login($_POST['UserName'], $_POST['PassWord']))
		{
			// pass to console (which doesnt yet exist)
			Header('Location: console.php');
			exit;
		}
		
		
	}

?>

<html xmlns="http://www.w3.org/1999/xhtml"><head><title>TelcoBlue.com.au Internal Systems Login</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

</head>
<script type="text/javascript" src="javascript/sha1.js"></script>


<style type="text/css" media="screen">
				
				body {margin: 0; font-family: "Nimbus Sans L", "Tahoma"; }
			    #topContainer { padding-top: 6em; background: #FFF; }
			    #logoImage {width:300px; height:91px; float: left; }
			    #loginContainer{padding: 0px 0px 18em 210px;}
			    #loginForm {float:left;height: 150px;width:500px; border-left: 1px solid #CCCCCC; padding:0px 8px 8px 30px;text-align:left;}
			    h3 { margin: 0; }
			    
			    div.Seperator { height: 5px; }
			    .Right { float: right; }
			    
			    .LoginBox { width: 200px; border: solid 1px #666666; padding: 3px; font-family: "Nimbus Sans L", "Tahoma"; height: 25px; }
			    
			    h1 { font-size: 14pt; color: #006599; font-family: "Nimbus Sans L", "Tahoma"; }
			    
			    label { font-size: 10pt; }
			    
			    .MsgError {
					border:              solid 1px #CC0000;
					background-color:    #f2dbdb;
					
					background-image:    url('img/template/MsgError.png');
					background-repeat:   no-repeat;
					background-position: left;
					
					padding-top:         5px;
					padding-left:        40px;
					padding-right:       5px;
					padding-bottom:      5px;
					
					height:              25px;
					
					font-size: 10pt;
					
					line-height:         25px;
					
					margin-bottom:       10px;
			    }
			     
			</style>
			
			<body>
			<div id="topContainer">
				<div id="loginContainer">
				<div id="logoImage">
				<img src="img/login/TCB_Logo.png" width="300" height="91"/>
				</div>
				<div id="loginForm">
				<h1>TelcoBlue Internal System</h1>
				<div class="Seperator"/>
				<form method="POST" action="login.php">
				<table>
				<tr>
				<td>
				<label for="UserName">Username:</label>
				</td>
				<td>
				<input type="text" name="UserName" class="LoginBox" maxlength="21"/>
				</td>
				</tr>
				<tr>
				<td>
				<label for="PassWord">Password:</label>
				</td>
				<td>
				<input type="password" name="PassWord" class="LoginBox"/>
				</td>
				</tr>
				<tr>
				<td/>
				<td>
				<input type="submit" value="Continue &#xBB;" class="Right"/>
				</td>
				</tr>
				</table>
				</form>
				</div>
				<div class="Clear"/></div>
				</div>

			
			</body>
		</html>
