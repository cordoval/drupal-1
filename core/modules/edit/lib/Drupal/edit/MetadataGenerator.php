<?php

/**
 * @file
 * Contains \Drupal\edit\MetadataGenerator.
 */

namespace Drupal\edit;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\edit\Access\EditEntityFieldAccessCheckInterface;
use Drupal\field\FieldInstanceInterface;

/**
 * Generates in-place editing metadata for an entity field.
 */
class MetadataGenerator implements MetadataGeneratorInterface {

   /**
   * An object that checks if a user has access to edit a given entity field.
   *
   * @var \Drupal\edit\Access\EditEntityFieldAccessCheckInterface
   */
  protected $accessChecker;

  /**
   * An object that determines which editor to attach to a given field.
   *
   * @var \Drupal\edit\EditorSelectorInterface
   */
  protected $editorSelector;

  /**
   * The manager for editor plugins.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $editorManager;

  /**
   * Constructs a new MetadataGenerator.
   *
   * @param \Drupal\edit\Access\EditEntityFieldAccessCheckInterface $access_checker
   *   An object that checks if a user has access to edit a given field.
   * @param \Drupal\edit\EditorSelectorInterface $editor_selector
   *   An object that determines which editor to attach to a given field.
   * @param \Drupal\Component\Plugin\PluginManagerInterface
   *   The manager for editor plugins.
   */
  public function __construct(EditEntityFieldAccessCheckInterface $access_checker, EditorSelectorInterface $editor_selector, PluginManagerInterface $editor_manager) {
    $this->accessChecker = $access_checker;
    $this->editorSelector = $editor_selector;
    $this->editorManager = $editor_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function generateEntityMetadata(EntityInterface $entity) {
    return array(
      'label' => $entity->label(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function generateFieldMetadata(FieldItemListInterface $items, $view_mode) {
    $entity = $items->getEntity();
    $field_name = $items->getFieldDefinition()->getName();

    // Early-return if user does not have access.
    $access = $this->accessChecker->accessEditEntityField($entity, $field_name);
    if (!$access) {
      return array('access' => FALSE);
    }

    // Early-return if no editor is available.
    $formatter_id = entity_get_render_display($entity, $view_mode)->getRenderer($field_name)->getPluginId();
    $editor_id = $this->editorSelector->getEditor($formatter_id, $items);
    if (!isset($editor_id)) {
      return array('access' => FALSE);
    }

    // Gather metadata, allow the editor to add additional metadata of its own.
    $label = $items->getFieldDefinition()->getLabel();
    $editor = $this->editorManager->createInstance($editor_id);
    $metadata = array(
      'label' => check_plain($label),
      'access' => TRUE,
      'editor' => $editor_id,
      'aria' => t('Entity @type @id, field @field', array('@type' => $entity->getEntityTypeId(), '@id' => $entity->id(), '@field' => $label)),
    );
    $custom_metadata = $editor->getMetadata($items);
    if (count($custom_metadata)) {
      $metadata['custom'] = $custom_metadata;
    }

    return $metadata;
  }

}
