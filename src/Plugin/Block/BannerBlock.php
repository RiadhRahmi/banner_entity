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
        $banners_ids = \Drupal::entityQuery('banner')->execute();
        $current_path = \Drupal::request()->getRequestUri();
        $node = \Drupal::routeMatch()->getParameter('node');

        if ($banners_ids) {
            foreach ($banners_ids as $banners_id) {
                $banner = Banner::load($banners_id);
                if (
                    (!empty($node) && !empty($banner->get("banner_content_type")->getString()) && $banner->get("banner_content_type")->getString() == $node->getType())
                    && (!empty($banner->get("banner_pages")->getString()) &&  $banner->get("banner_pages")->getString() == $current_path)
                ) {
                    $url_image = $banner->banner_image->entity ? file_create_url($banner->banner_image->entity->getFileUri()) : null;
                    $alt_image = $banner->banner_image->alt;
                    break;
                }elseif(
                    (!empty($node) && !empty($banner->get("banner_content_type")->getString()) &&
                        $banner->get("banner_content_type")->getString() == $node->getType() && empty($banner->get("banner_pages")->getString()))
                    || (!empty($banner->get("banner_pages")->getString()) &&  $banner->get("banner_pages")->getString() == $current_path &&
                        empty($banner->get("banner_content_type")->getString()))
                ){
                    $url_image = $banner->banner_image->entity ? file_create_url($banner->banner_image->entity->getFileUri()) : null;
                    $alt_image = $banner->banner_image->alt;
                }
            }
        }

        if (!empty($node)) {
            if (isset($node->field_image_banniere)) {
                if ($node->field_image_banniere->getValue()) {
                    $url_image = file_create_url($node->field_image_banniere->entity->getFileUri());
                    $alt_image = $node->field_image_banniere->alt;
                }
            }
        }


        $themeName = \Drupal::service('theme.manager')->getActiveTheme()->getName();

//        $content_block_page_title = \Drupal\block\Entity\Block::load($themeName . '_page_title');
//        $block_page_title = \Drupal::entityTypeManager()->getViewBuilder('block')->view($content_block_page_title);

        $request = \Drupal::request();
        $route_match = \Drupal::routeMatch();
        $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());


        $content_breadcrumbs = \Drupal\block\Entity\Block::load($themeName . '_breadcrumbs');
        $block_breadcrumbs = \Drupal::entityTypeManager()->getViewBuilder('block')->view($content_breadcrumbs);


        $build = [];

        $build['banner_block']['#theme'] = 'banner_block';
        $build['banner_block']['#url_image'] = $url_image ? $url_image : '';
        $build['banner_block']['#alt_image'] = $alt_image ? $alt_image : '';
        $build['banner_block']['#block_breadcrumbs'] = $block_breadcrumbs;
        $build['banner_block']['#block_page_title'] = $title;
        $build['banner_block']['#cache'] = array('max-age' => 0);

        return $build;
    }


}


