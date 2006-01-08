#!/usr/local/bin/php
<?php 
/*
	services_captiveportal_users.php
	part of m0n0wall (http://m0n0.ch/wall)
	
	Copyright (C) 2003-2005 Manuel Kasper <mk@neon1.net>.
	All rights reserved.
	Copyright (C) 2005 Pascal Suter <d-monodev@psuter.ch>.
	All rights reserved. 
	(files was created by Pascal based on the source code of services_captiveportal.php from Manuel)
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
	
	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.
	
	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.
	
	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/
$pgtitle = array("Services", "Captive portal");
require("guiconfig.inc");
if(isset($_POST['save'])){
	//value-checking
	if(trim($_POST['password1'])!="********" && 
	   trim($_POST['password1'])!="" && 
	   trim($_POST['password1'])!=trim($_POST['password2'])){
	   	//passwords are to be changed but don't match
	   	$input_errors[]="passwords don't match";
	}
	if((trim($_POST['password1'])=="" || trim($_POST['password1'])=="********") && 
	   (trim($_POST['password2'])=="" || trim($_POST['password2'])=="********")){
	   	//assume password should be left as is if a password is set already.
		if(!empty($config['users'][$_POST['old_username']]['password'])){
			$_POST['password1']="********";
			$_POST['password2']="********";
		} else {
			$input_errors[]="password must not be empty";
		}
	} else {
		if(trim($_POST['password1'])!=trim($_POST['password2'])){
		   	//passwords are to be changed or set but don't match
		   	$input_errors[]="passwords don't match";
		} else {
			//check password for invalid characters
			if(!preg_match('/^[a-zA-Z0-9_\-\.@\~\(\)\&\*\+�?!\$��\%;:]*$/',$_POST['username'])){
				$input_errors[] = "password contains illegal characters, only  letters from A-Z and a-z, _, -, .,@,~,(,),&,*,+,�,?,!,$,�,�,%,;,: and numbers are allowed";
				//test pw: AZaz_-.@~()&*+�?!$��%;:
			}
		}
	}
	if($_POST['username']==""){
		$input_errors[] = "username must not be empty!";
	}
	//check for a valid expirationdate if one is set at all (valid means, strtotime() puts out a time stamp
	//so any strtotime compatible time format may be used. to keep it simple for the enduser, we only claim 
	//to accept MM/DD/YYYY as inputs. advanced users may use inputs like "+1 day", which will be converted to 
	//MM/DD/YYYY based on "now" since otherwhise such an entry would lead to a never expiring expirationdate
	if(trim($_POST['expirationdate'])!=""){
		if(strtotime($_POST['expirationdate'])>0){
			if(strtotime("-1 day")>strtotime(date("m/d/Y",strtotime($_POST['expirationdate'])))){
				$input_errors[] = "selected expiration date lies in the past";			
			} else {
				//convert from any strtotime compatible date to MM/DD/YYYY
				$expdate = strtotime($_POST['expirationdate']);
				$_POST['expirationdate'] = date("m/d/Y",$expdate);
			}
		} else {
			$input_errors[] = "invalid expiration date format, use MM/DD/YYYY instead";
		}
	}
	//check username: only allow letters from A-Z and a-z, _, -, . and numbers from 0-9 (note: username can
	//not contain characters which are not allowed in an xml-token. i.e. if you'd use @ in a username, config.xml
	//could not be parsed anymore!
	if(!preg_match('/^[a-zA-Z0-9_\-\.]*$/',$_POST['username'])){
		$input_errors[] = "username contains illegal characters, only  letters from A-Z and a-z, _, -, . and numbers are allowed";
	}
	
	if(!empty($input_errors)){
		//there are illegal inputs --> print out error message and show formular again (and fill in all recently entered values
		//except passwords
		$_GET['act']="new";
		$_POST['old_username']=($_POST['old_username'] ? $_POST['old_username'] : $_POST['username']);
		$_GET['username']=$_POST['old_username'];
		foreach(Array("username","fullname","expirationdate") as $field){
			$config['users'][$_POST['old_username']][$field]=$_POST[$field];
		}
	} else {
		//all values are okay --> saving changes
		$_POST['username']=trim($_POST['username']);
		if($_POST['old_username']!="" && $_POST['old_username']!=$_POST['username']){
			//change the username (which is used as array-index)
			$config['users'][$_POST['username']]=$config['users'][$_POST['old_username']];
			unset($config['users'][$_POST['old_username']]);
		}
		foreach(Array('fullname','expirationdate') as $field){
			$config['users'][$_POST['username']][$field]=trim($_POST[$field]);
		}
		if(trim($_POST['password1'])!="********" && trim($_POST['password1'])!=""){
			$config['users'][$_POST['username']]['password']=md5(trim($_POST['password1']));
		}
		write_config();
		$savemsg=$_POST['username']." successfully saved<br>";
	}
} else if ($_GET['act']=="delete" && isset($_GET['username'])){
	unset($config['users'][$_GET['username']]);
	write_config();
	$savemsg=$_GET['username']." successfully deleted<br>";
}
//erase expired accounts
$changed=false;
if(is_array($config['users'])){
	foreach($config['users'] as $username => $user){
		if(trim($user['expirationdate'])!="" && strtotime("-1 day")>strtotime($user['expirationdate']) && empty($input_errors)){
			unset($config['users'][$username]);
			$changed=true;
			$savemsg.="$username has expired --> $username was deleted<br>";
		}
	}
	if($changed){
		write_config();
	}
}

?>
<?php include("fbegin.inc"); ?>
<script language="javascript" type="text/javascript" src="datetimepicker.js">
//Date Time Picker script- by TengYong Ng of http://www.rainforestnet.com
//Script featured on JavaScript Kit (http://www.javascriptkit.com)
//For this script, visit http://www.javascriptkit.com
</script>
<?php if ($input_errors) print_input_errors($input_errors); ?>
<?php if ($savemsg) print_info_box($savemsg); ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr><td>
  <ul id="tabnav">
	<li class="tabinact1"><a href="services_captiveportal.php">Captive portal</a></li>
	<li class="tabinact"><a href="services_captiveportal_mac.php">Pass-through MAC</a></li>
	<li class="tabinact"><a href="services_captiveportal_ip.php">Allowed IP addresses</a></li>
	<li class="tabact">Users</li>
  </ul>
  </td></tr>
  <tr>
  <td class="tabcont">
<?php
if($_GET['act']=="new" || $_GET['act']=="edit"){
	if($_GET['act']=="edit" && isset($_GET['username'])){
		$user=$config['users'][$_GET['username']];
	}
?>
	<form action="services_captiveportal_users.php" method="post" name="iform" id="iform">
              <table width="100%" border="0" cellpadding="6" cellspacing="0">
                <tr> 
                  <td width="22%" valign="top" class="vncellreq">Username</td>
                  <td width="78%" class="vtable"> 
                    <input name="username" type="text" class="formfld" id="username" size="20" value="<?=$_GET['username'];?>"> 
                    </td>
                </tr>
                <tr> 
                  <td width="22%" valign="top" class="vncellreq">Password</td>
                  <td width="78%" class="vtable"> 
                    <input name="password1" type="password" class="formfld" id="password1" size="20" value="<?php echo ($_GET['act']=='edit' ? "********" : "" ); ?>"> <br>
					<input name="password2" type="password" class="formfld" id="password2" size="20" value="<?php echo ($_GET['act']=='edit' ? "********" : "" ); ?>">
&nbsp;(confirmation)					</td>
                </tr>
                <tr> 
                  <td width="22%" valign="top" class="vncell">Full name</td>
                  <td width="78%" class="vtable"> 
                    <input name="fullname" type="text" class="formfld" id="fullname" size="20" value="<?=htmlspecialchars($user['fullname']);?>">
                    <br>
                    User's full name, for your own information only</td>
                </tr>
                <tr> 
                  <td width="22%" valign="top" class="vncell">Expiration date</td>
                  <td width="78%" class="vtable"> 
                    <input name="expirationdate" type="text" class="formfld" id="expirationdate" size="10" value="<?=$user['expirationdate'];?>">
                    <a href="javascript:NewCal('expirationdate','mmddyyyy')"><img src="cal.gif" width="16" height="16" border="0" alt="Pick a date"></a> 
                    <br> 
                    <span class="vexpl">Leave blank if the account shouldn't expire, otherwise enter the expiration date in the following format: mm/dd/yyyy</span></td>
                </tr>
                <tr> 
                  <td width="22%" valign="top">&nbsp;</td>
                  <td width="78%"> 
                    <input name="save" type="submit" class="formbtn" value="Save"> 
                    <input name="old_username" type="hidden" value="<?=$_GET['username'];?>">
                  </td>
                </tr>
              </table>
     </form>
<?php
} else {
?>
     <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td width="35%" class="listhdrr">Username</td>
                  <td width="20%" class="listhdrr">Full name</td>
                  <td width="35%" class="listhdr">Expires</td>
                  <td width="10%" class="list"></td>
		</tr>
<?php
	if(is_array($config['users'])){
		foreach($config['users'] as $username => $user){
?>
		<tr>
                  <td class="listlr">
                    <?=$username; ?>&nbsp;
                  </td>
                  <td class="listr">
                    <?=htmlspecialchars($user['fullname']);?>&nbsp;
                  </td>
                  <td class="listbg">
                    <?=$user['expirationdate']; ?>&nbsp;
                  </td>
                  <td valign="middle" nowrap class="list"> <a href="services_captiveportal_users.php?act=edit&username=<?=$username; ?>"><img src="e.gif" width="17" height="17" border="0"></a>
                     &nbsp;<a href="services_captiveportal_users.php?act=delete&username=<?=$username; ?>" onclick="return confirm('Do you really want to delete this User?')"><img src="x.gif" width="17" height="17" border="0"></a></td>
		</tr>
<?php
		}
	} ?>
	<tr> 
			  <td class="list" colspan="3"></td>
			  <td class="list"> <a href="services_captiveportal_users.php?act=new"><img src="plus.gif" width="17" height="17" border="0"></a></td>
		</tr>
 </table>
<?php } ?>
     
  </td>
  </tr>
  </table>
<?php include("fend.inc"); ?>
