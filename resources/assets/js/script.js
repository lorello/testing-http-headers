(function($) {
    $(function(){
    });
})(jQuery);

function call1(url_tocall) {
  jQuery.noConflict();
  (function($) {
    var geturl;
    geturl = $.ajax({
        url : url_tocall,
        success : function (data,stato) {
            $("#boxcontent").html(data);
            $("#callstatus").html('<div class="alert alert-info"><button class="close" data-dismiss="alert">×</button>'+ stato + '</div>');
            var res = []; 
            res = geturl.getAllResponseHeaders().split("\n"); //replace(/\n/g,"<br />");

            for (i=0; i < res.length; i++) {
              if (res[i]) {
                if (res[i].match(/^X/)) {
                  $("#browserheaders").append("<br />["+i+"] "+res[i]+" ");
                }
              }
            }
        },
        error : function (richiesta,stato,errori) {
            alert("Error occurred, call state is: "+stato);
            $("#callstatus").html('<div class="alert alert-error"><button class="close" data-dismiss="alert">×</button>'+ stato + '</div>');
        }
    }); 
  })(jQuery);
}

