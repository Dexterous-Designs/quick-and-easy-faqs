<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
if ( ! class_exists( 'Quick_And_Easy_FAQs_Admin' ) ) {

    class Quick_And_Easy_FAQs_Admin {

        /**
         * The ID of this plugin.
         */
        private $plugin_name;

        /**
         * The version of this plugin.
         */
        private $version;

        /**
         * The domain specified for this plugin.
         */
        private $domain;

        /**
         * FAQs options
         */
        public $options;

        protected static $_instance;

        /**
         * Initialize the class and set its properties.
         */
        public function __construct() {

            $this->plugin_name = QE_FAQS_PLUGIN_NAME;
            $this->version = QE_FAQS_PLUGIN_VERSION;
            $this->domain = QE_FAQS_PLUGIN_NAME;
            $this->options = get_option( 'quick_and_easy_faqs_options' );
            $this->admin_hooks_execution();

        }

        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        public function admin_hooks_execution() {
            register_activation_hook( __FILE__, array( $this, 'faqs_activation' ) ); 
            register_deactivation_hook( __FILE__, array( $this, 'faqs_deactivation' ) );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'init', array( $this, 'register_faqs_post_type' ) );
            add_action( 'init', array( $this, 'register_faqs_group_taxonomy' ) );
            add_action( 'admin_menu', array( $this, 'add_faqs_options_page' ) );
            add_action( 'admin_init', array( $this, 'initialize_faqs_options' ) );
            add_action( 'plugins_loaded', array( $this, 'faqs_load_textdomain' ) );

            add_filter( 'plugin_action_links_' . QE_FAQS_PLUGIN_BASENAME, array( $this, 'faqs_action_links' ) );
        }

        /**
         * The code that runs during plugin activation.
         * This action is documented in includes/class-quick-and-easy-faqs-activator.php
         */
        public function faqs_activation() {
            
        }

        /**
         * The code that runs during plugin deactivation.
         * This action is documented in includes/class-quick-and-easy-faqs-deactivator.php
         */
        public function faqs_deactivation() {
            
        }

        /**
         * Load the plugin text domain for translation.
         */
        public function faqs_load_textdomain() {

            load_plugin_textdomain(
                $this->domain,
                false, 
                dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
            );

        }

        /**
         * Register the stylesheets for the admin area.
         */
        public function admin_enqueue_styles() {
            // Add the color picker css file
            wp_enqueue_style( 'wp-color-picker' );
            // plugin custom css file
            wp_enqueue_style( $this->plugin_name, dirname( plugin_dir_url( __FILE__ ) ) . '/css/quick-and-easy-faqs-admin.css', array( 'wp-color-picker' ), $this->version, 'all' );
        }

        /**
         * Register the JavaScript for the admin area.
         */
        public function admin_enqueue_scripts() {
            wp_enqueue_script( $this->plugin_name, dirname( plugin_dir_url( __FILE__ ) ) . '/js/quick-and-easy-faqs-admin.js', array( 'jquery' , 'wp-color-picker' ), $this->version, false );
        }


        /**
         * Register FAQs custom post type
         */
        public function register_faqs_post_type() {

            $labels = array(
                'name'                => _x( 'FAQs', 'Post Type General Name', 'quick-and-easy-faqs' ),
                'singular_name'       => _x( 'FAQ', 'Post Type Singular Name', 'quick-and-easy-faqs' ),
                'menu_name'           => __( 'FAQs', 'quick-and-easy-faqs' ),
                'name_admin_bar'      => __( 'FAQs', 'quick-and-easy-faqs' ),
                'parent_item_colon'   => __( 'Parent FAQ:', 'quick-and-easy-faqs' ),
                'all_items'           => __( 'FAQs', 'quick-and-easy-faqs' ),
                'add_new_item'        => __( 'Add New FAQ', 'quick-and-easy-faqs' ),
                'add_new'             => __( 'Add New', 'quick-and-easy-faqs' ),
                'new_item'            => __( 'New FAQ', 'quick-and-easy-faqs' ),
                'edit_item'           => __( 'Edit FAQ', 'quick-and-easy-faqs' ),
                'update_item'         => __( 'Update FAQ', 'quick-and-easy-faqs' ),
                'view_item'           => __( 'View FAQ', 'quick-and-easy-faqs' ),
                'search_items'        => __( 'Search FAQ', 'quick-and-easy-faqs' ),
                'not_found'           => __( 'Not found', 'quick-and-easy-faqs' ),
                'not_found_in_trash'  => __( 'Not found in Trash', 'quick-and-easy-faqs' ),
            );

            $args = array(
                'label'               => __( 'faq', 'quick-and-easy-faqs' ),
                'description'         => __( 'Frequently Asked Questions', 'quick-and-easy-faqs' ),
                'labels'              => apply_filters( 'qe_faq_labels', $labels),
                'supports'            => apply_filters( 'qe_faq_supports', array( 'title', 'editor' ) ),
                'hierarchical'        => false,
                'public'              => false,
                'exclude_from_search' => false,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'menu_position'       => 10,
                'menu_icon'           => 'dashicons-format-chat',
                'show_in_admin_bar'   => false,
                'show_in_nav_menus'   => false,
                'can_export'          => true,
                'has_archive'         => false,
                'exclude_from_search' => true,
                'publicly_queryable'  => false,
                'capability_type'     => 'post',
                'show_in_rest'        => true,
                'rest_base'           => apply_filters( 'inspiry_faq_rest_base', __( 'faqs', 'quick-and-easy-faqs' ) ),
            );

            register_post_type( 'faq', apply_filters( 'qe_register_faq_arguments', $args) );

        }

        /**
         * Register FAQ Group custom taxonomy
         */
        public function register_faqs_group_taxonomy() {

            $labels = array(
                'name'                       => _x( 'FAQ Groups', 'Taxonomy General Name', 'quick-and-easy-faqs' ),
                'singular_name'              => _x( 'FAQ Group', 'Taxonomy Singular Name', 'quick-and-easy-faqs' ),
                'menu_name'                  => __( 'Groups', 'quick-and-easy-faqs' ),
                'all_items'                  => __( 'All FAQ Groups', 'quick-and-easy-faqs' ),
                'parent_item'                => __( 'Parent FAQ Group', 'quick-and-easy-faqs' ),
                'parent_item_colon'          => __( 'Parent FAQ Group:', 'quick-and-easy-faqs' ),
                'new_item_name'              => __( 'New FAQ Group Name', 'quick-and-easy-faqs' ),
                'add_new_item'               => __( 'Add New FAQ Group', 'quick-and-easy-faqs' ),
                'edit_item'                  => __( 'Edit FAQ Group', 'quick-and-easy-faqs' ),
                'update_item'                => __( 'Update FAQ Group', 'quick-and-easy-faqs' ),
                'view_item'                  => __( 'View FAQ Group', 'quick-and-easy-faqs' ),
                'separate_items_with_commas' => __( 'Separate FAQ Groups with commas', 'quick-and-easy-faqs' ),
                'add_or_remove_items'        => __( 'Add or remove FAQ Groups', 'quick-and-easy-faqs' ),
                'choose_from_most_used'      => __( 'Choose from the most used', 'quick-and-easy-faqs' ),
                'popular_items'              => __( 'Popular FAQ Groups', 'quick-and-easy-faqs' ),
                'search_items'               => __( 'Search FAQ Groups', 'quick-and-easy-faqs' ),
                'not_found'                  => __( 'Not Found', 'quick-and-easy-faqs' ),
            );

            $args = array(
                'labels'            => apply_filters( 'qe_faq_group_labels', $labels ),
                'hierarchical'      => true,
                'public'            => false,
                'exclude_from_search' => false,
                'rewrite'           => false,
                'show_ui'           => true,
                'show_in_menu' 		=> 'edit.php?post_type=faq',
                'show_admin_column' => true,
                'show_in_nav_menus' => false,
                'show_tagcloud'     => false,
                'show_in_rest'        => true,
                'rest_base'           => apply_filters( 'inspiry_faq_group_rest_base', __( 'faq_groups', 'quick-and-easy-faqs' ) ),
            );

            register_taxonomy( 'faq-group', array( 'faq' ), apply_filters( 'qe_register_faq_group_arguments', $args ) );

        }

        /**
         * Add plugin settings page
         */
        public function add_faqs_options_page(){

            /**
             * Add FAQs settings page
             */
            add_submenu_page(
                'edit.php?post_type=faq',
                __( 'Quick & Easy Settings', 'quick-and-easy-faqs' ),
                __( 'Settings', 'quick-and-easy-faqs' ),
                'manage_options',
                'quick_and_easy_faqs',
                array( $this, 'display_faqs_options_page')
            );

        }

        /**
         * Display FAQs settings page
         */
        public function display_faqs_options_page() {

            ?>
            <!-- Create a header in the default WordPress 'wrap' container -->
            <div class="wrap">

                <h2><?php _e( 'Quick and Easy FAQs Settings', 'quick-and-easy-faqs' ); ?></h2>

                <!-- Make a call to the WordPress function for rendering errors when settings are saved. -->
                <?php settings_errors(); ?>

                <!-- Create the form that will be used to render our options -->
                <form method="post" action="options.php">
                    <?php settings_fields( 'quick_and_easy_faqs_options' ); ?>
                    <?php do_settings_sections( 'quick_and_easy_faqs_options' ); ?>
                    <?php submit_button(); ?>
                </form>

            </div><!-- /.wrap -->
            <?php
        }

        /**
         * Initialize FAQs settings page
         */
        public function initialize_faqs_options(){

            // create plugin options if not exist
            if( false == $this->options ) {
                add_option( 'quick_and_easy_faqs_options' );
            }

            /**
             * Section
             */
            add_settings_section(
                'faqs_toggles_style',                                                       // ID used to identify this section and with which to register options
                __( 'FAQs Toggle Styles', 'quick-and-easy-faqs'),                           // Title to be displayed on the administration page
                array( $this, 'faqs_toggles_style_description'),                            // Callback used to render the description of the section
                'quick_and_easy_faqs_options'                                               // Page on which to add this section of options
            );

            add_settings_section(
                'faqs_common_style',
                __( 'FAQs Common Styles', 'quick-and-easy-faqs'),
                array( $this, 'faqs_common_style_description'),
                'quick_and_easy_faqs_options'
            );

            /**
             * Fields
             */
            add_settings_field(
                'faqs_toggle_colors',
                __( 'FAQs toggle colors', 'quick-and-easy-faqs' ),
                array( $this, 'faqs_select_option_field' ),
                'quick_and_easy_faqs_options',
                'faqs_toggles_style',
                array(
                    'id' => 'faqs_toggle_colors',
                    'default' => 'default',
                    'description' => __( 'Choose custom colors to apply colors provided in options below.', 'quick-and-easy-faqs' ),
                    'options' => array(
                        'default' => __( 'Default Colors', 'quick-and-easy-faqs' ),
                        'custom' => __( 'Custom Colors', 'quick-and-easy-faqs' ),
                    )
                )
            );
            add_settings_field(
                'toggle_question_color',
                __( 'Question text color', 'quick-and-easy-faqs' ),
                array( $this, 'faqs_color_option_field' ),
                'quick_and_easy_faqs_options',
                'faqs_toggles_style',
                array(
                    'id' => 'toggle_question_color',
                    'default' => '#333333',
                )
            );
            add_settings_field(
                'toggle_question_hover_color',
                __( 'Question text color on mouse over', 'quick-and-easy-faqs' ),
                array( $this, 'faqs_color_option_field' ),
                'quick_and_easy_faqs_options',
                'faqs_toggles_style',
                array(
                    'id' => 'toggle_question_hover_color',
                    'default' => '#333333',
                )
            );
            add_settings_field(
                'toggle_question_bg_color',
                __( 'Question background color', 'quick-and-easy-faqs' ),
                array( $this, 'faqs_color_option_field' ),
                'quick_and_easy_faqs_options',
                'faqs_toggles_style',
                array(
                    'id' => 'toggle_question_bg_color',
                    'default' => '#fafafa',
                )
            );
            add_settings_field(
                'toggle_question_hover_bg_color',
                __( 'Question background color on mouse over', 'quick-and-easy-faqs' ),
                array( $this, 'faqs_color_option_field' ),
                'quick_and_easy_faqs_options',
                'faqs_toggles_style',
                array(
                    'id' => 'toggle_question_hover_bg_color',
                    'default' => '#eaeaea',
                )
            );
            add_settings_field(
                'toggle_answer_color',
                __( 'Answer text color', 'quick-and-easy-faqs' ),
                array( $this, 'faqs_color_option_field' ),
                'quick_and_easy_faqs_options',
                'faqs_toggles_style',
                array(
                    'id' => 'toggle_answer_color',
                    'default' => '#333333',
                )
            );
            add_settings_field(
                'toggle_answer_bg_color',
                __( 'Answer background color', 'quick-and-easy-faqs' ),
                array( $this, 'faqs_color_option_field' ),
                'quick_and_easy_faqs_options',
                'faqs_toggles_style',
                array(
                    'id' => 'toggle_answer_bg_color',
                    'default' => '#ffffff',
                )
            );
            add_settings_field(
                'toggle_border_color',                                                      // ID used to identify the field throughout the theme
                __( 'Toggle Border color', 'quick-and-easy-faqs' ),                         // The label to the left of the option interface element
                array( $this, 'faqs_color_option_field' ),                                  // The name of the function responsible for rendering the option interface
                'quick_and_easy_faqs_options',                                              // The page on which this option will be displayed
                'faqs_toggles_style',                                                       // The name of the section to which this field belongs
                array(                                                                      // The array of arguments to pass to the callback. In this case, just a description.
                    'id' => 'toggle_border_color',
                    'default' => '#dddddd',
                )
            );
            add_settings_field(
                'faqs_custom_css',
                __( 'Custom CSS', 'quick-and-easy-faqs' ),
                array( $this, 'faqs_textarea_option_field' ),
                'quick_and_easy_faqs_options',
                'faqs_common_style',
                array(
                    'id' => 'faqs_custom_css',
                )
            );

            /**
             * Register Settings
             */
            register_setting( 'quick_and_easy_faqs_options', 'quick_and_easy_faqs_options' );
        }

        /**
         * FAQs toggle styles section description
         */
        public function faqs_toggles_style_description() {
            echo '<p>'. __( 'These settings only applies to FAQs with toggle style. As FAQs with list style use colors inherited from currently active theme.', 'quick-and-easy-faqs' ) . '</p>';
        }

        /**
         * FAQs common styles section description
         */
        public function faqs_common_style_description() {
            //echo '<p>'.__( '', 'quick-and-easy-faqs' ).'</p>';
            echo '<p></p>';
        }

        /**
         * Re-usable color options field for FAQs settings
         */
        public function faqs_color_option_field( $args ) {
            $field_id = $args['id'];
            if( $field_id ) {
                $val = ( isset( $this->options[ $field_id ] ) ) ? $this->options[ $field_id ] : $args['default'];
                $default_color = $args['default'];
                echo '<input type="text" name="quick_and_easy_faqs_options['.$field_id.']" value="' . $val . '" class="color-picker" data-default-color="' . $default_color . '">';
            } else {
                _e( 'Field id is missing!', 'quick-and-easy-faqs' );
            }
        }

        /**
         * Re-usable textarea options field for FAQs settings
         */
        public function faqs_textarea_option_field( $args ) {
            $field_id = $args['id'];
            if( $field_id ) {
                $val = ( isset( $this->options[ $field_id ] ) ) ? $this->options[ $field_id ] : '';
                echo '<textarea cols="60" rows="8" name="quick_and_easy_faqs_options[' . $field_id . ']" class="faqs-custom-css">' . $val . '</textarea>';
            } else {
                _e( 'Field id is missing!', 'quick-and-easy-faqs' );
            }
        }

        /**
         * Re-usable select options field for FAQs settings
         */
        public function faqs_select_option_field( $args ) {
            $field_id = $args['id'];
            if( $field_id ) {
                $existing_value = ( isset( $this->options[ $field_id ] ) ) ? $this->options[ $field_id ] : '';
                ?>
                <select name="<?php echo 'quick_and_easy_faqs_options[' . $field_id . ']'; ?>" class="faqs-select">
                    <?php foreach( $args['options'] as $key => $value ) { ?>
                        <option value="<?php echo $key; ?>" <?php selected( $existing_value, $key ); ?>><?php echo $value; ?></option>
                    <?php } ?>
                </select>
                <br/>
                <label><?php echo $args['description']; ?></label>
                <?php
            } else {
                _e( 'Field id is missing!', 'quick-and-easy-faqs' );
            }
        }

        /**
         * Add plugin action links
         */
        public function faqs_action_links( $links ) {
            $links[] = '<a href="'. get_admin_url( null, 'plugins.php?page=quick_and_easy_faqs' ) .'">' . __( 'Settings', 'quick-and-easy-faqs' ) . '</a>';
            return $links;
        } 
        
        /**
         * To log any thing for debugging purposes
         */
        public static function log( $message ) {
            if( WP_DEBUG === true ){
                if( is_array( $message ) || is_object( $message ) ){
                    error_log( print_r( $message, true ) );
                } else {
                    error_log( $message );
                }
            }
        }

    }
}

/**
 * Returns the main instance of Quick_And_Easy_FAQs_Admin to prevent the need to use globals.
 */
function init_qe_faqs_admin() {
	return Quick_And_Easy_FAQs_Admin::instance();
}

/**
 * Get it running
 */
init_qe_faqs_admin();