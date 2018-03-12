function sendNotify(type, icon, title, message, url){
    $.notify({
        icon: icon,
        title: title,
        message: message,
        url: url
    },{
        element: 'body',
        type: type,
        allow_dismiss: true,
        placement: {
            from: "bottom",
            align: "right"
        },
        offset: {
            x: 20,
            y: 20
        },
        spacing: 10,
        z_index: 1031,
        delay: 2500,
        timer: 5000,
        url_target: '_blank',
        mouse_over: false,
        animate: {
            enter: "animated bounceInUp",
            exit: "animated fadeOutDown"
        },
        template:   '<div data-notify="container" class="alert alert-dismissible alert-{0} alert--notify" role="alert">' +
        '<span data-notify="icon"></span> ' +
        '<span data-notify="title">{1}</span> ' +
        '<span data-notify="message">{2}</span>' +
        '<div class="progress" data-notify="progressbar">' +
        '<div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>' +
        '</div>' +
        '<a href="{3}" target="{4}" data-notify="url"></a>' +
        '<button type="button" aria-hidden="true" data-notify="dismiss" class="alert--notify__close"><i class="zmdi zmdi-close"></i></button>' +
        '</div>'
    });
}

function sendAlert(title, text, type){
    swal({
        title: title,
        html: text,
        type: type,
        buttonsStyling: false,
        confirmButtonClass: 'btn btn-primary',
        confirmButtonText: 'Փակել'
    });
}




$(function(){
    $(".select2-ajax").each(function(){
        intSelect2AJAX(this);
    });
    $(".select2-main").each(function(){
        intSelect2MAIN(this);
    });
    
    $("body").on('DOMNodeInserted', function(e) {
        $(e.target).find(".select2-ajax").each(function(){
            intSelect2AJAX(this);
        });
        $(e.target).find(".select2-main").each(function(){
            intSelect2MAIN(this);
        });
    });
    
    function intSelect2MAIN(element){
        $(element).select2({
            width: '100%',
            dropdownAutoWidth: true
        });
    }
    
    function intSelect2AJAX(element){
        $(element).select2({
            minimumInputLength: 0,
            maximumSelectionLength: 15,
            language: {
                noResults: function (params) {
                  return "չի գտնվել";
                }
            },
            tags: (typeof element.dataset.customtag!=="undefined") ? true : false,
            //createTag: function(params) {
            //    return undefined;
            //},
            ajax: {
                url: '/',
                dataType: 'json',
                type: "GET",
                quietMillis: 50,
                data: function (term){
                    return {
                        cmd: "getListJSON",
                        group: this[0].dataset.group,
                        parent: this[0].dataset.parent,
                        optgroup: (typeof this[0].dataset.optgroup!=="undefined") ? true : false,
                        query: term.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function(item) {
                            return item;
                        })
                    };
                }
            }
        });
    }
});




$(function(){
    if($(".content--full").length == 0){
        $(".navigation-trigger").addClass("hidden-xl-up");
        $(".sidebar").removeClass("sidebar--hidden");
    }else{
        $(".navigation-trigger").removeClass("hidden-xl-up");
        $(".sidebar").addClass("sidebar--hidden");
    }
}); 

$(function(){ 
    $("body").on("blur",".form-group--float .form-control",function(){
        (0==$(this).val().length) ? $(this).removeClass("form-control--active") : $(this).addClass("form-control--active")
    });
    $("body").on('DOMNodeInserted', function(e) {
        $(e.target).find(".form-group--float .form-control").each(function(){
            (0==$(this).val().length) ? $(this).removeClass("form-control--active") : $(this).addClass("form-control--active")
        });
    });
});

$(function(){ 
    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });
    $('body').popover({
        selector: '[data-toggle="popover"]',
        trigger: 'hover'
    });
}); 

$(function(){ 
    $(".owl-carousel").owlCarousel({
        items:1,
        loop:false,
        autoplay:false,
        nav: true,
        navText: ['<i class="zmdi zmdi-chevron-left"></i>','<i class="zmdi zmdi-chevron-right"></i>']
    });
    
    $("body").on('DOMNodeInserted', function(e) {
        if($(e.target).find(".owl-carousel").length > 0){
            $(e.target).find(".owl-carousel").owlCarousel({
                items:1,
                loop:false,
                autoplay:false,
                nav: true,
                navText: ['<i class="zmdi zmdi-chevron-left"></i>','<i class="zmdi zmdi-chevron-right"></i>']
            });
        }
    });
}); 

$(function(){ 
    $("body").on('DOMNodeInserted', function(e) {
        if($(e.target).find(".lightbox").length > 0){
            $(e.target).find(".lightbox").lightGallery(); 
        }
    });
});




// Mobile aside

$(window).on("load resize", function(e){
   if($(".top-nav__notifications").length > 0){
       if($(document).outerWidth() >= 768){
           $(".top-nav__notifications").find("> a").attr("data-toggle", "dropdown");
           $(".top-nav__notifications").find("> a").removeAttr("data-ma-action");
           $(".top-nav__notifications").find("> a").removeAttr("data-ma-target");
       }else{
           $(".top-nav__notifications").find("> a").removeAttr("data-toggle");
           $(".top-nav__notifications").find("> a").attr("data-ma-action", "aside-open");
           $(".top-nav__notifications").find("> a").attr("data-ma-target", ".aside-notification");
       }
   }
});


// Sortable

$(document).ready(function() {
	$(".sortable").parent().sortable({
        items: '> .sortable',
		update : function () {
            $(".loading").fadeIn();
			$(this).find('.sortable').each(function(index){
                var sort = index;
                var table = $(this).data("sort").split(',')[0];
                var id_name = $(this).data("sort").split(',')[1];
                var id_value = $(this).data("sort").split(',')[2];
                $.post('?cmd=sortable', {table : table, id_name : id_name, id_value : id_value, sort : sort}, function(data){
                    $(".loading").fadeOut();
                });
            });
		}
	});
});


// Live change

$(document).ready(function(){
    $("input.live, textarea.live, select.live").on("change keyup", function(e){
        
        if(e.type == 'change'){
            if ($(".live-save").length == 0) {
                jQuery('<div/>', {
                    class: 'live-save',
                    html: '<i class="fa fa-check-circle-o"></i> Պահպանված է</div>'
                }).appendTo('body');
                $(".live-save").css({
                    position : 'absolute',
                    zIndex : '9999',
                    display : 'none',
                    background : '#2F8C00',
                    color : '#FFF',
                    boxShadow : '0 0 8px rgba(0, 0, 0, 0.3)',
                    borderRadius : '6px',
                    padding : '10px 30px'
                });
            }
            $(".live-save").css({
                top : ($(this).offset().top - 40) + 'px',
                left : ($(this).offset().left + $(this).width()/2-80) + 'px'
            });
            if($(".live-save").css("display") == 'none'){
               $(".live-save").fadeIn(300).delay(300).fadeOut(300);
            }
        }
          
        var table_name = $(this).data("live").split(',')[0];
        var field_name = $(this).data("live").split(',')[1];
        var id_name = $(this).data("live").split(',')[2];
        var id_value = $(this).data("live").split(',')[3];
        if($(this).attr("type")=="checkbox" && this.dataset.anyway==undefined){
            if($(this).is(":checked")){
                var field_value = $(this).val();
            }else{
               var field_value = ""; 
            }
        }else{
            var field_value = $(this).val();
        }
        if(table_name && field_name && id_name && id_value){
            $.post('?cmd=changeField', {
                table_name : table_name,
                field_name : field_name,
                id_name : id_name,
                id_value : id_value,
                field_value : field_value
            }, function(data){
            });
        }
    });
});


// Form ajax

$(document).on('submit', '.form-ajax', function(e){
    e.preventDefault();
    var form = this;
    $(form).find(".form-message").html("");
    $(form).find(".form-control").removeClass("form-control-warning");
    $(form).find(".form-control").parent().removeClass("has-warning");
    $(form).find(".page-loader").fadeIn();
    $(this).ajaxSubmit(function(data){
        $(form).find(".page-loader").fadeOut();
        var response = $.parseJSON(data);
        
        if(response.location !== false && typeof response.location !== "undefined"){
            if(response.location.hash !== false){
                sendAlert("Ok", "", "success");
            }else if(response.location.href !== false){
                sendAlert("Ok", "", "success");
            }else if(response.location.reload === true){
                if($("form.login").length > 0){
                    location.reload();
                }else{
                    sendAlert("Ok", "", "success");
                }
            }
        }
        
        if(response.alert !== false && typeof response.alert !== "undefined"){    
            sendAlert(response.alert.title, response.alert.message, response.alert.type);
        }
        
        if(response.error !== false && typeof response.error !== "undefined"){
            if(response.error.field !== false){
                response.error.field.forEach(function(index){
                    $(form).find("[name="+index+"]").addClass("form-control-warning");
                    $(form).find("[name="+index+"]").parent().addClass("has-warning");
                });
            }
            if(response.error.message !== false){
                $(form).find(".form-message").html(response.error.message).hide().fadeIn();
            }
        }
        
        if(response.reset === true){
            form.reset();
        }

    }); 
});


// Item scroll to load

$(function(e){
    $("[data-reload]").each(function(index, value){
        intScrollToLoad(this);
        if(Number($("."+this.dataset.box).find("[data-id]").length)==0){
           getLoad(this);
        }
    });
    
    $("body").on('DOMNodeInserted', function(e) {
        if($(e.target).find("[data-reload]").length > 0){
            var reload_btn = $(e.target).find("[data-reload]").get(0);
            intScrollToLoad(reload_btn);
            if(Number($("."+reload_btn.dataset.box).find("[data-id]").length)==0){
               getLoad(reload_btn);
            }
        }
    });
    
    $("body").on("click", "[data-reload]", function(e){
        e.stopPropagation();
        getLoad(this);
        e.preventDefault();
    });
});

function intScrollToLoad(reload_btn){
    var box = "."+reload_btn.dataset.box;
    var items_count = 0;
    if($(reload_btn).data("reload") == "content"){
        $(box).scroll(function(){
            if($(this).scrollTop() + 50 >= ($(this).prop('scrollHeight')-$(this).prop('clientHeight'))){
                if($(box).find("[data-id]").length != items_count){
                    getLoad(reload_btn);
                    items_count = $(box).find("[data-id]").length;
                }
            }
        }); 
    }else{
        $(window).scroll(function(){
            if ($(window).scrollTop() + $(window).height() + 50 >= $(box).height() + $(box).offset().top){
                if($(box).find("[data-id]").length != items_count){
                    getLoad(reload_btn);
                    items_count = $(box).find("[data-id]").length;
                }
            }
        });
    }
}

function getLoad(reload_btn){
     var box = "."+reload_btn.dataset.box;
     var start = Number($(box).find("[data-id]").length);
     var end = reload_btn.dataset.end;
     var refresh_icon = $(reload_btn).find("i");
     var refresh_icon_class = $(reload_btn).find("i").attr("class");
     var href = reload_btn.href+((reload_btn.href.includes("?")) ? "&" : "?")+'start='+start+'&end='+end+'';
     $(refresh_icon).removeClass(refresh_icon_class).addClass("zmdi zmdi-refresh zmdi-hc-spin");
     $.get(href, function(data){
         if(data.length > 0){
             $(box).append(data);
         }
         $(refresh_icon).removeClass("zmdi zmdi-refresh zmdi-hc-spin").addClass(refresh_icon_class);
     });
}

function getLoadUpdate(reload_btn){
     var box = "."+reload_btn.dataset.box;
     var start = 0;
     if(reload_btn.dataset.end >= Number($(box).find("[data-id]").length)){
         var end = reload_btn.dataset.end;
     }else{
         var end = Number($(box).find("[data-id]").length);
     }
     var href = reload_btn.href+((reload_btn.href.includes("?")) ? "&" : "?")+'start='+start+'&end='+end+'';
     $.get(href, function(data){
         $(box).html(data);
     });
}

// Overlay

$(window).on("load", function(e){
    getOverlay();
});
    
$(window).on("hashchange", function(e){
    
    if($('.modal.send-message').length > 0 && location.hash.search("sendMessage")){
        var url = location.hash.substring(1);
        $('.modal.send-message .messages__sidebar .listview > a').removeClass("listview__item--active");
        $('.modal.send-message .messages__sidebar .listview > a[href="#'+url+'"]').addClass("listview__item--active");
        $.get("/overlay/"+url, function(data){
            $(".modal .messages .messages__body").remove();
            $(".modal .messages").append($(data).find(".messages__body").get(0).outerHTML);
        });
        return false;
    }
       
    if($('.modal').length > 0){
        $(".modal").remove();
        $(".modal-backdrop").remove();
        $("body").removeClass("modal-open");
    }
    
    getOverlay();
});

function getOverlay(){
    var url = location.hash.substring(1);
    if(url != "" && url != "close"){
        $.get("/overlay/"+url, function(data){
            if($('.modal').length == 0){
                if(data != ""){
                    $("body").append(data);
                    $('.modal').modal('show');
                }else{
                    location.hash = "signIn";
                }
            }
        });
    }
}

$(document).on('hide.bs.modal','.modal', function (){
    history.pushState({}, '', "#");
});

$(document).on('show.bs.modal','.modal', function (){
});

$(document).on('shown.bs.modal','.modal', function (){
});

$(document).on('hidden.bs.modal','.modal', function (){
    $("body").removeClass("modal-open");
    $(".modal").remove();
    $(".modal-backdrop").remove();
});


// Photo

function addPhoto(group, parent, file, cat, act) {
    $(".loading").fadeIn();
	formdata = new FormData();
    formdata.append("group", group);
    formdata.append("parent", parent);
	for ( i = 0; i < file.files.length; i++ ) {
       formdata.append("file[]", file.files[i]);
    }
    formdata.append("cat", cat);
    formdata.append("act", act);
	$.ajax({
		url: "?cmd=addPhoto",
		type: "POST",
		data: formdata,
		processData: false,
		contentType: false,
		success: function (data) {
          location.reload();
		}
	});     
}

function removePhoto(cat, photoID){
    $.post('?cmd=removePhoto', {cat : cat, photoID : photoID}, function(data){
        location.reload();
    });
}


// Option

function removeOption(optionID){
    $.post('?cmd=removeOption', {optionID : optionID}, function(data){
        location.reload();
    });
}

// Products

function removeProducts(productID){
    if(confirm("Ցանկանում եք հեռացնել Ապրանքը") == true) { 
        $.post('?cmd=removeProducts', {productID : productID}, function(data){
            location.reload();
        });
    }
}

// Order

function removeOrder(orderID){
    if(confirm("Ցանկանում եք հեռացնել պատվերը") == true) { 
        $.post('?cmd=removeOrder', {orderID : orderID}, function(data){
            location.reload();
        });
    }
}

// List

function removeList(listID){
    if(confirm("Ցանկանում եք հեռացնել բաժինը") == true) { 
        $.post('?cmd=removeList', {listID : listID}, function(data){
            location.reload();
        });
    }
}

