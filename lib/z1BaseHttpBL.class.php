<?php

/**
 * Http:BL Plugin allows you to verify IP addresses of clients connecting to your site against
 * the http://www.projecthoneypot.org Project Honey Pot network database.
 * to use this plugin, u need to register there, and request your personal access-key for free!
 *
 * @package    z1HttpBLPlugin
 * @author     Hermann Bernwald <httpbl [at] zero1 [dot] ath [dot] cx>
 * @version    SVN: $Id$
 */
class z1BaseHttpBL
{
  /**
   * hold a dns host from blacklist-server
   *
   * @var        string
   */
  protected static $dns_host=false;

  /**
   * hold a accesskey to query a blacklist server
   *
   * @var        string
   */
  protected static $access_key=false;

  /**
   * configure z1HttpBL.
   * set Accesskey and DNS-Host
   *
   * @param      string   Access-key for Blacklist-server on projecthoneypot.org
   * @param      string   dns-host for query ip
   * @return     bool   true
   * @throws     sfException   If access_key or dns-host not set
   */
  public static function configure($access_key=false, $dns_host=false)
  {
    if ($access_key===false && sfConfig::has('app_httpbl_access_key')===true) {
      $access_key = sfConfig::get('app_httpbl_access_key');
    }
    if ($dns_host===false && sfConfig::has('app_httpbl_dns_host')===true) {
      $dns_host = sfConfig::get('app_httpbl_dns_host');
    }
    if ($access_key===false || $dns_host===false) {
      throw new sfException('z1HttpBL need valid access_key and dns-host; please recheck your yaml-config!', 403);
    }
    self::setAccessKey($access_key);
    self::setDnsHost($dns_host);

    return true;
  }

  /**
   * reverseIp z1HttpBL
   *
   * @param      string   ip-address
   * @return     string   reverse ip-address
   * @throws     <b>sfException</b> If ip-address is not valid
   */
  protected static function reverseIp($ip)
  {
    $output = false;
    if (preg_match('/^(?:(?:25[0-5]|2[0-4]\d|[01]\d\d|\d?\d)(?(?=\.?\d)\.)){4}$/', $ip)===1) {
      $output = implode('.', array_reverse( explode('.', $ip, 4) ) );
    } else {
      throw new sfException('IP-address not valid', 403);
    }
    return $output;
  }

  /**
   *
   * @access     protected
   * @param      string   reverse ip-address
   * @return     string   full dns-host ready for query
   */
  protected static function getFormatDnsQuery($reverse_ip)
  {
    return self::$access_key.'.'.$reverse_ip.'.'.self::$dns_host;
  }

  /**
   * set Accesskey
   *
   * @param      string access key
   * @return     string access key
   */
  public static function setAccessKey($access_key)
  {
    return self::$access_key = $access_key;
  }

  /**
   * set DNS host
   *
   * @param      string DNS host
   * @return     string DNS host
   */
  public static function setDnsHost($dns_host)
  {
    return self::$dns_host = $dns_host;
  }

  /**
   * get Accesskey
   *
   * @return     string if setted get access key as string, otherwise false
   */
  public static function getAccessKey()
  {
    return self::$access_key;
  }

  /**
   * get DNS host
   *
   * @return     string if setted get DNS host as string, otherwise false
   */
  public static function getDnsHost()
  {
    return self::$dns_host;
  }

  /**
   *
   * @return     bool   if access-key and dns-host was set, return true, otherwise false
   */
  public static function isConfigured()
  {
    $output = true;
    if (self::$access_key===false || self::$dns_host===false) {
      $output = false;
    }
    return $output;
  }

  /**
   *
   * @param      string   ip-address from user
   * @return     string   result from blacklistserver, encoded as ip-address
   * @throws     sfException   if is not configured
   */
  public static function checkIp($ip)
  {
    $output = false;

    if (self::isConfigured()===false) {
      throw new sfException('z1HttpBL need valid access_key and dns-host; please recheck your yaml-config!', 403);
    }

    $reverse_ip = self::reverseIp(trim($ip));
    if ($reverse_ip!==false) {
      $result_ip = gethostbyname(self::getFormatDnsQuery($reverse_ip));
      $result_array = explode('.', $result_ip, 4);
      $output = z1ResponseHttpBL::createResponse($result_array);
    }
    return $output;
  }
}