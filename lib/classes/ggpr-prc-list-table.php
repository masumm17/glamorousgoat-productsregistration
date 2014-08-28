<?php
class GGRP_Product_List_Table extends GGPR_List_Table {
    public $admin_page_url;
    public $raw_items;
    public $options;
    /**
     * Constructor
     */
    public function __Construct(){
            parent::__construct( array(
                    'singular'	=> 'product',
                    'plural'	=> 'products',
                    'ajax'      => false
            ));
    }

    /**
     * prepare columns to dispaly data
     * @return array
     */
    function get_columns(){
        //Iclude Checkbox for bulk action
        $columns['cb'] = '<input type="checkbox" />';
        //Now prepare columns for table heading
        $columns['RegistrationCode']    = $this->options['regi_code'];
        $columns['Name']                = $this->options['name'];
        $columns['Address']             = $this->options['address'];
        $columns['PostalCode']          = $this->options['postal_code'];
        $columns['City']                = $this->options['city'];
        $columns['Country']             = $this->options['country'];
        $columns['PhoneNo']             = $this->options['phone_no'];
        $columns['EmailAddress']        = $this->options['emai'];
        $columns['InvoiceNo']           = $this->options['invoice_no'];
        $columns['Supplier']            = $this->options['supplier'];
        $columns['ActualSupplier']      = $this->options['actual_supplier_field_title'];
        $columns['DateOfPurchase']      = $this->options['purchase_date'];
        $columns['DateOfRegistration']  = $this->options['regi_date'];
        //You did your part return your outcome
        return $columns;
    }
    /**
     * Prepare rows fields with column value
     */
    function prepare_items() {
            
        //Get columns to display
        $columns = $this->get_columns();
        //Get hidden colulmns
        $hidden = array();
        //Get sortable columns
        $sortable = $this->get_sortable_columns();
        //Generate columns headers
        $this->_column_headers = array($columns, $hidden, $sortable);
        //All set now get the rows of table
        //It's noting, it's the leads
        $this->items = $this->get_items();
    }
    /**
     * 
     */
    public function get_items(){
        if(is_array($this->raw_items))
            return $this->raw_items;
        return array(
            'RegistrationCode'  => '',
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
            'ActualSupplier'        => '',
            'DateOfPurchase'        => '',
            'DateOfRegistration'    =>''
        );
    }
    /**
     * prepare sortable colmns
     * @return array
     */
    function get_sortable_columns() {
            $sortable_columns = array();
            return $sortable_columns;
    }
    /**
     * get options bulk action
     * @return string
     */
    function get_bulk_actions() {
        $actions = array(
                'bulk_delete'    => 'Empty'
        );
        return $actions;
    }
    /**
     * checkbox column content
     * @param array $item
     * @return string
     */
    function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="product_regis[]" value="%s" />', $item['RegistrationCode']
        );    
    }
    /**
     * Action colum content
     * @param array $item
     * @return string
     */
    function column_RegistrationCode($item) {
        $nonce = wp_create_nonce('ggpr-actions');
        $item_data = '<strong><a title="Edit" href="'. add_query_arg(array('ggpr_action'=>'edit', 'ggpr_code'=>$item['RegistrationCode']), $this->admin_page_url) .'" class="row-title">'. $item['RegistrationCode'] .'</a></strong>';
        $actions  = '<div class="row-actions">';
        $actions .=   '<span class="edit"><a title="Edit" href="'. add_query_arg(array('ggpr_action'=>'edit', 'ggpr_code'=>$item['RegistrationCode']), $this->admin_page_url) .'" class="edit">Edit</a></span> | ';
        $actions .=  ' <span class="trash"><a title="Empty" href="'. add_query_arg(array('ggpr_action'=>'empty', 'ggpr_code'=>$item['RegistrationCode'], '_wpnonce'=>$nonce), $this->admin_page_url) .'" class="submitdelete">Empty</a></span>';
        $actions .= '</div>';
        return $item_data.$actions;
    }

    /**
     * It is not certain to the name of the field.
     * So this is used to output te fields value.
     * @param array $item
     * @param string $column_name
     * @return string
     */
    function column_default( $item, $column_name ) {
        return $item[$column_name];
    }
    /**
     * Generate and display the Form fitler and Post filter.
     * @param string $which
     */
    public function extra_tablenav( $which ) {
        // Pagination
    }
}