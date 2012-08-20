<?php
/**
 * Module: System
 * Mode: Update 
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN") || !defined("BF_CONTEXT_MODULE"))
{
  exit();
}

?>

<h1>Update</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Updating</h3>
    <p>
      New versions of this software should be installed as soon as they are made available.<br />
      You can download the latest update information by clicking the button below.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=system&mode=update_scan">
        <span class="img" style="background-image:url(/acp/static/icon/arrow-circle-double.png)">&nbsp;</span>
        Check for Updates...
      </a>
    </span>
    
    <br /><br />
    
  </div>
</div>

<br />