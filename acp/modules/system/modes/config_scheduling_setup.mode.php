<?php
/**
 * Module: System
 * Mode: Scheduling System Setup
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

<h1>Scheduling System Setup</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Scheduling System Configuration</h3>
    <p>
      This software relies on regular scheduled events to record statistics and perform automated tasks.<br />
      These events are triggered by the <tt>cron</tt> program which needs to be configured correctly.
      <br /><br />
      b2bFront can try to set up scheduled events automatically.<br />
      If this process fails, you can
      <a href="http://kb.b2bfront.com/KB0014" class="new" target="_blank">configure cron for scheduled events</a>.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=system&mode=config_scheduling_setup_do">
        <span class="img" style="background-image:url(/acp/static/icon/gear.png)">&nbsp;</span>
        Run Automatic Configuration
      </a>
    </span>
    
    <br /><br />
    
    <p>
      <span class="grey"><strong>NB:</strong> This process will remove any existing non-b2bfront generated <tt>cron</tt> configuration.</span>
    </p>
    
  </div>
</div>