<?php
/**
 * Module: Dealers
 * Mode: Do Add
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
  
    'name' => array(
    
               'validations' => array(
                                 'unique' => array('bf_users'),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_name'),
               
               'name' => 'Name'
                   
              ),

    'password' => array(
    
               'validations' => array(
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_password'),
               
               'name' => 'Password'
                   
              ),

    'email' => array(
    
               'validations' => array(
                                 'email' => array(),
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_email'),
               
               'name' => 'Email'
                   
              ),
              
    'description' => array(
    
               'validations' => array(
                                 'done' => array()
                                ),
                                
               'value' => $BF->in('f_description'),
               
               'name' => 'Description'
                   
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
    $BF->admin->packAndRedirect('./?act=dealers&mode=add',
                                    $fieldName, (string)$validator);
                                    
    exit();
  }
}

$result = $BF->admin->api('Dealers')
                        ->add($BF->in('f_name'),
                              $BF->in('f_password'),
                              $BF->in('f_email'),
                              $BF->in('f_description'),
                              $BF->in('f_dealer_profile'),
                              $BF->in('f_account_code'),
                              $BF->in('f_address_building'),
                              $BF->in('f_address_street'),
                              $BF->in('f_address_city'),
                              $BF->in('f_address_postcode'),
                              $BF->in('f_phone_landline'),
                              $BF->in('f_phone_mobile'),
                              $BF->in('f_url'),
                              $BF->in('f_slogan'),
                              $BF->in('f_locale'),
                              $BF->in('f_bulk_exclude'),
                              $BF->in('f_dealer_band'),
                              $BF->admin->AID,
                              $BF->in('f_in_directory'));
   

$name = $BF->in('f_name');
$password = $BF->in('f_password');

// Decide notifications
if($BF->in('f_notify_email'))
{
  //
  // Notify via email
  //

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
  $email->addRecipient($BF->in('f_email'), array());
  
  // Set Subject Line
  $email->setSubject('Your new ' . $BF->config->get('com.b2bfront.site.title', true) . ' Account');
  
  // Build content
  $content = '
  
  Hello ' . $BF->in('f_description') . ',
  <br /><br />
  
  Your ' . $BF->config->get('com.b2bfront.site.title', true) . ' account is now ready! <br /><br />
  
  Please visit <a href="' . $BF->config->get('com.b2bfront.site.url', true) . '">' . 
  $BF->config->get('com.b2bfront.site.url', true) . '</a> and log in with these details to get started:
  <br /><br />
  
  <strong>Username</strong>: ' . $BF->in('f_name') . ' <br />
  <strong>Password</strong>: ' . $BF->in('f_password') . '<br /><br />
  
  If you have any questions at all, please do not hesitate to contact us at 
  <a href="mailto:' . $BF->config->get('com.b2bfront.crm.support-email', true) . '">' . 
  $BF->config->get('com.b2bfront.crm.support-email', true) . '</a>

  <br /><br /><br />
  <strong>
    Thanks,<br />
    ' . $BF->config->get('com.b2bfront.site.title', true) . '
  </strong>';
  
  // Set template values
  $email->assign(array(
    'date' => Tools::longDate(),
    'title' => 'Your new ' . $BF->config->get('com.b2bfront.site.title', true) . ' Account',
    'content' => $content,
    'url' => $BF->config->get('com.b2bfront.site.url', true) 
  ));
  
  // Send
  $email->send();
}

if($BF->in('f_notify_sms') && $BF->in('f_phone_mobile'))
{
  // Notify via SMS
  $smsMessage =  'Your ' . $BF->config->get('com.b2bfront.site.short-url', true) .
                 ' account details:' . "\n\n";
  $smsMessage .= 'User: ' . $name . "\n";
  $smsMessage .= 'Password: ' . $password;
  
  // Send SMS
  $smsObject = new SMS(& $BF);
  $smsObject->send($smsMessage, $BF->in('f_phone_mobile'));
}

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The Dealer ' . $BF->in('f_name') . ' was created.');
  header('Location: ./?act=dealers&letter=' . $name[0]);
}

?>