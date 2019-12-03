
$settings['hash_salt'] = file_get_contents('/var/www/html/drupal_site/drupal_salt.txt');

$config_directories['sync'] = '/var/www/html/drupal_site/drupal_sync/';

$databases['default']['default'] = array (
  'database' => "drupal",
  'username' => "drupaluser",
  'password' => "password",
  'prefix' => '',
  'host' => "localhost",
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

$settings['install_profile'] = 'minimal';
