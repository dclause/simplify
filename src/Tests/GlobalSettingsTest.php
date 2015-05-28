<?php

/**
 * @file
 * Test case for testing the global simplify configuration page.
 *
 * Sponsored by: www.drupal-addict.com
 */

namespace Drupal\simplify\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test simplify settings.
 *
 * @group Simplify
 *
 * @ingroup simplify
 */
class GlobalSettingsTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('simplify');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Simplify settings test.',
      'description' => 'Test the Simplify module settings page.',
      'group' => 'Simplify',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $admin_user = $this->drupalCreateUser(array('administer simplify'));
    $this->drupalLogin($admin_user);
  }

  /**
   * Check that Simplify module global configuration files saves settings.
   */
  public function testSettingSaving() {

    // Open admin UI.
    $this->drupalGet('/admin/config/user-interface/simplify');

    /* -------------------------------------------------------.
     * 1/ Check everything is there but unchecked.
     */

    // User 1.
    $this->assertNoFieldChecked('edit-simplify-user1', 'User 1 is unchecked.');
    // Node globals.
    $this->assertNoFieldChecked('edit-simplify-nodes-global-author', 'Author option is unchecked');
    $this->assertNoFieldChecked('edit-simplify-nodes-global-format', 'Format option is unchecked.');
    $this->assertNoFieldChecked('edit-simplify-nodes-global-options', 'Publishing option is unchecked.');
    $this->assertNoFieldChecked('edit-simplify-nodes-global-revision', 'Revision option is unchecked.');
    // User globals.
    $this->assertNoFieldChecked('edit-simplify-users-global-format', 'Text selection option is unchecked.');
    $this->assertNoFieldChecked('edit-simplify-users-global-status', 'Status option is unchecked.');
    // Taxonomy is not here.
    $this->assertNoRaw('Taxonomy', 'Taxonomy options are not available.');
    $this->assertNoField('edit-simplify-taxonomy-global-format', 'Text selection from taxonomy option is not available.');

    /* -------------------------------------------------------.
     * 2/ Check optionnal options are added if modules becomes available.
     */

    $this->container->get('module_installer')->install(array('book', 'taxonomy', 'block', 'comment', 'menu_ui', 'path'), TRUE);
    $this->drupalGet('/admin/config/user-interface/simplify');
    // Taxonomy.
    $this->assertNoFieldChecked('edit-simplify-taxonomy-global-format', 'Text selection option is unchecked.');
    $this->assertNoFieldChecked('edit-simplify-taxonomy-global-relations', 'Relation option is unchecked.');
    $this->assertNoFieldChecked('edit-simplify-taxonomy-global-relations', 'Url alias is unchecked.');
    // Blocks.
    $this->assertNoFieldChecked('edit-simplify-blocks-global-format', 'Text format option is unchecked.');

    /*  -------------------------------------------------------.
     * 3/ Check and validate some options.
     */

    $options = array(
      'simplify_user1' => TRUE,
      'simplify_nodes_global[author]' => 'author',
      'simplify_nodes_global[comment]' => 'comment',
      'simplify_nodes_global[options]' => 'options',
    );
    $this->drupalPostForm(NULL, $options, t('Save configuration'));
    // User1.
    $this->assertFieldChecked('edit-simplify-user1', 'User1 option is checked.');
    // Nodes.
    $this->assertFieldChecked('edit-simplify-nodes-global-author', 'Node authoring information option is checked.');
    $this->assertNoFieldChecked('edit-simplify-nodes-global-format', 'Node text fomat selection option is not checked.');
    $this->assertFieldChecked('edit-simplify-nodes-global-options', 'Node publishing options option is checked.');
    $this->assertNoFieldChecked('edit-simplify-nodes-global-revision', 'Node revision information option is not checked.');
    $this->assertNoFieldChecked('edit-simplify-nodes-global-book', 'Node book outline option is not checked.');
    $this->assertFieldChecked('edit-simplify-nodes-global-comment', 'Node comment settings option is checked.');
    $this->assertNoFieldChecked('edit-simplify-nodes-global-menu', 'Node menu settings option is not checked.');
    $this->assertNoFieldChecked('edit-simplify-nodes-global-path', 'Node URL path settings option is not checked.');
  }

}
