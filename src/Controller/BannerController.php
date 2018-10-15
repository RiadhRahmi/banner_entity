<?php

namespace Drupal\banner_entity\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\banner_entity\Entity\BannerInterface;

/**entityTypeManager
 * Class BannerController.
 *
 *  Returns responses for Banner routes.
 */
class BannerController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Banner  revision.
   *
   * @param int $banner_revision
   *   The Banner  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($banner_revision) {
    $banner = $this->entityTypeManager()->getStorage('banner')->loadRevision($banner_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('banner');

    return $view_builder->view($banner);
  }

  /**
   * Page title callback for a Banner  revision.
   *
   * @param int $banner_revision
   *   The Banner  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($banner_revision) {
    $banner = $this->entityTypeManager()->getStorage('banner')->loadRevision($banner_revision);
    return $this->t('Revision of %title from %date', ['%title' => $banner->label(), '%date' => format_date($banner->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Banner .
   *
   * @param \Drupal\banner_entity\Entity\BannerInterface $banner
   *   A Banner  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(BannerInterface $banner) {
    $account = $this->currentUser();
    $langcode = $banner->language()->getId();
    $langname = $banner->language()->getName();
    $languages = $banner->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $banner_storage = $this->entityTypeManager()->getStorage('banner');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $banner->label()]) : $this->t('Revisions for %title', ['%title' => $banner->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all banner revisions") || $account->hasPermission('administer banner entities')));
    $delete_permission = (($account->hasPermission("delete all banner revisions") || $account->hasPermission('administer banner entities')));

    $rows = [];

    $vids = $banner_storage->revisionIds($banner);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\banner_entity\BannerInterface $revision */
      $revision = $banner_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $banner->getRevisionId()) {
          $link = $this->l($date, new Url('entity.banner.revision', ['banner' => $banner->id(), 'banner_revision' => $vid]));
        }
        else {
          $link = $banner->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.banner.translation_revert', ['banner' => $banner->id(), 'banner_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.banner.revision_revert', ['banner' => $banner->id(), 'banner_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.banner.revision_delete', ['banner' => $banner->id(), 'banner_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['banner_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
