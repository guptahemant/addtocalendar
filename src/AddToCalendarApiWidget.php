<?php

namespace Drupal\addtocalendar;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class AddToCalendarApiWidget.
 */
class AddToCalendarApiWidget {

  /**
   * Declare various setting variables for add to calendar api widget.
   *
   * @var string
   */
  protected $atcStyle;
  protected $atcDisplayText;
  protected $atcTitle;
  protected $atcDescription;
  protected $atcLocation;
  protected $atcOrganizer;
  protected $atcOrganizerEmail;
  protected $atcDateStart;
  protected $atcDateEnd;
  protected $atcPrivacy;
  protected $atcDataSecure;
  protected $timeZone;

  /**
   * Hold the various calendars usable in the widget.
   *
   * @var array
   */
  protected $atcDataCalendars = [];

  /**
   * Constructs a new AddToCalendarApiWidget object.
   */
  public function __construct() {

    $this->atcStyle = 'blue';
    $this->atcDisplayText = 'Add to calendar';
    $this->atcTitle = 'Some event title';
    $this->atcDescription = 'Some event description';
    $this->atcLocation = 'Some event location';
    // Fetching site name and site email id.
    $config = \Drupal::config('system.site');
    $site_name = $config->get('name');
    $site_mail = $config->get('mail');

    $this->atcOrganizer = $site_name;
    $this->atcOrganizerEmail = $site_mail;
    $this->atcDateStart = 'now';
    $this->atcDateEnd = 'now';
    $this->atcPrivacy = 'public';
    $this->atcDataSecure = 'auto';
    $data_calendars = array('iCalendar',
      'Google Calendar',
      'Outlook',
      'Outlook Online',
      'Yahoo! Calendar',
    );
    $this->atcDataCalendars = $data_calendars;
    $this->timeZone = drupal_get_user_timezone();

  }

  /**
   * Use this function to set values for the widget.
   */
  public function setWidgetValues($config_values = array()) {
    foreach ($config_values as $key => $value) {
      $this->$key = $value;
    }
  }

  /**
   * Use this function to get a particular value from this widget.
   */
  public function getWidgetValue($config_value) {
    return $this->$config_value;
  }

  /**
   * Constructs and returns a renderable array widget for add to calendar.
   */
  public function generateWidget() {

    // Start building the renderable array.
    $build['addtocalendar'] = [];
    $display_text = t('%text', array('%text' => $this->atcDisplayText));
    $build['addtocalendar_button'] = [
      '#type' => 'html_tag',
      '#tag' => 'a',
      '#value' => $display_text->__toString(),
      '#attributes' => [
        'class' => 'atcb-link',
      ],
    ];
    $timeZone = $this->timeZone;

    // Assuming date and end_date is provide in UTC format.
    $date = new DrupalDateTime(preg_replace('/T/', ' ', $this->atcDateStart), 'UTC');
    $end_date = new DrupalDateTime(preg_replace('/T/', ' ', $this->atcDateEnd), 'UTC');

    $info = [
      'atc_date_start' => $date->format('Y-m-d H:i:s', ['timezone' => $timeZone]),
      'atc_date_end' => $end_date->format('Y-m-d H:i:s', ['timezone' => $timeZone]),
      'atc_title' => $this->atcTitle,
      'atc_description' => $this->atcDescription,
      'atc_location' => $this->atcLocation,
      'atc_organizer' => $this->atcOrganizer,
      'atc_organizer_email' => $this->atcOrganizerEmail,
      'atc_timezone' => $timeZone,
      'atc_privacy' => $this->atcPrivacy,
    ];

    foreach ($info as $key => $value) {
      $build['addtocalendar'][$key] = [
        '#type' => 'html_tag',
        '#tag' => 'var',
        '#value' => $value,
        '#attributes' => [
          'class' => $key,
        ],
      ];
    }

    $build['addtocalendar'] = [
      '#type' => 'html_tag',
      '#tag' => 'span',
      '#value' => render($build['addtocalendar_button']) . '<var class="atc_event">' . render($build['addtocalendar']) . '</var>',
      '#attributes' => [
        'class' => [
          'addtocalendar',
        ],
      ],
    ];
    // Add calendars.
    $calendars = implode(', ', $this->atcDataCalendars);
    $build['addtocalendar']['#attributes']['data-calendars'] = $calendars;

    $build['addtocalendar']['#attributes']['data-secure'] = $this->atcDataSecure;

    // Add styling.
    switch ($this->atcStyle) {
      case 'blue':
        $style['class'] = 'atc-style-blue';
        $style['library'] = 'addtocalendar/blue';
        break;

      case 'glow_orange':
        $style['class'] = 'atc-style-glow-orange';
        $style['library'] = 'addtocalendar/glow_orange';
        break;
    }

    if (!empty($style)) {
      $build['addtocalendar']['#attributes']['class'][] = $style['class'];
      $build['#attached']['library'][] = $style['library'];
    }
    $build['#attached']['library'][] = 'addtocalendar/base';
    return $build;
  }

}
