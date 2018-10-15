<?php

namespace Drupal\banner_entity\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;
use Drupal\node\Entity\NodeType;


/**
 * Defines the Banner entity.
 *
 * @ingroup banner_entity
 *
 * @ContentEntityType(
 *   id = "banner",
 *   label = @Translation("Banner"),
 *   handlers = {
 *     "storage" = "Drupal\banner_entity\BannerStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\banner_entity\BannerListBuilder",
 *     "views_data" = "Drupal\banner_entity\Entity\BannerViewsData",
 *     "translation" = "Drupal\banner_entity\BannerTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\banner_entity\Form\BannerForm",
 *       "add" = "Drupal\banner_entity\Form\BannerForm",
 *       "edit" = "Drupal\banner_entity\Form\BannerForm",
 *       "delete" = "Drupal\banner_entity\Form\BannerDeleteForm",
 *     },
 *     "access" = "Drupal\banner_entity\BannerAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\banner_entity\BannerHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "banner",
 *   data_table = "banner_field_data",
 *   revision_table = "banner_revision",
 *   revision_data_table = "banner_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer banner entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/banner/{banner}",
 *     "add-form" = "/admin/structure/banner/add",
 *     "edit-form" = "/admin/structure/banner/{banner}/edit",
 *     "delete-form" = "/admin/structure/banner/{banner}/delete",
 *     "version-history" = "/admin/structure/banner/{banner}/revisions",
 *     "revision" = "/admin/structure/banner/{banner}/revisions/{banner_revision}/view",
 *     "revision_revert" = "/admin/structure/banner/{banner}/revisions/{banner_revision}/revert",
 *     "revision_delete" = "/admin/structure/banner/{banner}/revisions/{banner_revision}/delete",
 *     "translation_revert" = "/admin/structure/banner/{banner}/revisions/{banner_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/banner",
 *   },
 *   field_ui_base_route = "banner.settings"
 * )
 */
class Banner extends RevisionableContentEntityBase implements BannerInterface
{

    use EntityChangedTrait;

    /**
     * {@inheritdoc}
     */
    public static function preCreate(EntityStorageInterface $storage_controller, array &$values)
    {
        parent::preCreate($storage_controller, $values);
        $values += [
            'user_id' => \Drupal::currentUser()->id(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function urlRouteParameters($rel)
    {
        $uri_route_parameters = parent::urlRouteParameters($rel);

        if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
            $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
        } elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
            $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
        }

        return $uri_route_parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function preSave(EntityStorageInterface $storage)
    {
        parent::preSave($storage);

        foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
            $translation = $this->getTranslation($langcode);

            // If no owner has been set explicitly, make the anonymous user the owner.
            if (!$translation->getOwner()) {
                $translation->setOwnerId(0);
            }
        }

        // If no revision author has been set explicitly, make the banner owner the
        // revision author.
        if (!$this->getRevisionUser()) {
            $this->setRevisionUserId($this->getOwnerId());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->get('name')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->set('name', $name);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedTime()
    {
        return $this->get('created')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedTime($timestamp)
    {
        $this->set('created', $timestamp);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner()
    {
        return $this->get('user_id')->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerId()
    {
        return $this->get('user_id')->target_id;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnerId($uid)
    {
        $this->set('user_id', $uid);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(UserInterface $account)
    {
        $this->set('user_id', $account->id());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublished()
    {
        return (bool)$this->getEntityKey('status');
    }

    /**
     * {@inheritdoc}
     */
    public function setPublished($published)
    {
        $this->set('status', $published ? TRUE : FALSE);
        return $this;
    }



    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Authored by'))
            ->setDescription(t('The user ID of author of the Banner entity.'))
            ->setRevisionable(TRUE)
            ->setSetting('target_type', 'user')
            ->setSetting('handler', 'default')
            ->setTranslatable(TRUE)
            ->setDisplayOptions('view', [
                'label' => 'hidden',
                'type' => 'author',
                'weight' => 0,
            ])
            ->setDisplayOptions('form', [
                'type' => 'entity_reference_autocomplete',
                'weight' => 5,
                'settings' => [
                    'match_operator' => 'CONTAINS',
                    'size' => '60',
                    'autocomplete_type' => 'tags',
                    'placeholder' => '',
                ],
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Name'))
            ->setDescription(t('The name of the Banner entity.'))
            ->setRevisionable(TRUE)
            ->setSettings([
                'max_length' => 50,
                'text_processing' => 0,
            ])
            ->setDefaultValue('')
            ->setDisplayOptions('view', [
                'label' => 'above',
                'type' => 'string',
                'weight' => -4,
            ])
            ->setDisplayOptions('form', [
                'type' => 'string_textfield',
                'weight' => -4,
            ])
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE)
            ->setRequired(TRUE);


        $nodeTypes = NodeType::loadMultiple();
        $options = [];
        foreach ($nodeTypes as $key => $type) {
            $options[$key] = $type->label();
        }
        $fields['banner_content_type'] = BaseFieldDefinition::create('list_string')
            ->setLabel(t('Content Type'))
            ->setDescription(t('Select a content type.'))
            ->setSettings(array(
                'allowed_values' => $options,
            ))
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'list_default',
                'weight' => -4,
            ))
            ->setDisplayOptions('form', array(
                'type' => 'options_select',
                'weight' => -4,
            ))
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);


        $fields['banner_pages'] = BaseFieldDefinition::create('string_long')
            ->setLabel(t('Pages'))
            ->setDescription(t("Specify pages by using their paths. Enter one path per line. The * character is a wildcard. An example path is <em class='placeholder'>/user/*</em> for every user page. <em class='placeholder'>&lt;front&gt;</em> is the front page."))
            ->setSettings(array(
                'default_value' => '',
                'max_length' => 255,
                'text_processing' => 0,
            ))
            ->setDisplayOptions('view', array(
                'label' => 'above',
                'type' => 'string_textarea',
                'weight' => -4,
            ))
            ->setDisplayOptions('form', array(
                'type' => 'string_textarea',
                'weight' => -4,
            ))
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);




         $fields['banner_image'] = BaseFieldDefinition::create('image')
             ->setLabel(t('Banner Image'))
             ->setSettings(array(
                 'file_directory' => 'banners',
                 'alt_field' => true,
                 'alt_field_required' => true,
                 'file_extensions' => 'png jpg jpeg',
             ))
             ->setDisplayOptions('view', array(
                 'label' => 'above',
                 'type' => 'image',
                 'weight' => -4,
             ))
             ->setDisplayOptions('form', array(
                 'label' => 'above',
                 'type' => 'image_image',
                 'weight' => -4,
             ))
             ->setDisplayConfigurable('form', TRUE)
             ->setDisplayConfigurable('view', TRUE);



        $fields['status'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('Publishing status'))
            ->setDescription(t('A boolean indicating whether the Banner is published.'))
            ->setRevisionable(TRUE)
            ->setDefaultValue(TRUE)
            ->setDisplayOptions('form', [
                'type' => 'boolean_checkbox',
                'weight' => 3,
            ]);

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The time that the entity was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setDescription(t('The time that the entity was last edited.'));

        $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('Revision translation affected'))
            ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
            ->setReadOnly(TRUE)
            ->setRevisionable(TRUE)
            ->setTranslatable(TRUE);

        return $fields;
    }

}
