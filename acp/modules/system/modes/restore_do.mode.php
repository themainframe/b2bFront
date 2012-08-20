<?php
/**
 * Module: System
 * Mode: Restore Points Do
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

// Get the ID of the restore point
$restorePointID = $BF->inInteger('f_id');

// Create the restore point before restoring
$BF->admin->api('RestorePoints')
              ->create('The system was restored to an earlier date.');

// Build an array of restore options
$selectedOptions = array();
$options = $BF->db->query();
$options->select('*', 'bf_restore_options')
        ->execute();
        
while($option = $options->next())
{
  if($BF->in('f_r_' . $option->id) == '1')
  {
    $selectedOptions[] = $option->id;
  }
}

// Restore the data
$BF->admin->api('RestorePoints')
              ->restore($restorePointID, $selectedOptions);
            

?>

<h1>Restore Operation Finished</h1>
<br />
<div class="panel">
  <div class="title">Restore Operation Completed</div>
  <div class="contents">
    <p>
    
      The restore operation completed successfully.<br /><br />
      
      If you are unhappy with the results, you can always restore <em>back</em> to the point that was just created.<br />
      This would effectively undo the restore operation.<br /><br />
      
      <a href="./?" title="Home">Click Here</a> to return to the Admin Control Panel dashboard.

    </p>
  </div>
</div>