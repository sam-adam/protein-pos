window.$ = window.jQuery = require("jquery");

/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('bootstrap-datepicker');
require('devbridge-autocomplete');

window.moment = require('moment');

/*!
 * Start Bootstrap - SB Admin 2 v3.3.7+1 (http://startbootstrap.com/template-overviews/sb-admin-2)
 * Copyright 2013-2016 Start Bootstrap
 * Licensed under MIT (https://github.com/BlackrockDigital/startbootstrap/blob/gh-pages/LICENSE)
 */
$(function() {
    $('#side-menu').metisMenu();
    $('[data-toggle="tooltip"]').tooltip();
});

//Loads the correct sidebar on window load,
//collapses the sidebar on window resize.
// Sets the min-height of #page-wrapper to window size
$(function() {
    const resizeHandler = function() {
        var topOffset = 50;
        var width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
        if (width < 768) {
            $('div.navbar-collapse').addClass('collapse');
            topOffset = 100; // 2-row-menu
        } else {
            $('div.navbar-collapse').removeClass('collapse');
        }

        var height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
        height = height - topOffset;
        if (height < 1) height = 1;
        if (height > topOffset) {
            $("#page-wrapper").css("min-height", (height) + "px");
        }
    };

    $(window).bind("load resize", resizeHandler);
    $(document).ready(function () {
        if (window.location.hash) {
            var $modal = $(window.location.hash);

            $modal.each(function () {
                var $this = $(this);

                if ($this.hasClass("modal")) {
                    $this.modal("show");
                }
            });
        }

        $(".modal").on("show.bs.modal", function () {
            var $this = $(this),
                id = $this.attr("id");

            if (id && window.location.hash !== id) {
                window.location.hash = id;
            }
        }).on("hide.bs.modal", function () {
            var $this = $(this),
                id = $this.attr("id");

            if (id && window.location.hash === ("#" + id)) {
                window.location.hash = "";
            }
        });
    });

    $(".datepicker").datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });

    // $("input[type='number']").each(function () {
    //     var $this = $(this);
    //
    //     $this.on("keydown", function (e) {
    //         var min = $this.attr("min"),
    //             max = $this.attr("max");
    //
    //         if (!isNaN(min) && e.target.value < min) {
    //             e.preventDefault();
    //         }
    //
    //         if (!isNaN(max) && e.target.value > max) {
    //             e.preventDefault();
    //         }
    //     });
    // });

    var url = window.location;
    // var element = $('ul.nav a').filter(function() {
    //     return this.href == url;
    // }).addClass('active').parent().parent().addClass('in').parent();
    var element = $('ul.nav a').filter(function() {
        return this.href == url;
    }).addClass('active').parent();

    while (true) {
        if (element.is('li')) {
            element = element.parent().addClass('in').parent();
        } else {
            break;
        }
    }

    resizeHandler();
});

require("./directives/tooltip.js");

Vue.component('SearchProduct', require('./components/SearchProduct.vue'));
Vue.component('SearchCustomer', require('./components/SearchCustomer.vue'));
Vue.component('ProductPerformanceChart', require('./components/ProductPerformanceChart.vue'));

// const app = new Vue({
//     el: '#app'
// });

if (!window.localStorage) {
    window.localStorage = {
        getItem: function (sKey) {
            if (!sKey || !this.hasOwnProperty(sKey)) { return null; }
            return unescape(document.cookie.replace(new RegExp("(?:^|.*;\\s*)" + escape(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*((?:[^;](?!;))*[^;]?).*"), "$1"));
        },
        key: function (nKeyId) {
            return unescape(document.cookie.replace(/\s*\=(?:.(?!;))*$/, "").split(/\s*\=(?:[^;](?!;))*[^;]?;\s*/)[nKeyId]);
        },
        setItem: function (sKey, sValue) {
            if(!sKey) { return; }
            document.cookie = escape(sKey) + "=" + escape(sValue) + "; expires=Tue, 19 Jan 2038 03:14:07 GMT; path=/";
            this.length = document.cookie.match(/\=/g).length;
        },
        length: 0,
        removeItem: function (sKey) {
            if (!sKey || !this.hasOwnProperty(sKey)) { return; }
            document.cookie = escape(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
            this.length--;
        },
        hasOwnProperty: function (sKey) {
            return (new RegExp("(?:^|;\\s*)" + escape(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
        }
    };
    window.localStorage.length = (document.cookie.match(/\=/g) || window.localStorage).length;
}

window.toggleFullScreen = function (isFullScreen) {
    window.localStorage.setItem('fullScreen', isFullScreen);
    window.adjustFullScreen();
};

window.adjustFullScreen = function () {
    const isFullScreen = window.localStorage.getItem("fullScreen");

    $("body").toggleClass("fullscreen", isFullScreen !== null && isFullScreen !== false);
};

// window.adjustFullScreen();