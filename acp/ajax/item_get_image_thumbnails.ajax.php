<?php
/**
 * Get Item Thumbnails
 * AJAX Responder
 *
 * Returns the URLs of an items thumbnails as a JSON hash.
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

// Content type
header('Content-type: text/json');

if(!$BF->admin->isAdmin)
{
  exit();
}

// Find all the images for this item
$BF->db->select('`bf_images`.*', 'bf_images, bf_item_images')
           ->where('`bf_item_images`.`item_id` = \'{1}\' AND `bf_item_images`.`image_id` = `bf_images`.`id`', $BF->inInteger('id'))
           ->order('`bf_item_images`.`priority`', 'DESC')
           ->execute();

// Produce JSON
$arrayOutput = array();

while($image = $BF->db->next())
{
  
  // Transform to thumbnail URL
  $url = Tools::getImageThumbnail($image->url);

  $arrayOutput[$image->id] = array(
    'url' => $url
  );
}

// Output
print json_encode($arrayOutput);

?>