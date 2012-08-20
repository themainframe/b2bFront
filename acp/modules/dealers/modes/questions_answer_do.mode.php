<?php
/**
 * Module: Dealers
 * Mode: Do Answer Question
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


// Mark as answered
$BF->db->update('bf_questions', array(
           'answered' => 1
         ))
       ->where("`id` = '{1}'", $BF->inInteger('f_id'))
       ->limit(1)
       ->execute();


$BF->db->insert('bf_question_answers', array(
           'content' => $BF->inUnfiltered('f_content'),
           'timestamp' => time(),
           'question_id' => $BF->inInteger('f_id'),
           'admin_id' => $BF->admin->AID
         ))
       ->execute();
       
// Build an email
$templateName = $BF->config->get('com.b2bfront.mail.default-template', true);
$XMLfile = BF_ROOT . '/extensions/mail_templates/' . $templateName . '/template.xml';

// Load XML
$XMLdata = simplexml_load_file($XMLfile);
$templateTitle = (string)$XMLdata->description;
$templateContentName = (string)$XMLdata->content;

// Build path
$contentPath = BF_ROOT . '/extensions/mail_templates/' . $templateName . 
  '/' . $templateContentName;
  
// Create email object
$email = new Email($BF);
$email->loadFromFile($contentPath);

// Add recipient to the email
$email->addRecipient($user->email, (array)$user);

// Set Subject Line
$email->setSubject('Answer to your question regarding ' . $item->sku);

// Build content
$content  = '

Hello ' . $user->description . ',
<br /><br />

With regards to your question about ' . $item->sku . ': <br /><br />

' . $BF->inUnfiltered('f_content') . '

<br /><br /><br />
<strong>
  Thanks,<br />
  ' . $BF->config->get('com.b2bfront.site.title', true) . '
</strong>
';

// Set template values
$email->assign(array(
  'date' => Tools::longDate(),
  'title' => $subject,
  'content' => $content,
  'url' => $BF->config->get('com.b2bfront.site.url', true) 
));

// Send
$email->send();


$BF->admin->notifyMe('OK', 'Your answer has been sent to ' . $user->description . '.');
header('Location: ./?act=dealers&mode=questions');

?>