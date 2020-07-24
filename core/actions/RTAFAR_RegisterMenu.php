<?php namespace RealTimeAutoFindReplace\actions;

/**
 * Class: Register custom menu
 * 
 * @package Action
 * @since 1.0.0
 * @author M.Tuhin <info@codesolz.net>
 */

if (!defined('CS_RTAFAR_VERSION')) {
    die();
}

use RealTimeAutoFindReplace\admin\functions\Masking;
use RealTimeAutoFindReplace\admin\options\Scripts_Settings;
use RealTimeAutoFindReplace\admin\builders\AdminPageBuilder;

if( ! \class_exists( 'RTAFAR_RegisterMenu' ) ){

class RTAFAR_RegisterMenu {

    /**
     * Hold pages
     *
     * @var type 
     */
    private $pages;

    /**
     *
     * @var type 
     */
    private $WcFunc;

    /**
     *
     * @var type 
     */
    public $current_screen;

    /**
     * Hold Menus
     *
     * @var [type]
     */
    public $rtafr_menus;

    public function __construct()
    {
        //call wordpress admin menu hook
        add_action('admin_menu', array($this, 'rtafar_register_menu'));
    }

    /**
     * Init current screen
     * 
     * @return type
     */
    public function init_current_screen()
    {
        $this->current_screen = get_current_screen();
        return $this->current_screen;
    }

    /**
     * Create plugins menu
     */
    public function rtafar_register_menu() {
        global $rtafr_menu;
        add_menu_page(
            __('Real time auto find and replace', 'real-time-auto-find-and-replace'),
            "Find & Replace",
            'manage_options',
            CS_RTAFAR_PLUGIN_IDENTIFIER,
            'cs-woo-altcoin-gateway',
            CS_RTAFAR_PLUGIN_ASSET_URI . 'img/icon-24x24.png',
            57
        );

        $this->rtafr_menus['add_masking_rule'] = add_submenu_page(
            CS_RTAFAR_PLUGIN_IDENTIFIER,
            __('Add Replace Rule', 'real-time-auto-find-and-replace'),
            "Add Masking Rule",
            'manage_options',
            'cs-add-replacement-rule',
            array($this, 'rtafr_page_add_rule')
        );

        $this->rtafr_menus['all_masking_rules'] = add_submenu_page(
            CS_RTAFAR_PLUGIN_IDENTIFIER,
            __('All Masking Rules', 'real-time-auto-find-and-replace'),
            "All Masking Rules",
            'manage_options',
            'cs-all-masking-rules',
            array($this, 'rtafr_page_all_masking_rules')
        );

        $this->rtafr_menus['replace_in_db'] = add_submenu_page(
            CS_RTAFAR_PLUGIN_IDENTIFIER,
            __('Replace in DB', 'real-time-auto-find-and-replace'),
            "Replace in Database",
            'manage_options',
            'cs-replace-in-database',
            array($this, 'rtafr_page_replace_in_db')
        );

        //load script
        add_action( "load-{$this->rtafr_menus['add_masking_rule']}", array( $this, 'rtafr_register_admin_settings_scripts') );
        add_action( "load-{$this->rtafr_menus['all_masking_rules']}", array( $this, 'rtafr_register_admin_settings_scripts') );
        add_action( "load-{$this->rtafr_menus['replace_in_db']}", array( $this, 'rtafr_register_admin_settings_scripts') );

        remove_submenu_page(CS_RTAFAR_PLUGIN_IDENTIFIER, CS_RTAFAR_PLUGIN_IDENTIFIER);

        //init pages
        $this->pages = new AdminPageBuilder();
        $rtafr_menu = $this->rtafr_menus;
    }

    /**
     * Add Replacement Rule
     *
     * @return void
     */
    public function rtafr_page_add_rule(){

        $title = 'Add';
        $option = array();
        if( isset($_GET['action']) && !empty($_GET['rule_id']) ){
            $option = Masking::get_rules( 'all', $_GET['rule_id'] );
            $option = (array)$option[ 0 ];
            $title = 'Update';
        }

        // pre_print( $option );

        $page_info = array(
            'title' => sprintf( __( '%s Masking Rule', 'real-time-auto-find-and-replace'), $title ),
            'sub_title' => __('These will not replace in database. Following find replace rules will take place before website render to browser.', 'real-time-auto-find-and-replace'),
        );

        if ( current_user_can( 'manage_options' ) ) {
            $AddNewRule = $this->pages->AddNewRule();
            if (is_object($AddNewRule)) {
                echo $AddNewRule->generate_page(array_merge_recursive( $page_info, array('gateway_settings' => array() )), $option );
            } else {
                echo $AddNewRule;
            }
            
        }else{
            $AccessDenied = $this->pages->AccessDenied();
            if (is_object($AccessDenied)) {
                echo $AccessDenied->generate_access_denided(array_merge_recursive( $page_info , array('gateway_settings' => array() )));
            } else {
                echo $AccessDenied;
            }
        }
    }

    public function rtafr_page_all_masking_rules(){
        $page_info = array(
            'title' => __('All Masking Rule', 'real-time-auto-find-and-replace'),
            'sub_title' => __('Following find replace rules will take place before website render to browser.', 'real-time-auto-find-and-replace'),
        );

        if ( current_user_can( 'manage_options' ) ) {
            $AllMaskingRules = $this->pages->AllMaskingRules();
            if (is_object($AllMaskingRules)) {
                echo $AllMaskingRules->generate_page(array_merge_recursive( $page_info, array('gateway_settings' => array() )));
            } else {
                echo $AllMaskingRules;
            }
            
        }else{
            $AccessDenied = $this->pages->AccessDenied();
            if (is_object($AccessDenied)) {
                echo $AccessDenied->generate_access_denided(array_merge_recursive( $page_info , array('gateway_settings' => array() )));
            } else {
                echo $AccessDenied;
            }
        }
    }

    /**
     * Generate default settings page
     * 
     * @return type
     */
    public function rtafr_page_replace_in_db()
    {
        
        $page_info = array(
            'title' => __('Replace In Database', 'real-time-auto-find-and-replace'),
            'sub_title' => __('Instantly & permanently replace string from database table\'s. It will take effect in WordPress\'s table\'s only.', 'real-time-auto-find-and-replace'),
        );

        if ( current_user_can( 'manage_options' ) ) {
            $Default_Settings = $this->pages->ReplaceInDB();
            if (is_object($Default_Settings)) {
                echo $Default_Settings->generate_default_settings(array_merge_recursive( $page_info, array('gateway_settings' => array() )));
            } else {
                echo $Default_Settings;
            }
            
        }else{
            $AccessDenied = $this->pages->AccessDenied();
            if (is_object($AccessDenied)) {
                echo $AccessDenied->generate_access_denided(array_merge_recursive( $page_info , array('gateway_settings' => array() )));
            } else {
                echo $AccessDenied;
            }
        }

    }

    /**
     * load funnel builder scripts
     */
    public function rtafr_register_admin_settings_scripts()
    {

        //register scripts
        add_action('admin_enqueue_scripts', array($this, 'rtafar_load_settings_scripts'));

        //init current screen
        $this->init_current_screen();

        //load all admin footer script
        add_action('admin_footer', array($this, 'rtafar_load_admin_footer_script'));
    }

    /**
     * Load admin scripts
     */
    public function rtafar_load_settings_scripts($page_id)
    {
        return Scripts_Settings::load_admin_settings_scripts($page_id, $this->rtafr_menus );

    }

    /**
     * load custom scripts on admin footer
     */
    public function rtafar_load_admin_footer_script() {
        return Scripts_Settings::load_admin_footer_script($this->current_screen->id, $this->rtafr_menus );
    }


}

}