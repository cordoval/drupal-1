<?php

/**
 * @file
 * Definition of Drupal\config\Tests\ConfigOverridesPriorityTest.
 */

namespace Drupal\config\Tests;

use Drupal\Core\Language\Language;
use Drupal\simpletest\DrupalUnitTestBase;

/**
 * Tests module overrides of configuration using event subscribers.
 */
class ConfigOverridesPriorityTest extends DrupalUnitTestBase {

  public static $modules = array('system', 'config', 'config_override');

  public static function getInfo() {
    return array(
      'name' => 'Override priority',
      'description' => 'Tests that language, module and settings.php are applied in the correct order.',
      'group' => 'Configuration',
    );
  }

  public function testOverridePriorities() {
    global $conf;
    $GLOBALS['config_test_run_module_overrides'] = FALSE;

    $non_overridden_mail =  'site@example.com';
    $language_overridden_mail = 'french@example.com';

    $language_overridden_name = 'French site name';
    $module_overridden_name = 'ZOMG overridden site name';
    $non_overridden_name = 'ZOMG this name is on disk mkay';

    $module_overridden_slogan = 'Yay for overrides!';
    $non_overridden_slogan = 'Yay for defaults!';

    /** @var \Drupal\Core\Config\ConfigFactory $config_factory */
    $config_factory = $this->container->get('config.factory');
    $config_factory
      ->get('system.site')
      ->set('name', $non_overridden_name)
      ->set('slogan', $non_overridden_slogan)
      ->set('mail', $non_overridden_mail)
      ->set('weight_select_max', 50)
      ->save();

    // Ensure that no overrides are applying.
    $this->assertEqual($non_overridden_name, $config_factory->get('system.site')->get('name'));
    $this->assertEqual($non_overridden_slogan, $config_factory->get('system.site')->get('slogan'));
    $this->assertEqual($non_overridden_mail, $config_factory->get('system.site')->get('mail'));
    $this->assertEqual(50, $config_factory->get('system.site')->get('weight_select_max'));

    // Override using language.
    $language = new Language(array(
      'name' => 'French',
      'id' => 'fr',
    ));
    $config_factory->setLanguage($language);
    $language_config_name = $config_factory->getLanguageConfigName($language->id, 'system.site');
    $config_factory
      ->get($language_config_name)
      ->set('name', $language_overridden_name)
      ->set('mail', $language_overridden_mail)
      ->save();

    $this->assertEqual($language_overridden_name, $config_factory->get('system.site')->get('name'));
    $this->assertEqual($non_overridden_slogan, $config_factory->get('system.site')->get('slogan'));
    $this->assertEqual($language_overridden_mail, $config_factory->get('system.site')->get('mail'));
    $this->assertEqual(50, $config_factory->get('system.site')->get('weight_select_max'));

    // Enable module overrides. Do not override system.site:mail to prove that
    // the language override still applies.
    $GLOBALS['config_test_run_module_overrides'] = TRUE;
    $config_factory->reset('system.site');
    $this->assertEqual($module_overridden_name, $config_factory->get('system.site')->get('name'));
    $this->assertEqual($module_overridden_slogan, $config_factory->get('system.site')->get('slogan'));
    $this->assertEqual($language_overridden_mail, $config_factory->get('system.site')->get('mail'));
    $this->assertEqual(50, $config_factory->get('system.site')->get('weight_select_max'));

    // Configure a global override to simulate overriding using settings.php. Do
    // not override system.site:mail or system.site:slogan to prove that the
    // language and module overrides still apply.
    $conf['system.site']['name'] = 'Site name global conf override';
    $config_factory->reset('system.site');
    $this->assertEqual('Site name global conf override', $config_factory->get('system.site')->get('name'));
    $this->assertEqual($module_overridden_slogan, $config_factory->get('system.site')->get('slogan'));
    $this->assertEqual($language_overridden_mail, $config_factory->get('system.site')->get('mail'));
    $this->assertEqual(50, $config_factory->get('system.site')->get('weight_select_max'));

    $old_state = $config_factory->getOverrideState();
    $config_factory->setOverrideState(FALSE);
    $this->assertEqual($non_overridden_name, $config_factory->get('system.site')->get('name'));
    $this->assertEqual($non_overridden_slogan, $config_factory->get('system.site')->get('slogan'));
    $this->assertEqual($non_overridden_mail, $config_factory->get('system.site')->get('mail'));
    $this->assertEqual(50, $config_factory->get('system.site')->get('weight_select_max'));
    $config_factory->setOverrideState($old_state);

    unset($GLOBALS['config_test_run_module_overrides']);
  }
}
