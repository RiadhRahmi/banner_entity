<?php

namespace Drupal\banner_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Banner entities.
 *
 * @ingroup banner_entity
 */
class BannerListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Banner ID');
    $header['name'] = $this->t('Name');
    $header['banner_content_type'] = $this->t('Content type');
    $header['banner_pages'] = $this->t('Pages');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\banner_entity\Entity\Banner */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.banner.edit_form',
      ['banner' => $entity->id()]
    );
      $row['banner_content_type'] = $entity->banner_content_type->value;
      $row['banner_pages'] = $entity->banner_pages->value;
    return $row + parent::buildRow($entity);
  }

}
