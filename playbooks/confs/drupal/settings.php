
$settings['hash_salt'] = file_get_contents('/var/www/html/drupal_site/drupal_salt.txt');

$settings['config_sync_directory'] = '../drupal_sync';


$databases['default']['default'] = array (
  'database' => "drupal",
  'username' => "drupaluser",
  'password' => "hidrupal",
  'prefix' => '',
  'host' => "localhost",
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

$settings['install_profile'] = 'minimal';
