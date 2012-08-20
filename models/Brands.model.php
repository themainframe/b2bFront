<?php
/** 
 * Model: Brands
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Brands extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();
    
    // Set this model's preferences
    if($this->parent->inInteger('id') > 0)
    {
      // Does the brand exist?
      $brand = new BOMBrand($this->parent->inInteger('id'), $this->parent);
      
      if(!$brand)
      { 
        // Stop rendering
        $this->parent->go('./?option=brands');
        return false;
      }
      
      // Make the brand name available
      $this->addValue('brand_name', $brand->name);
      $this->addValue('brand_img_url', Tools::getImageThumbnail($brand->image_path, 'lst'));
    
      // Find items of this brand and provide a table
      $query = $this->db->query();
      $query->select('0 AS is_parent, parent_item_id, id, sku, name, trade_price, pro_net_price, pro_net_qty, category_id,' . 
                   'rrp_price, cost_price, stock_free, visible, parent_item_id', 'bf_items')
            ->where('((brand_id = \'{1}\' ' .
                    ') AND `visible` = 1)' , $this->parent->inInteger('id'));
                    
      // Precache cart values
      $this->parent->cart->prefetch();
        
      // Construct table
      $dataView = new DataTable('items', $this->parent, $query);
      $dataView->setOption('alternateRows');
      $dataView->setOption('showTopPager');
      $dataView->setOption('showBottomPager');
      $dataView->setOption('subjectName', 'Item');
      $dataView->addColumns($this->defaultColumns); 
      
      // Add the table to the view template
      $this->addValue('table', $dataView->render());      
    }
    else
    {
      // Provide a list of brands
      $this->addValue('brands', $this->getBrands());
    }
    
    // Make the selected ID available
    $this->addValue('id', $this->parent->inInteger('id'));
    
    // Update CCTV
    $this->parent->security->action('Brands Page');
    
    return true;
  }
  
  /**
   * Obtain a data collection for the brands
   * @return array
   */
  private function getBrands()
  {
    $this->db->select('*', '`bf_brands`')
             ->order('name', 'asc')
             ->execute();
             
    // Collect brands
    $brandCollection = array();
    while($brand = $this->db->next())
    {
      // Get image
      $image = $brand->image_path;
      
      // No image?
      if(!$image)
      {
        // Default image
        $imageURL = 
          $this->parent->config->get('com.b2bfront.site.default-image', true);
      }
      else
      {
        $imageURL = Tools::getImageThumbnail($image);
      }
      
      $brandCollection[] = array(
        'name' => $brand->name,
        'id' => $brand->id,
        'url' => $imageURL
      );
    }
    
    return $brandCollection;
  }
}  
?>