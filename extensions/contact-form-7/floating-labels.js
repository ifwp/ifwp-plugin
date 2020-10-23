function ifwp_floating_labels_select(select){
    if(jQuery(select).val() == ''){
        jQuery(select).removeClass('placeholder-hidden');
    } else {
        jQuery(select).addClass('placeholder-hidden');
    }
}
function ifwp_floating_labels_textarea(textarea){
    var height = parseInt(jQuery(textarea).data('element'))
        padding = parseInt(jQuery(textarea).data('padding')),
        scroll = textarea.scrollHeight;
    jQuery(textarea).height(height).height(scroll - padding);
}
function ifwp_floating_labels(){
    if(jQuery('.ifwp-floating-labels > textarea').length){
        jQuery('.ifwp-floating-labels > textarea').each(function(){
            ifwp_floating_labels_textarea(this);
        });
    }
    if(jQuery('.ifwp-floating-labels > select').length){
        jQuery('.ifwp-floating-labels > select').each(function(){
            ifwp_floating_labels_select(this);
        });
    }
}
jQuery(function($){
    $('.ifwp-floating-labels > textarea').each(function(){
        var height = $(this).height(), // margin: no, border: no, padding: no, element: yes
            innerHeight = $(this).innerHeight(), // margin: no, border: no, padding: yes, element: yes
            outerHeight = $(this).outerHeight(); // margin: no, border: yes, padding: yes, element: yes
        $(this).data({
            'border': outerHeight - innerHeight,
            'element': height,
            'padding': innerHeight - height,
        });
    });
    ifwp_floating_labels();
    $(document).on(ifwp_page_visibility_event(), ifwp_floating_labels);
    $('.ifwp-floating-labels > textarea').on('input propertychange', function(){
        ifwp_floating_labels_textarea(this);
    });
    $('.ifwp-floating-labels > select').on('change', function(){
        ifwp_floating_labels_select(this);
    });
});
