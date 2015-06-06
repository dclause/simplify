<?php

/**
 * @file
 * Test case for testing the per content-type simplify configurations.
 *
 * Sponsored by: www.drupal-addict.com
 */

namespace Drupal\simplify\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test simplify per content-type settings.
 *
 * @group Simplify
 *
 * @ingroup simplify
 */
class PerContentTypeSettingsTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('node', 'comment', 'simplify');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Simplify per content-type settings test.',
      'description' => 'Test the Simplify per content-type settings.',
      'group' => 'Simplify',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $admin_user = $this->drupalCreateUser(array('administer content types', 'administer simplify'));
    $this->drupalLogin($admin_user);

    // Globally activate some options.
    $this->drupalGet('/admin/config/user-interface/simplify');
    $options = array(
      'simplify_user1' => TRUE,
      'simplify_nodes_global[author]' => 'author',
      'simplify_nodes_global[comment]' => 'comment',
      'simplify_nodes_global[options]' => 'options',
    );
    $this->drupalPostForm(NULL, $options, t('Save configuration'));

    // Create a content type.
    $type = $this->drupalCreateContentType(['type' => 'testing_type', 'name' => 'Testing type']);
  }

  /**
   * Check that Simplify module global configuration files saves settings.
   */
  public function testSettingSaving() {

    // Open admin UI.
    $this->drupalGet('/admin/structure/types/manage/testing_type');

    /* -------------------------------------------------------.
     * 1/ Check if everything is there but unchecked.
     */

    // Nodes.
    $this->assertFieldChecked('edit-simplify-nodes-author', 'Node authoring information option is checked.');
    $this->assertNoFieldChecked('edit-simplify-nodes-format', 'Node text fomat selection option is not checked.');
    $this->assertFieldChecked('edit-simplify-nodes-options', 'Node publishing options option is checked.');
    $this->assertNoFieldChecked('edit-simplify-nodes-revision', 'Node revision information option is not checked.');
    $this->assertFieldChecked('edit-simplify-nodes-comment', 'Node comment settings option is checked.');

    /* -------------------------------------------------------.
     * 2/ Check if everything is properly disabled if needed.
     */

    // Nodes.
    $author_info = $this->xpath('//input[@name="simplify_nodes[author]" and @disabled="disabled"]');
    $this->assertTrue(count($author_info) === 1, 'Node authoring information option is disabled.');

    $text_format = $this->xpath('//input[@name="simplify_nodes[format]" and @disabled="disabled"]');
    $this->assertTrue(count($text_format) === 0, 'Node text format option is not disabled.');

    $publishing_option = $this->xpath('//input[@name="simplify_nodes[options]" and @disabled="disabled"]');
    $this->assertTrue(count($publishing_option) === 1, 'Node publishing options option is disabled.');

    $revision_option = $this->xpath('//input[@name="simplify_nodes[revision]" and @disabled="disabled"]');
    $this->assertTrue(count($revision_option) === 0, 'Node revision information option is not disabled.');

    $comment_option = $this->xpath('//input[@name="simplify_nodes[comment]" and @disabled="disabled"]');
    $this->assertTrue(count($comment_option) === 1, 'Node comment settings option is disabled.');

    /* -------------------------------------------------------.
     * 3/ Save some options.
     */

    // Nodes.
    $options = array(
      'simplify_nodes[format]' => 'format',
    );
    $this->drupalPostForm(NULL, $options, t('Save content type'));

    /* -------------------------------------------------------.
     * 4/ Check if options are saved.
     */
    $this->drupalGet('/admin/structure/types/manage/testing_type');
    $this->assertFieldChecked('edit-simplify-nodes-format', 'Node text fomat selection option is checked.');

  }

}
