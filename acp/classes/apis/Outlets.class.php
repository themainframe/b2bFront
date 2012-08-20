<?php
/**
 * Outlets
 * Admin API
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Outlets extends API
{
  /**
   * Add an outlet.
   * @param string $price The price that the outlet is currently selling at
   * @param string $url The URL of the outlet page
   * @param integer $xmlNodeID The ID of the XML node that contains the price
   * @param boolean $state The state of the Outlet.  True = Receiving Data, False = Broken
   * @param integer $dealerID The ID of the Dealer associated with the outlet.
   * @param integer $itemID The ID of the Inventory item associated with the outlet.
   * @return boolean|integer The row ID in the outlets table that was created.
   */
  public function add($price, $url, $xmlNodeID, $state, $dealerID, $itemID)
  {
    $this->db->insert('bf_outlets', array(
                       'price' => $price,
                       'url' => $url,
                       'xml_node_id' => $xmlNodeID,
                       'state_ok' => $state,
                       'user_id' => $dealerID,
                       'item_id' => $itemID,
                       'modification_timestamp' => time()
                     ))->execute();
                     
    return $this->db->insertId;
  }

  /**
   * Remove an outlet.
   * @param string $outletID The ID of the outlet to remove.
   * @return boolean
   */
  public function remove($outletID)
  {
    // Remove the brand
    $this->db->delete('bf_outlets')
             ->where("`id` = '{1}'", $outletID)
             ->limit(1)
             ->execute();
             
    // Remove outlet snapshots
    $this->db->delete('bf_outlet_snapshots')
             ->where("`outlet_id` = '{1}'", $outletID)
             ->execute();
               
    return true;
  }
  
  /**
   * Update all working outlets
   * Create backlogs of old data.
   * There is no ability to update one outlet at a time.
   * @return boolean
   */
  public function updateAll()
  {
    // Select all outlets
    $query = $this->db->query();
    $query->select('*', 'bf_outlets')
           ->where('state_ok = 1')
           ->execute();
           
    // For each, update
    while($outlet = $query->next())
    {    
      // Try to download a new price
      $newPrice = $this->getPrice($outlet->url, $outlet->xml_node_id);
      
      // OK?
      if(!$newPrice)
      {
        // Broken!
        $this->db->update('bf_outlets', array(
                     'state_ok' => '0'
                   ))
                 ->where('id = \'{1}\'', $outlet->id)
                 ->limit(1)
                 ->execute();
                 
        // Do no more work
        continue;
      }
      
      // Update the outlet
      $this->db->update('bf_outlets', array(
                   'price' => $newPrice,
                   'modification_timestamp' => time()
                 ))
               ->where('id = \'{1}\'', $outlet->id)
               ->limit(1)
               ->execute();
               
      // Work out if the outlet has changed
      $changeCheck = $this->db->query();
      $changeCheck->select('*', 'bf_outlet_snapshots')
                  ->where('`outlet_id` = \'{1}\'', $outlet->id)
                  ->order('timestamp', 'desc')
                  ->limit(1)
                  ->execute();
                  
      if($changeCheck->count == 1)
      {
        $lastValue = $changeCheck->next();
        
        // Has the price changed?
        if($lastValue->price != $newPrice)
        {
          // Insert a new snapshot
          $this->db->insert('bf_outlet_snapshots', array(
                             'price' => $newPrice,
                             'outlet_id' => $outlet->id,
                             'timestamp' => time(),
                             'rise' => ($lastValue->price < $newPrice ? 1 : 0)
                           ))->execute();
        }
      }
      else
      {
        // First
        $this->db->insert('bf_outlet_snapshots', array(
                           'price' => $newPrice,
                           'outlet_id' => $outlet->id,
                           'timestamp' => time(),
                           'rise' => 1
                         ))->execute();
      }
              
      }
    
    return true;
  }
  
  /**
   * Get a price for the specified URL and Node ID
   * @param string $url The URL to search
   * @param integer $nodeID The Node ID to search for a price
   * @return string
   */
  private function getPrice($url, $nodeID)
  {
    // Download the page text
    $curlObject = curl_init();
    
    // set URL and other appropriate options
    curl_setopt($curlObject, CURLOPT_URL, $url);
    curl_setopt($curlObject, CURLOPT_HEADER, 0);
    curl_setopt($curlObject, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlObject, CURLOPT_FOLLOWLOCATION, 1);
    
    // Get the data
    $pageText = curl_exec($curlObject);
    
    // close cURL resource, and free up system resources
    curl_close($curlObject);
    
    // Try to parse for prices
    $document = new DOMDocument();
    $document->loadHTML($pageText);
    $documentXPath = new DOMXpath($document);
    $textNodes = $documentXPath->query('//text()');
    $index = 0;
    $results = array();
    
    foreach($textNodes as $domElement)
    {
      // Appear as a price?
      $textNode = preg_replace('/[^0-9A-Za-z\s&£.]/', '', trim($domElement->wholeText));
      
      if(preg_match('/([A-Za-z\s:\-]+)?(£|&pound;)?(\s+)?(&nbsp;)?(\s+)?[0-9]+\.[0-9]{2}/',
        $textNode, $matches) == 1)
      {
        $results[$index] = str_replace('£', '&pound;', $textNode);
      }
      
      $index ++;
    }
    
    // Search the specified cell
    if(!isset($results[$nodeID]))
    {
      $this->parent->log('Could not obtain price for ' . $url);
      return false;
    }
    
    // Format the price
    $price = Tools::cleanPrice($results[$nodeID]);
    
    return $price;
  }
  
}
?>