#!/usr/local/bin/php
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

$pgtitle = array("Status", "Captive portal");
require("guiconfig.inc");
?>
<?php include("fbegin.inc"); ?>
<?php

if ($_GET['act'] == "del") {
    captiveportal_disconnect_client($_GET['id'],6);
}

flush();

function clientcmp($a, $b) {
    global $order;
    return strcmp($a[$order], $b[$order]);
}

captiveportal_lock();
$cpdb = captiveportal_read_db();
captiveportal_unlock();

$cpdb_status = array();

foreach ($cpdb as $cpent) {
        $volume = getVolume($cpent[1]);
        $cpent[7] = $volume['output_bytes'];
        $cpent[8] = $volume['input_bytes'];
        if ($_GET['showact'])
            $cpent[9] = captiveportal_get_last_activity($cpent[1]);
        $cpdb_status[] = $cpent;
}


if ($_GET['order']) {
    if ($_GET['order'] == "ip")
        $order = 2;
    else if ($_GET['order'] == "mac")
        $order = 3;
    else if ($_GET['order'] == "user")
        $order = 4;
    else if ($_GET['order'] == "download")
        $order = 7;
    else if ($_GET['order'] == "upload")
        $order = 8;
    else if ($_GET['order'] == "lastact")
        $order = 9;
    else
        $order = 0;
    usort($cpdb_status, "clientcmp");
}
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="listhdrr"><a href="?order=ip&showact=<?=$_GET['showact'];?>">IP address</a></td>
    <td class="listhdrr"><a href="?order=mac&showact=<?=$_GET['showact'];?>">MAC address</a></td>
    <td class="listhdrr"><a href="?order=start&showact=<?=$_GET['showact'];?>">Session start</a></td>
    <td class="listhdrr"><a href="?order=download&showact=<?=$_GET['showact'];?>">Download</a></td>
    <td class="listhdrr"><a href="?order=upload&showact=<?=$_GET['showact'];?>">Upload</a></td>
    <?php if ($_GET['showact']): ?>
    <td class="listhdrr"><a href="?order=lastact&showact=<?=$_GET['showact'];?>">Last activity</a></td>
    <?php endif; ?>
    <td class="listhdr"><a href="?order=user&showact=<?=$_GET['showact'];?>">Username</a></td>
    <td class="list"></td>
  </tr>
<?php foreach ($cpdb_status as $cpent): ?>
  <tr>
    <td class="listlr"><?=$cpent[2];?></td>
    <td class="listr"><?=$cpent[3];?>&nbsp;</td>
    <td class="listr"><?=htmlspecialchars(date("m/d/Y H:i:s", $cpent[0]));?></td>
    <td class="listr"><?=format_bytes($cpent[7]);?></td>
    <td class="listr"><?=format_bytes($cpent[8]);?></td>
    <?php if ($_GET['showact']): ?>
    <td class="listr"><?php if ($cpent[9]) echo htmlspecialchars(date("m/d/Y H:i:s", $cpent[9]));?></td>
    <?php endif; ?>
    <td class="listr"><?=$cpent[4];?>&nbsp;</td>
    <td valign="middle" class="list" nowrap>
    <a href="?order=<?=$_GET['order'];?>&showact=<?=$_GET['showact'];?>&act=del&id=<?=$cpent[1];?>" onclick="return confirm('Do you really want to disconnect this client?')"><img src="x.gif" title="disconnect client" width="17" height="17" border="0"></a></td>
  </tr>
<?php endforeach; ?>
</table>
<p>
<form action="status_captiveportal.php" method="GET">
<input type="hidden" name="order" value="<?=$_GET['order'];?>">
<?php if ($_GET['showact']): ?>
<input type="hidden" name="showact" value="0">
<input type="submit" class="formbtn" value="Don't show last activity">
<?php else: ?>
<input type="hidden" name="showact" value="1">
<input type="submit" class="formbtn" value="Show last activity">
<?php endif; ?>
</form>
</p>
<?php include("fend.inc"); ?>
