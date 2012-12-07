<?php
/**
 * Dealers
 * Admin API
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Dealers extends API
{
  /**
   * Add a dealer.
   * @param string $name The name of the dealer.
   * @param string $password The password of the dealer account.
   * @param string $email The Email address for the dealer.
   * @param string $description The description of the dealer accout.
   * @param integer $profileID The Profile ID of the dealer
   * @param string $accountNumber The Account Number of the dealer
   * @param string $addressBuilding The building name/number
   * @param string $addressStreet The street name
   * @param string $addressCity The city
   * @param string $addressPostcode The postcode
   * @param string $phoneLandline A landline phone number
   * @param string $phoneMobile A mobile phone number
   * @param string $URL A website URL for the dealer
   * @param string $tagline The slogan or tagline of the dealer
   * @param integer $localeID The locale ID to use
   * @param boolean $exludeBulk Exclude the dealer from bulk emails
   * @param integer $discountBandID The discount band to place the user on.
   * @param boolean $inDirectory Show the user in the public directory if it exists.
   * @return boolean|integer The row ID in the users table that was created.
   */
  public function add($name, $password, $email, $description, $profileID, $accountNumber,
                      $addressBuilding, $addressStreet, $addressCity, $addressPostcode,
                      $phoneLandline, $phoneMobile, $URL, $tagline, $localeID, $excludeBulk,
                      $discountBandID, $adminID, $inDirectory)
  {
    $this->db->insert('bf_users', array(
                       'name' => $name,
                       'description' => $description,
                       'account_code' => $accountNumber,
                       'password' => md5(BF_SECRET . $password),  // Preappended Salt
                       'email' => $email,
                       'requires_review' => 0,
                       'include_in_bulk_mailings' => ($excludeBulk ? 0 : 1),
                       'address_building' => $addressBuilding,
                       'address_street' => $addressStreet,
                       'address_city' => $addressCity,
                       'address_postcode' => $addressPostcode,
                       'phone_mobile' => $phoneMobile,
                       'phone_landline' => $phoneLandline,
                       'profile_id' => $profileID,
                       'locale_id' => $localeID,
                       'slogan' => $tagline,
                       'url' => $URL,
                       'band_id' => intval($discountBandID),
                       'in_directory' => ($inDirectory ? 1 : 0)
                     ))->execute();
                     
    return $this->db->insertId;
  }
  
  /**
   * Modify an existing dealer.
   * @param integer $id The ID of the dealer to modify.
   * @param string $name The name of the dealer.
   * @param string $password The password of the dealer account.
   * @param string $email The Email address for the dealer.
   * @param string $description The description of the dealer accout.
   * @param integer $profileID The Profile ID of the dealer
   * @param string $accountNumber The Account Number of the dealer
   * @param string $addressBuilding The building name/number
   * @param string $addressStreet The street name
   * @param string $addressCity The city
   * @param string $addressPostcode The postcode
   * @param string $phoneLandline A landline phone number
   * @param string $phoneMobile A mobile phone number
   * @param string $URL A website URL for the dealer
   * @param string $tagline The slogan or tagline of the dealer
   * @param integer $localeID The locale ID to use
   * @param boolean $exludeBulk Exclude the dealer from bulk emails
   * @param integer $discountBandID The discount band to place the user on.
   * @param boolean $inDirectory Show the user in the public directory if it exists.
   * @return boolean
   */
  public function modify($id, $name, $password, $email, $description, $profileID, 
                         $accountNumber, $addressBuilding, $addressStreet, $addressCity,
                         $addressPostcode, $phoneLandline, $phoneMobile, $URL,
                         $tagline, $localeID, $excludeBulk, $discountBandID, $inDirectory)
  {
    // Build changes array
    $changes = array(
      'name' => $name,
      'description' => $description,
      'account_code' => $accountNumber,
      'email' => $email,
      'requires_review' => 0,
      'include_in_bulk_mailings' => ($excludeBulk ? 0 : 1),
      'address_building' => $addressBuilding,
      'address_street' => $addressStreet,
      'address_city' => $addressCity,
      'address_postcode' => $addressPostcode,
      'phone_mobile' => $phoneMobile,
      'phone_landline' => $phoneLandline,
      'profile_id' => $profileID,
      'locale_id' => $localeID,
      'slogan' => $tagline,
      'url' => $URL,
      'band_id' => intval($discountBandID),
      'in_directory' => ($inDirectory ? 1 : 0)
    );
    
    // Password change?
    if($password != '')
    {
      $changes['password'] = md5(BF_SECRET . $password);
    }

    $this->db->update('bf_users', $changes)
              ->where('`id` = \'{1}\'', $id)
              ->limit(1)
              ->execute();
                     
    return true;
  }
  
  /**
   * Add a dealer profile
   * @param string $name The name of the new profile
   * @param boolean $canSearch The dealer may use search features.
   * @param boolean $canSeeRRP The dealer may see RRP prices.
   * @param boolean $canSeePrices The dealer can view prices.
   * @param boolean $canSeeWholesale The dealer can view wholesale prices.
   * @param boolean $canOrder The dealer can produce orders.
   * @param boolean $canQuestion The dealer can submit questions.
   * @param boolean $canProRate The dealer will always use pro net prices.
   * @return boolean|integer   
   */
  public function addProfile($name, $canSeeRRP, $canSeePrices, $canSeeWholesale,
                             $canOrder, $canQuestion, $canProRate)
  {
    $this->db->insert('bf_user_profiles', array(
                       'name' => $name,
                       'can_see_rrp' => ($canSeeRRP ? 1 : 0),
                       'can_see_prices' => ($canSeePrices ? 1 : 0),
                       'can_wholesale' => ($canSeeWholesale ? 1 : 0),
                       'can_order' => ($canOrder ? 1 : 0),
                       'can_question' => ($canQuestion ? 1 : 0),
                       'can_pro_rate' => ($canProRate ? 1 : 0)
                     ))->execute();
                     
    return $this->db->insertId;
  } 
  
  /**
   * Make changes to a dealer profile
   * @param integer $dealerProfileID The ID of the profile to modify.   
   * @param string $name The new name for the profile.
   * @param boolean $canSeeRRP The dealer may see RRP prices.
   * @param boolean $canSeePrices The dealer can view prices.
   * @param boolean $canSeeWholesale The dealer can view wholesale prices.
   * @param boolean $canOrder The dealer can produce orders.
   * @param boolean $canQuestion The dealer can submit questions.
   * @param boolean $canProRate The dealer will always use pro net prices.
   * @return boolean    
   */
  public function modifyProfile($dealerProfileID, $name, $canSeeRRP, $canSeePrices,
                                $canSeeWholesale, $canOrder, $canQuestion, $canProRate)
  {
    $this->db->update('bf_user_profiles', array(
                       'name' => $name,
                       'can_see_rrp' => ($canSeeRRP ? 1 : 0),
                       'can_see_prices' => ($canSeePrices ? 1 : 0),
                       'can_wholesale' => ($canSeeWholesale ? 1 : 0),
                       'can_order' => ($canOrder ? 1 : 0),
                       'can_question' => ($canQuestion ? 1 : 0),
                       'can_pro_rate' => ($canProRate ? 1 : 0)
                     ))
             ->where('id = \'{1}\'', $dealerProfileID)
             ->limit(1)
             ->execute();
                     
    return true;
  }                          
  
  /**
   * Remove a dealer.
   * @param integer $dealerID The ID of the dealer to remove.
   * @return boolean
   */
  public function remove($dealerID)
  {
    // Remove the dealer
    $this->db->delete('bf_users')
             ->where("`id` = '{1}'", $dealerID)
             ->limit(1)
             ->execute();
          
    return true;
  }
  
  /**
   * Remove a dealer profile
   * @param integer $dealerProfileID The ID of the dealer profile to remove.
   * @return boolean
   */
  public function removeProfile($dealerProfileID)
  {
    // Remove the dealer
    $this->db->delete('bf_user_profiles')
             ->where("`id` = '{1}'", $dealerProfileID)
             ->limit(1)
             ->execute();
          
    return true;
  }
  
  /**
   * Count requests for accounts
   * @return integer
   */
  public function countRequests()
  {
    // Spawn a query and find unapproved accounts
    $query = $this->parent->db->query();
    $query->select('`id`', 'bf_users')
          ->where('`requires_review` = 1')
          ->execute();
          
    // Return the total
    return $query->count;
  }
  
  /**
   * Add a discount band
   * @param string $code The code for the new discount band.
   * @param string $name The name of the new discount band.
   * @return boolean
   */
  public function addDiscountBand($code, $name)
  {
    // Create the new band
    $query = $this->parent->db->query();
    $query->insert('bf_user_bands', array(
              'name' => $code,
              'description' => $name
            ))
          ->execute();
          
    return true;
  }
  
  /** 
   * Reset a discount band
   * @param integer $discountBandID The ID of the discount band to reset
   * @return boolean
   */
  public function resetDiscountBand($discountBandID)
  {
    // Set all matrix values for this band to 1.
    $this->db->delete('bf_matrix')
             ->where('`band_id` = \'{1}\'', $discountBandID)
             ->execute();
             
    // Add new values - collect all categories and bands into RAM
    $categories = $this->db->query();
    $categories->select('*', 'bf_categories')
               ->order('name', 'asc')
               ->execute();
                 
    // Load all bands
    $bands = $this->db->query();
    $bands->select('*', 'bf_user_bands')
          ->where('`id` = \'{1}\'', $discountBandID)
          ->order('name', 'asc')
          ->execute();
          
    while($category = $categories->next())
    {
      while($band = $bands->next())
      {
        // Add a row for this value
        $this->db->insert('bf_matrix', array(
                           'band_id' => $band->id,
                           'category_id' => $category->id,
                           'value' => '1.00000'
                         ))
                 ->execute();
      }
      
      // Skip to start of bands
      $bands->rewind();
    }
    
    return true;
  }

  /**
   * Count the number of dealers.
   * @return integer
   */
  public function count()
  {
    $dealers = $this->db->query();
    $dealers->select('1', 'bf_users')
            ->execute();
    
    return $dealers->count;
  }
}
?>