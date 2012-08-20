<?php
/**
 * Admin Module : Website
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

// Inside module
define('BF_CONTEXT_MODULE', true);

// Gain BFClass access
global $BF;

// Load the selected mode file
$selectedMode = Tools::removePaths($BF->in('mode'));
$selectedModePath = MODULE_PATH . '/modes/' . $selectedMode . '.mode.php';

// Verify Permissions
if(!$BF->admin->can('website'))
{
  print $BF->admin->notAllowed();
  exit();
}

// Check and load the mode file
if(Tools::exists($selectedModePath))
{
  include BF_ROOT . $selectedModePath;
}
else
{
?>
    <h1>Module Error</h1>
    <br />
    <p>
      The requested module failed to execute.<br />
      This exception generated an <a href="./?act=system&mode=events" title="Events" class="new">event</a>.
    </p>
<?php

// Log event
$BF->logEvent('ACP Error',
                  'Could not find the module file: ' . $selectedModePath);
                  
}

?>