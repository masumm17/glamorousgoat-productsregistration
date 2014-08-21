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
