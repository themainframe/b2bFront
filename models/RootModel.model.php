<?php
/** 
 * Model: Root Model
 * This model provides the base values for all other models.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class RootModel extends Model implements IModel
{
  /**
   * Define the default item listing column setup
   * @var array
   */
  public $defaultColumns = array();

  /**
   * Updates the model's values array.
   * @return boolean
   */
  public function execute()
  {
    // Execute the final Model::execute();
    parent::execute();
    
    // Define default item listing columns
    $this->defaultColumns = array(
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
                                              'linkURL' => '/?option=item&id={id}&p={is_parent}',
                                              'callback' => function($row, $parent, $value)
                                                            {
                                                              // Get cache value
                                                              $cacheValue = $parent->cache->getValue($row->id . 
                                                                '-tags');
                                                            
                                                              // Cached?
                                                              if($cacheValue === '' || $row->is_parent)
                                                              {
                                                                // No tags
                                                                return $value;
                                                              }
                                                              else
                                                              {
                                                                // Attach each tag
                                                                $tags = '';
                                                                $prefixes = '';
                                                                $suffixes = '';
                                                                
                                                                if($cacheValue === false)
                                                                {
                                                                  // Load from DB and cache
                                                                  // Are tags already cached?
                                                                  if(!$parent->cache->getValue('tags'))
                                                                  {
                                                                    $itemTags = $parent->db->query();
                                                                    $itemTags->select('*', 'bf_item_tags')
                                                                             ->execute();
                                                                       
                                                                    $tagCacheCollection = array();      
                                                                    while($itemTag = $itemTags->next())
                                                                    {
                                                                      $tagCacheCollection[$itemTag->id] = 
                                                                        array(
                                                                          'name' => $itemTag->name,
                                                                          'icon_path' => $itemTag->icon_path,
                                                                          'font_list_colour' => $itemTag->font_list_colour,
                                                                          'font_list_bold' => $itemTag->font_list_bold,
                                                                          'font_list_italic' => $itemTag->font_list_italic,
                                                                          'font_list_small_caps' =>
                                                                            $itemTag->font_list_small_caps
                                                                        );
                                                                    }
                                                                    
                                                                    // Save tags to cache
                                                                    $parent->cache->addValue('tags', $tagCacheCollection);
                                                                  }
                                                                  else
                                                                  {
                                                                    $tagCacheCollection = 
                                                                      $parent->cache->getValue('tags');
                                                                  }
                                                                  
                                                                  // Find tags for this item
                                                                  $thisItemTags = $parent->db->query();
                                                                  $thisItemTags->select('*', 'bf_item_tag_applications')
                                                                               ->where('`item_id` = \'{1}\'', $row->id)
                                                                               ->execute();
                                                                               
                                                                  // No tags present?
                                                                  if($thisItemTags->count == 0)
                                                                  {
                                                                    // Cache that this item has no tags
                                                                    $parent->cache->addValue($row->id . '-tags', '');
                                                                    
                                                                    // Nothing to add
                                                                    return $value;
                                                                  }
                                                                  else
                                                                  {
                                                                    // Build a tag collection
                                                                    $tagCollection = array();
                                                                    while($thisItemTag = $thisItemTags->next())
                                                                    {
                                                                      $tagCollection[] = 
                                                                        $tagCacheCollection[$thisItemTag->item_tag_id];
                                                                    }
                                                                    
                                                                    // Cache the collection
                                                                    $parent->cache->addValue($row->id . '-tags',
                                                                      $tagCollection);
                                                                      
                                                                    // Set cache value
                                                                    $cacheValue = $tagCollection;
                                                                  }
                                                                }

                                                                // Render
                                                                foreach($cacheValue as $tag)
                                                                {
                                                                  // Icons
                                                                  if($parent->config->get('com.b2bfront.item-tags.icons', true))
                                                                  {
                                                                    $tags .= '<img class="tagimg" src="' . $tag['icon_path'] . 
                                                                      '" title="' . $tag['name'] . '" alt="' . 
                                                                      $tag['name'] . '" />';
                                                                  }
                                                                  
                                                                  // Prefixes
                                                                  if($tag['font_list_colour'] != '' &&
                                                                    $parent->config->get('com.b2bfront.item-tags.backgrounds', true))
                                                                  {
                                                                    $prefixes .= '<span class="tag" style="background: ' .
                                                                      $tag['font_list_colour'] . 
                                                                      ' !important;">';
                                                                    $suffixes .= '</span>';
                                                                  }
                                                                  
                                                                  if($tag['font_list_bold'] &&
                                                                    $parent->config->get('com.b2bfront.item-tags.font-effects', true))
                                                                  {
                                                                    $prefixes .= '<span style="font-weight: bold;">';
                                                                    $suffixes .= '</span>';
                                                                  }
                                                                  
                                                                  if($tag['font_list_italic'] &&
                                                                    $parent->config->get('com.b2bfront.item-tags.font-effects', true))
                                                                  {
                                                                    $prefixes .= '<span style="font-style: italic;">';
                                                                    $suffixes .= '</span>';
                                                                  }
                                                                  
                                                                  if($tag['font_list_small_caps'] &&
                                                                    $parent->config->get('com.b2bfront.item-tags.font-effects', true))
                                                                  {
                                                                    $prefixes .= '<span style="font-variant: small-caps;">';
                                                                    $suffixes .= '</span>';
                                                                  }
                                                                }
                                                                
                                                                // Return the resulting tag construct if there are changes
                                                                return ($tags . $prefixes . $suffixes == '' ? 
                                                                  $value : 
                                                                  $tags . '&nbsp;' . $prefixes . $value . $suffixes);
                                                              }
     
                                                            }
                                            )
                             ),
                             array(
                               'dataName' => 'stock_free',
                               'niceName' => 'Stock',
                               'options' => array(
                                              'callback' => function($row, $parent)
                                                            {
                                                              if($row->is_parent)
                                                              {
                                                                // Cached?
                                                                if($parent->cache->getValue($row->id . 
                                                                  '-child-stock') == '1')
                                                                {
                                                                  return 1;
                                                                }
                                                              
                                                                // Find stock of children
                                                                $childItems = $parent->db->query();
                                                                $childItems->select('stock_free', 'bf_items')
                                                                           ->where('`parent_item_id` = \'{1}\' AND
                                                                              `stock_free` > 0', $row->id)
                                                                           ->execute();
                                                                           
                                                                // Cache result to avoid future queries being required
                                                                $parent->cache->addValue($row->id . 
                                                                  '-child-stock', '1');
                                                                           
                                                                // Return result
                                                                return ($childItems->count > 0 ? 1 : 0);
                                                              }
                                                              else
                                                              { 
                                                                // Normal stock calculation
                                                                return ($row->stock_free > 0 ? 1 : 0);  
                                                              }
                                                            },
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
                                                'callback' => function($row, $parent)
                                                              {
                                                                // For parent items, do not show a field
                                                                if($row->is_parent == 1)
                                                                {
                                                                  return '';
                                                                }
                                                                
                                                                $stockValue = $row->stock_free;
                                                                $stockValue = ($stockValue > 100 ? 100 : $stockValue);
                                                                $stockValue = ($stockValue < 0 ? 0 : $stockValue);
                                                              
                                                                return '<input title="' . $stockValue . ' in stock" tabindex="1" type="text" value="' . 
                                                                        $parent->cart->get($row->id) . 
                                                                       '" style="width: 35px;" name="qty_' .
                                                                        $row->id . '" />';
                                                              }
                                              ),
                               )
                               
                              : array())
                            
                           );
   
    // Add firefox hack
    $browser = get_browser(null, true);
    if($browser['browser'] == 'Firefox')
    {
      $this->addValue('ffSocialFix', '; width: 100%; position:relative; left: -880px; top: 20px');   
    }
    else
    {
      $this->addValue('ffSocialFix', '');
    }
 
    // By default no plugin scripts
    $this->addValue('pluginScriptHook', '');
    
    // Set config options for Site
    global $BF;
    $siteConfig = $BF->config->getDomain('com.b2bfront.site');

    // Add values
    foreach($siteConfig as $key => $value)
    {
      $this->addValue($key, $value);
    }
    
    // For Customer Relationship Management (CRM) template tag values:
    $siteConfig = $BF->config->getDomain('com.b2bfront.crm');

    // Add values
    foreach($siteConfig as $key => $value)
    {
      $this->addValue($key, $value);
    }
    
    // Set defaults
    $this->addValue('title', $BF->config->get('com.b2bfront.site.title', true)); 
    
    // Find main menu
    $mainMenuRow = $BF->db->getRow('bf_website_menus', 
                                   $BF->config->get('com.b2bfront.site.default-main-menu', true));
   
    // Load main menu
    $mainMenu = $BF->db->query();
    $mainMenu->select('*', 'bf_website_menu_items')
             ->where('`menu_id` = \'{1}\'', $mainMenuRow->id)
             ->execute();
             
    // Get all as array
    $mainMenuItems = $mainMenu->assoc('id');
    $this->addValue('menuItems', $mainMenuItems); 
    
    // Set selected value
    $this->addValue('selected', 
      $BF->in('option') == '' ? 'home' : $BF->in('option'));
      
    // Provide security information to the view for conditional rendering
    $this->add($this->parent->security->permissions);
    
    // Add user info
    foreach($this->parent->security->attributes as $key => $value)
    {
      $this->addValue('user_' . $key, $value);
    }
    
    // By default, no notification
    $this->addValue('notification', 0);
    
    // Provide basket information
    $this->addValue('basketCount', $this->parent->cart->count());
    
    // Get live chat users
    $liveChat = $this->db->query();
    $liveChat->select('*', 'bf_admins')
             ->where('`online` = \'1\'')
             ->limit(1)
             ->execute();
    
    $this->addValue('liveChatUsers', $liveChat->count);
    
    // Provide the URL of this page
    $this->addValue('url', Tools::getModifiedURL());
    
    // Admin?
    if($_SESSION['admin'] && $_SESSION['admin']['can_login'])
    {
      // Is admin
      $this->addValue('isAdmin', 1);
    }
    
    return true;
  }
}  
?>
