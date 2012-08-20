<?php
/**
 * Module: Website
 * Mode: Skin Chooser
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

<h1>Skin</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Skins</h3>
    <p>
      Skins define the look &amp; feel of the website.<br />
      They dictate colours, fonts, layouts and structure of pages.
    </p>
  </div>
</div>

<br /> 

<div class="panel">
  <div class="title">Installed Skins</div>
  <div class="contents">
    <table style="width: 100%;">
<?php

  // Find all skins
  $skinsRoot = BF_ROOT . '/skins/';
  $skinDirectoryListing = Tools::listDirectory($skinsRoot);
  $currentSkin = $BF->config->get('com.b2bfront.site.skin', true);
  
  foreach($skinDirectoryListing as $skinName)
  {
    // Try to load information
    $skinPath = $skinsRoot . '/' . $skinName . '/';
    
    if(file_exists($skinPath . 'skin.xml'))
    {
      // Read with SimpleXML
      $xml = simplexml_load_file($skinPath . 'skin.xml');
      
      // Define button
      $buttonSet  = "\n";
      $buttonSet .= '    <span class="button">' . "\n";
      $buttonSet .= '      <a href="./?act=website&mode=skin_set&name={name}">' . "\n";
      $buttonSet .= '        <span class="img" style="background-image:' . 
                    'url(/acp/static/icon/tick-button.png)">&nbsp;</span>' . "\n";
      $buttonSet .= '        Apply Skin' . "\n";
      $buttonSet .= '      </a>' . "\n";
      $buttonSet .= '    </span>' . "\n";
      
      // Show a row
      print '      <tr class="skinchooser_row' . 
            ($skinName == end($skinDirectoryListing) ? ' last' : '') . '">' . "\n";
      print '        <td class="skinchooser_preview" style="width: 260px;">' . "\n";
      print '          <img class="preview" style="width: 250px;" src="' . 
            $BF->config->get('com.b2bfront.site.url', true) .
            '/skins/' . $skinName . '/' . (string)$xml->preview->attributes()->name . '" />' . "\n";
      print '        </td>' . "\n";
      print '        <td class="skinchooser_info">' . "\n";
      print '          <strong>' . (string)$xml->title . '</strong>' . 
            (str_replace('.skin', '', $skinName) == $currentSkin ? '&nbsp; <em>(Current Skin)</em>' : '') . 
            '<br /><br />' . "\n";
      print '          ' . (string)$xml->description . '<br /><br />' . "\n";
      print '          By: <a target="_blank" class="new" href="' . 
            $xml->url . '">' . (string)$xml->author . '</a><br /><br />' . "\n";
      print '          <span class="grey">For ' . (string)$xml->target . '</span><br />' . "\n";
      print '        </td>' . "\n";
      print '        <td class="skinchooser_controls">' . "\n";
      print '          ' . (str_replace('.skin', '', $skinName) == $currentSkin ? 
            '' : str_replace('{name}', $skinName, $buttonSet)) . "\n";
      print '        </td>' . "\n";
      print '      </tr>' . "\n";
    }
  }

?>
    </table>
  </div>
</div>