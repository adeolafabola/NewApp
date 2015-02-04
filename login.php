<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<title>Login</title>
</head>

<body>
<div id="container">
		<div id="header">
        	<h1>Quest<span class="off">It</span></h1>
            <h2>An intelligent App creation service...</h2>
        </div>   
        
        <div id="menu">
        	<ul>
				
            </ul>
        </div>
        
        <div id="leftmenu">

        <div id="leftmenu_top"></div>

				<div id="leftmenu_main">
					<ul>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
						<li>&nbsp;</li>
					</ul>              
				</div>
                
              <div id="leftmenu_bottom"></div>
        </div>
        
		<div id="content">
        <div id="content_top"></div>
        <div id="content_main">
			
        	<div align="center"> <h1>Login</h1> </div>
						
				<table border="0" align="center" cellpadding=5>
					<tr>
						<td colspan="2" align="center">
							<h3>Login with your username and password.</h3>
						</td>
					</tr>
				</table>
				
				<?php

				require_once "formvalidator.php";
				$show_form=true;

				if(isset($_POST['submit']))
				{
					$validator = new FormValidator();
					$validator->addValidation("username", "req", "Please enter a username");
					$validator->addValidation("password", "req", "Please enter a password");
					
					if($validator->ValidateForm())
					{						
						include("config.php"); 

						// connect to the mysql server 
						$link = mysql_connect($server, $db_user, $db_pass) 
						or die ("Could not connect to mysql because ".mysql_error()); 

						// select the database 
						mysql_select_db($database) 
						or die ("Could not select database because ".mysql_error()); 

						$match = "select id from $authTable where username = '".$_POST['username']."' 
						and password = '".$_POST['password']."';"; 

						$username = $_POST['username'];

						$qry = mysql_query($match) 
						or die ("Could not match data because ".mysql_error()); 
						$num_rows = mysql_num_rows($qry); 

						if ($num_rows <= 0) 
						{ 
							echo "<b><p style='color:red;'>Invalid username and/or password. Please try again!</p></b>"; 
							//exit; 
						} 
						
						else 
						{
							$show_form=false;
							setcookie("loggedin", "TRUE", time()+(3600 * 24));
							setcookie("mysite_username", "$username");
							header("Location:viewRequests.php");
							die();
						}
					}
					
					else
					{
						$error_hash = $validator->GetErrors();
						foreach($error_hash as $inpname => $inp_err)
						{
							echo "<b><p style='color:red;'>$inp_err</p></b>\n";
						}
					}
				}

				if(true==$show_form)
				{
				?>
				
				<form action="login.php" method="post">
					<table border="0" align="center" cellpadding=5>
						<tr>
							<td>
								Username
							</td>
							
							<td>
								<input type="text" required name="username" id="username" size="20">
							</td>
						</tr>
						
						<tr>
							<td>
								Password
							</td>
							
							<td>
								<input type="password" required name="password" id="password" size="20">
							</td>
						</tr>
						
						<tr>
							<td colspan="2" style="text-align:center;">
								<input type="submit" name="submit" id="submit" value="Login" />
							</td>
						</tr>
					</table>
				</form>
			
        	<p>&nbsp;</p>
           	<p>&nbsp;</p>
			
		</div>
		
		<?PHP
		}
		?>
		
        <div id="content_bottom"></div>
            
            <div align="center" style="align:center;" id="footer"><h3 style="align:center;color:#ffffff">OVW Group</h3></div>
      </div>
   </div>
</body>
</html>
