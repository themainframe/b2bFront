<?php
/**
 * Module: Dealers
 * Mode: Refer Question
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


// Load the question to modify
$questionID = $BF->inInteger('id');

// Query for it
$BF->db->select('*', 'bf_questions')
           ->where('id = \'{1}\'', $questionID)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  $BF->go('./?act=dealers&mode=questions');
  exit();
}

$question = $BF->db->next();

?>

<h1>Refer <?php print $question->title; ?></h1>
<br />

<form action="./?act=dealers&mode=questions_refer_do" method="post">
<input type="hidden" name="f_id" value="<?php print $question->id; ?>" />

<div class="panel">
  <div class="title">Staff Selection</div>
  <div class="message">
    <p>
      <span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>
      <strong>Required Fields</strong> - You need to complete all of the fields in this panel.
      <br class="clear" />
    </p> 
  </div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <table class="fields">
      <tbody>
        <tr class="last">
          <td class="key">
            <strong>Refer to...</strong><br />
            Select the member of staff that may be able to answer this question.
          </td>
          <td class="value">
<?php

  // Query the database for Staff
  $query = $BF->db->query();
  $query->select('*', 'bf_admins')
        ->where('`name` NOT IN (\'rootd\', \'cron\', \'{1}d\')', $BF->admin->getInfo('name'))
        ->order('name', 'ASC')
        ->execute();
        
  // Create a UI element
  $dropDown = new DataDropDown('f_admin', $query, 'id', 'full_name');
  print $dropDown->render();

?>
          </td>
        </tr>
        </tbody>
    </table>
  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to refer this question.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Refer" />
    <br class="clear" />
  </div>
</div>

</form>