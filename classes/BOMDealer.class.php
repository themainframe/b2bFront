<?php
/** 
 * Business Object Model
 * Dealer Class
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class BOMDealer extends BOMObject
{
  /**
   * Load a dealer from the database with an ID
   * @param integer $dealerID The ID to initialise this object with.
   * @param BFClass* $parent The parent object.
   * @return BOMItem|boolean
   */
  public function __construct($dealerID, & $parent)
  {
    // Superclass constructor
    parent::__construct($parent);
    
    // Initialisation possible?
    if(!$dealerID)
    {
      return;
    }
    
    // Load the dealer
    $dealerRow = $parent->db->getRow('bf_users', $dealerID);
    
    // Missing
    if(!$dealerRow)
    {
      $this->attributes = false;
      $this->parent->log('BOM', 'No such user: ' . $dealerID);
      return false;
    }
    
    // Set each value
    foreach($dealerRow as $key => $value)
    {
      $this->{$key} = $value;
    }
  }
  
  /**
   * Load band name
   * @return string
   */
  protected function loadBandName()
  {
    // Is the user wholesale?
    
    // Get profile
    $profile = $this->parent->db->getRow('bf_user_profiles', $this->profile_id);
    if($profile)
    {
      if($profile->can_wholesale == '1')
      {
        return 'Wholesale';
      }
    }
  
    // Collect band name
    $getBand = $this->parent->db->getRow('bf_user_bands', $this->band_id);
    
    if($getBand)
    {
      return $getBand->description;
    }
    
    return '';
  }
}
?>