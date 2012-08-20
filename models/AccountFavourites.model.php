<?php
/** 
 * Model: Account Favourites
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class AccountFavourites extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();

    // Update CCTV
    $this->parent->security->action('Account Favourites');

    // Set this model's title and tab
    $this->addValue('title', $this->parent->config->get('com.b2bfront.site.title', true) . 
                    ' - My Favourites');
    $this->addValue('tab_account', 'selected');
  
    // Logged in?
    if(!$this->parent->security->loggedIn())
    {
      $this->parent->loadView('login');
   
      return false;
    }
    
    // Removal?
    if($this->parent->in('remove'))
    {
      // Perform removal
      $remove = $this->db->query();
      $remove->delete('bf_user_favourites')
             ->where('`item_id` = \'{1}\' AND `user_id` = \'{2}\'', $this->parent->inInteger('remove'), 
               $this->parent->security->UID)
             ->limit(1)
             ->execute();
            
      // Notify
      $this->addValue('notification', 'Your favourites have been updated.');
    }
    
    // Find the contents of my favourites
    $query = $this->db->query();
    $query->select('0 AS is_parent, parent_item_id, id, sku, name, trade_price, pro_net_price, pro_net_qty,' . 
                 'rrp_price, cost_price, stock_free, visible, parent_item_id', 'bf_items')
          ->where('visible = 1 AND `id` IN (SELECT `item_id` FROM `bf_user_favourites` WHERE `user_id` = \'{1}\') AND parent_item_id=-1 ' . 
                  ($selectedSubcategory ? ' AND `subcategory_id` = \'{2}\'' : ''), 
                  $this->parent->security->UID)
          ->text('UNION SELECT 1 AS is_parent, -1 AS parent_item_id, id, sku, name, trade_price, pro_net_price,' .
                 'pro_net_qty, rrp_price, cost_price, NULL, 1, -1 FROM `bf_parent_items`')
          ->where('((category_id = \'{1}\' ' . ($selectedSubcategory ? ' AND `subcategory_id` = \'{2}\'' : '') .
                  '))' , $this->parent->inInteger('id'), $selectedSubcategory);
             
    // Callback to handle stock state
    $stockStateCallback = function($row, $parent)
    {
      if($row->is_parent)
      {
        // Cached?
        if($parent->cache->getValue($row->id . '-child-stock') == '1')
        {
          return 1;
        }
      
        // Find stock of children
        $childItems = $parent->db->query();
        $childItems->select('stock_free', 'bf_items')
                   ->where('`parent_item_id` = \'{1}\' AND `stock_free` > 0', $row->id)
                   ->execute();
                   
        // Cache result to avoid future queries being required
        $parent->cache->addValue($row->id . '-child-stock', '1');
                   
        // Return result
        return ($childItems->count > 0 ? 1 : 0);
      }
      else
      { 
        // Normal stock calculation
        return ($row->stock_free > 0 ? 1 : 0);  
      }
    };
    
    // Precache cart values
    $this->parent->cart->prefetch();
    
    // Callback for quantity field generation
    $quantityField = function($row, $parent)
    {
      // For parent items, do not show a field
      if($row->is_parent == 1)
      {
        return '';
      }
    
      return '<input tabindex="1" type="text" value="' . $parent->cart->get($row->id) . 
             '" style="width: 35px;" name="qty_' . $row->id . '" />';
    };
             
    // Define button to remove items from the favourites list
    $removeButton  = "\n";
    $removeButton .= '<a href="./?option=account_favourites&remove={id}" class="shadowbox">' . "\n";
    $removeButton .= '  <img src="/share/icon/general/cross-circle.png" ' . 
                     'class="middle" alt="Cross" />' . "\n";
    $removeButton .= '  Remove' . "\n";
    $removeButton .= '</a>' . "\n";       
             
    // Construct table
    $dataView = new DataTable('items', & $this->parent, $query);
    $dataView->setOption('alternateRows');
    $dataView->setOption('showTopPager');
    $dataView->setOption('subjectName', 'Item');
    $dataView->addColumns(
                           array(
                             array(
                               'dataName' => '',
                               'niceName' => '',
                               'options' => array(
                                              'callback' => $this->parent->images->loadThumbnail,
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
                                              'linkURL' => '/?option=item&id={id}&p={is_parent}'
                                            )
                             ),
                             array(
                               'dataName' => 'stock_free',
                               'niceName' => 'Stock',
                               'options' => array(
                                              'callback' => $stockStateCallback,
                                              'formatAsToggleImage' => true,
                                              'toggleImageTrue' => 
                                                '/share/icon/general/tick-button.png',
                                              'toggleImageFalse' => 
                                                '/share/icon/general/cross-button.png',
                                              'newContent' => 
                                                '<div style="text-align: center;">{old}</div>'
                                            ),
                               'css' => array(
                                          'width' => '70px',
                                          'text-align' => 'center'
                                        )
                             ),
                             
                             array(
                               'dataName' => 'trade_price',
                               'niceName' => 'Trade',
                               'options' => array(
                                              'formatAsPrice' => true,
                                              'callback' => function($row, $parent)
                                                            {
                                                              if($parent->security->hasPermission('can_see_prices'))
                                                              {
                                                                return $row->trade_price;
                                                              }
                                                              else
                                                              {
                                                                // Prices not allowed
                                                                return '';
                                                              }
                                                            }
                                            ),
                               'css' => array(
                                          'width' => '70px'
                                        )
                             ),
                             array(
                               'dataName' => 'pro_net_price',
                               'niceName' => 
                                  ($this->parent->security->can('can_wholesale') ? 'W.Sale' : 'Pro Net'),
                               'options' => array(
                                              'formatAsPrice' => true,
                                              'callback' => function($row, $parent)
                                                            {
                                                              if($parent->security->hasPermission('can_see_prices'))
                                                              {
                                                                // Start a pricer
                                                                $pricer = new Pricer(& $parent);
                                                                return $pricer->myPrice($row);
                                                              }
                                                              else
                                                              {
                                                                // Prices not allowed
                                                                return '';
                                                              }
                                                            }
                                            ),
                               'css' => array(
                                          'width' => '70px'
                                        )
                             ),
                             array(
                               'dataName' => 'pro_net_qty',
                               'niceName' => 'PN QTY',
                               'options' => array(
                                              'callback' => function($row, $parent)
                                                            {
                                                              if($parent->security->hasPermission('can_see_prices'))
                                                              {
                                                                return $row->pro_net_qty;
                                                              }
                                                              else
                                                              {
                                                                // Prices + PN QTY not allowed
                                                                return '';
                                                              }
                                                            }
                                            ),
                               'css' => array(
                                          'width' => '65px'
                                        )
                             ),

                             
                             // Logged in?
                             ($this->parent->security->can('can_order') ?
                               
                               array(
                                 'dataName' => '',
                                 'niceName' => 'Order',
                                 'css' => array(
                                            'width' => '60px'
                                          ),
                                 'options' => array(
                                                'callback' => $quantityField
                                              ),
                               )
                               
                              : array()),
                              
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
    
    return true;
  }
}  
?>