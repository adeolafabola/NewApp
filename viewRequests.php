<?php
if (!isset($_COOKIE['loggedin']))
{
	header("Location:login.php");
	die();
}
else
{
	$mysite_username = $_COOKIE["mysite_username"]; 
}

include("config.php");

if(!mysql_connect($server, $db_user, $db_pass))
{
	echo "<h2>Could not connect to database!<br></h2>";
	die();
}

mysql_select_db($database);	
?>

<!--<!DOCTYPE html>-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="form.js" type="text/javascript"></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<title>View App Requests</title>
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
					<li class="menuitem"><a href="logout.php">Logout</a></li>  
                </ul>
			</div>
                
                
              <div id="leftmenu_bottom"></div>
        </div>
        
        
        
        
		<div id="content">
        
        
        <div id="content_top"></div>
        <div id="content_main">
			
				<?php			
					
					if(@$_REQUEST['action']=="approve")
					{
						$approveQuery=mysql_query("SELECT * FROM apps WHERE sn=".round($_REQUEST['id']));
					
						while( $rowToApprove=mysql_fetch_array($approveQuery) )
						{							
							$appName = $rowToApprove['app_name'];
							$appID = $rowToApprove['app_id'];
							$organisation = $rowToApprove['organisation'];
							$correspondingEmail = $rowToApprove['corresponding_email'];
							$latcoord = $rowToApprove['latcoord'];
							$longcoord = $rowToApprove['longcoord'];
							$locality = $rowToApprove['locality'];
							$colourScheme = $rowToApprove['colour_scheme'];
							$storedLogo = $rowToApprove['logo_image'];
							$storedBackgroundMap = $rowToApprove['background_image'];
							
							//copy template files over
							function recurse_copy($src,$dst) 
							{ 
								$dir = opendir($src); 
								@mkdir($dst); 
								while(false !== ( $file = readdir($dir)) ) { 
									if (( $file != '.' ) && ( $file != '..' )) { 
										if ( is_dir($src . '/' . $file) ) { 
											recurse_copy($src . '/' . $file,$dst . '/' . $file); 
										} 
										else { 
											copy($src . '/' . $file,$dst . '/' . $file); 
										} 
									} 
								} 
								closedir($dir); 
							} 
							
							recurse_copy("../apptemplate", "../".$appID);
							
							//move app logo and background image files, 
							//instead of just copying the files, move them so as to also clean up the uploads folder
							rename($storedLogo, "../".$appID."/web/css/images/logo.jpg");
							rename($storedBackgroundMap, "../".$appID."/web/css/images/backgroundmap.jpg");
							
							//edit files to replace variables in [[square brackets]]
							function replaceStringInFile($path, $string, $replacement)
							{
								set_time_limit(0);
								if (is_file($path) === true)
								{
									$file = fopen($path, 'r');
									$temp = tempnam('./', 'tmp');
									if (is_resource($file) === true)
									{
										while (feof($file) === false)
										{
											file_put_contents($temp, str_replace($string, $replacement, fgets($file)), FILE_APPEND);
										}
										fclose($file);
									}
									unlink($path);
								}
								return rename($temp, $path);
							}
			
							replaceStringInFile("../".$appID."/admin/config.php", "[[App ID]]", $appID);
							replaceStringInFile("../".$appID."/config.php", "[[App ID]]", $appID);
							
							replaceStringInFile("../".$appID."/getQuests.php", "[[Initial Lat]]", $latcoord);
							replaceStringInFile("../".$appID."/getQuests.php", "[[Initial Long]]", $longcoord);
							
							replaceStringInFile("../".$appID."/web/trailmap.html", "[[Initial Lat]]", $latcoord);
							replaceStringInFile("../".$appID."/web/trailmap.html", "[[Initial Long]]", $longcoord);
							
							replaceStringInFile("../".$appID."/web/mapOffline.html", "[[Initial Lat]]", $latcoord);
							replaceStringInFile("../".$appID."/web/mapOffline.html", "[[Initial Long]]", $longcoord);
							
							replaceStringInFile("../".$appID."/web/about.html", "[[Organisation Name]]", $organisation);
							replaceStringInFile("../".$appID."/web/about.html", "[[Locality]]", $locality);
							
							$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("../".$appID."/web/"));
							foreach($objects as $name => $object)
							{
								if(strpos($name,'.html')!== false)
								{
									replaceStringInFile($name, "[[App Name]]", $appName);
									
									/*
									replaceStringInFile($name, "[[App ID]]", $appID);
									replaceStringInFile($name, "[[Organisation Name]]", $organisation);
									replaceStringInFile($name, "[[Locality]]", $locality);
									replaceStringInFile($name, "[[Initial Lat]]", $latcoord);
									replaceStringInFile($name, "[[Initial Long]]", $longcoord);
									*/
								}
							}
							
							//create new sql file from sql template, edit sql file, create database, then delete sql file
							$appSqlFile = $appID.".sql";
							copy("app.sql", $appSqlFile);
							replaceStringInFile($appSqlFile, "[[App ID]]", $appID);
							$templine = '';	// Temporary variable, used to store current query
							$lines = file($appSqlFile);	// Read in entire file
							foreach ($lines as $line)	// Loop through each line
							{
								if (substr($line, 0, 2) == '--' || $line == '')	// Skip it if it's a comment
									continue;
								$templine .= $line;	// Add this line to the current segment

								if (substr(trim($line), -1, 1) == ';')	// If it has a semicolon at the end, it's the end of the query
								{
									mysql_query($templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysql_error() . '<br /><br />');	// Perform the query
									$templine = '';	// Reset temp variable to empty
								}
							}
							unlink($appSqlFile);
							
							//change status of app to "dev_in_progress" in heritage_apps table
							mysql_select_db($database);	
							mysql_query("update apps SET app_status='dev_in_progress' where app_id='$appID';");
							
							//send email to client
							$recipient = $correspondingEmail;
							$subject = 'WELCOME ABOARD!!';
							$message = "Hi,
							\r\nThank you for requesting for an App on our Heritage App Creation Platform. 
							We are pleased to inform you that your request has been granted, and we have set up a 
							content management interface for you to create your App.
							\r\nYou can access the interface by clicking on the link below and choosing a 
							username and password.
							
							\r\n\r\nhttp://turret-new.cs.st-andrews.ac.uk/".$appID."/admin/register.php
							
							\r\n\r\nYou can view the changes (and how the App would function on a mobile device) 
							in a web browser using this link:
							
							\r\n\r\nhttp://turret-new.cs.st-andrews.ac.uk/".$appID."/Web/splash.html
							
							\r\nNote: It is important to always start from the above link each time you make 
							changes to your App using the content management interface.
							 
							\r\nOnce you have logged into the management interface, you can access a brief 
							introduction and quick guide to get you started with the interface. We are happy 
							to address any inquiries you might have and provide assistance where necessary. We
							 hope you have a pleasurable experience creating your App.
							 
							\r\n\r\nRegards,
							\r\nOpen Virtual Worlds team";
							
							$message = wordwrap($message, 70, "\r\n");
							$headers = 'From: admin@openvirtualworlds.org' . "\r\n" .
										'Reply-To: admin@openvirtualworlds.org' . "\r\n" .
										'Bcc: admin@openvirtualworlds.org' . "\r\n" .
										'X-Mailer: PHP/' . phpversion();							
							
							if(mail($recipient, $subject, $message, $headers))
							{
								echo "<p style='color:red; text-align:center; align:center;'>".$organisation."'s App request has been approved. An email has been sent to ".$recipient." with further instructions.</p>";
							} 
							else 
							{
								echo "<p style='color:red; text-align:center; align:center;'>".$organisation."'s App request has been approved. An email has not been sent to ".$recipient.".</p>";
							}
						}
					}
					
					if(@$_REQUEST['action']=="decline")
					{
						$declineQuery=mysql_query("SELECT * FROM apps WHERE sn=".round($_REQUEST['id']));
					
						while( $rowToDecline=mysql_fetch_array($declineQuery) )
						{							
							$appName = $rowToDecline['app_name'];
							$appID = $rowToDecline['app_id'];
							$organisation = $rowToDecline['organisation'];
							$correspondingEmail = $rowToDecline['corresponding_email'];
							$latcoord = $rowToDecline['latcoord'];
							$longcoord = $rowToDecline['longcoord'];
							$locality = $rowToDecline['locality'];
							$colourScheme = $rowToDecline['colour_scheme'];
							$storedLogo = $rowToDecline['logo_image'];
							$storedBackgroundMap = $rowToDecline['background_image'];
							
							//change status of app to "declined" in heritage_apps table
							mysql_select_db($database);	
							mysql_query("update apps SET app_status='declined' where app_id='$appID';");
							
							//send email to client
							$recipient = $correspondingEmail;
							$subject = 'APP REQUEST DECLINED!!';
							$message = "Hi,
							\r\nThank you for requesting for an App on our Heritage App Creation Platform. 
							We regret to inform you that we cannot approve your request for an App at this time.
							Please accept our apologies for any inconvenience caused.
							 
							\r\n\r\nRegards,
							\r\nOpen Virtual Worlds team";
							
							$message = wordwrap($message, 70, "\r\n");
							$headers = 'From: admin@openvirtualworlds.org' . "\r\n" .
										'Reply-To: admin@openvirtualworlds.org' . "\r\n" .
										'Bcc: admin@openvirtualworlds.org' . "\r\n" .
										'X-Mailer: PHP/' . phpversion();							
							
							if(mail($recipient, $subject, $message, $headers))
							{
								echo "<p style='color:red; text-align:center; align:center;'>".$organisation."'s App request has been declined. An email has been sent to ".$recipient.".</p>";
							} 
							else 
							{
								echo "<p style='color:red; text-align:center; align:center;'>".$organisation."'s App request has been declined. An email has not been sent to ".$recipient.".</p>";
							}
						}
					}
				?>
									
				</table>
				</form>
				
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				<hr>
				<h1>APP REQUESTS</h1>
					<table style='border-color: #A9D1FA;' border=1 cellpadding=0 cellspacing=0>
						<tr bgcolor=#A9D1FA align ="center" style="text-align:center;">
							<td class=tabhead><img src=images/blank.gif width=30 height=6><br><b>S/N</b></td>
							<td class=tabhead><img src=images/blank.gif width=100 height=6><br><b>APP NAME</b></td>
							<td class=tabhead><img src=images/blank.gif width=100 height=6><br><b>APP ID</b></td>
							<td class=tabhead><img src=images/blank.gif width=100 height=6><br><b>ORGANISATION</b></td>
							<td class=tabhead><img src=images/blank.gif width=60 height=6><br><b>STATUS</b></td>
							<td class=tabhead><img src=images/blank.gif width=50 height=6></td>
							<td class=tabhead><img src=images/blank.gif width=50 height=6></td>
						</tr>
				
					<?php
					$result=mysql_query("SELECT * FROM apps where app_status='pending' ORDER BY sn;");
					//$result=mysql_query("SELECT * FROM apps where app_status='dev_in_progress' ORDER BY sn;");
						
						$i=0;
						$j=1;
						while( $row=mysql_fetch_array($result) )
						{
							if($i>0)
							{
								echo "<tr valign=bottom>";
								echo "<td bgcolor=#ffffff background='images/strichel.gif' colspan=7><img src=images/blank.gif width=1 height=1></td>";
								echo "</tr>";
							}
							echo "<tr valign=center>";
							echo "<td class=tabval>".htmlspecialchars($j++)."&nbsp;</td>";
							echo "<td class=tabval><b>".htmlspecialchars($row['app_name'])."</b></td>";
							echo "<td class=tabval>".htmlspecialchars($row['app_id'])."&nbsp;</td>";
							echo "<td class=tabval>".htmlspecialchars($row['organisation'])."&nbsp;</td>";
							echo "<td class=tabval>".htmlspecialchars($row['app_status'])."&nbsp;</td>";
							echo "<td class=tabval>
								<a onclick=\"return confirm('Edit site details?');\" href=viewRequests.php?action=approve&id=".$row['sn']."><span class=red>[APPROVE]</span></a>
							</td>";
							echo "<td class=tabval>
								<a onclick=\"return confirm('Delete site?');\" href=viewRequests.php?action=decline&id=".$row['sn']."><span class=red>[DECLINE]</span></a>
							</td>";
							echo "</tr>";
							$i++;

						}
					?>
										
					</table>
				
				
				<p>&nbsp;</p>
				<p>&nbsp;</p>
        	
		</div>
        <div id="content_bottom"></div>
            
            <div align="center" style="align:center;" id="footer"><h3 style="align:center;color:#ffffff">OVW Group</h3></div>
      </div>
   </div>
</body>
</html>
