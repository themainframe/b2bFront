<?php
/**
 * Module: Dealers
 * Mode: Answer Question
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

// Also grab the dealer and item specs
$BF->db->select('*', 'bf_items')
           ->where('id = \'{1}\'', $question->item_id)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  $BF->go('./?act=dealers&mode=questions');
  exit();
}

$item = $BF->db->next();

// Now the Dealer
$BF->db->select('*', 'bf_users')
           ->where('id = \'{1}\'', $question->user_id)
           ->limit(1)
           ->execute();
    
// Success?
if($BF->db->count != 1)
{
  // Failed
  $BF->go('./?act=dealers&mode=questions');
  exit();
}

$user = $BF->db->next();

?>

<h1><?php print $question->title; ?></h1>
<br />

<form action="./?act=dealers&mode=questions_answer_do" method="post">
<input type="hidden" name="f_id" value="<?php print $BF->inInteger('id'); ?>" id="id" />

<div class="panel">
  <div class="contents">
    <strong>Regarding </strong>  <?php print $item->name; ?> &nbsp; &nbsp; 
    <strong>Asked By</strong> <?php print $user->description; ?> &nbsp; &nbsp;
    <strong>On</strong> <?php print Tools::longDate($question->timestamp); ?>
    </em>
  </div>
  <div class="contents">
    <pre><?php print strip_tags(htmlentities($question->content)); ?></pre>
  </div>
</div> 

<br />

<div class="panel">
  <div class="title">
    <p>Compose an Answer</p>
  </div>
  <div class="contents" style="padding: 0px 20px 0px 20px;">
    <br />
    <strong>Write your answer below and then click Submit to send it to the dealer.</strong><br />
    <span class="grey">Basic HTML is permitted in this field.</span><br />
<textarea 
      style="max-width: 97%; width: 97%; min-height: 75px; margin-top:20px; padding: 10px; font-size: 10pt;"
      name="f_content" id="f_content"></textarea>    <table class="fields">
      <tr class="last">
        <td class="key">
          <strong>Publish this question &amp; answer for other dealers to read</strong><br />
          Allow other dealers to read this question and your answer.<br /><br />
          <span class="grey">The dealer will remain anonymous.</span>
        </td>
        <td class="value">
          <input type="checkbox" id="f_bulk_exclude" name="f_bulk_exclude" value="1" />
        </td>
      </tr> 
    </table>
  </div>
</div> 

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 7px 0px 0px 5px; float: left; color: #50954b;">
      <strong>Click the button to the right to submit this answer.</strong>
    </p>
    <input class="submit ok" type="submit" style="float: right;" value="Submit" />
    <br class="clear" />
  </div>
</div>

</form>