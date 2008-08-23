#!/usr/local/bin/php
<?php 
/*
	$Id$
	part of m0n0wall (http://m0n0.ch/wall)
	
	Copyright (C) 2003-2007 Manuel Kasper <mk@neon1.net>.
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

$pgtitle = array("m0n0wall webGUI");
$pgtitle_omit = true;
require("guiconfig.inc");

/* find out whether there's hardware encryption (hifn) */
unset($hwcrypto);
$dmesg = system_get_dmesg_boot();
if (preg_match("/^hifn.: (.*?),/m", $dmesg, $matches))
	$hwcrypto = $matches[1];

$specplatform = system_identify_specific_platform();

if ($_POST) {
	$config['system']['notes'] = base64_encode($_POST['notes']);
	write_config();
	header("Location: index.php");
	exit;
}

?>
<?php include("fbegin.inc"); ?>
<form action="" method="POST">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" summary="content pane">
              <tr align="center" valign="top"> 
                <td height="10" colspan="2">&nbsp;</td>
              </tr>
              <tr align="center" valign="top"> 
                <td height="170" colspan="2"><img src="logobig.gif" width="520" height="149" alt=""></td>
              </tr>
              <tr> 
                <td colspan="2" class="listtopic">System information</td>
              </tr>
              <tr> 
                <td width="25%" class="vncellt">Name</td>
                <td width="75%" class="listr">
                  <?php echo $config['system']['hostname'] . "." . $config['system']['domain']; ?>
                </td>
              </tr>
              <tr> 
                <td width="25%" valign="top" class="vncellt">Version</td>
                <td width="75%" class="listr"> <strong> 
                  <?php readfile("/etc/version"); ?>
                  </strong><br>
                  built on 
                  <?php readfile("/etc/version.buildtime"); ?>
                </td>
              </tr>
              <tr> 
                <td width="25%" class="vncellt">Platform</td>
                <td width="75%" class="listr"> 
                  <?=htmlspecialchars($specplatform['descr']); ?>
                </td>
              </tr><?php if ($hwcrypto): ?>
              <tr> 
                <td width="25%" class="vncellt">Hardware crypto</td>
                <td width="75%" class="listr"> 
                  <?=htmlspecialchars($hwcrypto);?>
                </td>
              </tr><?php endif; ?>
              <tr> 
                <td width="25%" class="vncellt">Uptime</td>
                <td width="75%" class="listr"> 
                  <?php
				  	exec("/sbin/sysctl -n kern.boottime", $boottime);
					preg_match("/sec = (\d+)/", $boottime[0], $matches);
					$boottime = $matches[1];
					$uptime = time() - $boottime;
					
					if ($uptime > 60)
						$uptime += 30;
					$updays = (int)($uptime / 86400);
					$uptime %= 86400;
					$uphours = (int)($uptime / 3600);
					$uptime %= 3600;
					$upmins = (int)($uptime / 60);
					
					$uptimestr = "";
					if ($updays > 1)
						$uptimestr .= "$updays days, ";
					else if ($updays > 0)
						$uptimestr .= "1 day, ";
					$uptimestr .= sprintf("%02d:%02d", $uphours, $upmins);
					echo htmlspecialchars($uptimestr);
				  ?>
                </td>
              </tr><?php if ($config['lastchange']): ?>
              <tr> 
                <td width="25%" class="vncellt">Last config change</td>
                <td width="75%" class="listr"> 
                  <?=htmlspecialchars(date("D M j G:i:s T Y", $config['lastchange']));?>
                </td>
              </tr><?php endif; ?>
			  <tr> 
                <td width="25%" class="vncellt">CPU usage</td>
                <td width="75%" class="listr">
				<a href="status_graph_cpu.php">view graph</a></td>
              </tr>
			  <tr> 
                <td width="25%" class="vncellt">Memory usage</td>
                <td width="75%" class="listr">
<?php

exec("/sbin/sysctl -n vm.stats.vm.v_active_count vm.stats.vm.v_inactive_count " .
	"vm.stats.vm.v_wire_count vm.stats.vm.v_cache_count vm.stats.vm.v_free_count", $memory);

$totalMem = $memory[0] + $memory[1] + $memory[2] + $memory[3] + $memory[4];
$freeMem = $memory[4];
$usedMem = $totalMem - $freeMem;
$memUsage = round(($usedMem * 100) / $totalMem, 0);
		  
echo " <img src='bar_left.gif' height='15' width='4' border='0' align='middle' alt=''>";
echo "<img src='bar_blue.gif' height='15' width='" . $memUsage . "' border='0' align='middle' alt=''>";
echo "<img src='bar_gray.gif' height='15' width='" . (100 - $memUsage) . "' border='0' align='middle' alt=''>";
echo "<img src='bar_right.gif' height='15' width='5' border='0' align='middle' alt=''> ";
echo $memUsage . "%";
?>
                </td>
              </tr>
              <tr> 
                <td width="25%" class="vncellt" valign="top">Notes</td>
                <td width="75%" class="listr">
                  <textarea name="notes" cols="75" rows="7" id="notes" class="notes"><?=htmlspecialchars(base64_decode($config['system']['notes']));?></textarea><br>
                  <input name="Submit" type="submit" class="formbtns" value="Save">
                </td>
              </tr>
            </table>
</form>
            <?php include("fend.inc"); ?>
