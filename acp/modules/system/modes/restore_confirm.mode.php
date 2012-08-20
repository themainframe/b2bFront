<?php
/**
 * Module: System
 * Mode: Restore Points : Do
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

// Load the restore point
$BF->db->select('*', 'bf_restore_points')
           ->where('id = \'{1}\'', $BF->inInteger('id'))
           ->limit(1)
           ->execute();
           
if(!$BF->db->count)
{
  // Invalid ID
  header('Location: ./?act=system&mode=restore');
  exit();
}

$restorePoint = $BF->db->next();

?>

<h1>Roll Back to Restore Point</h1>
<br />

<form action="./?act=system&mode=restore_do" method="post">

<div class="panel">
  <div class="title">Important Information</div>
  <div class="warning">
    <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>
    <strong>Important</strong> - Please pay special attention to the contents of this panel.
    <br class="clear">
  </div>
  <div class="contents">
    <p>
      You are about to restore the website to the state it was in on
      <strong><?php print Tools::longDate($restorePoint->timestamp); ?></strong><br />
      <strong>Remember: </strong>This also includes deleting any data you have entered since the restore point was made.
      <br /><br />
      Another restore point will be created before any changes are made, therefore this operation can be undone.
      <br /><br />
      Statistics will not be affected by this operation.<br />
      Please choose the parts of the website you would like to restore below.
    </p>
  </div>
</div>    
     
<br />

<input type="hidden" name="f_id" value="<?php print $BF->inInteger('id'); ?>" />
     
<div class="panel">
  <div class="title">Choose Data to Restore</div>
  <div class="contents fieldset"> 
    <table class="fields">
      <tbody>   
<?php

  // Obtain all restore options
  $BF->db->select('*', 'bf_restore_options')
             ->execute();
             
  while($restoreOption = $BF->db->next())
  {
?>
          <tr <?php print ($BF->db->last() ? 'class="last"' : ''); ?>>
            <td class="key">
              <strong><?php print $restoreOption->name; ?></strong><br />
              Check to restore <?php print $restoreOption->name; ?>.
            </td>
            <td class="value">
              <input type="checkbox" name="f_r_<?php print $restoreOption->id; ?>" value="1" />
            </td>
          </tr>
<?php
  }
?>      
      </tbody>
    </table>
  </div> 
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to perform the restore operation.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Restore" />
    <br class="clear" />
  </div>
</div>

</form>