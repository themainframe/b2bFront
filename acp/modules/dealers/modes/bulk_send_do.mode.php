<?php
/**
 * Module: Dealers
 * Mode: Do Send
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

@set_time_limit(0);

// Build the validation array
$validation = array(
  
    'subject' => array(
        
                   'validations' => array(
                                     'done' => array()
                                    ),
                                    
                   'value' => $BF->in('f_subject'),
                   
                   'name' => 'Subject'
                       
                  )
    
);

// Check each field
foreach($validation as $fieldName => $fieldData)
{
  // Create a validator
  $validator = new FormValue($fieldData['value'], $fieldData['name'], & $BF);

  // Check
  if($validator->batch($fieldData['validations'])->failed())
  {
    // Failed - Pack up fields and redirect
    $BF->admin->packAndRedirect('./?act=dealers&mode=bulk',
                                $fieldName, (string)$validator);
                                    
    exit();
  }
}

// Find each dealer
$dealers = $BF->db->query();
$dealers->select('*', 'bf_users');

// Opt-out mechanism?
if($BF->config->get('com.b2bfront.mail.honour-opt-outs', true))
{
  $dealers->where('`include_in_bulk_mailings` = 1');
}

// Find
$dealers->execute();

// Create email object
$email = new Email(& $BF);

// Load mail template
$mailTemplateName = str_replace('..', '', $BF->in('f_template'));

// Load file
$XMLfile = BF_ROOT . '/extensions/mail_templates/' . $mailTemplateName . '/template.xml';
$XMLdata = simplexml_load_file($XMLfile);
$templateTitle = (string)$XMLdata->description;
$templateContentName = (string)$XMLdata->content;

// Build path
$contentPath = BF_ROOT . '/extensions/mail_templates/' . $mailTemplateName . 
  '/' . $templateContentName;

$email->loadFromFile($contentPath);

// Send mail to each
while($dealer = $dealers->next())
{
  if($dealer->email == '')
  {
    // Don't send to empty emails or emails to empty addresses
    continue;
  }

  // Provide dealer information too
  $email->addRecipient($dealer->email, (array)$dealer);
}

// Set subject
$email->setSubject($BF->in('f_subject'));

// Set global template values
$email->assign(array(
  'date' => Tools::longDate(),
  'title' => $BF->in('f_subject'),
  'content' => $BF->inUnfiltered('f_content'),
  'url' => $BF->config->get('com.b2bfront.site.url', true),
  'unsubscribe' => $BF->config->get('com.b2bfront.site.url', true) . '/unsubscribe/'
));

// Save mail to disk
$storePath = Tools::randomPath(BF_ROOT . '/store/mail/', 'html', 'mail');

// Add name to the footer
$email->assign(array(
  'hardlink' => $BF->config->get('com.b2bfront.site.url', true) .
                '/store/mail/' . basename($storePath)
));

// Write
touch($storePath);
file_put_contents($storePath, $email->getText());

?>

<h1>Bulk Email Dealers</h1>
<br />
<div class="panel">
  <div class="contents" style="">
    <h3>Sending</h3>
    <p>
      The bulk email message is being sent.<br />
      You may continue to use b2bFront while this operation is in progress.<br /><br />
      
      You do not need to stay on this page and will be notified when the sending is finished.
    </p>
  </div>
</div>

<?php

// Non Blocking mode
Tools::nonBlockingMode();

// Send mail
$email->sendSleep(2);

// Send a notification to say the sending has finished
$BF->admin->notifyMe('Bulk Email Sending Finished', 
  'The bulk email message sending has finished.', 
  'tick-circle.png');

?>