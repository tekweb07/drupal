<?php

namespace Drupal\hello\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a calculator form.
 */
class CalculatorForm extends FormBase {

  /**
   * @var StateInterface
   */
  protected $state;

  /**
   * @var TimeInterface
   */
  protected $time;

  /**
   * {@inheritdoc}.
   */
  public function __construct(StateInterface $state, TimeInterface $time) {
    $this->state = $state;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}.
   */
  public function getFormID() {
    return 'hello_calculator';
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Champ destiné à afficher le résultat du calcul.
    if (isset($form_state->getRebuildInfo()['result'])) {
      $form['result'] = [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Result: ') . $form_state->getRebuildInfo()['result'],
      ];
    }
    $form['value1'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First value'),
      '#description' => $this->t('Enter first value.'),
      '#required' => TRUE,
      '#default_value' => '2',
      '#ajax'  => [
        'callback' => [$this, 'AjaxValidateNumeric'],
        'event' => 'keyup',
      ],
      '#prefix' => '<span id="error-message-value1"></span>',
    ];
    $form['operation'] = [
      '#type' => 'radios',
      '#title' => $this->t('Operation'),
      '#description' => $this->t('Choose operation for processing.'),
      '#options' => [
        'addition' => $this->t('Add'),
        'soustraction' => $this->t('Soustract'),
        'multiplication' => $this->t('Multiply'),
        'division' => $this->t('Divide'),
      ],
      '#default_value' => 'addition',
    ];
    $form['value2'] = [
      '#type' => 'textfield',
      '#title'  => $this->t('Second value'),
      '#description' => $this->t('Enter second value.'),
      '#required' => TRUE,
      '#default_value' => '2',
      '#ajax' => [
        'callback' => [$this, 'AjaxValidateNumeric'],
        'event' => 'keyup',
      ],
      '#prefix' => '<span id="error-message-value2"></span>',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Calculate'),
    ];

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function AjaxValidateNumeric(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $field = $form_state->getTriggeringElement()['#name'];
    if (is_numeric($form_state->getValue($field))) {
      $css = ['border' => '2px solid green'];
      $message = $this->t('OK!');
    } else {
      $css = ['border' => '2px solid red'];
      $message = $this->t('%field must be numeric!', ['%field' => $form[$field]['#title']]);
    }

    $response->AddCommand(new CssCommand("[name=$field]", $css));
    $response->AddCommand(new HtmlCommand('#error-message-' . $field, $message));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value_1 = $form_state->getValue('value1');
    $value_2 = $form_state->getValue('value2');
    $operation = $form_state->getValue('operation');

    if (!is_numeric($value_1)) {
      $form_state->setErrorByName('value1', $this->t('First value must be numeric!'));
    }
    if (!is_numeric($value_2)) {
      $form_state->setErrorByName('value2', $this->t('Second value must be numeric!'));
    }
    if ($value_2 == '0' && $operation == 'division') {
      $form_state->setErrorByName('value2', $this->t('Cannot divide by zero!'));
    }

    if (isset($form['result'])) {
      unset($form['result']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Récupère la valeur des champs.
    $value_1 = $form_state->getValue('value1');
    $value_2 = $form_state->getValue('value2');
    $operation = $form_state->getValue('operation');

    $resultat = '';
    switch ($operation) {
      case 'addition':
        $resultat = $value_1 + $value_2;
        break;
      case 'soustraction':
        $resultat = $value_1 - $value_2;
        break;
      case 'multiplication':
        $resultat = $value_1 * $value_2;
        break;
      case 'division':
        $resultat = $value_1 / $value_2;
        break;
    }

    // On passe le résultat.
    $form_state->addRebuildInfo('result', $resultat);
    // Reconstruction du formulaire avec les valeurs saisies.
    $form_state->setRebuild();
    // Enregistrement de l'heure de soumission avec State API.
    $this->state->set('hello_form_submission_time', $this->time->getCurrentTime());
  }

}
