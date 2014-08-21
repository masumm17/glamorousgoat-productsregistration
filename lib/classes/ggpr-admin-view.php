<?php
class GGPR_Admin_View {
    public $current_page;
    public $action;
    public $admin_page_url;
    public $admin_settings_url;
    
    public $options;
    /**
     * Constructor of the class
     * Do some inital tasks when instantiated
     */
    public function __construct($options=false) {
        if(is_array($options))
            $this->options = $options;
        
        add_action('admin_init', array($this, 'init'));
        // Add menu page level for the plugin
        add_action( 'admin_menu', array($this, 'admin_menu') );
        //Add scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Initiate Admin view
     */
    public function init(){
        if( isset($_REQUEST['page'])){
                if( GGPR_ADMIN_MENU_SLUG == $_REQUEST['page'] ){
                        $this->current_page = 'main';
                }elseif(GGPR_ADMIN_SEARCH_SLUG == $_REQUEST['page']){
                        $this->current_page = 'search';
                }
        }
    }
    /**
    * Add a top level admin menu
    */
    public function admin_menu(){
        add_menu_page( $this->get_option('admin_menu_title'), $this->get_option('admin_menu_title'), 'manage_options', GGPR_ADMIN_MENU_SLUG, array($this, 'admin_menu_page'), 'dashicons-products', '27.75' ); 
        add_submenu_page( GGPR_ADMIN_MENU_SLUG, $this->get_option('admin_search_title'), $this->get_option('admin_search_title'), 'manage_options', GGPR_ADMIN_SEARCH_SLUG, array($this, 'admin_menu_page') ); 
    }
    
    /**
    * Main Page
    */
    public function admin_menu_page(){
        $this->wrap_open();
        $this->title_section();
        $this->message_section();
        if($this->current_page == 'main'){
                //$this->main_page();
        }
        $this->wrap_close();
    }
    
    /**
     * Wrap open html
     */
    public function wrap_open(){
        echo '<div class="wrap">';
    }
    /**
     * Wrap colose html
     */
    public function wrap_close(){
        echo '</div>';
    }
    /**
     * Out title of current page
     */
    public function title_section(){
        if($this->current_page == 'main'){
            echo '<h2>'. $this->get_option('admin_menu_title') .'</h2>';
        }elseif($this->current_page == 'search'){
             echo '<h2>'. $this->get_option('admin_search_title') .'</h2>';
        }
    }
    
    /**
     * Output correct message
     */
    public function message_section(){
        $message_code = !empty($_GET['message'])?$_GET['message']:0;
        $message = '';
        switch($message_code){
            default : $message = '';
        }
        if($message){
            echo '<div id="message" class="updated below-h2">';
                    echo '<p>'. $message .'</p>'; 
            echo '</div>';
        }
    }
    
    /**
     * Get option
     * @param string $key
     * @return mixed
     */
    public function get_option($key){
        if(isset($this->options[$key]))
            return $this->options[$key];
        return false;
    }
    /**
     * Enquee admin scripts and styles
     */
    public function enqueue_scripts(){
        wp_enqueue_style('ggpr-back-end', GGPR_URL.'css/back-end.css');
        wp_enqueue_script('ggpr-back-end', GGPR_URL.'js/back-end.js', array('jquery'), GGPR_VER, true);
        wp_localize_script('ggpr-back-end', 'GGPROptions', $this->options);
}
}