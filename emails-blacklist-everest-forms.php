<?php
/**
 * Plugin Name: Emails Blacklist for Everest Forms
 * Plugin URI: https://scintelligencia.com/
 * Description: Admin will be able to blacklist by email addresses, domains, and other fields , Users with blacklisted forms won't be submit.
 * Version: 1.0
 * Author: Syed Muhammad Usman
 * Author URI: https://www.linkedin.com/in/syed-muhammad-usman/
 * License: GPL v2 or later
 * Stable tag: 1.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: form, forms, everest, everest form, everest forms
 * @author Syed Muhammad Usman
 */

if ( !class_exists( 'EmailsBlacklistEverestForms' ) ):
    class EmailsBlacklistEverestForms
    {
        /**
         * @var $_instance
         * @version 1.0
         * @since 1.0
         */
        private static $_instance;

        /**
         * EmailsBlacklistEverestForms constructor.
         * @version 1.0
         * @since 1.0
         */
        public function __construct()
        {
            if ( $this->check_requirements() )
                $this->run();
        }

        /**
         * Loads Class Instance
         * @return mixed
         * @version 1.0
         * @since 1.0
         */
        public static function get_instance()
        {
            if ( self::$_instance == null )
                self::$_instance = new self();

            return self::$_instance;
        }

        /**
         * Runs Plugins
         * @since 1.0
         * @version 1.0
         */
        public function run()
        {
            $this->constants();
            $this->includes();
            $this->add_actions();
            $this->register_hooks();
        }

        /**
         * Check Requirements
         * @version 1.0
         * @since 1.0
         */
        public function check_requirements()
        {
            if( !function_exists('is_plugin_active') )
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                if ( !is_plugin_active( 'everest-forms/everest-forms.php' ) )
                    add_action( 'admin_notices', array( $this, 'admin_notices' ) );
            else
                return true;
        }

        /**
         * Add Admin Notices
         * @version 1.0
         * @since 1.0
         */
        public function admin_notices( $notice = '' )
        {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e( 'In order to use Everest Forms Emails Blacklist make sure you\'ve Installed and Active <a href="https://wordpress.org/plugins/everest-forms/" target="_blank" />Everest Forms</a>', 'sample-text-domain' ); ?></p>
            </div>
            <?php
        }

        /**
         * @param $name
         * @param $value
         * @since 1.0
         * @version 1.0
         */
        public function define( $name, $value )
        {
            if ( !defined( $name ) )
                define( $name, $value );
        }

        /**
         * Defines Constants
         * @since 1.0
         * @version 1.0
         */
        public function constants()
        {
            $this->define( 'EFEB_VERSION', '1.0' );

            $this->define( 'EFEB_PREFIX', 'efeb_' );

            $this->define( 'EFEB_TEXT_DOMAIN', 'efeb' );

            $this->define( 'EFEB_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

            $this->define( 'EFEB_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
        }

        /**
         * Require File
         * @since 1.0
         * @version 1.0
         */
        public function file( $required_file ) {
            if ( file_exists( $required_file ) )
                require_once $required_file;
            else
                echo 'File Not Found';
        }

        /**
         * Include files
         * @since 1.0
         * @version 1.0
         */
        public function includes()
        {
            $this->file(EFEB_PLUGIN_DIR_PATH. 'includes/functions.php');
        }

        /**
         * Add Actions
         * @since 1.0
         * @version 1.0
         */
        public function add_actions()
        {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );

            add_action( 'admin_menu', array( $this, 'admin_menu' ) );

            add_action('wp_ajax_select_form', array( $this, 'select_form' ) );

            add_action('wp_ajax_nopriv_select_form', array( $this, 'select_form' ) );

            add_action('wp_ajax_blacklist_form', array( $this, 'blacklist_form' ) );

            add_action('wp_ajax_nopriv_blacklist_form', array( $this, 'blacklist_form' ) );

            //delete
            add_action('wp_ajax_delete', array( $this, 'delete' ) );

            add_action('wp_ajax_nopriv_delete', array( $this, 'delete' ) );

            //Validating form before submit
            add_filter( 'everest_forms_entry_save',  array( $this, 'form_entry_save' ), 10, 4 );
        }

        /**
         * Fires when before Everest Forms entry save
         * @param $bool
         * @param $fields
         * @param $entry
         * @param $form_data
         * @return bool
         * @version 1.0
         * @since 1.0
         */
        public function form_entry_save( $bool, $fields, $entry, $form_data )
        {
            $bool = true;

            $form_id = $form_data['id'];

            $blacklisted_forms = efeb_get_blacklist();

            $form = '';

            //If not form's field no blacklisted, return true
            if ( array_key_exists( $form_id, $blacklisted_forms ) )
                $form = $blacklisted_forms[$form_id];
            else
                return $bool;

            $values = $form['values'];

            $field = $form['field'];

            $by = $blacklisted_forms[$form_id]['by'];

            $values = explode( ',', $values );

            $form_field = '';

           if ( array_key_exists( $field, $entry['form_fields'] ) )
               $form_field = $entry['form_fields'][$field];

           if( $by == 'domain' )
           {
               //Extracting domain from email address
               $form_field = substr( $form_field, strpos( $form_field, '@' ) + 1 );

           }

           //If Email in list
           if ( in_array( $form_field, $values ) )
               $bool = false;




           return $bool;
        }

        /**
         * Adds Admin menu
         * @since 1.0
         * @version 1.0
         */
        public function admin_menu()
        {
            add_submenu_page(
                    'everest-forms',
                'Everest Forms Emails Blacklist',
                'Emails Blacklist',
                'manage_options',
                'everest-forms-emails-blacklist',
                array( $this, 'save_blacklist_page' )
            );
        }

        /**
         * Renders Page
         * @since 1.0
         * @version 1.0
         */
        public function save_blacklist_page()
        {
            $forms = evf_get_all_forms();

            $blacklisted_forms = efeb_get_blacklist();
            ?>
            <div class="wrap">
                <h1>Save Blacklist</h1>

                <div class="blacklisting-form">
                    <div>
                        <label for="forms">Select Form</label>
                        <select name="forms" id="forms">
                            <option value="">--Select Form--</option>
                            <?php
                            foreach ( $forms as $id => $form )
                            {
                                if ( !array_key_exists( $id, $blacklisted_forms ) )
                                    echo "<option value='$id'>$form</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <br>
                    <div>
                        <label for="field">Select Field</label>
                        <select name="field" id="field">
                            <option value="">--Select Field--</option>
                        </select>
                    </div>
                    <br>
                    <div>
                        <label for="by-email"><input type="radio" name="by" id="by-email" value="email"> By Email Address</label>
                        <br>
                        <br>
                        <label for="by-domain"><input type="radio" name="by" id="by-domain" value="domain"> By Domain</label>
                        <br>
                        <br>
                        <label for="by-other"><input type="radio" name="by" id="by-other" value="other"> By Other</label>
                        <p>Use can either use Email address or domain.</p>
                    </div>
                    <div>
                        <div>
                            <label for="value">Enter Value</label>
                        </div>
                        <textarea name="value" id="value"></textarea>
                        <p>
                            Use comma separated values to use more than one: smusman98@gmail.com, sciintelligencia@gmail.com OR gmail.com, yahoo.com
                        </p>
                    </div>
                    <br>
                    <div>
                        <input type="button" value="Save" id="save" class="button button-primary" name="save">
                    </div>
                </div>

                <div>
                    <h1>Blacklisted Forms</h1>
                    <table width="100%" class="wp-list-table widefat fixed striped table-view-list pages">
                        <thead>
                            <tr>
                                <th>
                                    Form
                                </th>
                                <th>
                                    Field
                                </th>
                                <th>
                                    Value
                                </th>
                                <th>
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody id="blacklisted-forms">
                        <?php
                        if ( $blacklisted_forms ):
                        foreach ( $blacklisted_forms as $form )
                        {

                            $form_id = $form['form_id'];

                            $form_name = $forms[$form_id];

                            $form_field = evf_get_form_fields( $form_id )[$form['field']]['label'];

                            $values = $form['values'];

                            echo "
                             <tr>
                                <th>{$form_name}</th>
                                <th>{$form_field}</th>
                                <th>{$values}</th>
                                <th><input type='button' class='delete button button-secondary' data-id='{$form_id}' value='Delete'></th>
                            </tr>
                            ";
                        }
                        endif;
                        ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>
                                    Form
                                </th>
                                <th>
                                    Field
                                </th>
                                <th>
                                    Value
                                </th>
                                <th>
                                    Action
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php
        }

        /**
         * AJAX On form change populate fileds
         * @since 1.0
         * @version 1.0
         */
        public function select_form()
        {
            if ( isset( $_POST['form_id'] ) )
            {
                $form_id = $_POST['form_id'];

                $form_fields = evf_get_form_fields( $form_id );

                $fields = array();

                $index = 0;

                foreach ( $form_fields as $id => $label )
                {
                    $fields[$index][] = $id;
                    $fields[$index][] = $label['label'];
                    $index++;
                }

                echo json_encode( $fields );

                die;
            }

        }

        /**
         * AJAX Black lists a form
         * @since 1.0
         * @version 1.0
         */
        public function blacklist_form()
        {
            if( isset( $_POST['form_id'] ) && isset( $_POST['field'] ) && isset( $_POST['value'] ))
            {
                $form = array();

                $form['form_id'] = $_POST['form_id'];
                $form['field'] = $_POST['field'];
                $form['values'] = $_POST['value'];
                $form['by'] = $_POST['by'];

                if ( empty( $form['form_id'] ) || empty( $form['field'] ) || empty( $form['values'] ) || empty( $form['by'] ) )
                {
                    echo 'Empty';
                    die;
                }

                efeb_update_blacklist( $form );

                echo 'Saved';
                die;
            }
        }

        /**
         * AJAX Deletes a form
         * @since 1.0
         * @version 1.0
         */
        public function delete()
        {
            if( isset( $_POST['form_id'] ) )
            {
                efeb_delete_blacklisted_form( $_POST['form_id'] );
                echo 'deleted';
                die;
            }
        }

        /**
         * Admin Enqueue Scripts
         * @since 1.0
         * @version 1.0
         */
        public function admin_enqueue()
        {
            wp_enqueue_style(EFEB_TEXT_DOMAIN . '-css', EFEB_PLUGIN_DIR_URL . 'assets/css/style.css', '', EFEB_VERSION);

            wp_enqueue_script(EFEB_TEXT_DOMAIN . '-js', EFEB_PLUGIN_DIR_URL . 'assets/js/custom.js', array('jquery'), EFEB_VERSION);
        }

        /**
         * Register Activation, Deactivation and Uninstall Hooks
         * @since 1.0
         * @version 1.0
         */
        public function register_hooks()
        {
            register_activation_hook( __FILE__, array( $this, 'activate' ) );

            register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        }

        /**
         * Runs on Plugin's activation
         * @since 1.0
         * @version 1.0
         */
        public function activate()
        {

        }

        /**
         * Runs on Plugin's Deactivation
         * @since 1.0
         * @version 1.0
         */
        public function deactivate()
        {

        }
    }
endif;

EmailsBlacklistEverestForms::get_instance();
