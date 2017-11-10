define([
    "jquery"
], function ($) {
    var mageJsComponent = function (config, node)
    {
        $.ajax({
            url: '/megamenu/index/index',
            cache: false,
            data: {},
            dataType: 'json'
        }).success(function (e) {
            $.each(e, function (index, item) {
                jQuery('li[data-category-id='+item.id+']').append(item.submenu_html)
            });
            
            $(node).menu('refresh');
        });
    };

    return mageJsComponent;
});