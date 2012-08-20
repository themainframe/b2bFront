<?php 
/**
 * Automated/Scheduled Scripts
 * Script: Daily Script
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
// Executes every Day
// Specifically, 00:00
//

$BF->logEvent('Automation',
                  'Executing `day-ly` script as scheduled.', 1);

// Remove old drafts
$maxAge = $BF->config->get('com.b2bfront.acp.drafts.max-age', true);
$maxAgeSeconds = $maxAge * 86400;
$BF->db->delete('bf_admin_drafts')
       ->where('UNIX_TIMESTAMP() - timestamp > {1}', $maxAgeSeconds)
       ->execute();
 
// Remove old RPs
$maxAge = $BF->config->get('com.b2bfront.restorepoints.ttl', true);
$maxAgeSeconds = $maxAge * 86400;

// Remove data
$BF->db->delete('bf_restore_points')
        ->where('UNIX_TIMESTAMP() - timestamp > {1}', $maxAgeSeconds)
        ->execute();
           
// Clear old log data
$BF->db->delete('bf_user_action_logs')
       ->where('UNIX_TIMESTAMP() - timestamp > 86400')
       ->execute();
  
//     
// Clear old admin notifications
//

// Remove all shown
$BF->db->delete('bf_admin_notifications')
       ->where('`logged` = 0 AND `email_required` = 0 AND `popup_needed` = 0')
       ->execute();
       
// Remove all dead popups that are outdated
$BF->db->delete('bf_admin_notifications')
       ->where('`popup_needed` = 1 AND (UNIX_TIMESTAMP() - `timestamp`) > 3600')
       ->execute();
       
//
// Remove old events
//
$BF->db->delete('bf_events')
       ->where('`(UNIX_TIMESTAMP() - `timestamp`) > 604800')
       ->execute();
  
//     
// Reset todays work on answering IM calls
//
$BF->db->update('bf_admins', array(
                 'call_answer_count' => 0
               ))
       ->execute();

?>