<?php
/**
 * Change Admin Chat Online/Offline Status
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

// Verify Permissions
if(!$BF->admin->can('chat'))
{
  $BF->admin->notAllowed();
  exit();
}

// Set my status
switch($BF->inInteger('status'))
{
  case '1':
    $BF->admin->goOnline();
    break;
    
  default:
    $BF->admin->goOffline();
    break;
}

// Empty result
print json_encode(array());

// Finished
$BF->shutdown();

?>