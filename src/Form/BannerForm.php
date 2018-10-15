<?php

namespace Drupal\banner_entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;

/**
 * Form controller for Banner edit forms.
 *
 * @ingroup banner_entity
 */
class BannerForm extends ContentEntityForm
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        /* @var $entity \Drupal\banner_entity\Entity\Banner */
        $form = parent::buildForm($form, $form_state);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state)
    {
        $entity = $this->entity;

        // Save as a new revision if requested to do so.
        if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
            $entity->setNewRevision();

            // If a new revision is created, save the current user as revision author.
            $entity->setRevisionCreationTime(REQUEST_TIME);
            $entity->setRevisionUserId(\Drupal::currentUser()->id());
        } else {
            $entity->setNewRevision(FALSE);
        }

        $status = parent::save($form, $form_state);

        switch ($status) {
            case SAVED_NEW:
                drupal_set_message($this->t('Created the %label Banner.', [
                    '%label' => $entity->label(),
                ]));
                break;

            default:
                drupal_set_message($this->t('Saved the %label Banner.', [
                    '%label' => $entity->label(),
                ]));
        }
        $form_state->setRedirect('entity.banner.canonical', ['banner' => $entity->id()]);
    }

}
