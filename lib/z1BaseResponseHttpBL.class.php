<?php

/**
 * ResponseHttp:BL class is a response object from Blacklist-server.
 *
 * @package    z1HttpBLPlugin
 * @author     Hermann Bernwald <httpbl [at] zero1 [dot] ath [dot] cx>
 * @version    SVN: $Id$
 */
class z1BaseResponseHttpBL
{
  /**
   * represents the number of days since last activity
   *
   * @var        int
   */
  protected $response_activity;

  /**
   * represents a threat score for IP
   *
   * @var        int
   */
  protected $response_score;

  /**
   * represents the type of visitor
   *
   * @var        int
   */
  protected $response_type;

  /**
   * define maximum age of last-activity for bad guy
   *
   * @var        int
   */
  protected $config_max_age;

  /**
   * define minimum score for a bad guy
   *
   * @var        int
   */
  protected $config_min_score;

  /**
   * define minimum type will be, to be a bad guy
   *
   * @var        int
   */
  protected $config_min_type;


  /**
   * create a ResponeHttpBL-object (as factory method)
   *
   * @param      array   $httpbl_response
   * @return     z1ResponseHttpBL   response object
   */
  public static function createResponse(array $httpbl_response)
  {
    return new self($httpbl_response);
  }

  /**
   * construct to build a object
   *
   * @access     private
   * @param      array   raw httpbl-response
   */
  private function __construct(array $httpbl_response)
  {
    $this->config_max_age = false;
    $this->config_min_score = false;
    $this->config_min_type = false;

    if (is_array($httpbl_response)===true &&
      isset($httpbl_response[0], $httpbl_response[1], $httpbl_response[2], $httpbl_response[3])===true &&
      $httpbl_response[0]==127) {
      $this->response_activity = (int)$httpbl_response[1];
      $this->response_score = (int)$httpbl_response[2];
      $this->response_type = (int)$httpbl_response[3];
    }
    $this->configure();
  }

  /**
   * configure this object, to use isBadGuy()-method
   * if all params is false,
   * try to find needed values in app.yml with sfConfig (worst depency)
   *
   * @param      int   max age of last activity
   * @param      int   min score for bad guy
   * @param      int   min type for bad guy
   * @return     bool  if all needed param was setted return true, otherwise false
   */
  public function configure($max_age=false, $min_score=false, $min_type=false)
  {

    if ($max_age===false && sfConfig::has('app_httpbl_max_age')===true) {
      $max_age = sfConfig::get('app_httpbl_max_age', false);
    }
    if ($min_score===false && sfConfig::has('app_httpbl_min_score')===true) {
      $min_score = sfConfig::get('app_httpbl_min_score', false);
    }
    if ($min_type===false && sfConfig::has('app_httpbl_min_type')===true) {
      $min_type = sfConfig::get('app_httpbl_min_type', false);
    }

    $output_max_age = $this->setConfigMaxAge($max_age);
    $output_min_score = $this->setConfigMinScore($min_score);
    $output_min_type = $this->setConfigMinType($min_type);

    return $output_max_age===true && $output_min_score===true && $output_min_type===true;
  }

  /**
   * set Max-Age for last activity
   *
   * @param      int   max age
   * @return     bool   if successfully set, return true, otherwise false
   */
  public function setConfigMaxAge($max_age)
  {
    $output = false;
    if (is_numeric($max_age)===true) {
      $this->config_max_age = (int)$max_age;
      $output = true;
    }
    return $output;
  }

  /**
   * set Min-Score for bad guy
   *
   * @param      int   min score
   * @return     bool   if successfully set, return true, otherwise false
   */
  public function setConfigMinScore($min_score)
  {
    $output = false;
    if (is_numeric($min_score)===true) {
      $this->config_min_score = (int)$min_score;
      $output = true;
    }
    return $output;
  }

  /**
   * set Min-type for bad guy
   *
   * @param      int   min type
   * @return     bool   if successfully set, return true, otherwise false
   */
  public function setConfigMinType($min_type)
  {
    $output = false;
    if (is_numeric($min_type)===true) {
      $this->config_min_type = (int)$min_type;
      $output = true;
    }
    return $output;
  }

  /**
   * get Max-Age for last activity
   *
   * @return     int   if was setted int, otherwise false
   */
  public function getConfigMaxAge()
  {
    return $this->config_max_age;
  }

  /**
   * get Min score
   *
   * @return     int   if was setted int, otherwise false
   */
  public function getConfigMinScore()
  {
    return $this->config_min_score;
  }

  /**
   * get Min type
   *
   * @return     int   if was setted int, otherwise false
   */
  public function getConfigMinType()
  {
    return $this->config_min_type;
  }

  /**
   * check if all params we need was setted
   *
   * @return     bool   if was configured return true, otherwise false
   */
  public function isConfigured()
  {
    $output = true;
    if ($this->getConfigMaxAge()===false || $this->getConfigMinScore()===false || $this->getConfigMinType()===false) {
      $output = false;
    }
    return $output;
  }

  /**
   * get Last activity
   *
   * @return     int
   */
  public function getLastActivity()
  {
    return $this->response_activity;
  }

  /**
   * get Score
   *
   * @return     int
   */
  public function getScore()
  {
    return $this->response_score;
  }

  /**
   * get Type
   *
   * @return     int
   */
  public function getType()
  {
    return $this->response_type;
  }

  /**
   * check if ip is search-engine
   *
   * @return     bool
   */
  public function isSearchEngine()
  {
    return $this->response_type===0;
  }

  /**
   * check if ip is Suspect
   *
   * @return     bool
   */
  public function isSuspicious()
  {
    return $this->response_type===1;
  }

  /**
   * check if ip is harvester
   *
   * @return     bool
   */
  public function isHarvester()
  {
    return $this->response_type===2;
  }

  /**
   * check if ip is Suspect + Harvester
   *
   * @return     bool
   */
  public function isSuspiciousHarvester()
  {
    return $this->response_type===3;
  }

  /**
   * check if ip is Spammer
   *
   * @return     bool
   */
  public function isSpammer()
  {
    return $this->response_type===4;
  }

  /**
   * check if ip is Suspect + Spammer
   *
   * @return     bool
   */
  public function isSuspiciousSpammer()
  {
    return $this->response_type===5;
  }

  /**
   * check if ip is Harvester + Spammer
   *
   * @return     bool
   */
  public function isHarvesterSpammer()
  {
    return $this->response_type===6;
  }

  /**
   * check if ip is Suspect + Harvester + Spammer
   *
   * @return     bool
   */
  public function isSuspiciousHarvesterSpammer()
  {
    return $this->response_type===7;
  }

  /**
   * check if ip is realy bad guy
   * compare last-activity, score and type
   * with your config
   *
   * @return     bool
   * @throws     sfException   if not configured
   */
  public function isBadGuy()
  {
    $output = false;
    if ($this->isConfigured()===false) {
      throw new sfException('z1ResultHttpBL need valid max_age, min_score and min_type parameter; please recheck your yaml-config!', 403);
    }
    if ($this->response_activity<=$this->config_max_age &&
      $this->response_score>=$this->config_min_score &&
      $this->response_type>=$this->config_min_type) {
      $output = true;
    }
    return $output;
  }
}