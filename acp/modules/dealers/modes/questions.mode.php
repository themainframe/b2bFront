<?php
/**
 * Module: Dealers
 * Mode: Questions
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

?>

<h1>Open Questions</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Questions</h3>
    <p>
      This feature allows you to answer questions about items submitted by dealers.<br />
      You can also choose to publish these answers to help other dealers in future.<br /><br />
      
      If you are unsure about how to answer a question, you can refer it to another member of staff.
    </p>
  </div>
</div>    
     

<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->select('`bf_questions`.*, COUNT(`bf_question_answers`.`id`) AS `answers`, `bf_items`.`sku` AS `sku`, ' . 
                 '`bf_users`.`description` AS `user`', 
                 'bf_items, bf_users, bf_questions')
        ->text('LEFT OUTER JOIN `bf_question_answers` ON `bf_questions`.`id` = `bf_question_answers`.`question_id`')
        ->where('`bf_items`.`id` = `bf_questions`.`item_id` AND `bf_users`.`id` = `bf_questions`.`user_id`')
        ->group('`bf_questions`.`id`');
  
  // Define boolean columns CSS text
  $columnCSS = array(
    'width' => '100px'
  );
  
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this question?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'questions_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Refer" href="./?act=dealers&mode=questions_refer&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/smiley-confused.png" alt="Refer" />' . "\n";
  $toolSet .= 'Refer</a>' . "\n";
  $toolSet .= '<a class="tool" title="Answer" href="./?act=dealers&mode=questions_answer&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/tick-circle.png" alt="Answer" />' . "\n";
  $toolSet .= 'Answer</a>' . "\n";

  // Create a data table view
  $questions = new DataTable('qu1', $BF, $query);
  $questions->setOption('alternateRows');
  $questions->setOption('showTopPager');
  
  // Detect ordering and apply automatically if none present
  if($BF->in('qu1_order') == '' || $BF->in('qu1_order_d') == '')
  {
    $BF->setIn('qu1_order', '0');
    $BF->setIn('qu1_order_d', 'a');
  }
  
  $questions->addColumns(array(
                          array(
                            'dataName' => 'answers',
                            'niceName' => '',
                            'options' => array(
                                           'formatAsToggleImage' => true,
                                           'toggleImageTrue' => '/acp/static/icon/tick-circle.png',
                                           'toggleImageFalse' => '/acp/static/icon/exclamation.png',
                                           'toggleImageTrueTitle' => 'This question has been answered.',
                                           'toggleImageFalseTitle' => 'This question has not yet been answered.',
                                         ),
                            'css' => array(
                                       'width' => '16px'
                                     )
                          ), 
                          array(
                            'dataName' => 'sku',
                            'niceName' => 'SKU',
                            'options' => array(
                                           'formatAsLink' => true,
                                           'linkNewWindow' => true,
                                           'linkURL' => '/acp/?act=inventory&mode=browse&f_term={sku}&f_in=sku&f_filter=-1' . 
                                                        '&inventory_pg=1&inventory_lpp=1&inventory_order_d=a&inventory_order=1' . 
                                                        '&no_save_default_view=1'
                                         ),
                          'css' => array('width' => '70px')
                          ),
                          array(
                            'dataName' => 'title',
                            'niceName' => 'Title'
                          ),
                          array(
                            'dataName' => 'user',
                            'niceName' => 'Asked by',
                            'css' => array(
                                       'width' => '180px'
                                     )
                          ),   
                          array(
                            'niceName' => 'Options',
                            'content' => $toolSet ,
                            'css' => array(
                                       'width' => '205px'
                                     )
                          )
                        )
                      );
  
  // Render & output content
  print $questions->render();
  
?>