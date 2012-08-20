<?php 
/**
 * Automated/Scheduled Scripts
 * Script: Hourly Script
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_AUTOMATION") && !defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

//
// Executes every Hour
// Specifically, 0 minutes past
//

$BF->logEvent('Automation',
  'Executing `hour-ly` script as scheduled.', 1);

//
// Update Outlets
//
$BF->admin->api('Outlets')
          ->updateAll();
      
//          
// Purge expired TTLs
//
$BF->purgeFileTTLs();

//
// Remove old CCTV records
//
$BF->db->delete('bf_cctv')
       ->where('UNIX_TIMESTAMP() - timestamp > 3600')
       ->execute();

//
// Resend queued SMS messages
//
$SMS = new SMS(& $BF);
$SMS->dequeueAll();

//
// Generate Data Jobs
//
$BF->db->delete('bf_data_jobs')
       ->execute();

$query = $BF->db->query();

$query->select('`bf_items`.*, COUNT(`bf_item_images`.`id`) AS `images`', 'bf_items')
      ->text('LEFT OUTER JOIN `bf_item_images` ON `bf_items`.`id` = `bf_item_images`.`item_id`')
      ->where('`bf_items`.`id` NOT IN (SELECT item_id FROM bf_data_jobs_ignore)')
      ->group('`bf_items`.`id`')
      ->execute();
            
while($item = $query->next())
{
  // Missing images?
  if($item->images == 0)
  {
    $BF->db->insert('bf_data_jobs', array(
                     'item_id' => $item->id,
                     'description' => 'No associated images.'
                   ))
           ->execute();
  }
  
  // Missing Barcode?
  if($item->barcode == '')
  {
    $BF->db->insert('bf_data_jobs', array(
                     'item_id' => $item->id,
                     'description' => 'Missing barcode.'
                   ))
           ->execute();
  }
  
  // Missing Brand?
  if($item->brand_id == '-1')
  {
    $BF->db->insert('bf_data_jobs', array(
                     'item_id' => $item->id,
                     'description' => 'Missing brand allocation.'
                   ))
           ->execute();
  }
}

//
// Remove old downloads
//
$BF->db->delete('bf_admin_downloads')
       ->where('UNIX_TIMESTAMP() - timestamp > 86400')
       ->execute();

// Files are cleaned by the TTL system - do nothing here.

//
// Log rotation
//

$logLocation = $BF->logLocation;
$logSize = filesize($logLocation);

// Maximum log size
if($logSize >= BF_MAX_LOG_SIZE)
{
  // Rotate into a new file
  for($logIndex = 1; $logIndex <= 10; $logIndex ++)
  {
    if(!file_exists($logLocation . '.' . $logIndex))
    {
      // Write here ... 
    
      break;
    }
  }
  
  if($logIndex == 11)
  {
    // Too many log files already, shift...  
  }
}

//
// Download tweets
// 
$twitterAccount = $BF->config->get('com.b2bfront.site.twitter-account', true);

if($twitterAccount)
{
  // Delete existing
  $removeTweets = $BF->db->query();
  $removeTweets->delete('bf_tweets')
               ->execute();

  // Get from API
  $APIText = file_get_contents('https://api.twitter.com/1/statuses/user_timeline.json?' . 
    'include_entities=true&screen_name=' . $twitterAccount . '&count=5');
    
  // Decode
  $tweets = json_decode($APIText);
  
  // Valid?
  if($tweets)
  {
    foreach($tweets as $tweet)
    {
      // Store
      $newTweet = $BF->db->query();
      $newTweet->insert('bf_tweets', array(
                   'text' => $tweet->text,
                   'account' => $tweet->user->screen_name,
                   'timestamp' => strtotime($tweet->created_at)
                 ))
               ->execute();
    }
  }  
}



?>
