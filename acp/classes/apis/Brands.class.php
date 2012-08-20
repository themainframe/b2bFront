<?php
/**
 * Brands
 * Admin API
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Brands extends API
{
  /**
   * Add a brand.
   * @param string $name The name of the brand.
   * @param string $imagePath The path to the logo image.
   * @param integer $primaryClass The primary classification of the brand.
   * @return boolean|integer The row ID in the brands table that was created.
   */
  public function add($name, $imagePath, $primaryClass)
  {
    $this->db->insert('bf_brands', array(
                       'name' => $name,
                       'image_path' => $imagePath,
                       'primary_classification_id' => intval($primaryClass)
                     ))->execute();
                     
    return $this->db->insertId;
  }

  /**
   * Remove a brand.
   * @param string $brandID The ID of the brand to remove.
   * @return boolean
   */
  public function remove($brandID)
  {
    // Remove the brand
    $this->db->delete('bf_brands')
             ->where("`id` = '{1}'", $brandID)
             ->limit(1)
             ->execute();
             
    // Update items to default brand
    $this->db->update('bf_items', array(
                       'brand_id' => '-1' 
                     ))
             ->where("`brand_id` = '{1}'", $brandID)
             ->execute();
               
    return true;
  }
}
?>