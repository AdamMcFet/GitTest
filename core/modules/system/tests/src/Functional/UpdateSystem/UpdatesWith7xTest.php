<?php

namespace Drupal\Tests\system\Functional\UpdateSystem;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\RequirementsPageTrait;

/**
 * Tests that the minimum schema version is correct even if only 7.x update
 * hooks are retained .
 *
 * @group Update
 */
class UpdatesWith7xTest extends BrowserTestBase {

  use RequirementsPageTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['update_test_with_7x'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The URL for the update page.
   *
   * @var string
   */
  private $updateUrl;

  /**
   * An administrative user.
   */
  private $updateUser;

  protected function setUp(): void {
    parent::setUp();
    require_once $this->root . '/core/includes/update.inc';
    $this->updateUrl = $GLOBALS['base_url'] . '/update.php';
    $this->updateUser = $this->drupalCreateUser([
      'administer software updates',
    ]);
  }

  public function testWith7x() {
    // Ensure that the minimum schema version is 8000, despite 7200 update
    // hooks and a 7XXX hook_update_last_removed().
    $this->assertEquals(8000, drupal_get_installed_schema_version('update_test_with_7x'));

    // Try to manually set the schema version to 7110 and ensure that no
    // updates are allowed.
    drupal_set_installed_schema_version('update_test_with_7x', 7110);

    // Click through update.php with 'administer software updates' permission.
    $this->drupalLogin($this->updateUser);
    $this->drupalGet($this->updateUrl, ['external' => TRUE]);
    $this->updateRequirementsProblem();
    $this->clickLink('Continue');
    $this->assertSession()->pageTextContains('Some of the pending updates cannot be applied because their dependencies were not met.');
  }

}
