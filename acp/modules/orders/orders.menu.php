<?php
/**
 * Admin Module Menu File : Orders
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN"))
{
  exit();
}

// Gain access to BFClass
global $BF;

// Verify Permissions
if($BF->admin->can('orders'))
{
  
  // Get primary mode
  $primaryMode = Tools::valueAt(explode('_', $BF->in('mode')), 0);
  
  ?>
          <li class="<?=Tools::conditional('unprocessed', $primaryMode, 'selected')?>">
            <a href="./?act=orders&mode=unprocessed" style="background-image: url(./static/icon/inbox--exclamation.png);">Unprocessed</a>
            
  <?php
  
    // Badges?
    $unprocessedOrderCount = $BF->admin->api('Orders')->countUnprocessed();
    if($unprocessedOrderCount > 0)
    {
      // Print badge
      print '          <span class="badge">' . 
            $unprocessedOrderCount . '</span>';
    }
  
  ?>
            
          </li>
          <li class="<?=Tools::conditional('processed', $primaryMode, 'selected')?>">
            <a href="./?act=orders&mode=processed" style="background-image: url(./static/icon/inbox.png);">Processed</a>
          </li>
          <li class="<?=Tools::conditional('held', $primaryMode, 'selected')?>">
            <a href="./?act=orders&mode=held" style="background-image: url(./static/icon/exclamation-octagon.png);">Held</a>
            
  <?php
  
    // Badges?
    $heldOrderCount = $BF->admin->api('Orders')->countHeld();
    if($heldOrderCount > 0)
    {
      // Print badge
      print '          <span class="badge">' . 
            $heldOrderCount . '</span>';
    }
  
  ?>
            
          </li>
          
<?php
}
?>