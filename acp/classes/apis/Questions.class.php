<?php
/**
 * Questions
 * Provides access to the Questions system from the ACP.
 * Admin API
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Questions extends API
{
  /**
   * Count unanswered questions
   * @return integer
   */
  public function countUnanswered()
  {
    // Spawn a query and find unanswered questions
    $query = $this->parent->db->query();
    $query->select('`bf_questions`.id', 'bf_questions')
          ->where('`id` NOT IN (SELECT `question_id` FROM `bf_question_answers`)')
          ->group('`bf_questions`.`id`')
          ->execute();
          
    // Return the total
    return $query->count;
  }
}
?>