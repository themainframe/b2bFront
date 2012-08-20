<?php
/**
 * Chat System - Get Admin
 * AJAX Responder
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_INDEX', true);

// Relative path for this - no BF_ROOT yet.
require_once('../startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);

// Verify that I am logged in
if(!$BF->security->loggedIn())
{
  // Not authenticated
  $BF->shutdown();
  exit();
}


// Get the admin with the lowest call answer count
$adminGet = $BF->db->query();
$adminGet->select('*', 'bf_admins')
         ->where('`online` = \'1\'')
         ->order('call_answer_count', 'asc')
         ->limit(1)
         ->execute();

if($adminGet->count == 0)
{
  // No ID
  print json_encode(array(
    'id' => -1
  ));

  $BF->shutdown();
  exit();
}

// Found admin
$admin = $adminGet->next();

// Update
$adminUpdate = $BF->db->query();
$adminUpdate->update('bf_admins', array(
                'call_answer_count' => $admin->call_answer_count + 1
              ))
            ->where('`id` = \'{1}\'', $admin->id)
            ->limit(1)
            ->execute();

// Reveal staff?
$reveal = $BF->config->get('com.b2bfront.crm.reveal-staff-names', true);
$title = $BF->config->get('com.b2bfront.site.title', true);
$firstName = explode(' ', $admin->full_name);
$firstName = $firstName[0];

// Output
print json_encode(array(
  'id' => $admin->id,
  'name' => ($reveal ? $firstName : $title . ' Staff')
));

// Finished
$BF->shutdown();

?>