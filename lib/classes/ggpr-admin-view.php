<?php
class GGPR_Admin_View {
    public $current_page;
    public $action;
    public $admin_page_url;
    public $admin_settings_url;
    
    public $options;
    public $search_data;
    public $search_results;
    public $show_search_result;
    /**
     * Constructor of the class
     * Do some inital tasks when instantiated
     */
    public function __construct($options=false) {
        if(is_array($options))
            $this->options = $options;
        
        // Initiate variables;
        $this->variables();
        
        add_action('admin_init', array($this, 'init'));
        // Add menu page level for the plugin
        add_action( 'admin_menu', array($this, 'admin_menu') );
        //Add scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    /**
     * Initiate valriables
     */
    public function variables(){
        $this->admin_page_url = add_query_arg(array('page'=>GGPR_ADMIN_MENU_SLUG),get_admin_url(false, '/admin.php'));
        $this->current_page = 'main';
        $this->action ='';
        $this->search_data = array(
            'RegistrationCode'  => '',
            'Name'          => '',
            'IsUsed'        => '',
            'NotUsed'       => '',
            'Address'       =>'',
            'PostalCode'    =>'',
            'City'          => '',
            'Country'       => '',
            'PhoneNo'       => '',
            'EmailAddress'  => '',
            'InvoiceNo'     => '',
            'Supplier'      => '',
            'DateOfPurchase'        => '',
            'DateOfRegistration'    =>''
        );
        $this->search_results = array();
        $this->show_search_result = false;
    }
    
    /**
     * Initiate Admin view
     */
    public function init(){
        if( isset($_REQUEST['page'])){
            if( GGPR_ADMIN_MENU_SLUG == $_REQUEST['page'] ){
                    $this->current_page = 'main';
                    $query_parts['page'] = GGPR_ADMIN_MENU_SLUG;
            }elseif(GGPR_ADMIN_SEARCH_SLUG == $_REQUEST['page']){
                    $this->current_page = 'search';
                    $query_parts['page'] = GGPR_ADMIN_SEARCH_SLUG;
            }
        }
        
        $this->admin_page_url = add_query_arg($query_parts,get_admin_url(false, '/admin.php'));
        $this->perform_action();
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
        if('main' == $this->current_page){
                //$this->main_page();
        }elseif('search' == $this->current_page){
            $this->search_page();
            if($this->show_search_result){
                $this->search_result_page();
            }
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
     * Search page
     */
    public function search_page(){
    ?>
    <div class="ggpr-search-form-wrap">
        <form class="ggpr-search-form" action="" method="post">
            <?php wp_nonce_field('ggpr-search'); ?>
            <input type="hidden" name="ggpr_action" value="search"/>
            <div class="ggpr-search-fields-wrap ggpr-group">
                <div class="ggpr-col3">
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_code"><?php echo $this->get_option('regi_code'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_code" name="ggpr_code" value="<?php echo esc_attr($this->search_data['RegistrationCode']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_name"><?php echo $this->get_option('name'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_name" name="ggpr_name" value="<?php echo esc_attr($this->search_data['Name']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_address"><?php echo $this->get_option('address'); ?></label>
                        <div class="ggpr-field">
                            <textarea id="ggpr_address" name="ggpr_address"><?php echo esc_html($this->search_data['Address']); ?></textarea>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_post_code"><?php echo $this->get_option('postal_code'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_post_code" name="ggpr_post_code" value="<?php echo esc_attr($this->search_data['PostalCode']); ?>"/>
                        </div>
                    </div>

                </div>
                <div class="ggpr-col3">
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_city"><?php echo $this->get_option('city'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_city" name="ggpr_city" value="<?php echo esc_attr($this->search_data['City']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_country"><?php echo $this->get_option('country'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_country" name="ggpr_country" value="<?php echo esc_attr($this->search_data['Country']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_phone"><?php echo $this->get_option('phone_no'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_phone" name="ggpr_phone" value="<?php echo esc_attr($this->search_data['PhoneNo']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_email"><?php echo $this->get_option('emai'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_email" name="ggpr_email" value="<?php echo esc_attr($this->search_data['EmailAddress']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_invoice_no"><?php echo $this->get_option('invoice_no'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_invoice_no" name="ggpr_invoice_no" value="<?php echo esc_attr($this->search_data['InvoiceNo']); ?>"/>
                        </div>
                    </div>
                </div>
                <div class="ggpr-col3">
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_supplier"><?php echo $this->get_option('supplier'); ?></label>
                        <div class="ggpr-field">
                            <textarea id="ggpr_supplier" name="ggpr_supplier"><?php echo esc_html($this->search_data['Supplier']); ?></textarea>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_dop"><?php echo $this->get_option('purchase_date'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_dop" name="ggpr_dop" value="<?php echo esc_attr($this->search_data['DateOfPurchase']); ?>" />
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_dor"><?php echo $this->get_option('regi_date'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_dor" name="ggpr_dor" value="<?php echo esc_attr($this->search_data['DateOfRegistration']); ?>" />
                        </div>
                    </div>
                    <div class="ggpr-submit-wrap">
                        <input class="ggpr-submit" type="submit" value="<?php echo $this->get_option('admin_search_title'); ?>"/>
                    </div> 
                </div>
            </div>
        </form>
    </div>
    <?php
    }
    
    public function search_result_page(){
    ?> 
<div class="ggpr-search-result">
    <h3><?php echo $this->get_option('search_results_title'); ?></h3>
    <form method="post" action="" id="ggpr-list-form">
    <?php
    $list_table = new GGRP_Product_List_Table();
    $list_table->admin_page_url = $this->admin_page_url;
    $list_table->raw_items = $this->search_results;
    $list_table->options = $this->options;
    $list_table->prepare_items();
    $list_table->display(); 
    
    ?>
    </form>
</div>
    <?php
    }
    /**
     * perform actions
     */
    public function perform_action(){
        if(empty($_REQUEST['ggpr_action'])){
            return;
        }
        // Save the action
        $this->action = trim($_REQUEST['ggpr_action']);
        // Check nonce
        if( empty($_REQUEST['_wpnonce']) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'ggpr-search')){
            wp_die(__('Are you sure you want to do this?', 'wpml_theme'));
            return;
        }
        
        switch($this->action){
            case 'search':
                $this->search();
                break;

        }
    }
    
    public function search(){
        $entry = new GGPR_Entries();
        $this->search_data = array(
            'RegistrationCode'  => '',
            'Name'          => '',
            'IsUsed'        => '',
            'Address'       =>'',
            'PostalCode'    =>'',
            'City'          => '',
            'Country'       => '',
            'PhoneNo'       => '',
            'EmailAddress'  => '',
            'InvoiceNo'     => '',
            'Supplier'      => '',
            'DateOfPurchase'        => '',
            'DateOfRegistration'    =>''
        );
        $do_search = false;
        
        if(!empty($_POST['ggpr_code'])){
            $this->search_data['RegistrationCode'] = $_POST['ggpr_code'];
            $do_search = true;
        }
        if(!empty($_POST['ggpr_name'])){
            $this->search_data['Name'] = $_POST['ggpr_name'];
            $do_search = true;
        }
        if(!empty($_POST['ggpr_used'])){
            $this->search_data['IsUsed'] = 1;
            $do_search = true;
        }
        if(!empty($_POST['ggpr_not_used'])){
            $this->search_data['NotUsed'] = 1;
            $do_search = true;
        }
        if(!empty($_POST['ggpr_address'])){
            $this->search_data['Address'] = $_POST['ggpr_address'];
            $do_search = true;
        }
        if(!empty($_POST['ggpr_post_code'])){
            $this->search_data['PostalCode'] = $_POST['ggpr_post_code'];
            $do_search = true;
        }
        if(!empty($_POST['ggpr_city'])){
            $this->search_data['City'] = $_POST['ggpr_city'];
            $do_search = true;
        }
        if(!empty($_POST['ggpr_code'])){
            $this->search_data['Country'] = $_POST['ggpr_country'];
            $do_search = true;
        }
        if(!empty($_POST['ggpr_phone'])){
            $this->search_data['PhoneNo'] = $_POST['ggpr_phone'];
            $do_search = true;
        }
        if(!empty($_POST['ggpr_email'])){
            $this->search_data['EmailAddress'] = $_POST['ggpr_email'];
            $do_search = true;
        }
        if(!empty($_POST['ggpr_invoice_no'])){
            $this->search_data['InvoiceNo'] = $_POST['ggpr_invoice_no'];
            $do_search = true;
        }
        if(!empty($_POST['ggpr_supplier'])){
            $this->search_data['Supplier'] = $_POST['ggpr_supplier'];
            $do_search = true;
        }
        if(!empty($_POST['ggpr_dop'])){
            $this->search_data['DateOfPurchase'] = $_POST['ggpr_dop'];
            $do_search = true;
        }
        if(!empty($_POST['ggpr_dor'])){
            $this->search_data['DateOfRegistration'] = $_POST['ggpr_dor'];
            $do_search = true;
        }
        // Check if every field is empty
        if(!$do_search){
            wp_redirect(add_query_arg('message', 91,$this->admin_page_url));
            die();
        }
        // Get results
        $this->show_search_result = true;
        $this->search_results = $entry->search($this->search_data);
        return true;
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