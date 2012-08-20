<?php 
/**
 * Main Index
 * Acts as the Controller for the MVC system.
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.8
 * @author Damien Walsh
 */

// Set context
define("BF_CONTEXT_INDEX", true);

// Include tools
require_once 'tools.php';

// Begin startup procedures
include 'startup.php';

// Start the main class.
$BF = new BFClass();
global $BF;

// Increment hits on homepage
$BF->stats->increment('com.b2bfront.stats.website.hits', 1);

// Build an option array from a Property List
// This dictates which model file should be loaded
$propertyListParser = new PropertyList();
$optionsPropertyList = $propertyListParser->parseFile(
  BF_ROOT . '/definitions/mvc_actions.plist');

// Valid?
if(!$optionsPropertyList)
{
  throw new Exception('No access to MVC PList at: ' . 
    '/definitions/mvc_actions.plist');
}

// Obtain options array
$options = $optionsPropertyList;

// Look up model to load
if(!array_key_exists($BF->inputs['option'], $options))
{
  // Default model
  $model = $options['home']['model'];
  $view = $options['home']['view'];
}
else
{
  // Load home model
  $model = $options[$BF->inputs['option']]['model'];
  $view = $options[$BF->inputs['option']]['view'];
}

// Override with forced login?
if($BF->config->get('com.b2bfront.security.require-authentication', true))
{
  if($model != 'Portal' && $model != 'Login' && 
     $model != 'Unsubscribe' && !$BF->security->attr('loggedIn'))
  {
    $model = 'Gateway';
    $view = 'gateway';
  }
}

// Render the home layout
$BF->loadView($view);

// Render the model
$BF->renderModel($model);

// Increment data buffer size statistic
$BF->stats->increment('com.b2bfront.stats.system.data-sent', 
  number_format($BF->out->getSize() / 1024, 2));

// Clean up
$BF->shutdown();
?>