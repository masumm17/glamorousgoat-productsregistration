<?php

/**
 * 
 * @param string $class
 * @param string $current
 * @param array $checks
 * @param bool $echo
 * @return string
 */
function ggpr_conditional_class($class='', $current='', $checks=array(), $echo = true){
    if(($class==='') || ($current===''))
        return '';
    if(!is_array($checks))
        return '';
    
    if(!in_array($current, $checks)){
        return '';
    }
    if($echo)
        echo " {$class} ";
    else 
        return $class;
}

function ggpr_default_options(){
    return array(
        'mail_subject'          => "Product registered!",
        'mail_body'             => "Thank you for registering your product",
        'mail_from'             => get_option('admin_email'),

        'suppliers_margin'      => 20,

        'regi_code'             => __("Registration Code", 'wpml_theme'),
        'is_used'               => __("Used", 'wpml_theme'),
        'not_used'               => __("Not Used", 'wpml_theme'),
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
        'admin_suppliers_title' => __("Add Actual Supplier", 'wpml_theme'),
        'admin_search_title'    => __("Search", 'wpml_theme'),
        'admin_edit_title'      => __("Edit Product Registraion Data", 'wpml_theme'),
        'search_by_title'       => __("Search by any of the following fields", 'wpml_theme'),
        'search_results_title'  => __("The following Products found", 'wpml_theme'),
        'regi_code_range_start' => __("Product registration code start"),
        'regi_code_range_end'   => __("Product registration code end"),
        'actual_supplier_field_title'=> __("Actual Supplier"),
        'regi_code_range_start_ne' => __("Product registration code start does not exists"),
        'regi_code_range_end_ne'   => __("Product registration code end does not exists"),
        
        'admin_confirm_text'    => __("Are you sure you want to edit these values?", 'wpml_theme'),
        'thanks'                => __("Thank you for rgeistaring your product.", 'wpml_theme'),
        'admin_updated_success' => __("Saved succesfully!", 'wpml_theme'),
        'admin_updated_failed'  => __("Data could not be saved. Please try later", 'wpml_theme'),
        'admin_code_changed'    => __("You can not change the product registration codes.", 'wpml_theme'),
        'admin_empty_success'   => __("Product registraion code row is emptied succesfully!", 'wpml_theme'),
        'admin_empty_failed'    => __("Product registraion code row could not be emptied. Please try later", 'wpml_theme'),
        'admin_empty_code'      => __("No product registraion code found.", 'wpml_theme'),
        'admin_empty_sf'        => __("You must enter atleast one value.", 'wpml_theme'),
        'nothing_found'         => __("No data found.'", 'wpml_theme'),
        'admin_empty_fields'    => __("Please fill all the fields"),
        'admin_suppliers_added'    => __("Suppliers added successfully.")

    );
}