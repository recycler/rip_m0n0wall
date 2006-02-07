<?php
/*
	$Id$
	part of m0n0wall (http://m0n0.ch/wall)
	
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
	All rights reserved.
	
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

$navlevelsep = ": ";	/* navigation level separator string */
$mandfldhtml = "";		/* display this before mandatory input fields */
$mandfldhtmlspc = "";	/* same as above, but with spacing */

function gentitle($title) {
	global $navlevelsep;
	return join($navlevelsep, $title);
}

function genhtmltitle($title) {
	global $config;
	return $config['system']['hostname'] . "." . $config['system']['domain'] . " - " . gentitle($title);
}

function dynamic_menu(&$menu, $menu_title) {
	global $allowed, $config;
	
	// Make sure the admin can see everything
	if ($_SERVER['REMOTE_USER'] === $config['system']['username']) {
		unset($allowed);
		foreach ($menu as $item) {
			$allowed[] = $item[0];
		}
	}
	
	foreach ($menu as $desc => $links) {
		if ($intersect = array_intersect($menu[$desc],$allowed)) {
			if (!isset($header_displayed) && !($menu_title=='Diagnostics')) {
				echo "\n			<strong>".$menu_title.'</strong><br>';
				$header_displayed = true;
			}
			else { 
				if (!isset($header_displayed)) {
					if (isset($config['system']['webgui']['expanddiags']) || strstr($_SERVER['SCRIPT_FILENAME'], "diag_") || strstr($_SERVER['SCRIPT_FILENAME'], "reboot")): ?>
					<a href="javascript:showhide('diag','tri_diag')"><img src="/tri_o.gif" id="tri_diag" width="14" height="10" border="0"></a><strong><a href="javascript:showhide('diag','tri_diag')" class="navlnk">Diagnostics</a></strong><br>
					<span id="diag">
					<?php else: ?>
					<a href="javascript:showhide('diag','tri_diag')"><img src="/tri_c.gif" id="tri_diag" width="14" height="10" border="0"></a><strong><a href="javascript:showhide('diag','tri_diag')" class="navlnk">Diagnostics</a></strong><br>
					<span id="diag" style="display: none">
					<?php endif;
					$header_displayed = true;			  
				}
				
			}
			// $intersect may contain one or more links, but we only need the first one
			// BUT, we are doing a foreach here because it might not be element [0]
			foreach ($intersect as $link) { 
				// Below lines are spaced out oddly to help improve the look of resulting HTML code.
				?>

				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="/<?=$link?>" class="navlnk"><?=$desc?></a><br><?
	   			// Only need the first one, so break out of foreach
	   			break;
			}
		}
	}
}

// Check for issues when changing the web admin username
// $allowed is set in auth.inc *IF* the user is not the webGUI admin
// so, if it's not set here, they were the admin user when they submited the admin username change.
if ((!is_array($allowed)) and 
    ($_SERVER['PHP_SELF'] == '/system.php') and 
    ($_SERVER['REMOTE_USER'] != $config['system']['username'])) {
	// webGUI username was just changed, let us change what PHP sees the remote user as so
	// the menu will build properly.  When user hits next link, they will be prompted to reauthenticate.
	$_SERVER['REMOTE_USER'] = $config['system']['username'];		
}

$menu['System']['General setup'] = array('system.php');
$menu['System']['Static routes'] = array('system_routes.php');
$menu['System']['Firmware']      = array('system_firmware.php');
$menu['System']['Advanced']      = array('system_advanced.php');
if ($_SERVER['REMOTE_USER'] === $config['system']['username']) {
	$menu['System']['User manager']  = array('system_usermanager.php');
}
else {
	$menu['System']['User password']  = array('system_usermanager.php');
}

$menu['Firewall']['Rules']          = array('firewall_rules.php');
$menu['Firewall']['NAT']            = array('firewall_nat.php',
											'firewall_nat_server.php',
							  				'firewall_nat_1to1.php',
							  				'firewall_nat_out.php');
$menu['Firewall']['Traffic shaper'] = array('firewall_shaper.php',
											'firewall_shaper_pipes.php',
							  				'firewall_shaper_queues.php',
											'firewall_shaper_magic.php');
$menu['Firewall']['Aliases']        = array('firewall_aliases.php');

$menu['Services']['DNS forwarder']	= array('services_dnsmasq.php');
$menu['Services']['Dynamic DNS'] 	= array('services_dyndns.php');
$menu['Services']['DHCP server']	= array('services_dhcp.php');
$menu['Services']['DHCP relay'] 	= array('services_dhcp_relay.php');
$menu['Services']['SNMP']  			= array('services_snmp.php');
$menu['Services']['Proxy ARP']  	= array('services_proxyarp.php');
$menu['Services']['Captive portal'] = array('services_captiveportal.php',
											'services_captiveportal_mac.php',
							                'services_captiveportal_ip.php',
			                  				'services_captiveportal_users.php',
							  				'services_captiveportal_elementmanager.php');
$menu['Services']['Wake on LAN']  	= array('services_wol.php');

$menu['VPN']['IPsec']  				= array('vpn_ipsec.php',
											'vpn_ipsec_mobile.php',
							  				'vpn_ipsec_keys.php',
											'vpn_ipsec_ca.php');
$menu['VPN']['PPTP']  				= array('vpn_pptp.php',
											'vpn_pptp_users.php');
											
$menu['Status']['System']			= array('index.php');
$menu['Status']['Interfaces']		= array('status_interfaces.php');

$menu['Status']['Traffic graph']	= array('status_graph.php');
$menu['Status']['Wireless']			= array('status_wireless.php');
if (isset($config['captiveportal']['enable'])) {
	$menu['Status']['Captive portal']	= array('status_captiveportal.php');
}

$menu['Diagnostics']['Logs']			= array('diag_logs.php',
												'diag_logs_filter.php',
							  					'diag_logs_dhcp.php',
							  					'diag_logs_portal.php',
							  					'diag_logs_vpn.php',
							  					'diag_logs_settings.php');  
$menu['Diagnostics']['DHCP Leases'] 	= array('diag_dhcp_leases.php');
$menu['Diagnostics']['IPsec'] 			= array('diag_ipsec_sad.php',
												'diag_ipsec_spd.php');
$menu['Diagnostics']['Ping/Traceroute'] = array('diag_ping.php',
												'diag_traceroute.php');
$menu['Diagnostics']['ARP Table'] 		= array('diag_arp.php');
$menu['Diagnostics']['NS Lookup'] 		= array('diag_nslookup.php');
$menu['Diagnostics']['Whois'] 			= array('diag_whois.php');
$menu['Diagnostics']['Firewall states'] = array('diag_ipfstat.php');
$menu['Diagnostics']['Reset state'] 	= array('diag_resetstate.php');
$menu['Diagnostics']['Backup/Restore'] 	= array('diag_backup.php');
$menu['Diagnostics']['Factory Defaults']= array('diag_defaults.php');
$menu['Diagnostics']['Reboot system'] 	= array('reboot.php');

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title><?=genhtmltitle($pgtitle);?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="gui.css" rel="stylesheet" type="text/css">
</head>

<body link="#0000CC" vlink="#0000CC" alink="#0000CC">
<script language="javascript">
<!--
var tri_open = "";
var tri_closed = "";

window.onload = preload;

function preload() {
	if (document.images) {
		tri_open = new Image(14,10);
		tri_closed = new Image(14,10);
		tri_open.src = "/tri_o.gif";
		tri_closed.src = "/tri_c.gif";
	}
}

function showhide(tspan, tri) {
	tspanel = document.getElementById(tspan);
	triel = document.getElementById(tri);
	if (tspanel.style.display == 'none') {
		tspanel.style.display = '';
		triel.src = "/tri_o.gif";
	} else {
		tspanel.style.display = 'none';
		triel.src = "/tri_c.gif";
	}
}
-->
</script>
<table width="750" border="0" cellspacing="0" cellpadding="2">
  <tr valign="bottom"> 
    <td width="150" height="65" align="center" valign="middle"> <strong><a href="http://m0n0.ch/wall" target="_blank"><img src="/logo.gif" width="150" height="47" border="0"></a></strong></td>
    <td height="65" bgcolor="#435370">
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr><td align="left" valign="bottom"><span class="tfrtitle">&nbsp;webGUI 
      Configuration</span></td>
	  <td align="right" valign="bottom">
	  <span class="hostname"><?=$config['system']['hostname'] . "." . $config['system']['domain'];?>&nbsp;</span>
	  </td></tr></table>
	</td>
  </tr>
  <tr valign="top"> 
    <td width="150" bgcolor="#9D9D9D">
	<table width="100%" border="0" cellpadding="6" cellspacing="0">
        <tr>
          <td><span class="navlnk"><font color="#FFFFFF"> 
            <?php 
                   
dynamic_menu($menu['System'], 'System');
            
// The Interfaces menu is different than the rest because of the Assign link and
// The dynamic nature of the optional interfaces.
$if_menu = array('(assign)' => 'interfaces_assign.php',
    			 'LAN' => 'interfaces_lan.php',
			  	 'WAN' => 'interfaces_wan.php',
			     'OPTx' => 'interfaces_opt.php');
if ($_SERVER['REMOTE_USER'] === $config['system']['username']) {
	$allowed = $if_menu;
}
if (array_intersect($if_menu,$allowed)) {
	echo '<strong>Interfaces</strong>';
	if ((!isset($config['system']['webgui']['noassigninterfaces'])) && 
	    (in_array('interfaces_assign.php',$allowed))) {
		echo '<a href="/interfaces_assign.php" class="navlnks"> (assign)</a>';
	}
    echo '<br>';
    if (in_array('interfaces_lan.php',$allowed)) {
    	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="/interfaces_lan.php" class="navlnk">LAN</a><br>';
    }
    if (in_array('interfaces_wan.php',$allowed)) {
    	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="/interfaces_wan.php" class="navlnk">WAN</a><br>';
    }
    if (in_array('interfaces_opt.php',$allowed)) {
    	for ($i = 1; isset($config['interfaces']['opt' . $i]); $i++) {
			echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="/interfaces_opt.php?index=' .$i.
			     '" class="navlnk">'.htmlspecialchars($config['interfaces']['opt' . $i]['descr']).'</a><br>';
    	}
    }
}             

dynamic_menu($menu['Firewall'], 'Firewall');
dynamic_menu($menu['Services'], 'Services');
dynamic_menu($menu['VPN'], 'VPN');
dynamic_menu($menu['Status'], 'Status');
?>
			  
<?php
/* extensions section */
if (is_dir("{$g['www_path']}/ext")):
?>
              <strong>Extensions</strong><br>
<?php
$dh = @opendir("{$g['www_path']}/ext");
if ($dh) {
	while (($extd = readdir($dh)) !== false) {
		if (($extd === ".") || ($extd === ".."))
			continue;
		@include("{$g['www_path']}/ext/" . $extd . "/menu.inc");
	}
	closedir($dh);
}
endif;
?>

<?php 
dynamic_menu($menu['Diagnostics'], 'Diagnostics');
?>
			  </span>
			  </font></span>
            </td>
        </tr></table></td>
    <td width="600"><table width="100%" border="0" cellpadding="10" cellspacing="0">
        <tr><td>
<?php if (!$pgtitle_omit): ?>
      <p class="pgtitle"><?=gentitle($pgtitle);?></p>
<?php endif; ?>