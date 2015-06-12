<?php

/**
 * @file
 * Test case for testing the per comment-type simplify configurations.
 *
 * Sponsored by: www.drupal-addict.com
 */

namespace Drupal\simplify\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test simplify per comment-type settings.
 *
 * @group Simplify
 *
 * @ingroup simplify
 */
class PerCommentTypeSettingsTest extends WebTestBase {

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
      'name' => 'Simplify per comment-type settings test.',
      'description' => 'Test the Simplify per comment-type settings.',
      'group' => 'Simplify',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $admin_user = $this->drupalCreateUser(array('administer comment types', 'administer simplify'));
    $this->drupalLogin($admin_user);

    // Globally activate some options.
    $this->drupalGet('/admin/config/user-interface/simplify');
    $options = array(
      'simplify_admin' => TRUE,
      'simplify_comments_global[format]' => 'format',
    );
    $this->drupalPostForm(NULL, $options, t('Save configuration'));

    // Create a comment type.
    $comment_type = entity_create('comment_type', array(
      'id' => 'testing_comment_type',
      'label' => $this->randomMachineName(),
      'description' => $this->randomMachineName(),
      'target_entity_type_id' => 'node_type',
    ));
    $comment_type->save();
  }

  /**
   * Check that Simplify module global configuration files saves settings.
   */
  public function testSettingSaving() {

    // Open admin UI.
    $this->drupalGet('/admin/structure/comment/manage/testing_comment_type');

    /* -------------------------------------------------------.
     * 1/ Check if everything is there but unchecked.
     */

    // Comments.
    $this->assertFieldChecked('edit-simplify-comments-format', 'Comment text fomat selection option is checked.');

    /* -------------------------------------------------------.
     * 2/ Check if everything is properly disabled if needed.
     */

    // Comments.
    $text_format = $this->xpath('//input[@name="simplify_comments[format]" and @disabled="disabled"]');
    $this->assertTrue(count($text_format) === 1, 'Comment text format option is disabled.');

    /* -------------------------------------------------------.
     * 3/ Remove global options.
     */

    $this->drupalGet('/admin/config/user-interface/simplify');
    $options = array(
      'simplify_admin' => TRUE,
      'simplify_comments_global[format]' => FALSE,
    );
    $this->drupalPostForm(NULL, $options, t('Save configuration'));

    // Open admin UI.
    $this->drupalGet('/admin/structure/comment/manage/testing_comment_type');

    // Comments.
    $this->assertNoFieldChecked('edit-simplify-comments-format', 'Comment text fomat selection option is not checked.');

    /* -------------------------------------------------------.
     * 3/ Save some options.
     */

    // Nodes.
    $options = array(
      'simplify_comments[format]' => 'format',
    );
    $this->drupalPostForm(NULL, $options, t('Save'));

    /* -------------------------------------------------------.
     * 4/ Check if options are saved.
     */
    $this->drupalGet('/admin/structure/comment/manage/testing_comment_type');
    $this->assertFieldChecked('edit-simplify-comments-format', 'Comment text fomat selection option is checked.');

  }

}
