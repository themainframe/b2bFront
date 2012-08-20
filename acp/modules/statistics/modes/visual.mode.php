<?php
/**
 * Module: Statistics
 * Mode: Visualisation Plugins
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

$plugin = $BF->in('plugin');

if($plugin)
{

  // Try to load the plugin
  $XMLfile = BF_ROOT . '/extensions/statistics_plugins/' . $plugin . '/plugin.xml';
  
  if(!Tools::exists($XMLFile))
  {
    $BF->admin->notifyMe('Unable to load plugin', 'Critical files are missing from the bundle.', 
      'cross-circle.png');
    $BF->go('./?act=statistics&mode=visual');
  }
  
  $XMLdata = simplexml_load_file($XMLfile);
  
  // Check XML data
  if(!$XMLdata)
  {
    $BF->admin->notifyMe('Unable to load plugin', 'Cannot read configuration file.', 'cross-circle.png');
    $BF->go('./?act=statistics&mode=visual');
  }
  

?>

<h1 style="float: left;"><?php print $XMLdata->title; ?></h1>
<h1 style="float: right; color: #afafaf;">
  <a href="./?act=statistics&mode=visual"
    style="color: #afafaf;">
    Back to Statistics Visualisations...
  </a>
</h1>
<br class="clear" />

<br />

<?php

  // Permission to run
  define('BF_CONTEXT_PLUGIN_ENV', true);
  
  // Load the include
  include BF_ROOT . '/extensions/statistics_plugins/' . $plugin . '/plugin.php';

}
else
{

?>

<h1>Statistics Visualisations</h1>
<br />

<div class="panel">
  <div class="contents">    
    <h3>About Statistics Visualisations</h3>
    <p>
      Statistics Visualisations are different ways of viewing your statistics.<br />
      The visualisation plugins below are ready to view instantly.<br /><br />
      
      b2bFront checks for plugins automatically in <tt>/extensions/statistics_plugins/</tt><br />
      You can install more Visualisation Plugins at <a href="http://my.b2bfront.com/" title="My b2bFront" target="_blank" class="new">my.b2bFront.com</a>
    </p>
  </div>
</div>

<br />

<?php

  // Plugin discovery
  $fileListing = Tools::listDirectory(BF_ROOT . '/extensions/statistics_plugins/');
?>

<table id="t1" class="data">
  <thead>
    <tr class="header">
      <td style="width: 16px;">
        &nbsp;
      </td>
      <td>
        Plugin Name
      </td>
      <td>
        Description
      </td>
    </tr>
  </thead>
  <tbody>
<?php
  
  // Show all plugins
  foreach($fileListing as $plugin)
  {

    $XMLfile = BF_ROOT . '/extensions/statistics_plugins/' . $plugin . '/plugin.xml';
    $XMLdata = simplexml_load_file($XMLfile);

?>
    <tr class="row">
      <td><img class="middle" src="/extensions/statistics_plugins/<?php print $plugin; ?>/<?php print $XMLdata->icon; ?>" /></td>
      <td class="name"><a href="./?act=statistics&mode=visual&plugin=<?php print $plugin; ?>"><?php print $XMLdata->title; ?></a></td>
      <td style="color: #afafaf"><?php print $XMLdata->description; ?></td>
    </tr>
    
<?php
  }
?>
      
  </tbody>
</table>

<?php
}
?>