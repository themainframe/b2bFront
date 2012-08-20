<?php
/**
 * CCTV Feed
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
$BF = new BFClass();
$BF->admin = new Admin(& $BF);

// Content type
header('Content-type: text/html');

if(!$BF->admin->isAdmin)
{
  exit();
}

/** 
 * Build a nice time string from a number of seconds
 * @param integer $seconds The number of seconds.
 * @return string
 */
function niceTime($seconds)
{
  return intval($seconds / 60) . ':' . str_pad($seconds % 60, 2, '0', STR_PAD_LEFT);
}

// Load the last CCTV changes
$BF->db->select('DISTINCT `user_id`, `timestamp`, `location`', 'bf_cctv')
       ->where('UNIX_TIMESTAMP() - timestamp < 1200')
       ->order('timestamp', 'desc')
       ->execute();
       
// Collect users so far and JSON array
$users = array();
$data = array(); 
      
// For each, generate a HTML line
while($userCCTV = $BF->db->next())
{
  // Calculate time
  $time = niceTime(time() - $userCCTV->timestamp);

  // Work out user
  if($userCCTV->user_id == '-1')
  {
    $user = 'Public';
  }
  else
  {
    // Get user name
    // (Repeated query is fine, will be cached)
    $userDetails = $BF->db->getRow('bf_users', $userCCTV->user_id);
    $user = $userDetails->name;
    
    if(in_array($user, $users))
    {
      continue;
    }
    
    $users[] = $user;
  }
  
  // Skip due to config override?
  if($user == '' || $user == 'Public')
  {
    continue;
  }
  
  // Get the basket count too
  $basketCount = $BF->db->query();
  $basketCount->select('id', 'bf_user_cart_items')
              ->where('`user_id` = \'{1}\'', ($user == 'Public' ? -1 : $userDetails->id))
              ->execute();
  
  $data[] = array(
    'name' => $user,
    'activity' => $userCCTV->location,
    'time' => $time,
    'id' => ($user == 'Public' ? -1 : $userDetails->id),
    'basketCount' => $basketCount->count
  );

  
}

print json_encode($data);

?>