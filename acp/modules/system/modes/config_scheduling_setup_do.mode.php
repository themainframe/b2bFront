<?php
/**
 * Module: System
 * Mode: Scheduling System Setup Do
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

$BF->log('Cron Config', 'Beginning crontab configuration repair.');

// Update cron configuration from template file
$cronTemplate = Tools::getText(BF_ROOT . '/acp/definitions/cron_config_template.txt');

// Replace out the {url} and {token} placeholders
$url = $BF->config->get('com.b2bfront.site.url', true);
$token = $BF->config->get('com.b2bfront.security.cron-token', true);
$cronTemplate = str_replace('{url}', $url, $cronTemplate);
$cronTemplate = str_replace('{token}', $token, $cronTemplate);

// Save cron configuration, first write the finished text to a tempfile
// The temp file will be outside of the served scope for security reasons.
$tempLocation = tempnam('/tmp', 'b2bfront-cron-configuration.tmp');
file_put_contents($tempLocation, $cronTemplate);

// Make crontab load the file for the current user
$BF->log('Cron Config', 'Writing crontab for ' . get_current_user() . '.');
system('crontab ' . $tempLocation);

// Remove the temporary file
unlink($tempLocation);

// Also stop warning me for 5 minutes until we can assertain if the fix worked
file_put_contents(BF_ROOT . '/automated/last-scheduled-event', time());
$BF->log('Cron Config', 'Moving configuration file items in to place.');

// Remove notifications that are old news
$BF->db->delete('bf_admin_notifications')
       ->where('`relevance` = \'{1}\'', 'scheduled-task-failure')
       ->execute();

// Generate a notification
$BF->admin->notifyMe('OK', '<tt>cron</tt> configuration was updated.');

// Finished
$BF->log('Cron Config', 'Crontab configuration repair completed.');
$BF->go('./?act=dashboard');

?>