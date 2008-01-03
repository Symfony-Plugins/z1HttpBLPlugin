<?php

include(dirname(__FILE__).'/../bootstrap/unit.php');

require_once(dirname(__FILE__).'/../../plugins/z1HttpBLPlugin/lib/z1BaseResponseHttpBL.class.php');
require_once(dirname(__FILE__).'/../../plugins/z1HttpBLPlugin/lib/z1ResponseHttpBL.class.php');

$t = new lime_test(25, new lime_output_color());

$app_config_params = array(
  'max_age' => 6,
  'min_score' => 1,
  'min_code' => 1
);

$test_ip = '127.1.1.6';

/* @var $response_httpbl z1ResponseHttpBL */
$response_httpbl = z1ResponseHttpBL::createResponse(explode('.', $test_ip, 4));

$t->is($response_httpbl->isConfigured(), false, 'z1ResponseHttpBL::isConfigured() is not configured, and return false');

$t->is($response_httpbl->getLastActivity(), 1, 'z1ResponseHttpBL::getLastActivity() same as dummy');
$t->is($response_httpbl->getScore(), 1, 'z1ResponseHttpBL::getBLScore() same as dummy');
$t->is($response_httpbl->getType(), 6, 'z1ResponseHttpBL::getType() same as dummy');

$t->is($response_httpbl->isSearchEngine(), false, 'z1ResponseHttpBL::isSearchEngine() return false');
$response_httpbl = z1ResponseHttpBL::createResponse(explode('.', '127.1.1.0', 4));
$t->is($response_httpbl->isSearchEngine(), true, 'z1ResponseHttpBL::isSearchEngine() return true');

$t->is($response_httpbl->isSuspicious(), false, 'z1ResponseHttpBL::isSuspicious() return false');
$response_httpbl = z1ResponseHttpBL::createResponse(explode('.', '127.1.1.1', 4));
$t->is($response_httpbl->isSuspicious(), true, 'z1ResponseHttpBL::isSuspicious() return true');

$t->is($response_httpbl->isHarvester(), false, 'z1ResponseHttpBL::isHarvester() return false');
$response_httpbl = z1ResponseHttpBL::createResponse(explode('.', '127.1.1.2', 4));
$t->is($response_httpbl->isHarvester(), true, 'z1ResponseHttpBL::isHarvester() return true');

$t->is($response_httpbl->isSuspiciousHarvester(), false, 'z1ResponseHttpBL::isSuspiciousHarvester() return false');
$response_httpbl = z1ResponseHttpBL::createResponse(explode('.', '127.1.1.3', 4));
$t->is($response_httpbl->isSuspiciousHarvester(), true, 'z1ResponseHttpBL::isSuspiciousHarvester() return true');

$t->is($response_httpbl->isSpammer(), false, 'z1ResponseHttpBL::isSpammer() return false');
$response_httpbl = z1ResponseHttpBL::createResponse(explode('.', '127.1.1.4', 4));
$t->is($response_httpbl->isSpammer(), true, 'z1ResponseHttpBL::isSpammer() return true');

$t->is($response_httpbl->isSuspiciousSpammer(), false, 'z1ResponseHttpBL::isSuspiciousSpammer() return false');
$response_httpbl = z1ResponseHttpBL::createResponse(explode('.', '127.1.1.5', 4));
$t->is($response_httpbl->isSuspiciousSpammer(), true, 'z1ResponseHttpBL::isSuspiciousSpammer() return true');

$t->is($response_httpbl->isHarvesterSpammer(), false, 'z1ResponseHttpBL::isHarvesterSpammer() return false');
$response_httpbl = z1ResponseHttpBL::createResponse(explode('.', '127.1.1.6', 4));
$t->is($response_httpbl->isHarvesterSpammer(), true, 'z1ResponseHttpBL::isHarvesterSpammer() return true');

$t->is($response_httpbl->isSuspiciousHarvesterSpammer(), false, 'z1ResponseHttpBL::isSuspiciousHarvesterSpammer() return false');
$response_httpbl = z1ResponseHttpBL::createResponse(explode('.', '127.1.1.7', 4));
$t->is($response_httpbl->isSuspiciousHarvesterSpammer(), true, 'z1ResponseHttpBL::isSuspiciousHarvesterSpammer() return true');

try
{
  $result_badguy = $response_httpbl->isBadGuy();
  $t->fail('z1ResponseHttpBL::isBadGuy() return without configure');
}
catch(Exception $e)
{
  $t->pass('z1ResponseHttpBL::isBadGuy() refuse without configure');
}


$result_configure = $response_httpbl->configure($app_config_params['max_age'], $app_config_params['min_score'], $app_config_params['min_code']);

$t->is($result_configure, true, 'z1ResponseHttpBL::configure() return true');

$t->is($response_httpbl->isConfigured(), true, 'z1ResponseHttpBL::isConfigured() return true');

try
{
  $result_badguy = $response_httpbl->isBadGuy();
  $t->pass('z1ResponseHttpBL::isBadGuy() return with configure');
  $t->is($result_badguy, true, 'z1ResponseHttpBL::isBadGuy() return true');
}
catch(Exception $e)
{
  $t->fail('z1ResponseHttpBL::isBadGuy() refuse with configure');
}
