<?php
/**
 * Module: Dealers
 * Mode: Discounting Matrix Modification
 * 
 * @copyright 2010-2011 Damien Walsh
 * @package b2bfront
 * @version 1.0
 * @author Damien Walsh
 */

// Context check
if(!defined("BF_CONTEXT_ADMIN") || !defined("BF_CONTEXT_MODULE"))
{
  exit();
}

?>

<h1>Discount Matrix</h1>
<br />

<div class="panel">
  <div class="contents" style="">
    <h3>About Discount Matrix</h3>
    <p>
      The Discount Matrix governs how each 
      <a href="./?act=dealers&mode=bands" target="_blank" class="new" title="Modify discount bands">discount band</a>
      affects items in different categories.<br />
      The values below are multipliers applied to the Trade prices of items.<br /><br />
      
      For example, a value of <em>0.95000</em> would mean a dealer on that band pays <em>95%</em> of
      the Trade price for items in the associated category.<br /><br />
      
      To modify a matrix value, click it and enter a replacement value, then click off or press enter
      to finish editing.  <br />
      Changes to the matrix values are saved automatically.
    </p>
  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 0px 0px 0px 7px; color: #50954b; text-align: center ">
      <strong>Changes made in this section are saved automatically.</strong>
    </p>  
  </div>
</div>

<br />

<div class="panel" style=" background: #fff; border: none;">
  <div class="contents" style="padding: 20px;">
    <table style="width: 100%;" class="data">
      <thead>
<?php

  // In one query, collect all categories
  $categories = $BF->db->query();
  $categories->select('*', 'bf_categories')
             ->order('name', 'asc')
             ->execute();
        
  // Load all bands
  $bands = $BF->db->query();
  $bands->select('*', 'bf_user_bands')
        ->order('name', 'asc')
        ->execute();
        
  // Load all band values into memory
  $bandValues = $BF->db->query();
  $bandValues->select('*', 'bf_matrix')
             ->execute();
   
  // Check each of the categories has entries present
  while($category = $categories->next())
  {
    $found =  false;
    
    // Look at band values
    while($bandValue = $bandValues->next())
    {
      if($bandValue->category_id == $category->id)
      {
        $found = true;
      }
    }
    
    // Create bandvalues if required
    if(!$found)
    {
      // Make a new value for each band for this category
      while($band = $bands->next())
      {
        $createBand = $BF->db->query();
        $createBand->insert('bf_matrix', array(
                             'value' => 1.0,
                             'band_id' => $band->id,
                             'category_id' => $category->id
                           ))
                   ->execute();
      }
      
      $bands->rewind();
    }
    
    // Reset
    $bandValues->rewind();    
  }
   
  // Reset used resources before loading data
  $bands->rewind();
  $bandValues->rewind();
  $categories->rewind();           
             
  // Arrange band values into a 2D Array
  $matrix = array();
  while($bandValue = $bandValues->next())
  {
    $matrix[$bandValue->category_id][$bandValue->band_id]['value'] = 
      (float)$bandValue->value;
      
    // Also attach the ID so that the row may be edited via AJAX
    $matrix[$bandValue->category_id][$bandValue->band_id]['id'] = 
      $bandValue->id;
  }
  
  // Reset used resources before drawing the UI
  $bands->rewind();
  $bandValues->rewind();
  $categories->rewind();
        
  
  // Write a header row
  
  print '      <tr>' . "\n" . 
        '        <td class="matrix_heading matrix_top_heading">&nbsp;</td>' . "\n";
  
  while($band = $bands->next())
  {
      print '        <td style="width: 150px;" class="matrix_heading matrix_top_heading">' . 
            $band->name . '</td>' . "\n";
  }
  
  print '      </tr>' . "\n";
        
  ?>
  
  </thead>
  
  <?php
        
  // Alternation
  $alternate = '#fff';
  
  // Display each category
  while($category = $categories->next())
  {
    ?>
        
      <tr style="height: 20px;">
        
        <td class="matrix_heading"><?php print $category->name; ?></td>
        
    <?php
    
      // For each category, write the band values out
      $bands->rewind();
      
      while($band = $bands->next())
      {
        
        // Get ID
        $matrixRowID = (int)$matrix[$category->id][$band->id]['id'];
      
        ?>
        
          <td class="matrix_editable" style="background: <?php print $alternate; ?>">
            <span id="matrix_<?php print $matrixRowID; ?>" table="bf_matrix" 
            class="editable ui-draggable ui-droppable" dual="true" rowid="<?php print $matrixRowID; ?>" cache="matrix" field="value"><?php print number_format($matrix[$category->id][$band->id]['value'], 5); ?></span>
          </td>
        
        <?php
      }
    
    ?>
        
      </tr>
      
    <?php
    
    $alternate = ($alternate == '#fff' ? '#efefef' : '#fff');
    
  }

?>

    </table>

  </div>
</div>

<br />

<div class="panel" style="border: 1px solid #50954b">
  <div class="contents">
    <p style="padding: 0px 0px 0px 7px; color: #50954b; text-align: center ">
      <strong>Changes made in this section are saved automatically.</strong>
    </p>  
  </div>
</div>