<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// classes
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('RWMB_Col_Close_Field')){
	class RWMB_Col_Close_Field extends RWMB_Field {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function html($meta, $field){
            return '';
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function outer_html($outer_html){
            if(!is_admin()){
        		$outer_html = '</div>';
        	}
        	return $outer_html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('RWMB_Col_Open_Field')){
	class RWMB_Col_Open_Field extends RWMB_Field {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function html($meta, $field){
            return '';
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function outer_html($outer_html, $field){
            if(!is_admin()){
                $classes = [];
            	foreach(['col', 'col-sm', 'col-md', 'col-lg', 'col-xl', 'offset', 'offset-sm', 'offset-md', 'offset-lg', 'offset-xl'] as $class){
            		if(array_key_exists($class, $field)){
            			if(is_numeric($field[$class])){
            				if(intval($field[$class]) >= 1 and intval($field[$class]) <= 12){
            					$classes[] = $class . '-' . $field[$class];
            				}
            			}
            		}
            	}
            	if(!$classes){
            		$classes[] = 'col';
            	}
            	$outer_html = '<div class="' . implode(' ', $classes) . '">';
        	}
        	return $outer_html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('RWMB_Raw_Html_Field')){
	class RWMB_Raw_Html_Field extends RWMB_Field {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function html($meta, $field){
            return '';
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function outer_html($outer_html, $field){
            if(!is_admin()){
                if(!empty($field['hide_on_mobile']) and wp_is_mobile()){
            		$outer_html = '';
            	} else {
                    if(!empty($field['std'])){
                		$outer_html = $field['std'];
                	} else {
                        $outer_html = '';
                    }
                }
        	}
        	return $outer_html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('RWMB_Row_Close_Field')){
	class RWMB_Row_Close_Field extends RWMB_Field {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function html($meta, $field){
            return '';
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function outer_html($outer_html){
            if(!is_admin()){
        		$outer_html = '</div>';
        	}
        	return $outer_html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('RWMB_Row_Open_Field')){
    class RWMB_Row_Open_Field extends RWMB_Field {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function html($meta, $field){
            return '';
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function outer_html($outer_html){
            if(!is_admin()){
        		$outer_html = '<div class="form-row">';
        	}
        	return $outer_html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}
