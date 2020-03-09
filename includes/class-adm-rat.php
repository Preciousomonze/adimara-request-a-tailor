<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly.

final class ADM_RAT{

    /**
     * The single instance of the class.
     *
     * @var ADM_RAT
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main instance
     * @return class object
     */
    public static function instance() {
        if( is_null(self::$_instance) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Class constructor
     */
    public function __construct() {
        $this->define_constants();//define the constants
        $this->includes();//include relevant files
		
    }

    /**
     * Constants define
     */
    private function define_constants() {
        $this->define('ADM_RAT_ABSPATH', dirname(ADM_RAT_PLUGIN_FILE) . '/');
        $this->define('ADM_RAT_PLUGIN_FILE', plugin_basename(ADM_RAT_PLUGIN_FILE));
        $this->define('ADM_RAT_ASSETS_PATH', plugins_url('assets/',__FILE__));
        if( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ){
            $this->define('ADM_RAT_MIN_SUFFIX', '');
		}
        else{
            $this->define('ADM_RAT_MIN_SUFFIX', '.min');
		}
    }

    /**
     * 
     * @param string $name
     * @param mixed $value
     */
    private function define($name, $value) {
        if ( !defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * Check request
     * @param string $type
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined('DOING_AJAX');
            case 'cron' :
                return defined('DOING_CRON');
            case 'frontend' :
                return (!is_admin() || defined('DOING_AJAX') ) && !defined('DOING_CRON');
        }
    }

    /**
     * load plugin files
     */
    public function includes() {
        if ( $this->is_request( 'frontend' ) ) {
            include_once( ADM_RAT_ABSPATH . 'public/class-adm-rat-cart.php' );
            include_once( ADM_RAT_ABSPATH . 'public/class-adm-rat-checkout.php' );
			include_once( ADM_RAT_ABSPATH . 'includes/api/class-adm-rat-rest.php' );
			//enqueue js
			 add_action( 'wp_enqueue_scripts', array($this,'enqueue_js' ) );
        }
		//include for all
		include_once( ADM_RAT_ABSPATH . 'includes/class-adm-rat-orders.php' );
		
    }
    
    /**
     * enqueues all necessary scripts
     */
    public function enqueue_js(){
        //wp_enqueue_script('NameMySccript','path/to/MyScript','dependencies_MyScript', 'VersionMyScript', 'InfooterTrueorFalse');
        $script_dep = array('adm_pk_js-script');
        wp_register_script('adm_rat_js-script',adm_rat()->plugin_url().'/assets/js/frontend'.ADM_RAT_MIN_SUFFIX.'.js',$script_dep,ADM_RAT_PLUGIN_VERSION,true);

		wp_enqueue_script('adm_rat_js-script');
    }
	
    /**
     * Plugin url
     * @return string path
     */
    public function plugin_url() {
        return untrailingslashit(plugins_url('/', ADM_RAT_PLUGIN_FILE));
    }

    /**
     * Display admin notice
     */
    public function admin_notices() {
        echo '<div class="error"><p>';
        _e('<strong>Adimara Request a tailor</strong> plugin requires <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> plugin to be active!', ADM_RAT_TEXT_DOMAIN);
        echo '</p></div>';
    }

}
