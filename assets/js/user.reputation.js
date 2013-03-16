(function(){
    if ( window.userReputation ) var userReputation = window.userReputation;
    
    var userReputation = window.userReputation = function() {return new userReputation.init();};
    
    userReputation.apply = function(o, c, defaults){
            if(defaults){userReputation.apply(o, defaults);}
            if(o && c && typeof c == 'object'){for(var p in c){o[p] = c[p];}}
            return o;
    };
    
    userReputation.apply(userReputation, {
        version: '1.0.0',
        
        init: function() {
            return this.version;
        },
        
        initialize: function() {
            var History = window.History;

            History.Adapter.bind(window, 'statechange', function(){
                var State = History.getState();

                var page = 1;
                if (typeof State.data.page != 'undefined')
                {
                    page = State.data.page;
                }
                
                jQuery.ajax({
                    type: 'POST',
                    url: urAjaxEndpoint,
                    dataType: 'html',
                    data: {
                        action: urPrefix + 'pagination_history',
                        user: urCurrentPageUserID,
                        page: page,
                        limit: urNumItemPerPage
                    },
                    success: function(html) {
                        jQuery('#user-reputation-history').parent().html(html);

                        jQuery(document.body).animate({
                            'scrollTop': jQuery('#user-reputation-history').offset().top - 50
                        }, 500, 'swing');

                        userReputation.setHistoryPaginationEvent();
                    },
                    error: function(){}
                });
            });

            userReputation.setHistoryPaginationEvent();
        },

        setHistoryPaginationEvent: function() {
            jQuery('.ur-history-pagination a').click(function(e) {
                e.preventDefault();

                var url = jQuery.url(jQuery(this).attr('href'));
                
                History.pushState(url.param(), 'Pagination', '?' + url.attr('query'));
            });
        },

        updateQueryStringParameter: function(uri, key, value) {
            var re = new RegExp("([?|&])" + key + "=.*?(&|$)", "i");
            separator = uri.indexOf('?') !== -1 ? "&" : "?";
            if (uri.match(re)) {
                return uri.replace(re, '$1' + key + "=" + value + '$2');
            }
            else {
                return uri + separator + key + "=" + value;
            }
        },
        
        noop: function() {}
    });
    
    userReputation.options = userReputation.options || {};
})();

jQuery(document).ready(function() {
    userReputation.initialize();
});
