<?php
/**
 * Module: Dealers
 * Mode: Bulk Preview
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

ob_clean();

print $email->getText();
exit();

$result = true;

?>