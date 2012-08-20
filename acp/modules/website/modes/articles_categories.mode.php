<?php
/**
 * Module: Website
 * Mode: Article Categories
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

<script type="text/javascript">

  $(function() {
  
    $('#dd_f_article_category').change(function() {
      window.location = './?act=website&mode=articles&f_category_id=' + $(this).val();
    });
  
  });

</script>

<h1>Article Categories</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Article Categories</h3>
    <p>
      Article Categories allow you to group <a href="./?act=website&mode=articles">articles</a> together by subject.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=website&mode=articles_add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)">&nbsp;</span>
        New Article Category...
      </a>
    </span>
    
    <br /><br />
    
  </div>
</div>

<br /> 

<?php

  // Create a new query to retreieve article categories
  $query = $BF->db->query();
  $query->select('*', 'bf_article_categories')
        ->where('`designation` <> \'-trash-\'');
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this article category?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'articles_categories_remove_do')) . '&id={id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=website&mode=articles_categories_modify&id={id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  
  // Create a data table view to show the pages
  $articleCategories = new DataTable('pg1', $BF, $query);
  $articleCategories->setOption('alternateRows');
  $articleCategories->setOption('showTopPager');
  $articleCategories->addColumns(array(
                                  array(
                                    'dataName' => 'name',
                                    'niceName' => 'Name'
                                  ),
                                  array(
                                    'dataName' => 'designation',
                                    'niceName' => 'Designation'
                                  ),
                                  array(
                                    'dataName' => '',
                                    'niceName' => 'Actions',
                                    'options' => array('fixedOrder' => false),
                                    'css' => array(
                                               'width' => '130px',
                                               'text-align' => 'right',
                                               'padding-right' => '10px'
                                             ),
                                    'content' => $toolSet
                                  )
                                ));
            
  // Render & output content
  print $articleCategories->render();
?>