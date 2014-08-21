<?php
/**
 * Base class GG_Product_Registration
 *
 * @package GG Product Registration
 *
 */

class GG_Product_Registration {
    
    public $options;
    
    public $script_loaded;
    
    public $product_code_submitted;
    
    public $product_code_empty;
    
    public $duplicate_code;
    
    public $product_code_exists;
    
    public $product_code_used;
    
    public $product_regi_codes;
    
    public $user_form_submitted;
    
    public $messages;
    
    public $error_fields;
    
    public $non_exists_code;
    
    public $used_codes;
    
    public $product_regi_data;
    
    public $product_data_validated;
    
    public $product_data_saved;
    
    public $sc_atts;
    
    
    /**
    * The constructor is executed when the class is instatiated and the plugin gets loaded.
    * @return void
    */	
    function __construct($plugin_file=__FILE__) {
        // add all basic action calls
        
        // Initiate variables;
        $this->variables();
        
        // Activation hook
        register_activation_hook( $plugin_file, array( $this, 'activate' ) );
        // Deactivation Hook
        register_deactivation_hook($plugin_file, array($this, 'deactivate'));
        // Unsinstall Hook
        register_uninstall_hook( $plugin_file, array( __CLASS__, 'uninstall' ) );

        //if(  is_admin()){
                //$adminHandler = new MHCF7DBEAdminView();
        //}
        
        //Add all actions when all plugins are loaded
        add_action('plugins_loaded', array($this, 'site_plugins_loaded'), 100);
    }
    
    /**
     * Initiate class variables
     */
    public function variables(){
        
        $this->options = get_option(GGPR_OPTION_NAME, $this->default_options());
        
        $this->script_loaded = false;
        $this->product_code_submitted = false;
        $this->product_code_empty = false;
        $this->duplicate_code = false;
        $this->product_code_exists = false;
        $this->product_code_used = false;
        $this->user_form_submitted = false;
        $this->product_data_validated = false;
        $this->product_data_saved = false;
        
        $this->product_regi_codes = array();
        $this->messages = array();
        $this->error_fields = array();
        $this->non_exists_code = array();
        $this->used_codes = array();
        $this->product_regi_data = array(
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
        
        $this->sc_atts = array();
    }

    /**
     * Add plugsins nnd filters
     */
    function site_plugins_loaded(){
        // Action Hooks
        $this->add_actions();
        // Filter Hooks
        $this->add_filters();
    }


    /**
     * All Action hooks
     */
    function add_actions(){
        add_action('init', array($this, 'shortcodes'));
        add_action('init', array($this, 'handle_form_submission'));
        
        // Add shortcode scripts and styles
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function shortcodes(){
        add_shortcode('ggpr-form', array($this, 'shortcode_ggpr_form'));
    }

    /**
     * All filter hooks
     */
    function add_filters(){
        // Filters goes here
    }
     
    /**
     *  Shortcode Handler
     * @param array $atts
     * @param string $content
     */
    public function shortcode_ggpr_form($atts, $content=""){
        $defaults = array(
            'seat_no'       => '8',
            'link_type'     => 'number',
            'link_images'   => '',
            'thankyou_link' => ''
        );
        $atts = shortcode_atts($defaults, $atts, 'ggpr-form');
        extract($atts);
        $seat_no = (int)$seat_no;
        if($seat_no < 1){
            return '';
        }
        $this->sc_atts = $atts;
        $link_images = explode(',', $link_images);
        $img_srcs = array();
        if(is_array($link_images)){
            foreach($link_images as $im){
                $im = trim($im);
                $matches = array();
                if(preg_match('/^(\d+):(.+)$/', $im, $matches)){
                    $img_srcs[(int)$matches[1]] = $matches[2];
                }
            }
            ksort($img_srcs);
        }
        
        $seat_selector_html = array();
        if('number' == $link_type){
            for($i=1;$i<=$seat_no;$i++){
                $seat_selector_html[$i] = '<a data-fields="' . $i . '" href="#" title="'. $i .' Seats" >' . $i . '</a>';
            }
        }elseif('image' == $link_type){
            for($i=1;$i<=$seat_no;$i++){
                $im = !empty($img_srcs[$i])?'<img alt="'. $i .'" src="'.$img_srcs[$i].'"/>':$i;
                $seat_selector_html[$i] = '<a data-fields="' . $i . '" href="#" title="'. $i .' Seats" >'. $im .'</a>';
            }
        }else{
            return '';
        }
        
        
        ob_start();
        // Check if scripts and styles are already included
        if(empty($this->script_loaded)){
            // Not included yet include here
            $this->include_scripts();
        }
        if((!$this->product_code_submitted || $this->product_code_empty || $this->duplicate_code || !$this->product_code_exists || $this->product_code_used)&& !$this->user_form_submitted ){
            $this->user_form('code-only', $seat_selector_html);
        }elseif(!$this->product_data_validated){
            $this->user_form('data', $seat_selector_html);
        }elseif(!$this->product_data_saved){
            // Do some error Handling
            $this->error_html();
        }else{
            $this->thanks_html();
        }
        
        return trim(ob_get_clean());
    }
    
    /**
     * Output User Form
     * @param string $type
     */
    public function user_form($type='full', $selectors=array()){
        $fields_num = count($this->product_regi_codes);

        ?>
        <div class="ggpr-form-wrap <?php if('code-only'==$type){echo ' ggpr-show-selector';} ?>">
            <?php if(count($selectors)>0){?>
            <ul class="ggpr-selector">
                <?php foreach ($selectors as $key => $link) {?>
                <li <?php if($key == $fields_num){echo ' class="ggpr-selected" ';} ?>><?php echo  $link;?></li>
                <?php } ?>
            </ul>
            <?php } ?>
            <form method="post" action="" class="ggpr-form" style="<?php if($fields_num==0){echo 'display:none;';} ?>">
                <?php wp_nonce_field('ggpr-submit', 'ggpr_nonce') ?>
                <?php if(!empty($this->sc_atts['thankyou_link'])){ ?><input type="hidden" name="ggpr-redirect" value="<?php echo esc_attr($this->sc_atts['thankyou_link']); ?>"/><?php } ?>
                <div class="ggpr-message"><?php $this->show_message(); ?></div>
                <?php if('code-only' == $type){ ?>
                <div class="ggpr-form-inner">
                    <input type="hidden" name="ggpr-action" value="check-only"/>
                    <div class="ggpr-field-wrap">
                        <label><?php echo $this->get_option('regi_code') ?></label>
                        <div class="ggpr-field">
                            <?php if($fields_num == 0){  ?>
                            <input class="ggpr-code-field" type="text" id="ggpr_product_regi_codes_0" name="ggpr_product_regi_codes[0]" value=""/>
                            <?php
                            }else{
                                foreach ($this->product_regi_codes as $key=>$code){
                            ?>
                            <input class="ggpr-code-field <?php ggpr_conditional_class('ggpr-non-exists', $code, $this->non_exists_code); ggpr_conditional_class('ggpr-code-used', $code, $this->used_codes); ggpr_conditional_class('ggpr-error', 'regi_codes'.$key, $this->error_fields); ?>" type="text" id="ggpr_product_regi_codes_<?php echo $key; ?>" name="ggpr_product_regi_codes[<?php echo $key; ?>]" value="<?php echo esc_attr($code); ?>"/>
                            <?php
                                }
                            }
                            ?>                      
                        </div>
                    </div>
                </div>
                <?php }elseif('data' == $type){ ?>
                
                <div class="ggpr-form-inner">
                    <input type="hidden" name="ggpr-action" value="check-save"/>
                    <div class="ggpr-field-wrap">
                        <label for="ggpr_product_regi_code"><?php echo $this->get_option('regi_code'); ?></label>
                        <div class="ggpr-field">
                            <?php foreach ($this->product_regi_codes as $key=>$code){ ?>
                            <input type="hidden" name="ggpr_product_regi_codes[<?php echo $key; ?>]" value="<?php echo esc_attr($code); ?>"/>
                            <span class="ggpr-regicodes"><?php echo esc_html($code); ?></span> 
                            <?php }?>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap">
                        <label for="ggpr_name"><?php echo $this->get_option('name'); ?></label>
                        <div class="ggpr-field">
                            <input class="<?php ggpr_conditional_class('ggpr-error', 'ggpr_name', $this->error_fields);?>" type="text" id="ggpr_name" name="ggpr_name" value="<?php echo esc_attr($this->product_regi_data['Name']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap">
                        <label for="ggpr_address"><?php echo $this->get_option('address'); ?></label>
                        <div class="ggpr-field">
                            <textarea class="<?php ggpr_conditional_class('ggpr-error', 'ggpr_address', $this->error_fields);?>" id="ggpr_address" name="ggpr_address"><?php echo esc_html($this->product_regi_data['Address']); ?></textarea>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap">
                        <label for="ggpr_post_code"><?php echo $this->get_option('postal_code'); ?></label>
                        <div class="ggpr-field">
                            <input class="<?php ggpr_conditional_class('ggpr-error', 'ggpr_post_code', $this->error_fields);?>" type="text" id="ggpr_post_code" name="ggpr_post_code" value="<?php echo esc_attr($this->product_regi_data['PostalCode']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap">
                        <label for="ggpr_city"><?php echo $this->get_option('city'); ?></label>
                        <div class="ggpr-field">
                            <input class="<?php ggpr_conditional_class('ggpr-error', 'ggpr_city', $this->error_fields);?>" type="text" id="ggpr_city" name="ggpr_city" value="<?php echo esc_attr($this->product_regi_data['City']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap">
                        <label for="ggpr_country"><?php echo $this->get_option('country'); ?></label>
                        <div class="ggpr-field">
                            <input class="<?php ggpr_conditional_class('ggpr-error', 'ggpr_country', $this->error_fields);?>" type="text" id="ggpr_country" name="ggpr_country" value="<?php echo esc_attr($this->product_regi_data['Country']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap">
                        <label for="ggpr_phone"><?php echo $this->get_option('phone_no'); ?></label>
                        <div class="ggpr-field">
                            <input class="<?php ggpr_conditional_class('ggpr-error', 'ggpr_phone', $this->error_fields);?>" type="text" id="ggpr_phone" name="ggpr_phone" value="<?php echo esc_attr($this->product_regi_data['PhoneNo']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap">
                        <label for="ggpr_email"><?php echo $this->get_option('emai'); ?></label>
                        <div class="ggpr-field">
                            <input class="<?php ggpr_conditional_class('ggpr-error', 'ggpr_email', $this->error_fields);?>" type="text" id="ggpr_email" name="ggpr_email" value="<?php echo esc_attr($this->product_regi_data['EmailAddress']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap">
                        <label for="ggpr_invoice_no"><?php echo $this->get_option('invoice_no'); ?></label>
                        <div class="ggpr-field">
                            <input class="<?php ggpr_conditional_class('ggpr-error', 'ggpr_invoice_no', $this->error_fields);?>" type="text" id="ggpr_invoice_no" name="ggpr_invoice_no" value="<?php echo esc_attr($this->product_regi_data['InvoiceNo']); ?>"/>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap">
                        <label for="ggpr_supplier"><?php echo $this->get_option('supplier'); ?></label>
                        <div class="ggpr-field">
                            <textarea class="<?php ggpr_conditional_class('ggpr-error', 'ggpr_supplier', $this->error_fields);?>" id="ggpr_supplier" name="ggpr_supplier"><?php echo esc_html($this->product_regi_data['Supplier']); ?></textarea>
                        </div>
                    </div>
                    <div class="ggpr-field-wrap">
                        <label for="ggpr_dop"><?php echo $this->get_option('purchase_date'); ?></label>
                        <div class="ggpr-field">
                            <input class="ggpr-date-picker <?php ggpr_conditional_class('ggpr-error', 'ggpr_dop', $this->error_fields);?>" type="text" id="ggpr_dop" name="ggpr_dop" value="<?php echo esc_attr($this->product_regi_data['DateOfPurchase']); ?>" />
                        </div>
                    </div>
                </div>
                <?php } ?>
                
                <div class="ggpr-submit-wrap">
                    <input class="ggpr-submit" type="submit" value="<?php echo $this->get_option('submit'); ?>"/>
                </div>   
            </form>
        </div>
        <?php
    }
    
    /**
     * Thanks html
     */
    public function thanks_html(){
        ?>
        <div class="ggpr-thanks-wrap">
            <p class="ggpr-thanks"><?php echo $this->get_option('thanks'); ?></p>
        </div>
        <?php
    }
    
    public function error_html(){
        ?>
        <div class="ggpr-unsuccess-wrap">
            <p class="ggpr-thanks"><?php echo $this->get_option('save_error'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Display Messages
     * @param bool $display
     */
    public function show_message($display = true){
        if(count($this->messages)==0){
            return;
        }
        foreach ($this->messages as $message){
            echo $message;
        }
    }
    
    public function has_shortcode($content, $tag="ggpr-form"){
        if ( shortcode_exists( $tag ) ) {
            if(preg_match_all( '/\['. $tag .'[^\]]*\]/', $content, $matches, PREG_SET_ORDER ))
                return true;
	}
        return false;
    }


    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts(){
        global $post;
        
        if( !is_admin() && is_a( $post, 'WP_Post' ) && $this->has_shortcode( $post->post_content, 'ggpr-form') ) {
            // Flag true
            $this->script_loaded = true;
            wp_enqueue_style('ggpr-front-end', GGPR_URL.'css/front-end.css');
            wp_enqueue_script('ggpr-front-end', GGPR_URL.'js/front-end.js', array('jquery'), GGPR_VER, true);
            wp_localize_script('ggpr-front-end', 'GGPROptions', $this->options);
        }
    }
    
    /**
     * Hardcode style and script files berfore the shortcode
     */
    public function include_scripts(){
        // Flag true
        $this->script_loaded = true;
        echo '<style type="text/css" src="'. GGPR_URL.'css/front-end.css' .'"></style>';
        echo '<script type="text/javascript" src="'. GGPR_URL.'js/front-end.js' .'"></script>';
    }
    
    public function handle_form_submission(){
        // Check if user submitted the form
        if(empty($_POST['ggpr-action'])){
           return false; 
        }
        // Check nonce and verify it
        if(empty($_POST['ggpr_nonce']) || !wp_verify_nonce($_POST['ggpr_nonce'], 'ggpr-submit')){
            return false;
        }
        $redirect_url = !empty($_POST['ggpr-redirect'])?trim($_POST['ggpr-redirect']):'';
        $product_codes = !empty($_POST['ggpr_product_regi_codes'])?$_POST['ggpr_product_regi_codes']:array();
        if(!$product_codes){
            $this->messages[] = '<p class="error">'. $this->get_option('no_code_entered') .'</p>';
            return;
        }
        $this->product_regi_codes = array_values($product_codes);
        unset($product_codes);
        
        //Get Action
        $action = trim($_POST['ggpr-action']);

        if('check-only' == $action){
            $this->product_code_submitted = true;
            if($this->check_product_codes())
                return false;
        }elseif('check-save' == $action){
            $this->user_form_submitted = true;
            if(!$this->check_product_regi_data()){
                return false;
            }
            // Everything is OK. Now save the data
            if(!$this->save_product_regi_data()){
                 $this->messages[] = '<p class="error">'. $this->get_option('unknown_error') .'</p>';
            }
            $this->product_data_saved = true;
            // Data saved Successfully.
            // Now time to thanks the user
            if($redirect_url){
                wp_safe_redirect($redirect_url);
                die();
            }
        }
    }
    
    /**
     * Check product registration code submitted by user
     * @return bool
     */
    public function check_product_codes(){
        $entry = new GGPR_Entries();
        if(count($this->product_regi_codes)==0)
            return;
        $this->product_code_exists = true;
        $this->product_code_used = false;
        $this->product_code_empty = false; 
        $this->duplicate_code = false;
        $this->duplicate_code = false;
        
        $duplicate = (array)array_intersect (array_unique($this->product_regi_codes), $this->product_regi_codes);
        if(count($duplicate) == count($this->product_regi_codes)){
            $duplicate = array();
        }
        
        foreach ($this->product_regi_codes as $key => $code) {
            if(!$code){
                $this->error_fields[] = 'regi_codes'.$key;
                $this->product_code_empty = true;
                continue;
            }
            
            if(in_array($code, $duplicate)){
                $this->error_fields[] = 'regi_codes'.$key;
                $this->duplicate_code = true;
                continue;
            }
            
            if(!$entry->code_exists($code)){
                 $this->product_code_exists = false;
                 $this->non_exists_code[] = $code;
            }
            if($entry->code_used($code)){
                $this->product_code_used = true;
                $this->used_codes[] = $code;
            }
        }
        if($this->product_code_empty){
            $this->messages[] = '<p class="error color-not-exists">'. $this->get_option('code_empty') .'</p>';
        }
        if($this->duplicate_code){
            $this->messages[] = '<p class="error color-not-exists">'. $this->get_option('code_duplicate') .'</p>';
        }
        if(!$this->product_code_exists){
            $this->messages[] = '<p class="error color-not-exists">'. $this->get_option('code_exist') .'</p>';
        }
        if($this->product_code_used){
            $this->messages[] = '<p class="error color-used">'. $this->get_option('code_used') .'</p>';
        }
        
        return !$this->product_code_exists && $this->product_code_used && $this->product_code_empty && $this->duplicate_code; 
                
    }
    /**
     * Check and validate product registration data
     * @return boolean
     */
    public function check_product_regi_data(){
        $this->product_data_validated = true;
        $bad_email = $bad_phone = false;
        // Get Name
        if(!empty($_POST['ggpr_name'])){
            $this->product_regi_data['Name'] = trim($_POST['ggpr_name']);
        }else{
            $this->error_fields[] = 'ggpr_name';
            $this->product_data_validated = false;
        }
        //Get Address
        if(!empty($_POST['ggpr_address'])){
            $this->product_regi_data['Address'] = trim($_POST['ggpr_address']);
        }else{
            $this->error_fields[] = 'ggpr_address';
            $this->product_data_validated = false;
        }
        //Get PostalCode
        if(!empty($_POST['ggpr_post_code'])){
            $this->product_regi_data['PostalCode'] = trim($_POST['ggpr_post_code']);
        }else{
            $this->error_fields[] = 'ggpr_post_code';
            $this->product_data_validated = false;
        }
        //Get City
        if(!empty($_POST['ggpr_city'])){
            $this->product_regi_data['City'] = trim($_POST['ggpr_city']);
        }else{
            $this->error_fields[] = 'ggpr_city';
            $this->product_data_validated = false;
        }
        //Get Country
        if(!empty($_POST['ggpr_country'])){
            $this->product_regi_data['Country'] = trim($_POST['ggpr_country']);
        }else{
            $this->error_fields[] = 'ggpr_country';
            $this->product_data_validated = false;
        }
        //Get PhoneNo
        if(!empty($_POST['ggpr_phone'])){
            $this->product_regi_data['PhoneNo'] = trim($_POST['ggpr_phone']);
        }else{
            $this->error_fields[] = 'ggpr_phone';
            $this->product_data_validated = false;
        }
        //Get EmailAddress
        if(!empty($_POST['ggpr_email'])){
            $this->product_regi_data['EmailAddress'] = trim($_POST['ggpr_email']);
            if(!filter_var($this->product_regi_data['EmailAddress'], FILTER_VALIDATE_EMAIL)) {
                $bad_email = true;
            }
        }else{
            $this->error_fields[] = 'ggpr_email';
            $this->product_data_validated = false;
        }
        //Get InvoiceNo
        if(!empty($_POST['ggpr_invoice_no'])){
            $this->product_regi_data['InvoiceNo'] = trim($_POST['ggpr_invoice_no']);
        }else{
            $this->error_fields[] = 'ggpr_invoice_no';
            $this->product_data_validated = false;
        }
        //Get Supplier
        if(!empty($_POST['ggpr_supplier'])){
            $this->product_regi_data['Supplier'] = trim($_POST['ggpr_supplier']);
        }else{
            $this->error_fields[] = 'ggpr_supplier';
            $this->product_data_validated = false;
        }
        //Get DateOfPurchase
        if(!empty($_POST['ggpr_dop'])){
            $this->product_regi_data['DateOfPurchase'] = trim($_POST['ggpr_dop']);
            $this->product_regi_data['DateOfPurchase'] = date('Y-m-d', strtotime($this->product_regi_data['DateOfPurchase']));
        }else{
            $this->error_fields[] = 'ggpr_dop';
            $this->product_data_validated = false;
        }
        
        if($bad_email){
            $this->product_data_validated = false;
            $this->error_fields[] = 'ggpr_email';
            $this->messages[] = '<p class="error color-used">'. $this->get_option('bad_email') .'</p>';
        }
        if($bad_phone){
            $this->product_data_validated = false;
            $this->error_fields[] = 'ggpr_phone';
            $this->messages[] = '<p class="error color-used">'. $this->get_option('bad_phone') .'</p>';
        }
        
        if($this->product_data_validated){
            $this->product_regi_data['IsUsed'] = 1;
            $this->product_regi_data['DateOfRegistration'] = date('Y-m-d H:i:s');
            return true;
        }
        
        
        $this->messages[] = '<p class="error color-used">'. $this->get_option('field_error') .'</p>';
        
        return false;
        
    }
    
    public function save_product_regi_data(){
        if($this->check_product_codes()){
            return false;
        }
        $entry = new GGPR_Entries();
        // Now save the product registration data in database
        if($entry->save_multiple_product_data($this->product_regi_codes, $this->product_regi_data)){
            $this->product_data_saved = false;
            return true;
        }
        return false;
    }
    
    /**
     * Get Default Options
     * @return array
     */
    public function default_options(){
        return array(
            'regi_code'             => __("Registration Code", 'wpml_theme'),
            'is_used'               => __("Is Used", 'wpml_theme'),
            'name'                  => __("Name", 'wpml_theme'),
            'address'               => __("Address", 'wpml_theme'),
            'postal_code'           => __("Postal Code", 'wpml_theme'),
            'city'                  => __("City", 'wpml_theme'),
            'country'               => __("Country", 'wpml_theme'),
            'phone_no'              => __("Phone No.", 'wpml_theme'),
            'emai'                  => __("Email Address", 'wpml_theme'),
            'invoice_no'            => __("Invoice No.", 'wpml_theme'),
            'supplier'              => __("Supplier", 'wpml_theme'),
            'purchase_date'         => __("Date of Purchase", 'wpml_theme'),
            'regi_date'             => __("Date of Registration", 'wpml_theme'),
            'submit'                => __("Submit", 'wpml_theme'),
            
            'no_code_entered'       => __("No product registration code entered.", 'wpml_theme'),
            'code_empty'            => __("Product registration code fields (highlighted) must not be empty.", 'wpml_theme'),
            'code_duplicate'        => __("Product registration codes (highlighted) must not be duplicated.", 'wpml_theme'),
            'code_exist'            => __("Product registration code (highlighted) does not exist.", 'wpml_theme'),
            'code_used'             => __("Product registration code (highlighted) is used.", 'wpml_theme'),
            'field_error'           => __("Please fix the highlighted fields", 'wpml_theme'),
            'bad_email'             => __("Please enter a valid email address", 'wpml_theme'),
            'bad_phone'             => __("Please enter a valide phone number", 'wpml_theme'),
            'unknown_error'         => __("Unknown error occured. Pelase try again.", 'wpml_theme'),
            
            'admin_menu_title'      => __("Product Registration", 'wpml_theme'),
            'admin_menu_page_title' => __("Product Registration", 'wpml_theme'),
            'search_by_code'        => __("Search by registration code", 'wpml_theme'),
            'search_by_data'        => __("Search by user data", 'wpml_theme'),
            'admin_confirm_text'    => __("Are you sure you want to edit these values?", 'wpml_theme'),
            'admin_confirm'         => __("Yes, Update please", 'wpml_theme'),
            
            'thanks'                => __("Thank you for rgeistaring your product.", 'wpml_theme'),
            'saved'                 => __("Saved succesfully!", 'wpml_theme'),
            'save_error'            => __("Data could not be saved. Please try later", 'wpml_theme')
            
        );
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
     * Run on plugin activation
     */
    function activate() {
        if(!get_option(GGPR_OPTION_NAME)){
            add_option(GGPR_OPTION_NAME, $this->default_options());
        }
        do_action( 'ggpr_activate' );
    }

    /**
     * Run on plugin deactivation
     */
    function deactivate() {
        //delete_option(GGPR_OPTION_NAME);
        do_action( 'ggpr_deactivate' );
    }

    /**
     * Run on plugin deactivation
     */
    static function uninstall() {
        do_action( 'ggpr_uninstall' );	
    }

}