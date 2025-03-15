<?php

declare(strict_types=1);

namespace Drupal\my_block_example\Plugin\Block;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides an exampleblock block.
 *
 * @Block(
 *   id = "my_example_block",
 *   admin_label = @Translation("ExampleBlock"),
 *   category = @Translation("Custom"),
 *   context_definitions = {
 *      "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *    }
 * )
 */
final class MyExampleBlockBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'example' => $this->t('Hello world!'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['example'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Example'),
      '#default_value' => $this->configuration['example'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['example'] = $form_state->getValue('example');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    try {
      // Get the context value. If the context is not found, ContextException
      // is thrown. Make sure to handle it accordingly.
      /** @var \Drupal\user\Entity\User $user */
      $node = $this->getContextValue('node');

    }
    catch (ContextException $e) {

      $node = NULL;
    }
    // Retrieve the message from the configuration and pass it into the render
    // array.
    $message = $this->getConfiguration()['example'];

    return [
      '#theme' => 'basic_twig_block',
      '#msg' => $message,
      '#node' => $node
    ];
  }


  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account): AccessResultInterface {
    return AccessResult::allowedIfHasPermission($account, 'access to my block');
  }

}
