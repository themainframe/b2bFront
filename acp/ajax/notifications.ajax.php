<?php
/**
 * Notifications
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

if(!$BF->admin->isAdmin)
{
  exit();
}

// Set my last activity


// Check for notifications requiring download and display
// Only last 5 and within the last 60 seconds
$BF->db->select('*', 'bf_admin_notifications')
           ->where('`admin_id` = {1} AND
                    `popup_needed` = 1 AND
                    (UNIX_TIMESTAMP() - `timestamp`) < 60 AND (UNIX_TIMESTAMP() - `timestamp`) > -3',
                   $BF->admin->AID
                  )
           ->order('timestamp', 'DESC')
           ->limit(5)
           ->execute();

// Produce JSON
$arrayOutput = array();

while($popup = $BF->db->next())
{
  $arrayOutput[$popup->id] = array(
    'title' => $popup->title,
    'content' => $popup->content,
    'icon' => $popup->icon_url,
    'persist' => $popup->persist
  );
}

// Load chat messages
$chat = $BF->db->query();
$chat->select('*', 'bf_chat')
     ->where('`admin_id` = \'{1}\'',
      $BF->admin->AID)
     ->order('timestamp', 'asc')
     ->execute();
    
     
// Collect chat messages
$chatMessages = array();
     
while($chatMessage = $chat->next())
{
  $chatMessages[] = array(
    'id' => $chatMessage->id,
    'content' => $chatMessage->content,
    'user_id' => $chatMessage->user_id,
    'time' => date('G:i', $chatMessage->timestamp),
    'read' => ($chatMessage->read == 0 ? 'false' : 'true'),
    'meta' => $chatMessage->meta,
    'direction' => $chatMessage->direction
  );      
}

// Mark old chat messages
$markChat = $BF->db->query();
$markChat->update('bf_chat', array(
             'read' => 1
           ))
         ->where('`admin_id` = \'{1}\'',
           $BF->admin->AID)
         ->execute();

// Get CCTV/online dealers overview
$dealers = $BF->db->query();
$dealers->select('DISTINCT `user_id`, `timestamp`, `location`', 'bf_cctv')
        ->where('UNIX_TIMESTAMP() - timestamp < 1200')
        ->order('user_id', 'desc')
        ->execute();
   
$onlineUsers = array();
       
while($dealer = $dealers->next())
{
  // Get the user and provide info to JS
  $user = $BF->db->getRow('bf_users', $dealer->user_id);
  
  // Valid?
  if(!$user)
  {
    continue;
  }
  
  // See if another admin is talking to them
  $otherAdminCheck = $BF->db->query();
  $otherAdminCheck->select('*', 'bf_chat')
                  ->where('`admin_id` <> \'{1}\' AND `user_id` = \'{2}\' AND (UNIX_TIMESTAMP() - `timestamp` ) < 60 AND `meta` = \'\'', $BF->admin->AID,
                     $user->id)
                  ->limit(1)
                  ->execute();
                 
  if($otherAdminCheck->count == 1)
  {
    $talkAdmin = $otherAdminCheck->next();
    $talkID = $talkAdmin->admin_id;

    // Get the admin
    $admin = $BF->db->getRow('bf_admins', $talkID);
  }
                
  // Get the admin ID if so.
  $inUse = ($otherAdminCheck->count > 0 ? $admin->full_name : 'false');
    
  // Find what the user is doing
  $cctvQuery = $BF->db->query();
  $cctvQuery->select('*', 'bf_cctv')
            ->where('`user_id` = \'{1}\'', $user->id)
            ->order('timestamp', 'desc')
            ->limit(1)
            ->execute();
            
  $activity = '';
  if($cctvQuery->count == 1)
  {
    $activity = $cctvQuery->next()->location;
  }
    
  $onlineUsers[$user->id] = array(
    'name' => $user->name,
    'description' => ($user->description == '' ? $user->name : $user->description),
    'id' => $user->id,
    'state' => $inUse,
    'activity' => Tools::truncate($activity, 50)
  );
}

// Get enable flag
$enableNotifications = $BF->in('notifications');

// Are they enabled?
if($enableNotifications == true)
{
  // Nothing to update?
  if(count($arrayOutput) != 0)
  {
    // Update 
    $downloadedIDs = array_keys($arrayOutput);
    $downloadedIDsCSV = Tools::CSV($downloadedIDs);
    
    $BF->db->update('bf_admin_notifications', array(
                 'popup_needed' => '0'
               ))
               ->where('`id` IN ({1})', $downloadedIDsCSV)
               ->limit(5)
               ->execute();
    
    // Delete any delete_on_view notifications
    $BF->db->delete('bf_admin_notifications')
               ->where('`id` IN ({1}) AND `delete_on_view` = \'1\'', $downloadedIDsCSV)
               ->limit(5)
               ->execute();
  }
}
else
{
  $arrayOutput = array();
}

// Get online admins
$onlineAdmins = $BF->db->query();
$onlineAdmins->select('*', 'bf_admins')
             ->where('UNIX_TIMESTAMP() - `last_activity_timestamp` < 300 OR `online` = 1')
             ->order('name', 'asc')
             ->execute();
             
$onlineStaff = array();

while($onlineAdmin = $onlineAdmins->next())
{
  $onlineStaff[] = array(
    'online' => $onlineAdmin->online,
    'name' => $onlineAdmin->name,
    'full_name' => $onlineAdmin->full_name
  );
}

// Produce sub-array
$notifications = array(
  'notifications' => $arrayOutput,
  'chatMessages' => $chatMessages,
  'onlineUsers' => $onlineUsers,
  'status' => ($BF->admin->getInfo('online', true) == '1' ? 'true' : 'false'),
  'admins' => $onlineStaff
);

// Output
print json_encode($notifications);

$BF->shutdown();

?>