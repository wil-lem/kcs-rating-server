<?php

class KcsRatingsServerSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'KCS Ratings Server',
            'manage_options',
            'kcs-ratings-server-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'kcs_ratings_server_connection_options' );
        ?>
        <div class="wrap">
            <h1>Gedenken 2D Settings</h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'kcs_ratings_server_settings' );
                do_settings_sections( 'kcs-ratings-server-setting-admin' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'kcs_ratings_server_settings', // Option group
            'kcs_ratings_server_connection_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'kcs_ratings_server_settings_connection', // ID
            'Connection', // Title
            array( $this, 'print_section_info' ), // Callback
            'kcs-ratings-server-setting-admin' // Page
        );



        add_settings_field(
            'secret',
            'Secret',
            array( $this, 'secret_callback' ),
            'kcs-ratings-server-setting-admin',
            'kcs_ratings_server_settings_connection'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['secret'] ) )
            $new_input['secret'] = sanitize_text_field( $input['secret'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }


    public function print_message($message,$type = NULL) {
        $message_class = 'updated';
        if($type =='error') {
            $message_class = 'error';
        }

        echo '
        
                <div class="' . $message_class . ' notice">
                    <p>' . $message . '</p>
                </div>
        ';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function secret_callback()
    {
        printf(
            '<input type="text" id="secret" name="kcs_ratings_server_connection_options[secret]" value="%s" />',
            isset( $this->options['secret'] ) ? esc_attr( $this->options['secret']) : ''
        );
    }
}
