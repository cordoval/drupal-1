<?php
/**
 * @file
 * Contains Drupal\Tests\Core\Asset\AssetLibraryTest.
 */

namespace Drupal\Tests\Core\Asset;

use Drupal\Core\Asset\AssetLibrary;
use Drupal\Tests\UnitTestCase;

/**
 *
 * @group Asset
 */
class AssetLibraryTest extends UnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'Asset Library tests',
      'description' => 'Tests that the AssetLibrary behaves correctly.',
      'group' => 'Asset',
    );
  }

  public function getLibraryFixture() {
    $library = new AssetLibrary();
    $library->setTitle('foo')
      ->setVersion('1.2.3')
      ->setWebsite('http://foo.bar')
      ->addDependency('foo', 'bar');
    return $library;
  }

  public function testConstructorValueInjection() {
    $values = array(
      'title' => 'foo',
      'version' => '1.2.3',
      'website' => 'http://foo.bar',
      'dependencies' => array(array('foo', 'bar')),
    );
    $library = new AssetLibrary($values);

    $fixture = $this->getLibraryFixture();
    $this->assertEquals($fixture->getTitle(), $library->getTitle(), 'Title passed correctly through the constructor.');
    $this->assertEquals($fixture->getVersion(), $library->getVersion(), 'Version passed correctly through the constructor.');
    $this->assertEquals($fixture->getWebsite(), $library->getWebsite(), 'Website passed correctly through the constructor.');
    $this->assertEquals($fixture->getDependencies(), $library->getDependencies(), 'Dependencies information passed correctly through the constructor.');
  }

  public function testAddDependency() {
    $library = $this->getLibraryFixture();
    $library->addDependency('baz', 'bing');
    $this->assertEquals($library->getDependencies(), array(array('foo', 'bar'), array('baz', 'bing')), 'Dependencies added to library successfully.');
  }

  public function testClearDependencies() {
    $library = $this->getLibraryFixture();
    $library->clearDependencies();
    $this->assertEmpty($library->getDependencies(), 'Dependencies recorded in the library were cleared correctly.');
  }

  public function testFrozenNonwriteability() {
    $library = $this->getLibraryFixture();
    $library->freeze();
    try {
      $library->setTitle('bar');
      $this->fail('No exception thrown when attempting to set a new title on a frozen library.');
    }
    catch (\LogicException $e) {}

    try {
      $library->setVersion('2.3.4');
      $this->fail('No exception thrown when attempting to set a new version on a frozen library.');
    }
    catch (\LogicException $e) {}

    try {
      $library->setWebsite('http://bar.baz');
      $this->fail('No exception thrown when attempting to set a new website on a frozen library.');
    }
    catch (\LogicException $e) {}

    try {
      $library->addDependency('bing', 'bang');
      $this->fail('No exception thrown when attempting to add a new dependency on a frozen library.');
    }
    catch (\LogicException $e) {}

    try {
      $library->clearDependencies();
      $this->fail('No exception thrown when attempting to clear dependencies from a frozen library.');
    }
    catch (\LogicException $e) {}
  }
}
