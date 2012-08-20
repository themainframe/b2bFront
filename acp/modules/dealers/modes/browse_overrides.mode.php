<?php
/**
 * Module: Dealers
 * Mode: Modify
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

// Get the ID
$ID = $BF->inInteger('id');

// Get the row information
$BF->db->select('*', 'bf_users')
           ->where('id = \'{1}\'', $ID)
           ->limit(1)
           ->execute();
           
// Check the ID was valid
if($BF->db->count < 1)
{
  // Return the user to the selection interface
  header('Location: ./?act=dealers&mode=browse');
  exit();
}

// Retrieve the row
$row = $BF->db->next();

?>

<h1>Pricing Overrides for <?php print $row->description; ?></h1>
<br />

<div class="panel">
  <div class="contents">
  
      <h3>About Pricing Overrides</h3>
      
      <p>
        Pricing Overrides allow you to offer specific items to specific dealers at fixed prices.<br />
        If a Pricing Override is present for a dealer and a specific item, the override price will <em>always</em>
        be used.
        
        <br /><br />
        
        You should consider creating a <a href="./?act=dealers&mode=bands" target="_blank" class="new">Discount Band</a> if you need to discount a large number
        of dealers or items. 
      </p>
        
      <br />
      
      <form id="add_form" action="./?act=dealers&mode=browse_overrides_add_do" method="post">
        <input type="hidden" name="f_uid" value="<?php print $BF->inInteger('id'); ?>" id="uid" />
        <input type="hidden" name="f_ids" id="item-ids" value="" />
      </form>
      
      <span class="button">
        <a href="#" onclick="selectItems(true, function(d) { $('#item-ids').val(d.join(',')); $('#add_form').submit(); })">
          <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)">&nbsp;</span>
          Add Items...
        </a>
      </span>
      
      </form>
      
      <br /><br />
      
  </div>
</div>


<br />

<?php

  // Create a query
  $query = $BF->db->query();
  $query->text(str_replace("\n", '', '
  
    SELECT `bf_user_prices`.`trade_price` AS `trade_price`,
           `bf_user_prices`.`pro_net_price` AS `pro_net_price`,
           `bf_user_prices`.`id` AS `id`,
           
           `bf_items`.`name` AS `name`,
           `bf_items`.`sku` AS `sku`

    FROM `bf_user_prices` INNER JOIN `bf_items` ON `bf_user_prices`.`item_id` = `bf_items`.`id`
    
    WHERE `bf_user_prices`.`user_id` = ' . $BF->inInteger('id') . '
      
    GROUP BY `bf_user_prices`.`id`
    
  ') . ' ');
  
  // Define boolean columns CSS text
  $columnCSS = array(
    'width' => '89px'
  );
  
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this Pricing Override?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'browse_overrides_remove_do')) . 
                    '&uid=' . $BF->inInteger('id') . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";

  // Create a data table view
  $overrides = new DataTable('pr1', $BF, $query);
  $overrides->setOption('alternateRows');
  $overrides->setOption('showTopPager');
  $overrides->addColumns(array(
                            array(
                              'dataName' => 'sku',
                              'niceName' => 'SKU',
                              'css' => array(
                                         'width' => '50px' 
                                       )
                            ),
                            array(
                              'dataName' => 'name',
                              'niceName' => 'Item Name'
                            ),
                            array(
                              'dataName' => 'trade_price',
                              'niceName' => 'Override Trade Price',
                              'css' => $columnCSS,
                              'options' => array(
                                             'editable' => true,
                                             'editableTable' => 'bf_user_prices',
                                             'editableCache' => 'user-' . $BF->inInteger('id')
                                           )
                            ),
                            array(
                              'dataName' => 'pro_net_price',
                              'niceName' => 'Override Pro Net Price',
                              'css' => $columnCSS,
                              'options' => array(
                                             'editable' => true,
                                             'editableTable' => 'bf_user_prices',
                                             'editableCache' => 'user-' . $BF->inInteger('id')
                                           )
                            ),
                            array(
                              'niceName' => 'Options',
                              'content' => $toolSet ,
                              'css' => array(
                                         'width' => '75px'
                                       )
                            )
                          )
                        );
  
  // Render & output content
  print $overrides->render();

?>