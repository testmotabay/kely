function gotoproduct(id, url) {
    window.open(url);
}

$(document).ready(function() {
    $("#catsub1menu").hover(function() {
        $("#subcategories1").show();
    }, function() {
        $("#subcategories1").hide();
    });
});


$(document).ready(function() {
    $("#manufacturers").hover(function() {
        $("#manufacturers-list").show();
    }, function() {
        $("#manufacturers-list").hide();
    });
});


function pghtml(body) {
    $("#pg_body").html(body);
}

function pgloader() {
    $("#pg_body").html("<div align='center' class='loader'><img src='img/ajax-loader.gif'/></div>");
}

function pgshow() {
    pgloader();
    $("#pg").css("display", "block")
    $('#pg').animate({
        opacity: 1.0,
        height: '1200'
    }, 500, function() {
        // Animation complete.
    });
}

function loadcms(id) {
    advAJAX.get({
        url: "loader.php?go=cms&cid="+id,
        onLoading: function(obj) {
            pgshow();
        },
        onError: function(obj) {},
        onSuccess: function(obj) {
            pghtml(obj.responseText);
        }
    });
}


function changelang(id) {
    advAJAX.get({
        url: "loader.php?go=changelang&lid="+id,
        onLoading: function(obj) {
            contentloader();
        },
        onError: function(obj) {},
        onSuccess: function(obj) {
            document.location.reload(true);
        }
    });
}


function changecurrency(id) {
    advAJAX.get({
        url: "loader.php?go=changecurrency&cid="+id,
        onLoading: function(obj) {
            contentloader();
        },
        onError: function(obj) {},
        onSuccess: function(obj) {
            document.location.reload(true);
        }
    });
}

function pgclose() {
    $('#pg').animate({
        opacity: 1.0,
        height: '0'
    }, 500, function() {
        $("#pg").css("display", "none")
    });
}




function contenthtml(body) {
    $("#contentload_body").fadeTo("fast", 0.0, function() {
        $("#contentload_body").html(body);
        $("#contentload_loader").fadeTo("fast", 0.0);
    });
    $("#contentload_body").fadeTo("fast", 1.0, function() {
        rebind();
        reloadSocial();
    });

}


function contentloader() {
    $("#contentload_body").fadeTo("fast", 0.10, function() {
        $("#contentload_loader").fadeTo("fast", 1.0);
    });
}

function hidemenu() {
    $(".subcategories, .subcategories2, .subcategories3").hide();
}

function loadproducts(cid, page) {
    advAJAX.get({
        url: "loader.php?go=products&cid="+cid+"&page="+page,
        onLoading: function(obj) {
            hidemenu();
            contentloader();
        },
        onError: function(obj) {},
        onSuccess: function(obj) {
            contenthtml(obj.responseText);
        }
    });
}


function loadmanufacturers(cid, page) {
    advAJAX.get({
        url: "loader.php?go=manufacturers&cid="+cid+"&page="+page,
        onLoading: function(obj) {
            hidemenu();
            contentloader();
        },
        onError: function(obj) {},
        onSuccess: function(obj) {
            contenthtml(obj.responseText);
        }
    });
}

function loadpromotions(page) {
    advAJAX.get({
        url: "loader.php?go=promotions&page="+page,
        onLoading: function(obj) {
            contentloader();
        },
        onError: function(obj) {},
        onSuccess: function(obj) {
            contenthtml(obj.responseText);
        }
    });
}


function loadnewproducts(page) {
    advAJAX.get({
        url: "loader.php?go=newproducts&page="+page,
        onLoading: function(obj) {
            contentloader();
        },
        onError: function(obj) {},
        onSuccess: function(obj) {
            contenthtml(obj.responseText);
        }
    });
}

function loadhomepage() {
    advAJAX.get({
        url: "loader.php?go=homepage",
        onLoading: function(obj) {
            contentloader();
        },
        onError: function(obj) {},
        onSuccess: function(obj) {
            contenthtml(obj.responseText);
        }
    });
}



function rebind() {
    $(".productpic").hover(
        function() {
            $(this).fadeTo("fast", 0.6);
        },
        function() {
            $(this).fadeTo("fast", 1.0);
        }
    );
}

function reloadSocial() {
    $(document).ready(function() {
        if (typeof(FB) != "undefined"){
            FB.XFBML.parse(document, function() {
                $('.fb_button_container iframe').css('height', '');
                $('.fb_button_container .fb-like > span').css('height', '');
            });
        }
    });
}