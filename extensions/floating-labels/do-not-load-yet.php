<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// classes
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('IFWP_Floating_Labels')){
    class IFWP_Floating_Labels {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static protected
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static protected function mb_get_placeholder($field = []){
            if(!empty($field['placeholder'])){
                return $field['placeholder'];
            }
            if(!empty($field['name'])){
                return $field['name'];
            }
            if(!empty($field['id'])){
                return $field['id'];
            }
            return 'Placeholder goes here';
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static protected function mb_is_text($field){
			return in_array($field['type'], ['email', 'number', 'password', 'tel', 'text']);
		}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function add_form_tag_select(){
            wpcf7_add_form_tag(['select', 'select*'], [__CLASS__, 'select_form_tag_handler'], [
               'name-attr' => true,
               'selectable-values' => true,
           ]);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function add_form_tag_text(){
            wpcf7_add_form_tag(['text', 'text*', 'email', 'email*', 'url', 'url*', 'tel', 'tel*'], [__CLASS__, 'text_form_tag_handler'], [
               'name-attr' => true,
           ]);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function add_form_tag_textarea(){
            wpcf7_add_form_tag(['textarea', 'textarea*'], [__CLASS__, 'textarea_form_tag_handler'], [
               'name-attr' => true,
           ]);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function load(){
            add_action('plugins_loaded', [__CLASS__, 'plugins_loaded']);
            add_filter('rwmb_outer_html', [__CLASS__, 'rwmb_outer_html'], 20, 2);
            add_action('wp_enqueue_scripts', [__CLASS__, 'wp_enqueue_scripts']);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function plugins_loaded(){
            remove_action('wpcf7_init', 'wpcf7_add_form_tag_select', 10, 0);
            remove_action('wpcf7_init', 'wpcf7_add_form_tag_text', 10, 0);
            remove_action('wpcf7_init', 'wpcf7_add_form_tag_textarea', 10, 0);
            add_action('wpcf7_init', [__CLASS__, 'add_form_tag_select'], 10, 0);
            add_action('wpcf7_init', [__CLASS__, 'add_form_tag_text'], 10, 0);
            add_action('wpcf7_init', [__CLASS__, 'add_form_tag_textarea'], 10, 0);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function rwmb_outer_html($outer_html, $field){
			if(!is_admin()){
                if(!empty($field['ifwp']['floating_labels'])){
                    if(self::mb_is_text($field)){
                        $placeholder = self::mb_get_placeholder($field);
                        if(function_exists('str_get_html')){
                            $html = str_get_html($outer_html);
                            $input = $html->find('input', 0);
                            $input->addClass('form-control');
                            $input->outertext = '<div class="ifwp-floating-labels">' . $input->outertext . '<label class="rounded">' . $placeholder . '</label></div>';
                            $outer_html = $html;
                        }
                    }
                }
            }
            return $outer_html;
		}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function select_form_tag_handler($tag){
            $html = wpcf7_select_form_tag_handler($tag);
            $ifwp = $tag->get_option('ifwp');
            if($ifwp){
                if(in_array('floating_labels', $ifwp)){
                    $placeholder = 'Placeholder goes here';
    				if($tag->values and $tag->values[0] == ''){
    					$placeholder = $tag->labels[0];
    				} else {
                        array_unshift($tag->labels, $placeholder);
		                array_unshift($tag->values, '');
                    }
                    if(function_exists('str_get_html')){
                        $html = str_get_html($html);
                        $span = $html->find('.wpcf7-form-control-wrap', 0);
                        $select = $span->find('select', 0);
            			$select->addClass('custom-select');
            			$select->outertext = $select->outertext . '<label class="rounded">' . $placeholder . '</label>';
                        $html = '<div class="ifwp-floating-labels ' . $span->class . '">' . $span->innertext . '</div>';
                    }
                }
            }
            return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function text_form_tag_handler($tag){
            $html = wpcf7_text_form_tag_handler($tag);
            $ifwp = $tag->get_option('ifwp');
            if($ifwp){
                if(in_array('floating_labels', $ifwp)){
                    $placeholder = 'Placeholder goes here';
    				if($tag->values and $tag->has_option('placeholder') or $tag->has_option('watermark')){
    					$placeholder = $tag->values[0];
    				}
                    if(function_exists('str_get_html')){
                        $html = str_get_html($html);
                        $span = $html->find('.wpcf7-form-control-wrap', 0);
                        $input = $span->find('input', 0);
            			$input->addClass('form-control');
            			$input->outertext = $input->outertext . '<label class="rounded">' . $placeholder . '</label>';
                        $html = '<div class="ifwp-floating-labels ' . $span->class . '">' . $span->innertext . '</div>';
                    }
                }
            }
            return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function textarea_form_tag_handler($tag){
            $html = wpcf7_textarea_form_tag_handler($tag);
            $ifwp = $tag->get_option('ifwp');
            if($ifwp){
                if(in_array('floating_labels', $ifwp)){
                    $placeholder = 'Placeholder goes here';
    				if($tag->values and $tag->has_option('placeholder') or $tag->has_option('watermark')){
    					$placeholder = $tag->values[0];
    				}
                    if(function_exists('str_get_html')){
                        $html = str_get_html($html);
                        $span = $html->find('.wpcf7-form-control-wrap', 0);
                        $textarea = $span->find('textarea', 0);
            			$textarea->addClass('form-control');
            			$textarea->outertext = $textarea->outertext . '<label class="rounded">' . $placeholder . '</label>';
                        $html = '<div class="ifwp-floating-labels ' . $span->class . '">' . $span->innertext . '</div>';
                    }
                }
            }
            return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function wp_enqueue_scripts(){
            $ver = filemtime(plugin_dir_path(__FILE__) . 'floating-labels.css');
            wp_enqueue_style('ifwp-floating-labels', plugin_dir_url(__FILE__) . 'floating-labels.css', [], $ver);
            $ver = filemtime(plugin_dir_path(__FILE__) . 'floating-labels.js');
            wp_enqueue_script('ifwp-floating-labels', plugin_dir_url(__FILE__) . 'floating-labels.js', ['jquery'], $ver, true);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// loader
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

IFWP_Floating_Labels::load();
