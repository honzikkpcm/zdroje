function showLoading(){
    var loading_wrap = $("#load_screen_wrap");
    var loading = $('<div/>').attr('id','load_screen');
        loading.hide();
    var loading_in = $('<div/>').attr('id','loading');
        loading_in.append($('<div/>').addClass('loader'));
        loading.append(loading_in);
        loading_wrap.append(loading);
        loading.fadeIn();
}


$("[data-ajax]").unbind("click"), $("[data-ajax]").on("click", function(i){
    i.preventDefault();     
//    var sfake = $(this).attr("href").replace('.html','/');
//    var sreal = $(this).attr("href");

    var sfake = "about-us/";
    var sreal = "about-us.html";  


    var n = $(".page_content");
    var m = $("#video-container, #landing"); 
    var r = $('#menu');
    n.fadeOut(), m.fadeOut(), showLoading();
    n.load(sreal, {
        ajax: !0
    }, function(i, r, o) {
        return "error" != r ? (history.pushState({
            path: sfake
        }, "", sfake), n.fadeIn(), $("#load_screen").fadeOut(), $(document).prop("title", n.find("#pageTitle").html()), $('#menu').attr('style', 'position:fixed !important'), !1) : void(window.location.href = s)
    });
}), $(window).bind("popstate", function(e) {
    var t = e.originalEvent.state;
    var n = $(".page_content");
    var r = $('#menu');
        t && n.load(t.path, {
            ajax: !0
        }, function() {
            n.fadeIn(), $('#load_screen').fadeOut(), $(document).prop("title", n.find("#pageTitle").html()), $('#menu').attr('style', 'position:fixed !important')
        });
});
var s = window.location.hash.substring(1);
s.indexOf("redirect-predator-id") >= 0 && (document.cookie = "anchor=" + s, window.location.href = window.location.href.replace(/#.*$/, ""))
history.replaceState({ path: window.location.href }, "");
