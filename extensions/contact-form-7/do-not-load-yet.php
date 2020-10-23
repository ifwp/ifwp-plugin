<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// classes
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('IFWP_Contact_Form_7')){
    class IFWP_Contact_Form_7 {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function add_form_tag_text(){
            wpcf7_add_form_tag(['text', 'text*', 'email', 'email*', 'url', 'url*', 'tel', 'tel*'], [__CLASS__, 'text_form_tag_handler'], [
               'name-attr' => true,
           ]);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function ifwp_plugin_loaded(){

		}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function load(){
			add_action('ifwp_plugin_loaded', [__CLASS__, 'ifwp_plugin_loaded']);
            add_action('plugins_loaded', [__CLASS__, 'plugins_loaded']);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function plugins_loaded(){
            remove_action('wpcf7_init', 'wpcf7_add_form_tag_text', 10, 0);
            add_action('wpcf7_init', [__CLASS__, 'add_form_tag_text'], 10, 0);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function text_form_tag_handler($tag){
            $html = wpcf7_text_form_tag_handler($tag);
            $ifwp = $tag->get_option('ifwp');
            if($ifwp){
                if(in_array('floating_labels', $ifwp)){
                    $placeholder = 'Placeholder goes here';
    				if($tag->has_option('placeholder') or $tag->has_option('watermark')){
    					if($tag->values){
    						$placeholder = $tag->values[0];
    					}
    				}
                    $html = str_get_html($html);
                    $span = $html->find('.wpcf7-form-control-wrap', 0);
                    $input = $span->find('input', 0);
        			$input->addClass('form-control mw-100');
        			$input->outertext = $input->outertext . '<label class="rounded">' . $placeholder . '</label>';
                    $html = '<div class="ifwp-floating-labels ' . $span->class . '">' . $span->innertext . '</div>';
                }
            }
            return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// loader
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

IFWP_Contact_Form_7::load();
