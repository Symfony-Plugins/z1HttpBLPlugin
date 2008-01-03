<?php

/**
 * Filter with using HttpBL-plugin
 *
 * @package    z1HttpBLPlugin
 * @author     Hermann Bernwald <httpbl [at] zero1 [dot] ath [dot] cx>
 * @version    SVN: $Id$
 */
class z1HttpBLFilter extends sfFilter
{
  public function execute($filterChain)
  {
    $redirect = false;

    if ($this->isFirstCall()===true) {
      $dns_host = sfConfig::get('app_httpbl_dns_host');
      $xs_key = sfConfig::get('app_httpbl_access_key');
      $max_age = sfConfig::get('app_httpbl_max_age');
      $min_score = sfConfig::get('app_httpbl_min_score');
      $min_type = sfConfig::get('app_httpbl_min_type');

      // only optional, to remove sfConfig depency in z1HttpBL
      z1HttpBL::configure($xs_key, $dns_host);
      /* @var $httpBL_result z1ResultHttpBL */
      $httpBL_result = z1HttpBL::checkIp($_SERVER['REMOTE_ADDR']);
      // need configure, to use isBadGuy(), otherwise we need to check this param manually
      $httpBL_result->configure($max_age, $min_score, $min_type);

      $this->getContext()->getUser()->setAttribute('result_type', $httpBL_result->getResultCode(), 'plugin/httpBL');

      if ($httpBL->isBadGuy()===true) {
        $this->getContext()->getUser()->setAttribute('is_badguy', true, 'plugin/httpBL');
        $redirect = true;
      }
    }

    if ($this->getContext()->getUser()->getAttribute('is_badguy', false, 'plugin/httpBL')===true) {
      $redirect = true;
    }

    if ($redirect===true) {
      $banned_link = sfConfig::get('app_httpbl_banned_link');
      return $context->getController()->redirect($banned_link);
    }

    $filterChain->execute();
  }
}