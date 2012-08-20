<?php
/**
 * RestorePoints
 * Provides Restore Point and Backup functions.
 * Admin API
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
class RestorePoints extends API
{
  /**
   * The full path to a directory where restore points are saved by this API
   * @var string
   */
  protected $path = '/restore_points/';
  
  /**
   * The full path to the mysqldump binary
   * If mysqldump is in $PATH, may be simply 'mysqldump'
   * @var string
   */
  protected $mysqldumpPath = '/usr/local/zend/mysql/bin/mysqldump';
  
  /**
   * Create a RP at this moment
   * @param string $reason The reason for the Restore Point's creation.
   * @return boolean True on success, False on failure.
   */
  public function create($reason)
  { 
    // Load path information
    
    // Reason
    if(!trim($reason))
    {
      $reason = 'Automatic Restore Point';
    }
    
    // Create a data entry for the RP.
    $this->db->insert('bf_restore_points', array(
                         'creation_reason' => $reason,
                         'timestamp' => time()
                       ))
             ->execute();
               
    // Recover the Insert ID
    $restorePointID = $this->db->insertID;
    
    // Make a directory
    system('mkdir ' . BF_ROOT . $this->path . '/rp' . $restorePointID);
    system('chmod 777 ' . BF_ROOT . $this->path . '/rp' . $restorePointID);
    
    // Remake a symlink to the latest location
    system('rm ' . BF_ROOT . $this->path . '/rp-latest');
    system('ln -s ' . BF_ROOT . $this->path . '/rp' . $restorePointID . ' ' . 
      BF_ROOT . $this->path . '/rp-' . 'latest');
    
    // Create a file
    system('touch ' . BF_ROOT . $this->path . '/rp' . $restorePointID . '/all-db-tables.sql');
    system('chmod 777 ' . BF_ROOT . $this->path . '/rp' . $restorePointID . '/all-db-tables.sql');
    
    // Build command
    $command = 'mysqldump ' . BF_SQL_DB . ' -u ' . BF_SQL_USER . ' --password=' . addslashes(BF_SQL_PASS);
    
    // Add path information
    $command .= ' > ' . BF_ROOT . $this->path . '/rp' . $restorePointID . 
                '/all-db-tables.sql';
  
    // Execute
    system($command);
    
    // Clean up
    unset($command);
    
    return true;
  }
  
  /**
   * Restore to the specified RP.
   * Perform the restoration using the Restore Option IDs given.
   * @param integer $restorePointID The Restore Point ID to restore to.
   * @param array $restoreOptions An array of Restore Options to apply.
   * @return boolean True on success, False on failure
   */
  public function restore($restorePointID, $restoreOptions)
  {
    // Convert ID to integer
    $restorePointID = intval($restorePointID);
    
    // Check options
    if(!is_array($restoreOptions))
    {
      return false;
    }
    
    // Check options and build an "IN" clause
    $clause = 'IN(';
    foreach($restoreOptions as $restoreOption)
    {
      $clause .= intval($restoreOption) . ',';
    }
    $clause = substr($clause, 0, -1) . ')';
    
    // Query for options
    $options = $this->db->query();
    $options->select('*', 'bf_restore_options')
            ->where('id ' . $clause)
            ->execute();
            
    while($option = $options->next())
    {
      // Restore this option
      $command = 'cat ' . $this->path . '/rp' . $restorePointID . '/' . $option->filename . 
                 '.sql | mysql ' . BF_SQL_DB . ' -u ' . BF_SQL_USER . ' --password=' . BF_SQL_PASS;
                 
      // Execute
      system($command);
    }
    
    return true;
  }
   
}
?>