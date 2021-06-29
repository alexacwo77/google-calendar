<?php

  class GC_Plugin
  {
    static $instance;

    public $results_obj;

    public function __construct()
    {
      add_action('admin_menu', [$this, 'plugin_menu' ]);
      add_action('admin_init', [$this, 'remove_default_stylesheets' ], 1);

      wp_enqueue_script( 'angular-js', plugins_url('/js/angular-1.5.8.min.js',__FILE__ ));
      wp_localize_script( 'angular-js', 'angular_object', ['ajax_url' => admin_url('admin-ajax.php')]);

      wp_enqueue_style('font-awesome', plugins_url('/css/font-awesome-4.7.0.min.css',__FILE__ ), [], '1.1');
    }

    function remove_default_stylesheets()
    {
      // TODO: temp fix
      wp_deregister_style('forms');
      wp_enqueue_style('forms', plugins_url('/css/custom-forms.css',__FILE__ ), [], '1.2');
    }

    public function plugin_menu()
    {
      $hook = add_menu_page(
        'Google Calendar',
        'Google Calendar',
        'manage_options',
        'google_calendar',
        [ $this, 'plugin_settings_page' ]
      );
    }

    public function plugin_settings_page()
    {
      $pluginPageHtml = file_get_contents(plugins_url('/gc_view.html',__FILE__ ));
      echo $pluginPageHtml;
    }

    public static function get_instance()
    {
      if ( ! isset( self::$instance ) ) {
        self::$instance = new self();
      }

      return self::$instance;
    }
  }
?>