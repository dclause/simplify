<?php

/**
 * @file
 * Test case for testing the per taxonomy simplify configurations.
 *
 * Sponsored by: www.drupal-addict.com
 */

namespace Drupal\simplify\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Component\Utility\Unicode;

/**
 * Test simplify per taxonomy settings.
 *
 * @group Simplify
 *
 * @ingroup simplify
 */
class PerTaxonomySettingsTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('taxonomy', 'simplify');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Simplify per taxonomy settings test.',
      'description' => 'Test the Simplify per taxonomy settings.',
      'group' => 'Simplify',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $admin_user = $this->drupalCreateUser(array('administer taxonomy', 'administer simplify'));
    $this->drupalLogin($admin_user);

    // Globally activate some options.
    $this->drupalGet('/admin/config/user-interface/simplify');
    $options = array(
      'simplify_admin' => TRUE,
      'simplify_taxonomies_global[format]' => 'format',
    );
    $this->drupalPostForm(NULL, $options, t('Save configuration'));

    // Create a vocabulary.
    $vocabulary = entity_create('taxonomy_vocabulary', array(
      'name' => $this->randomMachineName(),
      'description' => $this->randomMachineName(),
      'vid' => 'testing_vocabulary',
    ));
    $vocabulary->save();
  }

  /**
   * Check that Simplify module global configuration files saves settings.
   */
  public function testSettingSaving() {

    // Open vocabulary admin UI.
    $this->drupalGet('/admin/structure/taxonomy/manage/testing_vocabulary');

    /* -------------------------------------------------------.
     * 1/ Check if everything is there but unchecked.
     */

    // Vocabularys settings.
    $this->assertFieldChecked('edit-simplify-taxonomies-format', 'Vocabulary text fomat selection option is checked.');
    $this->assertNoFieldChecked('edit-simplify-taxonomies-relations', 'Vocabulary relations option is not checked.');

    /* -------------------------------------------------------.
     * 2/ Check if everything is properly disabled if needed.
     */

    // Vocabularys settings.
    $text_format = $this->xpath('//input[@name="simplify_taxonomies[format]" and @disabled="disabled"]');
    $this->assertTrue(count($text_format) === 1, 'Vocabulary text format option is disabled.');

    $text_format = $this->xpath('//input[@name="simplify_taxonomies[relations]" and @disabled="disabled"]');
    $this->assertTrue(count($text_format) === 0, 'Vocabulary relations option is not disabled.');

    /* -------------------------------------------------------.
     * 3/ Save some options.
     */

    // Vocabularys settings.
    $options = array(
      'simplify_taxonomies[relations]' => 'relations',
    );
    $this->drupalPostForm(NULL, $options, t('Save'));

    /* -------------------------------------------------------.
     * 4/ Check if options are saved.
     */
    $this->drupalGet('/admin/structure/taxonomy/manage/testing_vocabulary');
    $this->assertFieldChecked('edit-simplify-taxonomies-relations', 'Vocabulary relations option is checked.');

  }

}
