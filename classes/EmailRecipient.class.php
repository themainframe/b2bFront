<?php
/** 
 * EmailRecipient Class
 * A recipient of an Email class instance.
 * Contains merge targets for Mail-Merge style mailshots.
 * 
 * @author Damien Walsh <damien@transcendsolutions.net>
 * @package b2bfront
 * @version 1.0
 */
class EmailRecipient extends Base
{
  /** 
   * The address of this individual
   * @var string
   */
  public $address = '';

  /**
   * An associative array containing target=>value for mail merge placeholders.
   * @var string
   */
  public $mergeValues = array();
  
  /**
   * Create a new Email Recipient
   * @param string $emailAddress The email address of this individual
   * @param array $values Optionally an associative array of mail merge values for this recipient.
   * @return EmailRecipient
   */
  public function __construct($emailAddress, $values = array())
  {
    $this->address = $emailAddress;
    $this->mergeValues = $values;
  }
}
?>