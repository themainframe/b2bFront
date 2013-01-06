<?php
/**
 * Data
 * Admin API
 *
 * Handles the importing of data from static formats such as XML and Spreadsheet.
 * API must be initiated before it is used. 
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class Data extends API
{
  /**
   * The object initiation state
   * @var boolean
   */
  public $initiated = false;
  
  /**
   * Initiate the object by loading required classes
   * @return boolean
   */
  public function initiate()
  {
    if(!$this->initiated)
    {
      // Load class files
      require_once BF_ROOT . '/libraries/phpexcel/Classes/PHPExcel.php';
      require_once BF_ROOT . '/libraries/phpexcel/Classes/PHPExcel/IOFactory.php';
      
      $this->initiated = true;
    }
  
    return true;
  }
  
  /**
   * Check if a path can be loaded and is suitable for importing
   * If importing would fail, the method returns the reason for the failure as a string.
   * 
   *   N.B. This method returns both Booleans and Strings.
   *        Use the === operator to evaluate its output.
   * 
   * @param string $path The path to the file to test.
   * @return boolean|string
   */
  public function canImport($path)
  {
    // This could take time... 5 Minutes max execution time.
    set_time_limit(60 * 5);

    // Test for initiated class
    if(!$this->initiated)
    {
      $this->parent->log('Data: Must be initiate()\'d before operating on data.');
      return 'Internal error.  See log.';
    }
    
    // Check the file exists
    if(!file_exists($path))
    {
      $this->parent->log('Data: Cannot open ' . $path . ' for reading.');
      return 'The upload failed.';
    }
    
    // Test the file using PHPExcel
    $phpExcel = null;
    
    try
    {
      // Load the file
      $phpExcel = new PHPExcel();
      
      $this->parent->log('Data', $path . ': Loading data...');
      $phpExcel = PHPExcel_IOFactory::load($path);
      $this->parent->log('Data', $path . ': Loaded sheet.');
      
      // Obtain the sheet
      $phpExcel->setActiveSheetIndex(0);
      $sheet = $phpExcel->getActiveSheet();
      $this->parent->log('Data', $path . ': Locking sheet...');
      
      // Check if the SKU column is defined
      $SKUDefined = false;
      foreach ($sheet->getRowIterator() as $row)
      {
        $cellIterator = $row->getCellIterator();
            
        foreach ($cellIterator as $cell)
        {
          // Check if this cell is SKU
          $value = $this->toColumnHeading($cell->getCalculatedValue());
          
          if($value == 'sku')
          {
            $SKUDefined = true;
            $this->parent->log('Data', $path . ': Found SKU column.');
            break;
          }
        }
         
        // Only examine first row   
        break;
      }
      
      // Was SKU defined? 
      if(!$SKUDefined)
      {
        // Generic failure
        $this->parent->log('Data: User did not define SKU in ' . $path);
        return 'An SKU column must be present.';
      }
    }
    catch(Exception $exception)
    {
      // Failed to open using IOFactory
      $this->parent->log('Data: PHPExcel: ' . $exception->getMessage() . ': ' . $path);
      return 'The type of file cannot be read.';
    }
    
    if(!$phpExcel)
    {
      // Generic failure
      $this->parent->log('Data: PHPExcel failed to read ' . $path);
      return 'The type of file cannot be read.';
    }
    
    // OK
    return true;
  }
  
  /**
   * Imports inventory information into the database from a static file.
   * The file is read by the PHPExcel Third Party package.
   * The formats it accepts are documented in the Readme information.
   *
   * This method can either update rows or create new ones.
   * It produces a report of the actions taken once the changes are made.
   * The report is contained in an associative (string => mixed) array:
   * 
   *   ['total'] => An integer representing the total row count.
   *   ['noaction'] => Array of SKUs for which no action was taken
   *   ['updated'] => Array of SKUs that were updated.
   *   ['created'] => Array of SKUs that were created.
   *
   *   Array of SKUs in this context refers to an array of string => string:
   * 
   *   [sku] => Reason for failure (as text)
   *
   * @param string $path The path to the file to import
   * @param boolean $createNewRows Create new rows in the inventory.
   * @param boolean $createResultRows Optionally write result rows to the database. Default false.
   * @return array
   */
  public function import($path, $createNewRows, $createResultRows = false)
  {
    // This could take time... 5 Minutes max execution time.
    set_time_limit(60 * 5);

    // Parse the file, first check it can be imported
    if($this->canImport($path) !== true)
    {
      // Failed
      $this->parent->log('Data: ' . $path . ' will not be imported.');
      return false;
    }
    
    $this->parent->log('Data', $path . ' will be imported now.');
    
    // Load the file
    $phpExcel = new PHPExcel();
    $phpExcel = PHPExcel_IOFactory::load($path);
    
    // Obtain the sheet
    $phpExcel->setActiveSheetIndex(0);
    $sheet = $phpExcel->getActiveSheet();
    
    // Keep a record of nonactions, updates and creates
    $noAction = array();
    $updated = array();
    $created = array();
    $total = 0;
    $userRows = 0;
    
    // Collect headings in this sheet
    $headings = array();
    
    // User headings
    $userHeadings = array();
    
    // Collect values for each heading
    $rows = array();
    
    // Read each row in the sheet
    $headingRow = true;
    
    foreach ($sheet->getRowIterator() as $row)
    {
      $cellIterator = $row->getCellIterator();
      
      // Keep track of current index and the contents of the row.
      $columnIndex = 0;
      $currentRow = array();
      
      foreach ($cellIterator as $cell)
      {
        if($headingRow)
        {
          // Collect the heading name
          $value = $this->toColumnHeading($cell->getCalculatedValue());
          
          $this->parent->log('Data', $path . ': Discovered column ' . $value . '.');
          
          // Make an exception and convert due dates
          if($value == 'due date' || $value == 'due')
          {
            // Due Date cell, not a user cell
            $headings[$columnIndex] = 'stock_date';  
            $userHeadings[$columnIndex] = false;   
            
            // Advance column index
            $columnIndex ++;
            
            // Skip rest of logic for this cell
            continue;
          }
          
          // Check the heading
          $dataColumnName = $this->toDataColumn($value);
          
          if($dataColumnName)
          {
            $headings[$columnIndex] = $dataColumnName;  
            
            // Not a user heading
            $userHeadings[$columnIndex] = false;   
          }
          else
          {
            // Last chance, is this a user?
            $user = $this->toUserColumn($value);
            $userHeadings[$columnIndex] = $user;
            
            if($user)
            {
              // Mark column as a user column
              $headings[$columnIndex] = 'user' . $user;
            }
          }
        }
        else
        {
          // Actual values
          if(isset($headings[$columnIndex]))
          {
            // Get the value
            $value = $cell->getValue();
          
            // Due date needs converting
            if($headings[$columnIndex] == 'stock_date')
            {
              // Non-Empty value?
              if($value != '')
              {
                // Convert the date from Excel to UNIX timestamp
                $value = strtotime(PHPExcel_Style_NumberFormat::toFormattedString($value, 'D-M-YYYY'))
                   + 43200;  // Add 12 hours to be during the day for safety.
              }
            }
            
            // This needs storing
            $currentRow[$headings[$columnIndex]] = $value;
          }
        }
        
        // Advance column index
        $columnIndex ++;
      }
      
      // Add to the rows collection
      if(!$headingRow)
      {
        $rows[] = $currentRow;
      }
      
      // Nolonger heading row
      $headingRow = false;
    }

    //
    // Update or Insert the rows
    //
    foreach($rows as $row)
    {
      // Build array of database column name => value
      // Clear initial values
      $data = array();
      $sku = '';
      
      foreach($headings as $headingName)
      {
        $data[$headingName] = str_replace('£', '', $row[$headingName]);
      }
      
      // Update SKU
      $sku = $data['sku'];
      
      // Empty SKU?
      if(trim($sku) == '')
      {
        // Don't process this row
        continue;
      }
      
      // One more row...
      $total ++;
      
      if($createNewRows)
      {
        try
        {
          // Get the row - may not exist
          $row = $this->parent->db->getRow('bf_items', $data['sku'], 'sku');
          
          // In stock now?
          if($row && $row->stock_free <= 0 && $data['stock_free'] > 0)
          {
            // Delete replenishments
            $deleteReplenish = $this->parent->db->query();
            $deleteReplenish->delete('bf_stock_replenishments')
                            ->where('`item_id` = \'{1}\'', $row->id)
                            ->execute();
            
            // Add an arrival marker
            $replenish = $this->parent->db->query();
            $replenish->insert('bf_stock_replenishments', array(
                                'timestamp' => time(),
                                'notification_sent' => 0,
                                'item_id' => $row->id
                              ))
                      ->execute();
          }
          
          // Get userless, SKUless data
          $newData = $this->removeSKU($this->removeUsers($data));
        
          // Get item count now
          $itemCountOld = $this->parent->db->query();
          $itemCountOld->select('id', 'bf_items')
                       ->execute()
                       ->count;
        
          // Use INSERT with updating if rows already exist
          $this->parent->db->insert('bf_items', $data)
                           ->orUpdate($this->removeSKU($newData))
                           ->execute();
          
          // Get item count now
          $itemCountNow = $this->parent->db->query();
          $itemCountNow->select('id', 'bf_items')
                       ->execute()
                       ->count;
                       
          if($itemCountNow > $itemCountOld)
          {
            // Created + 1
            $created[$sku] = 'The item was created.';
          }
          else
          {
            // Matched + 1
            $updated[$sku] = 'The item was updated.';
          }
                      
        }
        catch(Exception $exception) { 
        
          print $exception->getMessage() . '<br><br>';
        
        }
      }
      else
      {
          try
          {
            // Get the row
            $row = $this->parent->db->getRow('bf_items', $data['sku'], 'sku');
            
            // In stock now?
            if($row->stock_free <= 0 && $data['stock_free'] > 0)
            {
              // Delete replenishments
              $deleteReplenish = $this->parent->db->query();
              $deleteReplenish->delete('bf_stock_replenishments')
                              ->where('`item_id` = \'{1}\'', $row->id)
                              ->execute();
              
              // Add an arrival marker
              $replenish = $this->parent->db->query();
              $replenish->insert('bf_stock_replenishments', array(
                                  'timestamp' => time(),
                                  'notification_sent' => 0,
                                  'item_id' => $row->id
                                ))
                        ->execute();
            }
            
            // Get userless, SKUless data
            $newData = $this->removeSKU($this->removeUsers($data));
          
  
            // Just update
            $this->parent->db->update('bf_items', $this->removeSKU($newData))
                             ->where('sku = \'{1}\'', $sku)
                             ->limit(1)
                             ->execute();
          }
          catch(Exception $exception) { }
                         
          // Count matches
          $matches = $this->parent->db->info['rows_matched'];
    
          // Perform actions for user headings
          for($hID = 0; $hID < count($userHeadings); $hID ++)
          {
            if($userHeadings[$hID] != false)
            {
              $item = $this->parent->db->getRow('bf_items', $sku, 'sku');
              
              if(!$item)
              {
                // Invalid item
                break;
              }
              
              $itemID = $item->id;
              $userID = intval($userHeadings[$hID]);
              $userValue = floatval($data['user' . $userHeadings[$hID]]);
              
              // Make change
              // Remove override first
              $removal = $this->parent->db->query();
              $removal->delete('bf_user_prices')
                      ->where('`item_id` = \'{1}\' AND `user_id` = \'{2}\'',
                        $itemID, $userID)
                      ->execute();
                      
              // Updating something...
              $matches = 1;
                      
              // Add new records if not blank
              if(!empty($data['user' . $userHeadings[$hID]]))
              {
                $insertion = $this->parent->db->query();
                $insertion->insert('bf_user_prices', array(
                              'item_id' => $itemID,
                              'user_id' => $userID,
                              'trade_price' => $userValue,
                              'pro_net_price' => $userValue
                            ))
                          ->execute();
                          
                $userRows ++;
              }
            }
          }            
                      
                           
          if($matches == 1)
          {
            // Update OK
            $updated[$sku] = 'The item was updated.';
          }
          else
          {
            // Not found
            $noAction[$sku] = 'The item was not found in the Inventory.';
          }
          
      }
    }
     
 
    // Data imports + 1
    $this->parent->stats->increment('com.b2bfront.stats.admins.data-imports', 1);
     
    return array(
      'noaction' => $noAction,
      'updated' => $updated,
      'created' => $created,
      'user' => $userRows,
      'total' => $total
    );
  }
  
  /**
   * Convert a string to a comparable column heading
   * @param string $text The column heading
   * @return string
   */
  private function toColumnHeading($text)
  {
    return strtolower(trim($text));
  }
  
  /**
   * Convert a "nice" column heading to a potential user column heading.
   * @param string $text The column heading to convert
   * @return string|boolean 
   */
  private function toUserColumn($text)
  {
    // Find a user
    $user = $this->parent->db->getRow('bf_users', $text, 'name');
    
    if(!$user)
    {
      // Doesn't exist
      return false;
    }
    
    // Success
    return $user->id;
  }

  /**
   * Convert a "nice" column heading to a "database" column name.
   * @param string $text The column heading to convert
   * @return string|boolean
   */
  private function toDataColumn($text)
  {
    // Define the column translation table
    $columns = array(
      'sku' => 'sku',
      'part number' => 'sku',
      
      // Multiple values for Name
      'name' => 'name',
      'item name' => 'name',
      
      // Multiple values for Trade Price
      'trade price' => 'trade_price',
      'trade' => 'trade_price',
      
      // Multiple values for Pro Net Price
      'pro net' => 'pro_net_price',
      'pro net price' => 'pro_net_price',
      
      // Multiple values for Pro Net Quantity
      'pn qty' => 'pro_net_qty',
      'pro net qty' => 'pro_net_qty',
      'pro net quantity' => 'pro_net_qty',
      'pro net' => 'pro_net_qty',
      
      // Safety columns
      'wholesale price' => 'wholesale_price',
      'wholesale' => 'wholesale_price',
      'rrp' => 'rrp_price',
      'cost price' => 'cost_price',
      'cost' => 'cost_price',
      'free stock' => 'stock_free',
      'held stock' => 'stock_held',
      'stock free' => 'stock_free',
      'stock' => 'stock_free',
      'free' => 'stock_free',
      'stock' => 'stock_free',
      'barcode' => 'barcode',
      'description' => 'description'
    );
    
    // Find the column
    if(array_key_exists($this->toColumnHeading($text), $columns))
    {
      return $columns[$this->toColumnHeading($text)];
    }
    
    // Not found
    return false;
  }
  
  /**
   * Remove SKU from a data set to make it suitable for updating
   * @param array $data The data set as a string => string array
   * @return array
   */
  private function removeSKU($data)
  {
    $newData = $data;
    
    if(isset($newData['sku']))
    {
      unset($newData['sku']);
    }
    
    return $newData;
  }
  
  /**
   * Remove Users from a data set to make it suitable for updating
   * @param array $data The data set as a string => string array
   * @return array
   */
  private function removeUsers($data)
  {
    $newData = $data;
    
    foreach($newData as $key => $value)
    {
      if(strpos($key, 'user') !== false)
      {
        unset($newData[$key]);
      }
    }
    
    return $newData;
  }
  
  /**
   * Schedule a data application
   * @param string $name The name of the schedule for the user's reference
   * @parram string $timestamp The time when the file will be applied.
   * @param string $path The path to the file that will be applied
   * @param boolean $createNewSKUs Create new SKUs for rows that do not exist.
   * @param boolean $notifySMS Notify the admin via SMS on application
   * @param boolean $notifyEmail Notify the admin via Email on application
   * @param integer $adminID The admin that created the schedule
   * @return boolean
   */
  public function schedule($name, $timestamp, $path, $createNewSKUs, $notifySMS,
                           $notifyEmail, $adminID)
  {
    // Create a schedule row
    $this->db->insert('bf_scheduled_imports', array(
                       'name' => $name,
                       'timestamp' => $timestamp,
                       'path' => $path,
                       'create_new_skus' => $createNewSKUs,
                       'notification_email' => $notifyEmail,
                       'notification_sms' => $notifySMS,
                       'completed' => 0,
                       'admin_id' => $adminID
                     ))
             ->execute();
    
    return true;
  }
  
  /**
   * Remove a scheduled import.
   * @param string $scheduleID The ID of the schedule to remove.
   * @return boolean
   */
  public function unSchedule($scheduleID)
  {
    // Find the schedule
    $this->db->select('*', 'bf_scheduled_imports')
             ->where("`id` = '{1}'", $scheduleID)
             ->limit(1)
             ->execute();
             
    if($this->db->count == 0)
    {
      return false;
    } 
             
    // Get the row
    $scheduleRow = $this->db->next();
    
    // Remove the file
    @unlink($scheduleRow->path);
  
    // Remove the schedule
    $this->db->delete('bf_scheduled_imports')
             ->where("`id` = '{1}'", $scheduleRow->id)
             ->limit(1)
             ->execute();
               
    return true;
  }

  /**
   * Update a data application
   * @param string $scheduleID The ID of the schedule to be updated
   * @parram string $timestamp The time when the file will be applied.
   * @param boolean $createNewSKUs Create new SKUs for rows that do not exist.
   * @param boolean $notifySMS Notify the admin via SMS on application
   * @param boolean $notifyEmail Notify the admin via Email on application
   * @return boolean
   */
  public function updateSchedule($scheduleID, $timestamp, $createNewSKUs, $notifySMS,
                                 $notifyEmail)
  {
    // Create a schedule row
    $this->db->update('bf_scheduled_imports', array(
                      'timestamp' => $timestamp,
                      'create_new_skus' => $createNewSKUs,
                      'notification_email' => $notifyEmail,
                      'notification_sms' => $notifySMS
                    ))
            ->execute();
    
    return true;
  }
}
?>