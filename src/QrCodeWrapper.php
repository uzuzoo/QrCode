<?php

/*
 * (c) Alex Wright "https://github.com/uzuzoo/QrCode"
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Uzuzoo\QrCode;
use DateTime;
use Uzuzoo\QrCode\QrCode;
use Uzuzoo\QrCode\Exceptions\WrapperIncompleteDataException;

/**
 * Generate QR Code.
 */
class QrCodeWrapper extends QrCode
{
  const EMAIL_TYPE_HOME = 'HOME';
  const EMAIL_TYPE_WORK = 'WORK';
  const EMAIL_TYPE_OTHER = 'OTHER';

  private $allowedEmailtypes = array(
    self::EMAIL_TYPE_HOME,
    self::EMAIL_TYPE_WORK,
    self::EMAIL_TYPE_OTHER,
  );

  private $defaultEmailType = self::EMAIL_TYPE_OTHER;

  const ADDRESS_TYPE_DOMESTIC = 'dom';
  const ADDRESS_TYPE_INTERNATIONAL = 'intl';
  const ADDRESS_TYPE_POSTAL = 'postal';
  const ADDRESS_TYPE_PARCEL = 'parcel';
  const ADDRESS_TYPE_HOME = 'home';
  const ADDRESS_TYPE_WORK = 'work';

  /**
   * Allowed Address Types
   * @var array
   */
  private $allowedAddressTypes = array(
    self::ADDRESS_TYPE_DOMESTIC,
    self::ADDRESS_TYPE_INTERNATIONAL,
    self::ADDRESS_TYPE_POSTAL,
    self::ADDRESS_TYPE_PARCEL,
    self::ADDRESS_TYPE_HOME,
    self::ADDRESS_TYPE_WORK,
  );

  /**
   * Default address types
   * @var array
   */
  private $defaultAddressTypes = array(
    self::ADDRESS_TYPE_INTERNATIONAL,
    self::ADDRESS_TYPE_POSTAL,
    self::ADDRESS_TYPE_PARCEL,
  );

  public function setText($text='')
  {
    return parent::setText($text);
  }

  public function setUrl($url='')
  {
    return $this->setText($url);
  }

  public function getAllowedAddressTypes()
  {
    return $this->allowedAddressTypes;
  }

  public function getAllowedEmailTypes()
  {
    return $this->allowedEmailtypes;
  }

  /**
   * @param array $params
   */
  public function setVcardV3($params = array())
  {
    $text = $this->buildVcardV3($params);
    return $this->setText($text);
  }



  /**
   * @param mixed $params
   * http://www.evenx.com/vcard-3-0-format-specification
   */
  private function buildVcardV3($params = array())
  {
    if(!isset($params['personName'])) {
      throw new WrapperIncompleteDataException('vCard3: personName must be supplied.');
    }

    $component = array();
    $component[] = 'BEGIN:VCARD'; // begin the vcard wrapper
    $component[] = 'VERSION:3.0';  // vCard Version
    // vCard Person Name
    $component[] = $this->buildVcardV3PersonName($params);
    // vCard address (optional)
    if(isset($params['address'])) {
      $component[] = $this->buildVcardV3Address($params);
    }




    // vCard Birthday (YYYY-MM-DD)
    if((isset($params['birthday'])) && ($this->isDate($params['birthday']))) {
      $component[] = 'BDAY:'.$params['birthday'];
    }
    // vCard email
    if((isset($params['emails'])) && ($params['emails'])) {
      $component[] = $this->buildVcardV3Emails($params);
    }


    // vCard Organisation
    if((isset($params['organisationName'])) && ($params['organisationName'])) {
      $component[] = 'ORG:'.$params['organisationName'];
    }
    // vCard Job Title
    if((isset($params['jobTitle'])) && ($params['jobTitle'])) {
      $component[] = 'ROLE:'.$params['jobTitle'];
    }

    $component[] = 'END:VCARD'; // end the vcard wrapper
    return implode("\n", $component);
  }


  private function buildVcardV3Emails($params = array())
  {
    $component = array();
    foreach ($params['emails'] as $emails) {
      if(is_array($emails) && isset($emails['email']) && ($emails['email'])) {
        // Emails are sent as an array
        $type = ((isset($emails['type']) && in_array(strtoupper($emails['type']), $this->getAllowedEmailTypes())) ? strtoupper($emails['type']) : $this->defaultEmailType);
        $component[] = 'EMAIL;'.$type.';PREF,INTERNET:'.$emails['email'];
      } elseif($emails) {
        // emails are sent as strings
        $component[] = 'EMAIL;'.$this->defaultEmailType.';PREF:'.$emails;
      }
    }
    return implode("\n", $component);
  }

  private function buildVcardV3Address($params = array())
  {
    $component = array();
    $goodTypes = array();
    if(isset($params['address']['type'])) {
      $goodTypes = array_intersect(explode(",", $params['address']['type']), $this->allowedAddressTypes);
    }
    $goodTypes = (($goodTypes) ? $goodTypes : $this->defaultAddressTypes);


    $component[] = 'ADR;TYPE='.implode(",", $goodTypes).":".implode(";", array(
      @$params['address']['poAddress'],
      @$params['address']['extendedAddress'],
      @$params['address']['street'],
      @$params['address']['locality'],
      @$params['address']['region'],
      @$params['address']['postalCode'],
      @$params['address']['country'],
    ));
    return implode("\n", $component);
  }

  private function buildVcardV3PersonName($params = array())
  {
    // minimum requirements is firstName, lastName & displayName
    if((!isset($params['personName']))
    || (!isset($params['personName']['firstName']))
    || (!isset($params['personName']['lastName']))
    || (!$params['personName']['firstName'])
    || (!$params['personName']['lastName'])) {
      throw new WrapperIncompleteDataException('vCard3: firstName & lastName must be supplied.');
    }
    $pName = $params['personName'];
    $component = array();
    $component[] = 'N:'.implode(";", array($pName['lastName'], $pName['firstName'], $pName['otherName'], $pName['namePrefix'], $pName['nameSuffix']));
    $component[] = 'FN:'.((isset($pName['displayName']) && ($pName['displayName'])) ? $pName['displayName'] : implode(" ", array($pName['lastName'], $pName['firstName'])));
    return implode("\n", $component);
  }

  /**
   * Check a date is a valid date
   * @param mixed $date
   * @param mixed $format
   */
  private function isDate($date, $format = 'Y-m-d')
  {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
  }

}
