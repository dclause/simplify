<?php

/**
 * @file
 * Contains \Drupal\simplify\Form\SimplifyAdminForm.
 */

namespace Drupal\simplify\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure simplify global configurations.
 */
class SimplifyAdminForm extends ConfigFormBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Constructs a SimplifyAdminForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandler $module_handler) {
    parent::__construct($config_factory);
    $this->moduleHandler =  $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['simplify.global'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simplify_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $simplify_config = $this->config('simplify.global');

    // User 1 permission.
    $form['simplify_user1'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Hide fields from User 1'),
      '#description' => $this->t("By default, Drupal gives User 1 <em>all</em> permissions (including Simplify's <em>View hidden fields</em> permission). This means that User 1 will always be able to view all hidden fields (and is by design).<br>Check this box to override this functionality and hide fields from User 1. NOTE: As this option overrides default Drupal behaviour, it should be used sparingly and only when you fully understand the consequences."),
      '#default_value' => $simplify_config->get('simplify_user1'),
    );

    // Nodes.
    $form['nodes'] = array(
      '#type' => 'details',
      '#title' => $this->t('Nodes'),
      '#description' => $this->t("These fields will be hidden from <em>all</em> node forms. Alternatively, to hide fields from node forms of a particular content type, edit the content type and configure the hidden fields there."),
      '#open' => TRUE,
    );
    $form['nodes']['simplify_nodes_global'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Hide'),
      '#options' => $this->simplifyGetFields('nodes'),
      '#default_value' => $simplify_config->get('simplify_nodes_global'),
    );

    // Users.
    $form['users'] = array(
      '#type' => 'details',
      '#title' => $this->t('Users'),
      '#description' => $this->t("These fields will be hidden from all user account forms."),
      '#open' => TRUE,
    );
    $form['users']['simplify_users_global'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Hide'),
      '#options' => $this->simplifyGetFields('users'),
      '#default_value' => $simplify_config->get('simplify_users_global'),
    );

    // Comments.
    if ($this->moduleHandler->moduleExists('comment')) {
      $form['comments'] = array(
        '#type' => 'details',
        '#title' => $this->t('Comments'),
        '#description' => $this->t("These fields will be hidden from <em>all</em> comment forms. Alternatively, to hide fields from comment forms for nodes of a particular content type, edit the content type and configure the hidden fields there."),
        '#open' => TRUE,
      );
      $form['comments']['simplify_comments_global'] = array(
        '#type' => 'checkboxes',
        '#title' => $this->t('Hide'),
        '#options' => $this->simplifyGetFields('comments'),
        '#default_value' => $simplify_config->get('simplify_comments_global'),
      );
    }

    // Taxonomy.
    if ($this->moduleHandler->moduleExists('taxonomy')) {
      $form['taxonomy'] = array(
        '#type' => 'details',
        '#title' => $this->t('Taxonomy'),
        '#description' => $this->t("These fields will be hidden from <em>all</em> taxonomy term forms. Alternatively, to hide fields from taxonomy term forms for a particular vocabulary, edit the vocabulary and configure the hidden fields there."),
        '#open' => TRUE,
      );
      $form['taxonomy']['simplify_taxonomy_global'] = array(
        '#type' => 'checkboxes',
        '#title' => $this->t('Hide'),
        '#options' => $this->simplifyGetFields('taxonomy'),
        '#default_value' => $simplify_config->get('simplify_taxonomy_global'),
      );
    }

    // Blocks.
    if ($this->moduleHandler->moduleExists('block')) {
      $form['blocks'] = array(
        '#type' => 'details',
        '#title' => $this->t('Blocks'),
        '#description' => $this->t("These fields will be hidden from all block forms."),
        '#open' => TRUE,
      );
      $form['blocks']['simplify_blocks_global'] = array(
        '#type' => 'checkboxes',
        '#title' => $this->t('Hide'),
        '#options' => $this->simplifyGetFields('blocks'),
        '#default_value' => $simplify_config->get('simplify_blocks_global'),
      );
    }

    // Profiles.
    if ($this->moduleHandler->moduleExists('profile2_page')) {
      $form['profiles'] = array(
        '#type' => 'details',
        '#title' => $this->t('Profiles'),
        '#description' => $this->t("These fields will be hidden from all profile forms."),
        '#open' => TRUE,
      );
      $form['profiles']['simplify_profiles_global'] = array(
        '#type' => 'checkboxes',
        '#title' => $this->t('Hide'),
        '#options' => $this->simplifyGetFields('profiles'),
        '#default_value' => $simplify_config->get('simplify_profiles_global'),
      );
    }

    // Remove empty values from saved variables.
    // (see: http://drupal.org/node/61760#comment-402631)
    $form['array_filter'] = array(
      '#type' => 'hidden',
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::configFactory()->getEditable('simplify.global')
      ->set('simplify_user1', $form_state->getValue('simplify_user1'))
      ->set('simplify_nodes_global', $this->getConfigValue($form_state, 'simplify_nodes_global'))
      ->set('simplify_users_global', $this->getConfigValue($form_state, 'simplify_users_global'))
      ->set('simplify_comments_global', $this->getConfigValue($form_state, 'simplify_comments_global'))
      ->set('simplify_taxonomy_global', $this->getConfigValue($form_state, 'simplify_taxonomy_global'))
      ->set('simplify_blocks_global', $this->getConfigValue($form_state, 'simplify_blocks_global'))
      ->set('simplify_profiles_global', $this->getConfigValue($form_state, 'simplify_profiles_global'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Gets an array representing the configuration form values or empty array
   * is empty or not applicable.
   *
   * @param string $form_state
   *   The form state array.
   * @param string $config_type
   *   The configuration name to be retrieved.
   *
   * @return array
   *   An array representing the configuration or empty array if the
   *   configuration is not applicable.
   */
  protected function getConfigValue(FormStateInterface $form_state, $config_name) {
    $value = $form_state->getValue($config_name);
    return !empty($value) ? $value : array();
  }

  /**
   * Get an array of fields (by type) that can be hidden.
   *
   * @param string $type
   *   The category type of fields to build.
   *
   * @return array
   *   An array of cehckbox option fields.
   */
  protected function simplifyGetFields($type) {
    $fields = array();

    switch ($type) {
      // Nodes.
      case 'nodes':
        // Drupal core:
        $fields['author'] = $this->t('Authoring information');
        $fields['format'] = $this->t('Text format selection');
        $fields['options'] = $this->t('Publishing options');
        $fields['revision'] = $this->t('Revision information');
        if ($this->moduleHandler->moduleExists('book')) {
          $fields['book'] = $this->t('Book outline');
        }
        if ($this->moduleHandler->moduleExists('comment')) {
          $fields['comment'] = $this->t('Comment settings');
        }
        if ($this->moduleHandler->moduleExists('menu_ui')) {
          $fields['menu'] = $this->t('Menu settings');
        }
        if ($this->moduleHandler->moduleExists('path')) {
          $fields['path'] = $this->t('URL path settings');
        }
        // Third-party modules:
        if ($this->moduleHandler->moduleExists('domain')) {
          $fields['domain'] = $this->t('Domain access');
        }
        if ($this->moduleHandler->moduleExists('entity_translation') && entity_translation_enabled('node')) {
          $fields['entity_translation'] = $this->t('Entity translation');
        }
        if ($this->moduleHandler->moduleExists('metatag')) {
          $fields['metatag'] = $this->t('Meta tags');
        }
        if ($this->moduleHandler->moduleExists('node_noindex')) {
          $fields['node_noindex'] = $this->t('Node noindex');
        }
        if ($this->moduleHandler->moduleExists('redirect')) {
          $fields['redirect'] = $this->t('URL redirects');
        }
        if ($this->moduleHandler->moduleExists('xmlsitemap_node')) {
          $fields['xmlsitemap'] = $this->t('XML sitemap');
        }
        break;

        // Users.
      case 'users':
        // Drupal core:
        $fields['format'] = $this->t('Text format selection');
        $fields['status'] = $this->t('Status (blocked/active)');
        if ($this->moduleHandler->moduleExists('contact')) {
          $fields['contact'] = $this->t('Contact settings');
        }
        if ($this->moduleHandler->moduleExists('overlay')) {
          $fields['overlay'] = $this->t('Administrative overlay');
        }
        // Third-party modules:
        if ($this->moduleHandler->moduleExists('metatag')) {
          $fields['metatag'] = $this->t('Meta tags');
        }
        if ($this->moduleHandler->moduleExists('redirect')) {
          $fields['redirect'] = $this->t('URL redirects');
        }
        break;

        // Comments.
      case 'comments':
        // Drupal core:
        $fields['format'] = $this->t('Text format selection');
        break;

        // Taxonomy.
      case 'taxonomy':
        // Drupal core:
        $fields['format'] = $this->t('Text format selection');
        $fields['relations'] = $this->t('Relations');
        if ($this->moduleHandler->moduleExists('path')) {
          $fields['path'] = $this->t('URL alias');
        }
        // Third-party modules:
        if ($this->moduleHandler->moduleExists('metatag')) {
          $fields['metatag'] = $this->t('Meta tags');
        }
        if ($this->moduleHandler->moduleExists('redirect')) {
          $fields['redirect'] = $this->t('URL redirects');
        }
        if ($this->moduleHandler->moduleExists('xmlsitemap_taxonomy')) {
          $fields['xmlsitemap'] = $this->t('XML sitemap');
        }
        break;

        // Blocks.
      case 'blocks':
        // Drupal core:
        $fields['format'] = $this->t('Text format selection');
        break;

        // Profiles.
      case 'profiles':
        $fields['format'] = $this->t('Text format selection');
        break;
    }

    // Allow other modules to alter the array of fields that can be hidden
    //drupal_alter('simplifyGetFields', $fields, $type);
// \Drupal::moduleHandler()->alter('simplifyGetFields', $fields, $type);
    return $fields;
  }
}
