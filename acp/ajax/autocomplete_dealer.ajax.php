<?php
/**
 * Autocomplete Dealers
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

// Search for dealers with the current prefix
$user = $BF->in('term');

$BF->db->select('*', 'bf_users')
           ->where('name LIKE \'{1}%\' OR description LIKE \'{1}%\'', $user)
           ->limit(15)
           ->execute();

           
// Buffer all
$users = array();
while($user = $BF->db->next())
{
  $users[] = array('id' => $user->id, 
                   'label' => $user->description,
                   'value' => $user->id);
}

// Output as JSON
print json_encode($users);

?>