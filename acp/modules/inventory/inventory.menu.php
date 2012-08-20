<?php
/**
 * Admin Module Menu File : Inventory
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

// Get primary mode
$primaryMode = Tools::valueAt(explode('_', $BF->in('mode')), 0);

?>
        <li class="<?=Tools::conditional('browse', $primaryMode, 'selected')?>">
          <a href="./?act=inventory&mode=browse" style="background-image: url(./static/icon/magnifier.png);">Browse</a>
        </li>
        <li class="<?=Tools::conditional('add', $primaryMode, 'selected')?>">
          <a href="./?act=inventory&mode=add" style="background-image: url(./static/icon/plus-circle.png);">Add</a>
        </li>
        <li class="<?=Tools::conditional('organise', $primaryMode, 'selected')?>">
          <a href="./?act=inventory&mode=organise" style="background-image: url(./static/icon/folder-open.png);">Categories</a>
        </li>
        <li class="<?=Tools::conditional('classifications', $primaryMode, 'selected')?>">
          <a href="./?act=inventory&mode=classifications" style="background-image: url(./static/icon/zones.png);">Classifications</a>
        </li>
        <li class="<?=Tools::conditional('tags', $primaryMode, 'selected')?>">
          <a href="./?act=inventory&mode=tags" style="background-image: url(./static/icon/tags.png);">Item Tags</a>
        </li>
        <li class="<?=Tools::conditional('brands', $primaryMode, 'selected')?>">
          <a href="./?act=inventory&mode=brands" style="background-image: url(./static/icon/reg-trademark.png);">Brands</a>
        </li>
        <li class="<?=Tools::conditional('outlets', $primaryMode, 'selected')?>">
          <a href="./?act=inventory&mode=outlets" style="background-image: url(./static/icon/store.png);">Outlets</a>
        </li>
        <li class="<?=Tools::conditional('arrivals', $primaryMode, 'selected')?>">
          <a href="./?act=inventory&mode=arrivals" style="background-image: url(./static/icon/wooden-box-label.png);">Back In Stock</a>
          
<?php

  // Badges?
  $arrivalCount = $BF->db->query()
                         ->select('*,`bf_items`.`id` AS itemid', 
                            '`bf_items`,  `bf_stock_replenishments`')
                         ->where('`bf_items`.`id` = `bf_stock_replenishments`.`item_id` AND `bf_items`.`stock_free` > 0')
                         ->execute()
                         ->count;
                         
  if($arrivalCount > 0)
  {
    // Print badge
    print '          <span class="badge">' . 
          $arrivalCount . '</span>';
  }

?>
          
        </li>
        <li class="<?=Tools::conditional('requests', $primaryMode, 'selected')?>">
          <a href="./?act=inventory&mode=requests" style="background-image: url(./static/icon/flag.png);">Requests</a>
          
<?php

  // Badges?
  $requestCount = $BF->db->query();
  $requestCount->text('SELECT `bf_items`.`id` AS id, `bf_items`.`name`, `bf_items`.`sku`, COUNT(`bf_users`.`id`) AS `dealers` FROM `bf_items`, `bf_user_stock_notifications`, `bf_users` ' . 
    'WHERE `bf_users`.`id` = `bf_user_stock_notifications`.`user_id` AND `bf_items`.`id` = `bf_user_stock_notifications`.`item_id` GROUP BY `bf_user_stock_notifications`.`item_id`');
  $requestCount = $requestCount->execute()->count;
                         
  if($requestCount > 0)
  {
    // Print badge
    print '          <span class="badge grey">' . 
          $requestCount . '</span>';
  }

?>
          
        </li>