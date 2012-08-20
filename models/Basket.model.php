<?php
/** 
 * Model: Basket
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Basket extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();
    
    // Verify security
    if(!$this->parent->security->can('can_order'))
    {
      // Override the view
      $this->parent->loadView('alert');
      $this->addValue('alertText', '<strong>This account cannot place orders.</strong>' . 
        '<br /><br />Please contact us to inquire about ordering!');
      
      // Stop rendering model
      return false;
    }
    
    // Set count
    $this->addValue('basketCount', $this->parent->cart->count());
    
    //
    // Remove item?
    //
    if($this->parent->in('remove'))
    {
      $removeID = $this->parent->inInteger('remove');
      $this->parent->cart->remove($removeID);
      //$this->addValue('notification', 'Your basket has been updated.');
      $this->addValue('basketCount', $this->parent->cart->count());
    }
    
    //
    // Add item?
    //
    if($this->parent->in('add'))
    {
      $addID = $this->parent->inInteger('add');
      $this->parent->cart->add($addID, $this->parent->inInteger('qty'));
      //$this->addValue('notification', 'Your basket has been updated.');
      $this->addValue('basketCount', $this->parent->cart->count());
    }
    
    //
    // Clear?
    //
    if($this->parent->in('clear'))
    {
      $this->parent->cart->clear();
      $this->parent->stats->increment('com.b2bfront.stats.users.baskets-cleared', 1);
      $this->addValue('notification', 'Your basket has been cleared.');
      $this->addValue('basketCount', 0);
    }
    
    // 
    // Multi modification?
    //
    if($this->parent->in('multi'))
    {
      // Get a full copy of inputs
      $inputs = $this->parent->allIn();
      
      // Find quantities
      foreach($inputs as $key => $value)
      {
        if(substr($key, 0, 4) == 'qty_')
        { 
          // Get Item ID and quantity value
          $itemID = intval(substr($key, 4));
          $quantity = intval($value);
          
          // Set
          $this->parent->cart->add($itemID, $quantity);
        }
      }
      
      // Provide a button to the return-page
      $returnURL = $this->parent->in('rtn_url');
      $this->addValue('rtn_url', $returnURL);
      //$this->addValue('notification', 'Your basket has been updated.');
      $this->addValue('basketCount', $this->parent->cart->count());
    }
    
    // Set this models preferences
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - Basket');
    $this->addValue('tab_basket', 'selected');
    
    // Update CCTV
    $this->parent->security->action('Basket');
    
    // Load all basket items
    $basketItems = $this->parent->db->query();
    $basketItems->select('`bf_user_cart_items`.*, `bf_items`.*, `bf_items`.`id` AS itemid',
                         'bf_user_cart_items')
                ->text('LEFT OUTER JOIN `bf_items` ON `bf_user_cart_items`.`item_id` = ' .
                     '`bf_items`.`id` ')
                ->where('`bf_user_cart_items`.`user_id` = \'{1}\'', $this->parent->security->UID)
                ->group('`bf_user_cart_items`.`id`');
    
    // Define button to remove items
    $removeButton  = "\n";
    $removeButton .= '<a href="./?option=basket&remove={itemid}" class="shadowbox">' . "\n";
    $removeButton .= '  <img src="/share/icon/general/cross-circle.png" ' . 
                     'class="middle" alt="Cross" />' . "\n";
    $removeButton .= '  Remove' . "\n";
    $removeButton .= '</a>' . "\n";
    
    // Callback for quantity field generation
    $quantityField = function($row, $parent)
    {
      return '<input tabindex="1" type="text" value="' . $row->quantity . 
             '" style="width: 40px;" name="qty_' . $row->itemid . '" />';
    };
    
    // Keep track of total
    $GLOBALS['total'] = 0.00;

    // Callback for price calculation
    $calculatePrice = function($row, $parent)
    {
      $pricer = new Pricer(& $parent);
      $subtotal = $pricer->subtotal($row, $row->quantity);
      
      // Update total
      $GLOBALS['total'] += $subtotal;
      
      // Subtotal allowed?
      if(!$parent->security->hasPermission('can_see_prices'))
      {
        $subtotal = '';
      }
      
      return $subtotal;
    };
    
    // Callback for the price of each item
    $eachPrice = function($row, $parent)
    {
      // Subtotal allowed?
      if(!$parent->security->hasPermission('can_see_prices'))
      {
        return '';
      }
    
      $pricer = new Pricer(& $parent);
      return $pricer->each($row, $row->quantity);
    };
    
    // Construct table
    $dataView = new DataTable('basketItems', $this->parent, $basketItems);
    $dataView->setOption('alternateRows');
    $dataView->setOption('noDataText', 'Your basket is currently empty.');
    $dataView->addColumns(
                           array(
                             array(
                               'dataName' => '',
                               'niceName' => '',
                               'options' => array(
                                              'callback' => 
                                                $this->parent->images->loadThumbnail,
                                              'formatAsImage' => true,
                                              'cellCss' => array(
                                                           'background' => '#fff'
                                                           ),
                                            'fixedOrder' => true
                                          ),
                               'css' => array(
                                          'width' => '40px'
                                        )
                             ),
                             array(
                               'dataName' => 'sku',
                               'niceName' => 'SKU',
                               'css' => array(
                                          'width' => '60px'
                                        )
                             ),
                             array(
                               'dataName' => 'name',
                               'niceName' => 'Name',
                               'options' => array(
                                              'formatAsLink' => true,
                                              'linkURL' =>
                                                '/?option=item&id={id}&p={is_parent}'
                                            )
                             ),
                             array(
                               'dataName' => 'trade_price',
                               'niceName' => 'Price',
                               'options' => array(
                                              'callback' => $eachPrice,
                                              'formatAsPrice' => true,
                                              'fixedOrder' => true
                                            ),
                               'css' => array(
                                          'width' => '60px'
                                        )
                             ),
                             array(
                               'dataName' => 'quantity',
                               'niceName' => 'Quantity',
                               'options' => array(
                                              'callback' => $quantityField
                                            ),
                               'css' => array(
                                          'width' => '70px'
                                        )
                             ),
                             array(
                               'dataName' => 'trade_price',
                               'niceName' => 'Overall',
                               'options' => array(
                                              'callback' => $calculatePrice,
                                              'formatAsPrice' => true,
                                              'fixedOrder' => true
                                            ),
                               'css' => array(
                                          'width' => '70px'
                                        )
                             ),
                             array(
                               'dataName' => '',
                               'niceName' => '',
                               'css' => array(
                                          'width' => '80px'
                                        ),
                               'options' => array(
                                              'newContent' => $removeButton,
                                              'fixedOrder' => true
                                            )
                             )
                           )
                         );

    // Add the table to the view template
    $this->addValue('table', $dataView->render());
    
    // Provide total
    $this->addValue('total', 
      $this->parent->security->hasPermission('can_see_prices') ? $GLOBALS['total'] : '');
          
    // Provide return URL
    $this->addValue('rtn_url', $this->parent->in('rtn_url'));
    
    return true;
  }
}  
?>