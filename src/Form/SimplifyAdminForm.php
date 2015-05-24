<?php

/**
 * @file
 * Contains \Drupal\simplify\Form\SimplifyAdminForm.
 */

namespace Drupal\simplify\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Path\PathValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandler;

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

    // User 1 permission
    $form['simplify_user1'] = array(
        '#type' => 'checkbox',
        '#title' => t('Hide fields from User 1'),
        '#description' => t("By default, Drupal gives User 1 <em>all</em> permissions (including Simplify's <em>View hidden fields</em> permission). This means that User 1 will always be able to view all hidden fields (and is by design).<br>Check this box to override this functionality and hide fields from User 1. NOTE: As this option overrides default Drupal behaviour, it should be used sparingly and only when you fully understand the consequences."),
        '#default_value' => $simplify_config->get('simplify_user1'),
    );

    // Nodes
    $form['nodes'] = array(
      '#type' => 'details',
      '#title' => t('Nodes'),
      '#description' => t("These fields will be hidden from <em>all</em> node forms. Alternatively, to hide fields from node forms of a particular content type, edit the content type and configure the hidden fields there."),
      '#open' => TRUE,
    );
    $form['nodes']['simplify_nodes_global'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Hide'),
      '#options' => $this->simplify_get_fields('nodes'),
      '#default_value' => $simplify_config->get('simplify_nodes_global'),
    );

    // Users
    $form['users'] = array(
      '#type' => 'details',
      '#title' => t('Users'),
      '#description' => t("These fields will be hidden from all user account forms."),
      '#open' => TRUE,
    );
    $form['users']['simplify_users_global'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Hide'),
      '#options' => $this->simplify_get_fields('users'),
      '#default_value' => $simplify_config->get('simplify_users_global'),
    );

    // Comments
    if ($this->moduleHandler->moduleExists('comment')) {
      $form['comments'] = array(
        '#type' => 'details',
        '#title' => t('Comments'),
        '#description' => t("These fields will be hidden from <em>all</em> comment forms. Alternatively, to hide fields from comment forms for nodes of a particular content type, edit the content type and configure the hidden fields there."),
        '#open' => TRUE,
      );
      $form['comments']['simplify_comments_global'] = array(
        '#type' => 'checkboxes',
        '#title' => t('Hide'),
        '#options' => $this->simplify_get_fields('comments'),
        '#default_value' => $simplify_config->get('simplify_comments_global'),
      );
    }

    // Taxonomy
    if ($this->moduleHandler->moduleExists('taxonomy')) {
      $form['taxonomy'] = array(
        '#type' => 'details',
        '#title' => t('Taxonomy'),
        '#description' => t("These fields will be hidden from <em>all</em> taxonomy term forms. Alternatively, to hide fields from taxonomy term forms for a particular vocabulary, edit the vocabulary and configure the hidden fields there."),
        '#open' => TRUE,
      );
      $form['taxonomy']['simplify_taxonomy_global'] = array(
        '#type' => 'checkboxes',
        '#title' => t('Hide'),
        '#options' => $this->simplify_get_fields('taxonomy'),
        '#default_value' => $simplify_config->get('simplify_taxonomy_global'),
      );
    }

    // Blocks
    if ($this->moduleHandler->moduleExists('block')) {
      $form['blocks'] = array(
        '#type' => 'details',
        '#title' => t('Blocks'),
        '#description' => t("These fields will be hidden from all block forms."),
        '#open' => TRUE,
      );
      $form['blocks']['simplify_blocks_global'] = array(
        '#type' => 'checkboxes',
        '#title' => t('Hide'),
        '#options' => $this->simplify_get_fields('blocks'),
        '#default_value' => $simplify_config->get('simplify_blocks_global'),
      );
    }

    // Profiles
    if ($this->moduleHandler->moduleExists('profile2_page')) {
      $form['profiles'] = array(
        '#type' => 'details',
        '#title' => t('Profiles'),
        '#description' => t("These fields will be hidden from all profile forms."),
        '#open' => TRUE,
      );
      $form['profiles']['simplify_profiles_global'] = array(
          '#type' => 'checkboxes',
          '#title' => t('Hide'),
          '#options' => $this->simplify_get_fields('profiles'),
          '#default_value' => $simplify_config->get('simplify_profiles_global'),
      );
    }

    // Remove empty values from saved variables (see: http://drupal.org/node/61760#comment-402631)
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
    $this->config('simplify.global')
      ->set('simplify_user1', $form_state->getValue('simplify_user1'))
      ->set('simplify_nodes_global', $form_state->getValue('simplify_nodes_global'))
      ->set('simplify_users_global', $form_state->getValue('simplify_users_global'))
      ->set('simplify_comments_global', $form_state->getValue('simplify_comments_global'))
      ->set('simplify_taxonomy_global', $form_state->getValue('simplify_taxonomy_global'))
      ->set('simplify_blocks_global', $form_state->getValue('simplify_blocks_global'))
      ->set('simplify_profiles_global', $form_state->getValue('simplify_profiles_global'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Get an array of fields (by type) that can be hidden.
   */
  private function simplify_get_fields($type) {
    $fields = array();

    switch ($type) {
      // Nodes
      case 'nodes':
        // Drupal core
        $fields['author'] = t('Authoring information');
        $fields['format'] = t('Text format selection');
        $fields['options'] = t('Publishing options');
        $fields['revision'] = t('Revision information');
        if ($this->moduleHandler->moduleExists('book')) {
          $fields['book'] = t('Book outline');
        }
        if ($this->moduleHandler->moduleExists('comment')) {
          $fields['comment'] = t('Comment settings');
        }
        if ($this->moduleHandler->moduleExists('menu_ui')) {
          $fields['menu'] = t('Menu settings');
        }
        if ($this->moduleHandler->moduleExists('path')) {
          $fields['path'] = t('URL path settings');
        }
        // Third-party modules
        if ($this->moduleHandler->moduleExists('domain')) {
          $fields['domain'] = t('Domain access');
        }
        if ($this->moduleHandler->moduleExists('entity_translation') && entity_translation_enabled('node')) {
          $fields['entity_translation'] = t('Entity translation');
        }
        if ($this->moduleHandler->moduleExists('metatag')) {
          $fields['metatag'] = t('Meta tags');
        }
        if ($this->moduleHandler->moduleExists('node_noindex')) {
          $fields['node_noindex'] = t('Node noindex');
        }
        if ($this->moduleHandler->moduleExists('redirect')) {
          $fields['redirect'] = t('URL redirects');
        }
        if ($this->moduleHandler->moduleExists('xmlsitemap_node')) {
          $fields['xmlsitemap'] = t('XML sitemap');
        }
        break;

        // Users
      case 'users':
        // Drupal core
        $fields['format'] = t('Text format selection');
        $fields['status'] = t('Status (blocked/active)');
        if ($this->moduleHandler->moduleExists('contact')) {
          $fields['contact'] = t('Contact settings');
        }
        if ($this->moduleHandler->moduleExists('overlay')) {
          $fields['overlay'] = t('Administrative overlay');
        }
        // Third-party modules
        if ($this->moduleHandler->moduleExists('metatag')) {
          $fields['metatag'] = t('Meta tags');
        }
        if ($this->moduleHandler->moduleExists('redirect')) {
          $fields['redirect'] = t('URL redirects');
        }
        break;

        // Comments
      case 'comments':
        // Drupal core
        $fields['format'] = t('Text format selection');
        break;

        // Taxonomy
      case 'taxonomy':
        // Drupal core
        $fields['format'] = t('Text format selection');
        $fields['relations'] = t('Relations');
        if ($this->moduleHandler->moduleExists('path')) {
          $fields['path'] = t('URL alias');
        }
        // Third-party modules
        if ($this->moduleHandler->moduleExists('metatag')) {
          $fields['metatag'] = t('Meta tags');
        }
        if ($this->moduleHandler->moduleExists('redirect')) {
          $fields['redirect'] = t('URL redirects');
        }
        if ($this->moduleHandler->moduleExists('xmlsitemap_taxonomy')) {
          $fields['xmlsitemap'] = t('XML sitemap');
        }
        break;

        // Blocks
      case 'blocks':
        // Drupal core
        $fields['format'] = t('Text format selection');
        break;

        // Profiles
      case 'profiles':
        $fields['format'] = t('Text format selection');
        break;
    }

    // Allow other modules to alter the array of fields that can be hidden
    //drupal_alter('simplify_get_fields', $fields, $type);

    return $fields;
  }
}