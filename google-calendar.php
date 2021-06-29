<?php
  /**
   * Plugin Name: Google Calendar
   * Plugin URI: https://www.volitionremovals.com
   * Description: Plugin for selecting admin availability for clients
   * Version: 1.0
   * Author: Volition Removals
   * Author URI: https://www.volitionremovals.com
   **/

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  add_action('init', 'gc_check_if_is_admin');

  function gc_check_if_is_admin()
  {
    if (is_super_admin() ) {

      global $wpdb;
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

      $googleCalendarTable = $wpdb->prefix . "google_calendar";
      if($wpdb->get_var("show tables like '$googleCalendarTable'") != $googleCalendarTable) {

        $sqlQuery = "CREATE TABLE " . $googleCalendarTable . " (
          `id` smallint(5) NOT NULL AUTO_INCREMENT,
          `week_day` varchar(12) NOT NULL,
          `is_available` tinyint(1) NOT NULL,
          `from` varchar(20) NULL DEFAULT NULL,
          `to` varchar(20) NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `id_week_day` (`id`,`week_day`)
        );";
        dbDelta($sqlQuery);

        $weekDays = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        foreach ($weekDays as $weekDay) {
          $wpdb->insert(
            $googleCalendarTable,
            [
              'week_day' => $weekDay
            ]
          );
        }
      }

      require_once( 'gc_class.php' );
      GC_Plugin::get_instance();
    }
  }

  add_action( 'wp_ajax_get_google_calendar', 'gc_get_google_calendar' );
  add_action( 'wp_ajax_nopriv_get_google_calendar', 'gc_get_google_calendar' );
  function gc_get_google_calendar()
  {
    global $wpdb;
    $googleCalendarTable = $wpdb->prefix . "google_calendar";

    $googleCalendarAvailability = $wpdb->get_results( "SELECT * FROM $googleCalendarTable" );

    echo json_encode($googleCalendarAvailability);

    wp_die();
  }

  add_action( 'wp_ajax_update_google_calendar', 'gc_update_google_calendar' );
  add_action( 'wp_ajax_nopriv_update_google_calendar', 'gc_update_google_calendar' );
  function gc_update_google_calendar()
  {
    global $wpdb;

    $googleCalendarTable = $wpdb->prefix . "google_calendar";
    $dataToUpdate = [
      'is_available' => $_POST['data']['is_available'] == 'true' ? true : false,
      'from' => $_POST['data']['from'],
      'to' => $_POST['data']['to']
    ];
    $whereQuery = ['week_day' => $_POST['week_day']];

    $wpdb->update($googleCalendarTable, $dataToUpdate, $whereQuery);

    echo json_encode([
      'message' => 'success'
    ]);

    wp_die();
  }