<?php

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
<title>Request For App</title>
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
			
				<?php					
					if(@$_POST['appName']!="")
					{
						$appName=mysql_real_escape_string($_POST['appName']);
						$organisation=mysql_real_escape_string($_POST['organisation']);
						$correspondingEmail=mysql_real_escape_string($_POST['correspondingEmail']);
						$latcoord=mysql_real_escape_string($_POST['latcoord']);
						$longcoord=mysql_real_escape_string($_POST['longcoord']);
						$locality=mysql_real_escape_string($_POST['locality']);
						$colourScheme=mysql_real_escape_string($_POST['colourScheme']);
				
						$filesAreAccepted = false;
					
						$allowedExts = array("jpeg", "jpg", "JPEG", "JPG");
						
						$temp = explode(".", $_FILES["appLogoImage"]["name"]);
						$appLogoImageExtension = end($temp);
						
						$temp = explode(".", $_FILES["appBackgroundImage"]["name"]);
						$appBackgroundImageExtension = end($temp);
						
						$appID = preg_replace('/\s+/', '', $appName);
						
						$appID = strtolower($appID);
												
						$appID = 'app_'.rand(1,999999).'_'.substr($appID, 0, 3).'_'.rand(1,999999); 
						
						$storedLogo = 'logo'.$appID.'.jpg'; 
						$storedBackgroundMap = 'backgroundmap'.$appID.'.jpg'; 				
						
						$storedLogo='uploads/'.$storedLogo;
						$storedBackgroundMap='uploads/'.$storedBackgroundMap;
						
						if(
							(($_FILES["appLogoImage"]["type"] == "image/jpeg")
							|| ($_FILES["appLogoImage"]["type"] == "image/jpg")
							|| ($_FILES["appLogoImage"]["type"] == "image/pjpeg"))
							&&
							(($_FILES["appBackgroundImage"]["type"] == "image/jpeg")
							|| ($_FILES["appBackgroundImage"]["type"] == "image/jpg")
							|| ($_FILES["appBackgroundImage"]["type"] == "image/pjpeg"))
							&& 
							in_array($appLogoImageExtension, $allowedExts) && in_array($appBackgroundImageExtension, $allowedExts)
						)
						{
							$filesAreAccepted = true;
						}
						
						if($filesAreAccepted)
						{
							move_uploaded_file($_FILES['appLogoImage']['tmp_name'], $storedLogo);
							move_uploaded_file($_FILES['appBackgroundImage']['tmp_name'], $storedBackgroundMap);
							
							mysql_query("INSERT INTO apps (app_name, app_id, app_status, organisation, corresponding_email, latcoord, longcoord, locality, colour_scheme, logo_image, background_image) VALUES('$appName', '$appID', 'pending', '$organisation', '$correspondingEmail', '$latcoord', '$longcoord', '$locality', '$colourScheme', '$storedLogo', '$storedBackgroundMap');");
							
							//send email to client
							$recipient = $correspondingEmail;
							$subject = 'APP REQUEST RECEIVED';
							$message = "Hi\r\nThank you for requesting for an App on our Heritage App Creation Platform. 
							Your request is currently being treated and we will be in touch soon.
							
							\r\n\r\nRegards,
							\r\nOpen Virtual Worlds team";
							
							$message = wordwrap($message, 70, "\r\n");
							$headers = 'From: admin@openvirtualworlds.org' . "\r\n" .
										'Reply-To: admin@openvirtualworlds.org' . "\r\n" .
										'Bcc: admin@openvirtualworlds.org' . "\r\n" .
										'X-Mailer: PHP/' . phpversion();
										
							if(mail($recipient, $subject, $message, $headers))
							{
								echo "<p style='color:red; text-align:center; align:center;' id='form-submit-alert'>Thank you for requesting for an App. An email has been sent to ".$_POST['correspondingEmail']." with further instructions.</p>";							
							} 
							else 
							{
								echo "<p style='color:red; text-align:center; align:center;' id='form-submit-alert'>Thank you for requesting for an App. An email will be sent to ".$_POST['correspondingEmail']." as soon as the request is treated.</p>";							
							}
						}
						
						else
						{
							echo "<p style='text-align:center;color:red;'>Error: Invalid file(s). Only JPG images are accepted!</p>";
						}
					}
					
			
				?>
									
				</table>
				</form>
				
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				<hr>
				<h1>REQUEST FOR APP</h1>

				<form action=requestApp.php method=post enctype ="multipart/form-data">
					<table style='border-color: #A9D1FA;' border=1 cellpadding=0 cellspacing=0>
						
						<tr bgcolor=#A9D1FA align =left style='text-align:center'>
							<td bgcolor=#A9D1FA colspan=2 style="text-align:center;color:navy; font-weight: bold;">REQUIRED FIELDS</td>
						</tr>
						
						<tr bgcolor=#A9D1FA align =left style=text-align:left>
							<td class=tabhead align='left' style='text-align:left'><img src=images/blank.gif width=70 height=6><br><b>APP NAME</b></td>
							<td class=tabval> <input type=text required size=57 name=appName placeholder="Please provide a name">	</td>
						</tr>
						
						<tr bgcolor=#A9D1FA align =left style=text-align:left>
							<td class=tabhead align='left' style='text-align:left'><img src=images/blank.gif width=70 height=6><br><b>ORGANISATION</b></td>
							<td class=tabval> <input type=text required size=57 name=organisation placeholder="Please provide your organisation">	</td>
						</tr>
						
						<tr bgcolor=#A9D1FA align =left style=text-align:left>
							<td class=tabhead align='left' style='text-align:left'><img src=images/blank.gif width=70 height=6><br><b>EMAIL</b></td>
							<td class=tabval> <input type=email required size=57 name=correspondingEmail placeholder="Please provide your email address">	</td>
						</tr>
						
						<tr bgcolor=#A9D1FA align =left style=text-align:left>
							<td class=tabhead align='left' style='text-align:left'><img src=images/blank.gif width=70 height=6><br><b>STARTING LATITUDE</b></td>
							<td class=tabval> <input type=text size=57 required name=latcoord placeholder="Please provide initial Lat. Coordinate"> </td>
						</tr>
						
						<tr bgcolor=#A9D1FA align =left style=text-align:left>
							<td class=tabhead align='left' style='text-align:left'><img src=images/blank.gif width=70 height=6><br><b>STARTING LONGITUDE</b></td>
							<td class=tabval> <input type=text size=57 required name=longcoord placeholder="Please provide initial Long. Coordinate">	</td>
						</tr>
						
						<tr bgcolor=#A9D1FA align =left style=text-align:left>
							<td class=tabhead align='left' style='text-align:left'><img src=images/blank.gif width=70 height=6><br><b>LOCALITY</b></td>
							<td class=tabval> <input type=text size=57 required name=locality placeholder="Please provide the locality or region name">	</td>
						</tr>
						
						
						<tr bgcolor=#A9D1FA align =left style=text-align:left>
							<td class=tabhead align='left' style='text-align:left'><img src=images/blank.gif width=70 height=6><br><b>COLOUR SCHEME</b></td>
							<td class=tabval> 
								Brown<input type='radio' name='colourScheme' value='brown' required  checked>
								&nbsp;
								Blue<input type='radio' name='colourScheme' value='blue' required>
								&nbsp;
								Pink<input type='radio' name='colourScheme' value='pink' required>
								&nbsp;
								Red<input type='radio' name='colourScheme' value='red' required>
								&nbsp;
								Grey<input type='radio' name='colourScheme' value='grey' required>
								&nbsp;
							</td>
						</tr>
						
						<tr bgcolor=#A9D1FA align =left style=text-align:left>
							<td class=tabhead align='left' style='text-align:left'><img src=images/blank.gif width=70 height=6><br><b>APP LOGO (JPEG)</b></td>
							<td class=tabval> <input type=file size=19 required accept="image/jpeg" name="appLogoImage" id="appLogoImage" value="Upload file">	</td>
						</tr>
						
						<tr bgcolor=#A9D1FA align =left style=text-align:left>
							<td class=tabhead align='left' style='text-align:left'><img src=images/blank.gif width=70 height=6><br><b>APP BACKGROUND (JPEG)</b></td>
							<td class=tabval> <input type=file size=19 required accept="image/jpeg" name="appBackgroundImage" id="appBackgroundImage" value="Upload file">	</td>
						</tr>
						
					
						<tr valign=bottom>
						<td bgcolor=#A9D1FA colspan=2 style='text-align:center;'>

								<input onclick="return confirm('Request for new App?')" type=submit value='Request'>
								<button onclick="if(confirm('Discard changes?')){ window.location.href='requestApp.php'; return false;} else {return false;}">Cancel</button>						
						</td>
						</tr>
						
					</table>
				</form>
				
        	<p>&nbsp;</p>
           	<p>&nbsp;</p>
        	
		</div>
        <div id="content_bottom"></div>
            
            <div align="center" style="align:center;" id="footer"><h3 style="align:center;color:#ffffff">OVW Group</h3></div>
      </div>
   </div>
</body>
</html>
