<?php 
/**
 * Automated/Scheduled Scripts
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Set context
define('BF_CONTEXT_ADMIN', true);
define('BF_CONTEXT_AUTOMATION', true);
 
// Text resource
header('Content-type: text/plain');
 
// Load startup
require_once('../acp/admin_startup.php');

// Load common classes
require_once(BF_ROOT . '/acp/classes/Admin.class.php');

// Load tools
include_once(BF_ROOT . '/tools.php');

// Create a new kernel object and set it's admin property
$BF = new BFClass(true);

// Check if this script may run
$token = $BF->config->get('com.b2bfront.security.cron-token', true);
if($token != $BF->in('token'))
{
  // Cannot run
  $BF->shutdown();
  exit();
}
// No load time limit
set_time_limit(0);

// Get the robot credentials
$username = $BF->config->get('com.b2bfront.security.cron-staff-username', true);
$password = $BF->config->get('com.b2bfront.security.cron-staff-password', true);

// Set admin object
$BF->admin = new Admin(& $BF);

// Log in silently
if(!$BF->admin->logIn($username, $password, true))
{ 
  // Failed to log in
  $BF->shutdown();
  exit();
}

// Remove failed automation system warnings - automation is clearly working
$BF->db->delete('bf_admin_notifications')
       ->where('`relevance` = \'{1}\'', 'scheduled-task-failure')
       ->execute();

// What form of run is this?
$run = strtolower($BF->in('run'));
switch($run)
{

  case '5minute':

    // Execute
    include BF_ROOT . '/automated/periods/5minute.ly.php';
    
    break;

  case 'hour':
  
    // Execute
    include BF_ROOT . '/automated/periods/hour.ly.php';
    
    break;
    
  case 'day':
  
    // Execute
    include BF_ROOT . '/automated/periods/day.ly.php';
    
    break;

  case 'week':
  
    // Execute
    include BF_ROOT . '/automated/periods/week.ly.php';

    break;
    
  case 'month':
  
    // Execute
    include BF_ROOT . '/automated/periods/month.ly.php';
    
    break;

}

// Update last timestamp
file_put_contents(BF_ROOT . '/automated/last-scheduled-event', time());

// Clean up
$BF->shutdown();
?>