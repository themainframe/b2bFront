<?php
/**
 * Module: Dealers
 * Mode: Do Remove Question
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

// Remove
$BF->db->delete('bf_questions')
       ->where("`id` = '{1}'", $BF->inInteger('id'))
       ->limit(1)
       ->execute();

$BF->db->delete('bf_question_answers')
       ->where("`question_id` = '{1}'", $BF->inInteger('id'))
       ->execute();

$BF->admin->notifyMe('OK', 'The question was removed.');
header('Location: ./?act=dealers&mode=questions');

?>