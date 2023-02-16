<?php

namespace Drupal\simplify\Tests;

use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\BrowserTestBase;

/**
 * Test simplify per vocabulary settings.
 *
 * @group Simplify
 *
 * @ingroup simplify
 */
class PerVocabularySettingsTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['path', 'taxonomy', 'simplify'];

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Simplify per taxonomy settings test.',
      'description' => 'Test the Simplify per taxonomy settings.',
      'group' => 'Simplify',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $admin_user = $this->drupalCreateUser([
      'administer url aliases',
      'administer taxonomy',
      'administer simplify',
    ]);
    $this->drupalLogin($admin_user);

    // Create a vocabulary.
    $vocabulary = Vocabulary::create([
      'name' => $this->randomMachineName(),
      'description' => $this->randomMachineName(),
      'vid' => 'testing_vocabulary',
    ]);
    $vocabulary->save();
  }

  /**
   * Check that Simplify module global configuration files saves settings.
   */
  public function testSettingSaving() {

    /* -------------------------------------------------------.
     * 0/ Check vocabulary edit term standard page.
     */

    $this->drupalGet("/admin/structure/taxonomy/manage/testing_vocabulary/add");

    $this->assertSession()->responseContains('About text formats');
    $this->assertSession()->responseContains('Relations');
    $this->assertSession()->responseContains('URL alias');

    /* -------------------------------------------------------.
     * 1/ Per vocabulary settings.
     */

    // Globally activate some options.
    $this->drupalGet('/admin/config/user-interface/simplify');
    $options = [
      'simplify_admin' => TRUE,
      'simplify_taxonomies_global[format]' => 'format',
    ];
    $this->submitForm($options, $this->t('Save configuration'));

    // Open vocabulary admin UI.
    $this->drupalGet('/admin/structure/taxonomy/manage/testing_vocabulary');

    // Check if everything is there and global options are considered.
    $this->assertSession()->checkboxChecked('edit-simplify-taxonomies-format', 'Vocabulary text fomat selection option is checked.');
    $this->assertSession()->checkboxNotChecked('edit-simplify-taxonomies-relations', 'Vocabulary relations option is not checked.');
    $this->assertSession()->checkboxNotChecked('edit-simplify-taxonomies-path', 'Vocabulary Path settings option is not checked.');

    // Check if everything is properly disabled if needed.
    $text_format = $this->xpath('//input[@name="simplify_taxonomies[format]" and @disabled="disabled"]');
    $this->assertTrue(count($text_format) === 1, 'Vocabulary text format option is disabled.');

    $text_format = $this->xpath('//input[@name="simplify_taxonomies[relations]" and @disabled="disabled"]');
    $this->assertTrue(count($text_format) === 0, 'Vocabulary relations option is not disabled.');

    $text_format = $this->xpath('//input[@name="simplify_taxonomies[path]" and @disabled="disabled"]');
    $this->assertTrue(count($text_format) === 0, 'Vocabulary URL alias option is not disabled.');

    // Save some custom options.
    $options = [
      'simplify_taxonomies[relations]' => 'relations',
      'simplify_taxonomies[path]' => 'path',
    ];
    $this->submitForm($options, $this->t('Save'));

    // Check if options are saved.
    $this->drupalGet('/admin/structure/taxonomy/manage/testing_vocabulary');
    $this->assertSession()->checkboxChecked('edit-simplify-taxonomies-relations', 'Vocabulary relations option is checked.');
    $this->assertSession()->checkboxChecked('edit-simplify-taxonomies-path', 'Vocabulary URL alias option is checked.');

    /* -------------------------------------------------------.
     * 2/ Check settings effect on "term edit" page.
     */
    $this->drupalGet("/admin/structure/taxonomy/manage/testing_vocabulary/add");

    $this->assertSession()->responseNotContains('About text formats');
    $this->assertSession()->responseNotContains('Relations');
    $this->assertSession()->responseNotContains('URL alias');
  }

}
