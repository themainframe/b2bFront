<?php
/**
 * Preview Mail
 * External View
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass(true);
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

// Load mail template
$mailTemplateName = str_replace('..', '', $BF->in('name'));

// Load file
$XMLfile = BF_ROOT . '/extensions/mail_templates/' . $mailTemplateName . '/template.xml';
$XMLdata = simplexml_load_file($XMLfile);
$templateTitle = (string)$XMLdata->description;
$templateContentName = (string)$XMLdata->content;

// Build path
$contentPath = BF_ROOT . '/extensions/mail_templates/' . $mailTemplateName . 
  '/' . $templateContentName;

// Content exists?
if(Tools::exists($contentPath))
{
  // Load file
  $fileContent = file_get_contents($contentPath);
  
  // Replace URL
  $fileContent = str_replace('{url}', 
    $BF->config->get('com.b2bfront.site.url'), $fileContent);

  // Replace Date
  $fileContent = str_replace('{date}', 
    Tools::longDate(), $fileContent);
 
  // Replace Title
  $fileContent = str_replace('{title}', 
    'A Sample Newsletter', $fileContent);
 
  // Replace Content
  $fileContent = str_replace('{content}', 
    str_repeat(Tools::loremIpsum(true), 2), $fileContent);
 
  
  // Output
  print $fileContent;
}
else
{
  $BF->log('Mail Templates', 'Template data for ' . $templateContentName . 
           ' could not be loaded.');
  print 'Template data for ' . $templateContentName . 
    ' could not be loaded.';
}

?>