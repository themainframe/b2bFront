<?php
/**
 * Module: Website
 * Mode: Articles
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

<h1>Articles</h1>
<br />

<div class="panel">
  <div class="contents">
    <h3>About Articles</h3>
    <p>
      Articles allow you to provide content to the website.<br /><br />
      For example, if a part of your skin displays a specified item, you should create an article that contains that item here with the appropriate name.<br />
      By choosing an appropriate Article Category, you can make articles appear in various places on the website.
      <br /><br />
      <a href="./?act=website&mode=articles_categories" title="Article Categories">Click here</a> to modify
      Article Categories.
    </p>
    
    <br />
    <span class="button">
      <a href="./?act=website&mode=articles_add">
        <span class="img" style="background-image:url(/acp/static/icon/plus-button.png)">&nbsp;</span>
        New Article...
      </a>
    </span>
    
    <br /><br />
    
  </div>
</div>

<br />

<div class="panel">
  <div class="contents" style="padding: 0px 0px 8px 10px;">
      
      <img src="./static/icon/folder.png" class="middle" alt="Folder" />
      &nbsp;
      
      <strong>Filter by Article Category: </strong> &nbsp; 
      <?php
        
        $articleCategories = $BF->db->query();
        $articleCategories->select('*', 'bf_article_categories')
                          ->order('name', 'ASC')
                          ->execute();
        
        // Create a UI element
        $dropDown = new DataDropDown('f_article_category', $articleCategories, 'id', 'name',
                                     array('-1' => 'None'));
        $dropDown->setOption('defaultSelection', $BF->in('f_category_id'));
        $dropDown->setOption('css', array(
          'margin-top' => '10px'
        ));
        print $dropDown->render();

      
      ?>
  </div>
</div>

<br /> 

<?php

  // Create a new query to retreieve articles and their category names
  $query = $BF->db->query();
  $query->select('`bf_articles`.`id` AS article_id, `bf_articles`.`name` AS article_name,' . 
                 ' `bf_article_categories`.`name` AS article_category, `bf_articles`.`type` AS article_type',
                 'bf_articles')
        ->text('INNER JOIN `bf_article_categories` ON ' . 
               '`bf_articles`.`article_category_id` = `bf_article_categories`.`id`');
        
  // Where clause?
  if($BF->inInteger('f_category_id') != '-1' && $BF->in('f_category_id') != '')
  {
    $query->where('`bf_articles`.`article_category_id` = \'{1}\'', $BF->inInteger('f_category_id'));
  }
        
  // Define a tool set HTML
  $confirmationJS = 'confirmation(\'Really remove this article?\', function() { window.location=\'' .
                    Tools::getModifiedURL(array('mode' => 'articles_remove_do')) . '&id={article_id}\'; })';
  
  // Buttons
  $toolSet  = "\n";
  $toolSet .= '<a onclick="' . $confirmationJS . '" class="tool" title="Remove">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/cross-circle.png" alt="Remove" />' . "\n";
  $toolSet .= 'Remove</a>' . "\n";
  $toolSet .= '<a class="tool" title="Modify" href="./?act=website&mode=articles_modify&id={article_id}">' . "\n";
  $toolSet .= '  <img src="/acp/static/icon/zone--pencil.png" alt="Modify" />' . "\n";
  $toolSet .= 'Modify</a>' . "\n";
  
  // Create a data table view to show the pages
  $articles = new DataTable('pg1', $BF, $query);
  $articles->setOption('alternateRows');
  $articles->setOption('showTopPager');
  $articles->addColumns(array(
                          array(
                            'dataName' => 'article_name',
                            'niceName' => 'Article Name'
                          ),
                          array(
                            'dataName' => 'article_category',
                            'niceName' => 'Article Category'
                          ),
                          array(
                            'dataName' => 'article_type',
                            'niceName' => 'Type',
                            'options' => array(
                                           'callback' => function($row)
                                                         {
                                                           switch($row->article_type)
                                                           {
                                                             case 'ART_TEXT':              return 'Text';
                                                             case 'ART_ITEM':              return 'Item Link';
                                                             case 'ART_ITEM_COLLECTION':   return 'Item Collection Link';
                                                             case 'ART_IMAGE':             return 'Image';
                                                             case 'ART_CATEGORY':          return 'Category Link';
                                                             default:                      return 'Unknown';
                                                           }
                                                         }
                                         )
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
  print $articles->render();
?>