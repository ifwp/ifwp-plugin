<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// classes
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('IFWP_Meta_Box')){
    class IFWP_Meta_Box {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static protected
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static protected function format_single_value($field, $value, $args, $post_id){
            if($field['timestamp']){
                $value = self::from_timestamp($value, $field);
            } else {
                $value = [
                    'timestamp' => strtotime($value),
                    'formatted' => $value,
                ];
            }
            return empty($args['format']) ? $value['formatted'] : date_i18n($args['format'], $value['timestamp']);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static protected function format_value($field, $value, $args, $post_id){
            if(!$field['multiple']){
                return self::format_single_value($field, $value, $args, $post_id);
            }
            $output = '<ul>';
            foreach($value as $single){
                $output .= '<li>' . self::format_single_value($field, $single, $args, $post_id) . '</li>';
            }
            $output .= '</ul>';
            return $output;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static protected function from_timestamp($meta, $field){
            return [
                'timestamp' => $meta ? $meta : null,
                'formatted' => $meta ? date_i18n($field['php_format'], intval($meta)) : '',
            ];
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static protected function the_value($value, $field, $args, $object_id){
            $types = [
                'date' => 'RWMB_Date_Field',
                'datetime' => 'RWMB_Datetime_Field',
            ];
            if(array_key_exists($field['type'], $types)){
                $value = call_user_func([$types[$field['type']], 'get_value'], $field, $args, $object_id);
                if(false === $value){
                    return '';
                }
                return self::format_value($field, $value, $args, $object_id);
            }
            return $value;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function ifwp_plugin_loaded(){
			$meta_box = ifwp_tab('Meta Box');
			$meta_box->add_field('add_custom_field_types', [
                'label_description' => 'Custom field types: col_close, col_open, raw_html, row_close, row_open.',
				'name' => 'Add custom field types?',
				'type' => 'switch',
			]);
            if($meta_box->add_custom_field_types){
                add_action('init', function(){
                    if(class_exists('RWMB_Field')){
                        require_once(plugin_dir_path(__FILE__) . 'custom-field-types.php');
                    }
                });
                add_filter('rwmb_col_close_outer_html', ['RWMB_Col_Close_Field', 'outer_html'], 20);
                add_filter('rwmb_col_open_outer_html', ['RWMB_Col_Open_Field', 'outer_html'], 20, 2);
                add_filter('rwmb_raw_html_outer_html', ['RWMB_Raw_Html_Field', 'outer_html'], 20, 2);
                add_filter('rwmb_row_close_outer_html', ['RWMB_Row_Close_Field', 'outer_html'], 20);
                add_filter('rwmb_row_open_outer_html', ['RWMB_Row_Open_Field', 'outer_html'], 20);
            }
            $meta_box->add_field('center_submit_buttons', [
				'name' => 'Center submit buttons?',
				'type' => 'switch',
			]);
            if($meta_box->center_submit_buttons){
                add_action('rwmb_frontend_before_submit_button', function($config){
                	 if(!is_admin()){
                		echo '<div class="text-center">';
                	}
                }, 20);
                add_action('rwmb_frontend_after_submit_button', function($config){
                	 if(!is_admin()){
                		echo '</div>';
                	}
                }, 20);
            }
            $meta_box->add_field('use_date_i18n', [
				'name' => 'Use date_i18n instead of gmdate?',
				'type' => 'switch',
			]);
            if($meta_box->use_date_i18n){
                add_filter('rwmb_the_value', [__CLASS__, 'the_value'], 20, 4);
            }
            $meta_box->add_field('use_select2_full', [
				'name' => 'Use select2.full instead of select2?',
				'type' => 'switch',
			]);
            if($meta_box->use_select2_full){
                add_action('rwmb_enqueue_scripts', function(){
        			if(wp_script_is('rwmb-select2', 'enqueued')){
        				wp_dequeue_script('rwmb-select2');
        			}
        			if(wp_script_is('rwmb-select2', 'registered')){
        				wp_deregister_script('rwmb-select2');
        			}
        			wp_register_script('rwmb-select2', plugin_dir_url(__FILE__) . 'select2.full.min.js', ['jquery'], '4.0.10', true);
        			wp_enqueue_script('rwmb-select2'); // $field['js_options']['containerCssClass'] = 'form-control-lg';
                });
            }
            add_action('rwmb_enqueue_scripts', function(){
                wp_register_style('select2-bootstrap', plugin_dir_url(__FILE__) . 'select2-bootstrap.min.css', ['rwmb-select2'], '1.0.0');
    			wp_enqueue_style('select2-bootstrap'); // $field['js_options']['theme'] = 'bootstrap';
            });
		}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function load(){
			add_action('ifwp_plugin_loaded', [__CLASS__, 'ifwp_plugin_loaded']);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// loader
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

IFWP_Meta_Box::load();
