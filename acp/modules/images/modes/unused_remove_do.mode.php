<?php
/**
 * Module: Images
 * Mode: Do Remove Unused Image
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

$imageID = $BF->inInteger('id');
$image = $BF->db->getRow('bf_images', $imageID);

// Use API to remove the image
$BF->admin->api('Images')
          ->remove($imageID);

if($result !== false)
{
  $BF->admin->notifyMe('OK', 'The image \'' . basename($image->url) . '\' was removed.');
  $BF->go('./?act=images&mode=unused');
}

?>