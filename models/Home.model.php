<?php
/** 
 * Model: Home
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Home extends RootModel
{
  /**
   * Produces output and stores it in the value collection.
   * @return boolean
   */
  public function execute()
  {
    // Load parent defaults
    parent::execute();
  
    // Get masthead images
    $this->addValue('showMastheadItems', 1);    
    $this->addValue('masthead-items', $this->getMastheadItems());
    
    // Set this model's preferences
    $this->addValue('categories', $this->getCategories());
    $this->addValue('option', 'home');
    
    // Get grouped categories
    $categoryGroups = $this->parent->db->query();
    $categoryGroups->select('*', 'bf_category_groups')
                   ->order('name', 'ASC')
                   ->execute();
                   
    while($categoryGroup = $categoryGroups->next())
    {
      $this->addValue('categories-' . str_replace(' ', '', $categoryGroup->name),
        $this->getCategories($categoryGroup->id));
    }
                   
                   
    
    // Enable banners?
    if($this->parent->config->get('com.b2bfront.site.ticker', true))
    {
      $this->addValue('enableBanners', '1');

      // Provide banners
      $bannerArticleCategory = 
        $this->parent->config->get('com.b2bfront.site.ticker-article-category', true);
      
      // Find all banners
      $banners = $this->parent->db->query();
      $banners->select('*', 'bf_articles')
              ->where('`article_category_id` = \'{1}\'', $bannerArticleCategory)
              ->execute();
              
      // Create a collection
      $bannerCollection = array();
      while($banner = $banners->next())
      {
        $bannerCollection[] = array(
          'text' => $banner->meta_content,
          'image' => $banner->content
        );
      }
      
      // Make banners available
      $this->addValue('banners', $bannerCollection);  
    }
    else
    {
      $this->addValue('enableBanners', '0');
    }
    
    // Update CCTV
    $this->parent->security->action('Home Page');
    
    return true;
  }
  
  /**
   * Obtain a data collection for the categories
   * @param integer $groupID Optionally The group to restrict searching to, default none (null).
   * @return array
   */
  private function getCategories($groupID = null)
  {
    $this->db->select('*', '`bf_categories`')
             ->where('`visible` = 1' . 
                ($groupID ? ' AND `category_group_id` = \'' . intval($groupID) . '\'' : ''))
             ->order('name', 'asc')
             ->execute();
             
    // Collect categories
    $categoryCollection = array();
    while($category = $this->db->next())
    {
      // Get image
      $image = $this->parent->db->getRow('bf_images', $category->image_id);
      
      // No image?
      if(!$image)
      {
        // Default image
        $imageURL = 
          $this->parent->config->get('com.b2bfront.site.default-image', true);
      }
      else
      {
        $imageURL = Tools::getImageThumbnail($image->url);
      }
      
      $categoryCollection[] = array(
        'name' => $category->name,
        'id' => $category->id,
        'url' => $imageURL
      );
    }
    
    return $categoryCollection;
  }
  
  /**
   * Obtain a data collection for masthead items on the homepage.
   * @return array
   */
  private function getMastheadItems()
  {
    $this->db->select('*', '`bf_item_tags`')
             ->where('`masthead` = 1 AND `masthead_image_path` <> \'\'')
             ->order('name', 'asc')
             ->execute();
             
    // Collect Item Tags
    $mastheadCollection = array();
    while($item = $this->db->next())
    {
      $mastheadCollection[] = array(
        'name' => $item->name,
        'link' => '/?option=tag&id=' . $item->id,
        'url' => $item->masthead_image_path
      );
    }
    
    return $mastheadCollection;
  }
}  
?>
