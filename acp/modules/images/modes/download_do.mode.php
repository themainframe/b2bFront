<?php
/**
 * Module: Images
 * Mode: Download All
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

if(file_exists('/tmp/imbundle/'))
{
?>

<h1>Download All Images</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>Already in progress</h3>
    <p>
      A bundle build is already in progress.<br />
      Please wait for the current build to complete before starting another.<br />
    </p>
    
  </div>
</div>

<?php
}
else
{
?>

<h1>Download All Images</h1>
<br />

<div class="panel">
  <div class="contents">
    <p>
      The download is now being built.<br />
      You will be notified when the download is ready.<br />
      You can continue to use the ACP while the bundle is being built.<br /><br />
      
      The download will be placed in the <strong>My Downloads</strong> section of the ACP when ready.<br /><br />
      
      Go to the <a href="./">ACP Dashboard</a>.
    </p>
    
  </div>
</div>

<?php

}

// Non Blocking mode
Tools::nonBlockingMode();
set_time_limit(0);

// Start building the zip in /tmp
system('mkdir /tmp/imbundle');

// Get all categories
$categories = $BF->db->query();
$categories->select('*', 'bf_categories')
           ->order('name', 'asc')
           ->execute();
           
// Get all images
$images = $BF->db->query();
$images->select('*', 'bf_images')
       ->execute();
      
$imgs = array();
while($image = $images->next())
{
  $imgs[$image->id] = $image->url;
}
           
while($category = $categories->next())
{  
  // Excluded?
  if($BF->in('images_download_' . $category->id) != '1')
  {
    // Skip
    continue;
  }
  
  $BF->log('Images', 'Processing category ' . $category->name);

  // Make a directory
  $dirName = 
    str_replace('/', '', str_replace(',', '', 
    str_replace('&', '', str_replace(',', '', 
    str_replace(' ', '', trim($category->name))))));
  system('mkdir /tmp/imbundle/' . $dirName);
  
  $BF->log('Images', 'Created directory ' . $dirName);
  
  // Find all items
  $items = $BF->db->query();
  $items->select('*', 'bf_items')    
        ->where('`category_id` = \'{1}\'', $category->id)
        ->order('sku', 'asc')
        ->execute();
        
  while($item = $items->next())
  {
    // Make the item directory
    $itemDir = '/tmp/imbundle/' . $dirName . '/' . $item->sku . '/';
    system('mkdir ' . $itemDir);
    
    // Make SKU description?
    if($BF->in('f_description_files') == '1')
    {
      // Write SKU description to file
      file_put_contents($itemDir . '/' . $item->sku . '-description.html', 
        '<h3 style="font-family: Arial, verdana,sans-serif">' . $item->name . '</h3>' . "\n\n" .
        $item->description);
    }
    
    // Find all images
    $itemImages = $BF->db->query();
    $itemImages->select('*', 'bf_item_images')
               ->where('`item_id` = \'{1}\'', $item->id)
               ->execute();
               
    while($image = $itemImages->next())
    {
      // Add to the directory
      $path = str_replace($BF->config->get('com.b2bfront.site.url', true), BF_ROOT, $imgs[$image->image_id]);
      system('cp ' . $path . ' ' . $itemDir);
      $BF->log('Images', 'Copying image file: ' . $path . ' to ' . $itemDir);
    }
  }
  
  $BF->log('Images', 'Done processing category ' . $category->name);
}

// Zip up the directory, surpressing output
$retValue = '';
$BF->log('Images', 'Creating archive.');
system('cd /tmp/imbundle/ && zip -r /tmp/imbundle.zip . > /dev/null', &$retValue);
$BF->log('Images', 'Archive creation completed: ' . $retValue);

// Remove the old imbundle staging directory
system('rm /tmp/imbundle -rf');
$BF->log('Images', 'Removed staging directory /tmp/imbundle');

// Move the zipped bundle to an area where it can be downloaded.
$dlName = 'all-images-' . date('m-d-y') . '-' . rand(0, 99999999) . '.zip';
system('mv /tmp/imbundle.zip ' . BF_ROOT . '/temp/' . $dlName);

// Create a download for 24 hours...
$BF->setFileTTL(BF_ROOT . '/temp/' . $dlName, 86400);
$BF->log('Images', 'Setting TTL on ' . $dlName);

// Create a download
$BF->db->insert('bf_admin_downloads', array(
           'name' => $dlName,
           'path' => Tools::relativePath(BF_ROOT . '/temp/' . $dlName),
           'timestamp' => time(),
           'admin_id' => $BF->admin->AID
         ))
       ->execute();

$BF->log('Images', 'Download created.');

// Send a notification to say the download is ready
$BF->admin->notifyMe('Image Bundle Ready', 
  'Visit the <a href="./?act=dashboard&mode=downloads">My Downloads</a>' . 
  ' section of the ACP to download it.', 
  'tick-circle.png');

// Clean up
$BF->shutdown();

?>