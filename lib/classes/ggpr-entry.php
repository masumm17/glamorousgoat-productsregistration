<?php

class GGPR_Entries {
    public function get_product_data($col_search = array()){
        global $wpdb;
        // Check if any of the column is provided
        if(empty($col_search))
            return FALSE;
        
    }
    /**
     * Save product registratin data for a single products
     * @global obj $wpdb
     * @param array $codes
     * @param array $data
     * @return boolean
     */
    public function save_product_data($code = '', $data = array()){
        global $wpdb;
        if( empty($code) || empty($data) || !is_array($data))
            return false;
        
        $format_values = array();
        $set_place_holders = array();
        $in_placeholders = array();
        // Prepare SET components
        foreach ($data as $col_name => $col_value){
            if('IsUsed' == $col_name){
                $set_place_holders[] = "prc.IsUsed=%d";
            }else{
                $set_place_holders[] = "prc.{$col_name}=%s";
            }
            $format_values[] = $col_value;
        }
        // Now append the last format value for the $code iteself
        $format_values[] = $code;
        // Make the prepare formatted placeholder part
        $set_place_holders = implode(', ', $set_place_holders);
        
        // Make sql SET part
        $set = " SET {$set_place_holders} ";
        // Make sql WHERE part
        $where = " WHERE (1=1) AND (prc.RegistrationCode='%s')";
        // Combine alll to make sql prepare string
        $query = "UPDATE " . GGPR_TABLE_NAME ." AS prc " . $set . $where;
        // Prepare the sql for database query
        $query = $wpdb->prepare($query, $format_values);
        // Get resutls from database
        return $wpdb->query($query);
    }

    /**
     * Save product registratin data for multiple products
     * @global obj $wpdb
     * @param array $codes
     * @param array $data
     * @return boolean
     */
    public function save_multiple_product_data($codes = array(), $data = array()){
        global $wpdb;
        if( empty($codes) || !is_array($codes) || empty($data) || !is_array($data))
            return false;
        
        $format_values = array();
        $set_place_holders = array();
        $in_placeholders = array();
        // Prepare SET components
        foreach ($data as $col_name => $col_value){
            if('IsUsed' == $col_name){
                $set_place_holders[] = "prc.IsUsed=%d";
            }else{
                $set_place_holders[] = "prc.{$col_name}=%s";
            }
            $format_values[] = $col_value;
        }
        
        $set_place_holders = implode(', ', $set_place_holders);
        
        // Prepare IN component
        foreach($codes as $code){
            $in_placeholders[] = '%s';
            $format_values[] = $code;
        }
        $in_placeholders = implode(',', $in_placeholders);
        
        $set = " SET {$set_place_holders} ";
        
        $where = " WHERE (1=1) AND (prc.RegistrationCode IN ({$in_placeholders}))";
        
        $query = "UPDATE " . GGPR_TABLE_NAME ." AS prc " . $set . $where;
        // Prepare the sql for database query
        $query = $wpdb->prepare($query, $format_values);
        // Get resutls from database
        return $wpdb->query($query);
    }
    
    /**
     * Check if reigstration code exists
     * @global obj $wpdb
     * @param string $code
     */
    public function code_exists($code){
        global $wpdb;
        $query = $wpdb->prepare("SELECT count(prc.RegistrationCode) as count FROM " . GGPR_TABLE_NAME ." AS prc WHERE (1=1) AND (prc.RegistrationCode=%s)", $code);
        $results = $wpdb->get_col($query);
        
        if(isset($results[0])){
            return (bool)$results[0];
        }
        return true;
    }
    
    /**
     * Check if reigstration code is used
     * @global obj $wpdb
     * @param string $code
     */
    public function code_used($code){
        global $wpdb;
        $query = $wpdb->prepare("SELECT count(prc.RegistrationCode) as count FROM " . GGPR_TABLE_NAME ." AS prc WHERE (1=1) AND (prc.RegistrationCode=%s) AND (prc.IsUsed = 1)", $code);
        $results = $wpdb->get_col($query);
        
        if(isset($results[0])){
            return (bool)$results[0];
        }
        return true;
    }
}