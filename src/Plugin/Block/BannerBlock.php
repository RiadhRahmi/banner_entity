<?php

namespace Drupal\banner_entity\Plugin\Block;

use Drupal\banner_entity\Entity\Banner;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a 'BannerBlock' block.
 *
 * @Block(
 *  id = "banner_block",
 *  admin_label = @Translation("Banner block"),
 * )
 */
class BannerBlock extends BlockBase
{

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $current_path = \Drupal::request()->getRequestUri();
        $query = \Drupal::entityQuery('banner')
            ->condition('banner_pages', $current_path);
        $banners_ids = $query->execute();

        if ($banners_ids) {
            $banner = Banner::load(current($banners_ids));
            $url_image = $banner->banner_image->entity ? file_create_url($banner->banner_image->entity->getFileUri()) : null;
            $alt_image = $banner->banner_image->alt;
        }

        $build = [];

        $build['banner_block']['#theme'] = 'banner_block';
        $build['banner_block']['#url_image'] = $url_image ? $url_image : '';
        $build['banner_block']['#alt_image'] = $alt_image ? $alt_image : '';
        $build['banner_block']['#cache'] = array('max-age' => 0);

        return $build;
    }


}


