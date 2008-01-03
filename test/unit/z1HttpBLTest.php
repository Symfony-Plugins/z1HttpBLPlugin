<?php

include(dirname(__FILE__).'/../bootstrap/unit.php');


require_once(dirname(__FILE__).'/../../plugins/z1HttpBLPlugin/lib/z1BaseHttpBL.class.php');
require_once(dirname(__FILE__).'/../../plugins/z1HttpBLPlugin/lib/z1HttpBL.class.php');
require_once(dirname(__FILE__).'/../../plugins/z1HttpBLPlugin/lib/z1BaseResponseHttpBL.class.php');
require_once(dirname(__FILE__).'/../../plugins/z1HttpBLPlugin/lib/z1ResponseHttpBL.class.php');

$t = new lime_test(9, new lime_output_color());

$app_config_params = array(
  'dns_host' => 'dnsbl.httpbl.org',
  'access_key' => 'test'
);

$test_ip = '127.1.1.6';

$list_bogus_ip = array(
            '256.1.1.6',
            '255.1.6',
            '253.1.1.06.',
            '.252.1.1.6',
            '252.1.1.256',
            '252.1.256.6',
            '252.256.1.6',
            '256.256.256.256',
            '256.0.0.0',
            '252.1.0.-1',
            '352.1.1.6',
            '.1.1.6',
            '252.1.1.',
            '252.11.6',
            '2521.1.6',
            '252.1.16',
            '.252.1.1.6.'
          );

$test_ip_bogus = $list_bogus_ip[mt_rand(0, count($list_bogus_ip))];

$t->is(z1HttpBL::isConfigured(), false, 'z1HttpBL::isConfigured() return false, because not configured');


$result_configure = z1HttpBL::configure($app_config_params['access_key'], $app_config_params['dns_host']);


$t->ok($result_configure, 'z1HttpBL::configure() return true');
$t->is(z1HttpBL::getAccessKey(), $app_config_params['access_key'], 'z1HttpBL::getAccessKey() get right access key');
$t->is(z1HttpBL::getDnsHost(), $app_config_params['dns_host'], 'z1HttpBL::getDnsHost() get right dns_host');

$t->is(z1HttpBL::isConfigured(), true, 'z1HttpBL::isConfigured() get true');

/* @var $test_result_dummy z1ResponseHttpBL */
$test_result_dummy = z1ResponseHttpBL::createResponse(explode('.', $test_ip, 4));

/* @var $test_result z1ResponseHttpBL */
$test_result = z1HttpBL::checkIp($test_ip);

$t->cmp_ok($test_result, '==', $test_result_dummy, 'z1HttpBL::checkIp() get right z1ResponseHttpBL');


try
{
  $result_configure = z1HttpBL::configure($app_config_params['access_key']);
  $t->fail('z1HttpBL::configure() accept only access_key parameter without dns_host');
}
catch(Exception $e)
{
  $t->pass('z1HttpBL::configure() refuse configuration without dns_host');
}

try
{
  $result_configure = z1HttpBL::configure(false, $app_config_params['dns_host']);
  $t->fail('z1HttpBL::configure() accept only dns_host parameter without access_key');
}
catch(Exception $e)
{
  $t->pass('z1HttpBL::configure() refuse configuration without access_key');
}


try
{
  $test_result_bogus = z1HttpBL::checkIp($test_ip_bogus);
  $t->fail('z1HttpBL::checkIp() works with bogus ip: '.$test_ip_bogus);
}
catch(Exception $e)
{
  $t->pass('z1HttpBL::checkIp() return Exception, because its not work with bogus ip: '.$test_ip_bogus);
}
