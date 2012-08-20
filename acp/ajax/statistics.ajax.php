<?php
/**
 * Statistics
 * AJAX Responder
 * Sends AJAX replies to the jQueryFileTree objects.
 *
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */
 
// Set context
define('BF_CONTEXT_ADMIN', true);

// Relative path for this - no BF_ROOT yet.
require_once('../admin_startup.php');
require_once(BF_ROOT . 'tools.php');

// New BFClass & Admin class
$BF = new BFClass();
$BF->admin = new Admin(& $BF);

if(!$BF->admin->isAdmin)
{
  exit();
}

// Find the statDomain ID if supplied
$statDomainID = $BF->in('dir');

// Get the tree_name variable too.
// This allows multiple jQueryFileTrees on one page
$treeName = $BF->in('tree_name');

// Is statDomain ID set?
if($statDomainID)
{
  // Allow showing of subcategories?
  if($BF->in('restrict') != '1')
  {
    // Produce HTML
    print '<ul class="jqueryFileTree" style="display: none;">' . "\n";
  
    // List the contents of that category
    $statistics = $BF->db->query();
    $statistics->select('*', 'bf_statistics')
               ->where('domain_id = {1}', $statDomainID)
               ->order('description', 'asc')
               ->execute();
                  
    while($statistic = $statistics->next())
    {
      print '  <li class="directory collapsed magnifier">' . "\n";
      print '    <a domain="' . $catid . '" statistic="' . $statistic->id . '" class="' . 
            $treeName . '_dir_link dir_link" href="#" rel="-1">';
      print htmlentities($statistic->description) . '</a>' . "\n";
      print '</li>' . "\n\n";
    }
    
    print '</ul>';
    
  }
}
else
{

  // Produce HTML
  print '<ul class="jqueryFileTree" style="display: none;">' . "\n";
  
  // List all statistics domains
  $statisticsDomains = $BF->db->query();
  $statisticsDomains->select('*', 'bf_statistics_domains')
                    ->order('title', 'asc')
                    ->execute();
             
  while($statisticsDomain = $statisticsDomains->next())
  {
    print '  <li style="background-image:url(' . $statisticsDomain->icon_path . ')" class="directory collapsed">' . "\n";
    print '    <a cat="' . 
          $statisticsDomain->id . '" statistic="-1" class="' . 
          $treeName . '_dir_link dir_link" href="#" rel="' . 
          ($statisticsDomain->id) . '">';
    print htmlentities($statisticsDomain->title) . '</a>' . "\n";
    print '</li>' . "\n\n";
  }
  
  print '</ul>';
}

$BF->shutdown();

?>