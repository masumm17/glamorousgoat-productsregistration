<?php
class GGPR_Admin_View {
    public $current_page;
    public $action;
    public $admin_page_url;
    public $admin_settings_url;
    
    public $options;
    public $search_data;
    public $supplier_data;
    public $search_results;
    public $show_search_result;
    public $inpage_message_code;
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
            'ActualSupplier'        => '',
            'DateOfPurchase'        => '',
            'DateOfRegistration'    =>''
        );
        $this->supplier_data = array(
            'RegistrationCodeStart'  => '',
            'RegistrationCodeEnd'          => '',
            'ActualSupplier'        => ''
        );
        $this->search_results = array();
        $this->show_search_result = false;
        $this->inpage_message_code = false;
    }
    
    /**
     * Initiate Admin view
     */
    public function init(){
        $query_parts = array();
        if( isset($_REQUEST['page'])){
            if( GGPR_ADMIN_MENU_SLUG == $_REQUEST['page'] ){
                $this->current_page = 'main';
                $query_parts['page'] = GGPR_ADMIN_MENU_SLUG;
            }elseif(GGPR_ADMIN_SUPPLIERS_SLUG == $_REQUEST['page']){
                $this->current_page = 'actual_supplier';
                $query_parts['page'] = GGPR_ADMIN_SUPPLIERS_SLUG;
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
        add_submenu_page( GGPR_ADMIN_MENU_SLUG, $this->get_option('admin_suppliers_title'), $this->get_option('admin_suppliers_title'), 'manage_options', GGPR_ADMIN_SUPPLIERS_SLUG, array($this, 'admin_menu_page') ); 
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
            $this->main_page();
        }elseif('actual_supplier' == $this->current_page){
            $this->actual_suppliers_page();
        }elseif('search' == $this->current_page){
            if('edit' == $this->action){
                $this->show_edit_page();
            }else{
                $this->search_page();
            }
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
        }elseif($this->current_page == 'actual_supplier'){
            echo '<h2>'. $this->get_option('admin_suppliers_title') .'</h2>';
        }elseif($this->current_page == 'search'){
            if('edit' == $this->action){
                echo '<h2>'. $this->get_option('admin_edit_title') .'</h2>';
            }else{
                echo '<h2>'. $this->get_option('admin_search_title') .'</h2>';
            }
        }
    }
    
    /**
     * Output correct message
     */
    public function message_section(){
        $message_code = !empty($_GET['message'])?$_GET['message']:$this->inpage_message_code;
        $message = '';
        $class = '';
        switch($message_code){
            case 92: 
            case 95: 
            case 97: 
                $message = $this->get_option('admin_empty_code'); 
                $class = ' error';
                break;
            
            case 93: 
                $message = $this->get_option('admin_empty_success'); 
                break;
            case 94: 
                $message = $this->get_option('admin_empty_failed'); 
                $class = ' error';
                break;
            
            case 81: 
                $message = $this->get_option('admin_empty_fields'); 
                $class = ' error';
                break;
            case 82: 
                $message = $this->get_option('admin_suppliers_added'); 
                break;
            
            case 83: 
                $message = $this->get_option('regi_code_range_start_ne'); 
                $class = ' error';
                break;
            case 84: 
                $message = $this->get_option('regi_code_range_end_ne'); 
                break;
            
            case 91: 
                $message = $this->get_option('admin_empty_sf'); 
                $class = ' error';
                break;
            
            case 98: $message = $this->get_option('admin_code_changed'); break;
            case 99: $message = $this->get_option('admin_updated_success'); break;
            case 100: 
                $message = $this->get_option('admin_updated_failed'); 
                $class = ' error';
                break;
            
        
            default : $message = '';break;
        }
        if($message){
            echo '<div id="message" class="updated below-h2 '. $class .'">';
                    echo '<p>'. $message .'</p>'; 
            echo '</div>';
        }
    }
    
    public function main_page(){
        $options = wp_parse_args($this->options, ggpr_default_options());
        $textareas = array('mail_body');
    ?>
<div class="ggpr-options-wrap">
    <form method="post" action="" class="ggpr-options-form">
        <?php wp_nonce_field('ggpr-actions'); ?>
         <input type="hidden" name="ggpr_action" value="save_options"/>
         <div class="ggpr-options-fields-wrap">
             <?php foreach ($options as $key=>$value){ ?>
             <div class="ggpr-field-wrap ggpr-group">
                 <label for="<?php echo esc_attr($key); ?>"><?php echo $key ?></label>
                <div class="ggpr-field">
                    <?php if(in_array($key, $textareas)){ ?>
                    <textarea id="<?php echo esc_attr($key); ?>" name="ggpr_options[<?php echo esc_attr($key); ?>]"><?php echo esc_html($value); ?></textarea>
                    <?php }else{?>
                    <input type="text" id="<?php echo esc_attr($key); ?>" name="ggpr_options[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($value); ?>"/>
                    <?php }?>
                </div>
            </div>
            <?php }?>
            <div class="ggpr-submit-wrap">
                <input class="ggpr-submit" type="submit" value="<?php echo $this->get_option('submit'); ?>"/>
            </div> 
         </div>
    </form>
</div>
    <?php
    }
    
    /**
     * Actual Suppliers page
     */
    public function actual_suppliers_page(){
    ?>
    <div class="ggpr-search-form-wrap">
        <form class="ggpr-supplier-form" action="" method="post">
            <?php wp_nonce_field('ggpr-actions'); ?>
            <input type="hidden" name="ggpr_action" value="add_supplier"/>
            <div class="ggpr-search-fields-wrap ggpr-group">
                <div class="ggpr-col3">
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_code_start"><?php echo $this->get_option('regi_code_range_start'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_code_start" name="ggpr_code_start" value="<?php echo esc_attr($this->supplier_data['RegistrationCodeStart']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_code_end"><?php echo $this->get_option('regi_code_range_end'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_code_end" name="ggpr_code_end" value="<?php echo esc_attr($this->supplier_data['RegistrationCodeEnd']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_actual_suplier"><?php echo $this->get_option('actual_supplier_field_title'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_actual_suplier" name="ggpr_actual_suplier" value="<?php echo esc_attr($this->supplier_data['ActualSupplier']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-submit-wrap">
                        <input class="ggpr-submit" type="submit" value="<?php echo $this->get_option('submit'); ?>"/>
                    </div> 
                </div>
            </div>
        </form>
    </div>
    <?php
        
    }
    
    /**
     * Search page
     */
    public function search_page(){
    ?>
    <div class="ggpr-search-form-wrap">
        <form class="ggpr-search-form" action="" method="post">
            <?php wp_nonce_field('ggpr-actions'); ?>
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
                            <input type="text" id="ggpr_supplier" name="ggpr_supplier" value="<?php echo esc_attr($this->search_data['Supplier']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_actual_supplier"><?php echo $this->get_option('actual_supplier_field_title'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_actual_supplier" name="ggpr_actual_supplier" value="<?php echo esc_attr($this->search_data['ActualSupplier']); ?>"/>
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
    /**
     * Search Result page
     */
    public function search_result_page(){
    ?> 
    <div class="ggpr-search-result">
        <?php if($this->search_results){ ?>
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
        <?php }else{?>
        <p class="warning"><?php echo $this->get_option('nothing_found'); ?></p>
        <?php } ?>
    </div>
    <?php
    }
    /**
     * Show edit page
     */
    public function show_edit_page(){
        if(empty($_REQUEST['ggpr_code']))
            return;
        $entry = new GGPR_Entries();
        $product_data = $entry->get_prodcut_data($_REQUEST['ggpr_code']);
        if(!$product_data){
            $product_data = $this->search_data;
        }
    ?>
    <div class="ggpr-edit-form-wrap">
        <form class="ggpr-edit-form" action="" method="post">
            <?php wp_nonce_field('ggpr-actions'); ?>
            <input type="hidden" name="ggpr_action" value="update"/>
            <input type="hidden" name="ggpr_code" value="<?php echo esc_attr($product_data['RegistrationCode']); ?>"/>
            <div class="ggpr-search-fields-wrap ggpr-group">
                <div class="ggpr-col3">
                    <div class="ggpr-field-wrap ggpr-group">
                        <label><?php echo $this->get_option('regi_code'); ?></label>
                        <div class="ggpr-field">
                            <strong><?php echo esc_attr($_REQUEST['ggpr_code']); ?></strong>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_name"><?php echo $this->get_option('name'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_name" name="ggpr_name" value="<?php echo esc_attr($product_data['Name']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_post_code"><?php echo $this->get_option('postal_code'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_post_code" name="ggpr_post_code" value="<?php echo esc_attr($product_data['PostalCode']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_email"><?php echo $this->get_option('emai'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_email" name="ggpr_email" value="<?php echo esc_attr($product_data['EmailAddress']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_phone"><?php echo $this->get_option('phone_no'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_phone" name="ggpr_phone" value="<?php echo esc_attr($product_data['PhoneNo']); ?>"/>
                        </div>
                    </div>
                </div>
                <div class="ggpr-col3">
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_address"><?php echo $this->get_option('address'); ?></label>
                        <div class="ggpr-field">
                            <textarea id="ggpr_address" name="ggpr_address"><?php echo esc_html($product_data['Address']); ?></textarea>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_city"><?php echo $this->get_option('city'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_city" name="ggpr_city" value="<?php echo esc_attr($product_data['City']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_country"><?php echo $this->get_option('country'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_country" name="ggpr_country" value="<?php echo esc_attr($product_data['Country']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_invoice_no"><?php echo $this->get_option('invoice_no'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_invoice_no" name="ggpr_invoice_no" value="<?php echo esc_attr($product_data['InvoiceNo']); ?>"/>
                        </div>
                    </div>
                </div>
                <div class="ggpr-col3">
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_supplier"><?php echo $this->get_option('supplier'); ?></label>
                        <div class="ggpr-field">
                            <textarea id="ggpr_supplier" name="ggpr_supplier"><?php echo esc_html($product_data['Supplier']); ?></textarea>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_dop"><?php echo $this->get_option('purchase_date'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_dop" name="ggpr_dop" value="<?php echo esc_attr($product_data['DateOfPurchase']); ?>" />
                        </div>
                    </div>
                    <div class="ggpr-field-wrap ggpr-group">
                        <label for="ggpr_dor"><?php echo $this->get_option('regi_date'); ?></label>
                        <div class="ggpr-field">
                            <input type="text" id="ggpr_dor" name="ggpr_dor" value="<?php echo esc_attr($product_data['DateOfRegistration']); ?>" />
                        </div>
                    </div>
                    <div class="ggpr-submit-wrap">
                        <input class="ggpr-submit" type="submit" value="<?php echo $this->get_option('submit'); ?>"/>
                    </div> 
                </div>
            </div>
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
        if( !in_array($this->action, array('edit')) && (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'ggpr-actions')) ){
            wp_die(__('Are you sure you want to do this?', 'wpml_theme'));
            return;
        }
        
        switch($this->action){
            case 'search':
                $this->search();
                break;
            case 'update': 
                $this->update_product_data();
                break;
            case 'empty':
                $this->empty_prodcut_data();
                break;
            case 'edit':
                if(empty($_REQUEST['ggpr_code'])){
                    wp_redirect(add_query_arg('message', 95,$this->admin_page_url));
                    die();
                }
                break;
            case 'add_supplier':
                $this->add_supplier();
                break;
            case 'save_options':
                $this->save_options();
                break;
        }
    }
    /**
     * 
     * @return boolean
     */
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
            'ActualSupplier'        => '',
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
        if(!empty($_POST['ggpr_actual_supplier'])){
            $this->search_data['ActualSupplier'] = $_POST['ggpr_actual_supplier'];
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
     * Update Product Data
     */
    public function update_product_data(){
        if(empty($_REQUEST['ggpr_code'])){
            wp_redirect(add_query_arg('message', 97,$this->admin_page_url));
            die();
        }
        if($_GET['ggpr_code'] != $_POST['ggpr_code']){
            wp_redirect(add_query_arg('message', 98,$this->admin_page_url));
            die();
        }
        $product_data = array(
            'Name'          => '',
            'IsUsed'        => 0,
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
        if(!empty($_POST['ggpr_name'])){
            $product_data['Name'] = $_POST['ggpr_name'];
            $product_data['IsUsed'] = 1;
        }
        if(!empty($_POST['ggpr_address'])){
            $product_data['Address'] = $_POST['ggpr_address'];
            $product_data['IsUsed'] = 1;
        }
        if(!empty($_POST['ggpr_post_code'])){
            $product_data['PostalCode'] = $_POST['ggpr_post_code'];
            $product_data['IsUsed'] = 1;
        }
        if(!empty($_POST['ggpr_city'])){
            $product_data['City'] = $_POST['ggpr_city'];
            $product_data['IsUsed'] = 1;
        }
        if(!empty($_POST['ggpr_country'])){
            $product_data['Country'] = $_POST['ggpr_country'];
            $product_data['IsUsed'] = 1;
        }
        if(!empty($_POST['ggpr_phone'])){
            $product_data['PhoneNo'] = $_POST['ggpr_phone'];
            $product_data['IsUsed'] = 1;
        }
        if(!empty($_POST['ggpr_email'])){
            $product_data['EmailAddress'] = $_POST['ggpr_email'];
            $product_data['IsUsed'] = 1;
        }
        if(!empty($_POST['ggpr_invoice_no'])){
            $product_data['InvoiceNo'] = $_POST['ggpr_invoice_no'];
            $product_data['IsUsed'] = 1;
        }
        if(!empty($_POST['ggpr_supplier'])){
            $product_data['Supplier'] = $_POST['ggpr_supplier'];
            $product_data['IsUsed'] = 1;
        }
        if(!empty($_POST['ggpr_dop'])){
            $product_data['DateOfPurchase'] = $_POST['ggpr_dop'];
            $product_data['IsUsed'] = 1;
        }
        if(!empty($_POST['ggpr_dor'])){
            $product_data['DateOfRegistration'] = $_POST['ggpr_dor'];
            $product_data['IsUsed'] = 1;
        }
        $entry = new GGPR_Entries();
        
        if($entry->save_product_data($_REQUEST['ggpr_code'], $product_data)){
            wp_redirect(add_query_arg(array('message'=>99, 'ggpr_action'=>'edit','ggpr_code'=>$_GET['ggpr_code']) ,$this->admin_page_url));
            die();
        }
        wp_redirect(add_query_arg('message', 100,$this->admin_page_url));
        die();
    }
    /**
     * Empty Product Data
     */
    public function empty_prodcut_data(){
        if(empty($_REQUEST['ggpr_code'])){
            wp_redirect(add_query_arg('message', 92,$this->admin_page_url));
            die();
        }
        $product_data = array(
            'Name'          => '',
            'IsUsed'        => 0,
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
        $entry = new GGPR_Entries();
        if($entry->save_product_data($_REQUEST['ggpr_code'], $product_data)){
            wp_redirect(add_query_arg(array('message'=>93, 'ggpr_action'=>'edit','ggpr_code'=>$_GET['ggpr_code']) ,$this->admin_page_url));            
            die();
        }
        wp_redirect(add_query_arg('message', 94,$this->admin_page_url));
        die();
    }
    /**
     * Save options
     */
    public function save_options(){
        if(!isset($_POST['ggpr_options']) || !is_array($_POST['ggpr_options'])){
            wp_redirect($this->admin_page_url);
            die();
        }
        $options = wp_parse_args($this->options, ggpr_default_options());
        $new_options = array();
        foreach ($options as $key=>$val){
            $new_options[$key] = isset($_POST['ggpr_options'][$key])?$_POST['ggpr_options'][$key]:'';
        }
        if(update_option(GGPR_OPTION_NAME, $new_options) || ($new_options == $this->options)){
            wp_redirect(add_query_arg(array('message'=>99), $this->admin_page_url));
            die();
        }else{
            wp_redirect(add_query_arg(array('message'=>100), $this->admin_page_url));
            die();
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
     * 
     */
    public function add_supplier(){
        $is_set = true;
        if(!empty($_POST['ggpr_code_start'])){
            $this->supplier_data['RegistrationCodeStart'] = $_POST['ggpr_code_start'];
        }else{
            $is_set = false;
        }
        if(!empty($_POST['ggpr_code_end'])){
            $this->supplier_data['RegistrationCodeEnd'] = $_POST['ggpr_code_end'];
        }else{
            $is_set = false;
        }
        if(!empty($_POST['ggpr_actual_suplier'])){
            $this->supplier_data['ActualSupplier'] = $_POST['ggpr_actual_suplier'];
        }else{
            $is_set = false;
        }
        // Check if every field is empty
        if(!$is_set){
            $this->inpage_message_code = 81;
            return false;
        }
        // Save suppliers
        $entry = new GGPR_Entries();
        if(!$entry->code_exists($this->supplier_data['RegistrationCodeStart'])){
            $this->inpage_message_code = 83;
            return false;
        }
        if(!$entry->code_exists($this->supplier_data['RegistrationCodeEnd'])){
            $this->inpage_message_code = 84;
            return false;
        }
        // Everything is OK
        // Now save Actual Suppliers
        $resutl = false;
        if(strcmp($this->supplier_data['RegistrationCodeStart'], $this->supplier_data['RegistrationCodeEnd']) < 1){
            $resutl = $entry->set_actual_supplier($this->supplier_data['ActualSupplier'], $this->supplier_data['RegistrationCodeStart'], $this->supplier_data['RegistrationCodeEnd']);
        }else{
            $resutl = $entry->set_actual_supplier($this->supplier_data['ActualSupplier'], $this->supplier_data['RegistrationCodeEnd'], $this->supplier_data['RegistrationCodeStart']);
        }
        
        if(!$resutl){
            wp_redirect(add_query_arg(array('message'=>100), $this->admin_page_url));
            die();
        }else{
            wp_redirect(add_query_arg(array('message'=>82), $this->admin_page_url));
            die();
        }
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