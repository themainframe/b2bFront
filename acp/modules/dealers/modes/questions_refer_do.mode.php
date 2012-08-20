<?php
/**
 * Module: Dealers
 * Mode: Question Referal Do
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

$adminID = $BF->inInteger('f_admin');
$questionID = $BF->inInteger('f_id');

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

// Notify
$result = $BF->admin->sendNotification($adminID, $question->title, $BF->admin->getInfo('full_name') . 
  ' has referred a question.<br /><a href="./?act=dealers&mode=questions_answer&id=' .
  $question->id . '">Click here</a> to view it.', 'information.png', true, true, 'question-' .
  $question->id);

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The question has been referred.');
  header('Location: ./?act=dealers&mode=questions');
}

?>