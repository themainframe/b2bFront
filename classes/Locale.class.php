<?php
/** 
 * Locale Class
 * Represents the current locale containing country-specific localisation data.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class Locale extends Base
{
  /**
   * Create a new Locale object.
   * @param integer $localeID The ID of the locale to load.
   * @param BFClass* $parent A reference to the parent object.
   * @return View
   */
  function __construct($localeID, & $parent)
  {
    // Copy parent object reference
    $this->parent = $parent;
    
    // Load the locale
    $localeRow = $this->parent->db->getRow('bf_locales', $localeID);
    
    if(!$localeRow)
    {
      // Locale does not exist.
      // Defaults mode.
      
      return;
    }
    
    // Set up the properties of this object for fast access.
    $this->currencySymbol = $this->CS = $localeRow->currency_html_entity;
    $this->exchangeRate = $this->XR = $localeRow->currency_xr;
    $this->languageCode = $localeRow->language_code;
    $this->currencyName = $localeRow->currency_name;
    $this->iconPath = $localeRow->icon_path;
  }
}
?>