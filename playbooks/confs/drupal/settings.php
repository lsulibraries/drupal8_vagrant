
if (getenv('DRUPAL_SALT') && empty($settings['hash_salt'])) {
    $settings['hash_salt'] = file_get_contents('/var/html/www/drupal_salt');
};

$config_directories['sync'] = '/var/www/html/drupal_sync/';

<!-- $databases['default']['default'] = array (
  'database' => "drupal",
  'username' => "drupaluser",
  'password' => "password",
  'prefix' => '',
  'host' => "localhost",
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
); -->

$settings['install_profile'] = 'minimal';
