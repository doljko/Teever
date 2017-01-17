
/*
* Pixor - Copyright (c) 2015 Federico Schiocchet - Pixor (www.pixor.it)
*/

(function ($) {
    $.fn.googleMap = function (n) {
        if ($(this).attr("data-trigger") != "initialized") {
            var arrGmap = getAttrs(this);
            if (isEmpty(arrGmap["coords"])) {
                initializeGMapByAdress(this);
            } else initializeGMap(this);
            $(this).attr("data-trigger", "initialized");
        }
    };
    function getAttrs(obj) {
        var arrGmap = {};
        arrGmap["coords"] = $(obj).attr("data-coords");
        arrGmap["address"] = $(obj).attr("data-address");
        arrGmap["marker"] = $(obj).attr("data-marker");
        arrGmap["marker-pos"] = $(obj).attr("data-marker-pos");
        arrGmap["marker-pos-top"] = $(obj).attr("data-marker-pos-top");
        arrGmap["marker-pos-left"] = $(obj).attr("data-marker-pos-left");
        arrGmap["skin"] = $(obj).attr("data-skin");
        arrGmap["zoom"] = $(obj).attr("data-zoom");
        if (isEmpty(arrGmap["zoom"])) arrGmap["zoom"] = 12;
        if (isEmpty(arrGmap["marker-pos-top"])) arrGmap["marker-pos-top"] = 0;
        if (isEmpty(arrGmap["marker-pos-left"])) arrGmap["marker-pos-left"] = 0;
        return arrGmap;
    }
    function initializeGMap(obj) {
        var arrGmap = getAttrs(obj);
        var cx = parseFloat(arrGmap["coords"].split(",")[0]);
        var cy = parseFloat(arrGmap["coords"].split(",")[1]);
        geocoder = new google.maps.Geocoder();
        var latlng = new google.maps.LatLng(cx, cy);
        var mapOptions = {
            zoom: parseInt(arrGmap["zoom"], 10),
            scrollwheel: false,
            center: latlng
        }
        GMap = new google.maps.Map(obj.get(0), mapOptions);
        if (!isEmpty(arrGmap["skin"])) GMap.setOptions({ styles: arrSkins[arrGmap["skin"]] });
        var arrMarker = {
            position: latlng,
            map: GMap,
        };
        if (!isEmpty(arrGmap["marker"])) arrMarker = { position: latlng, map: GMap, icon: arrGmap["marker"] };
        var marker = new google.maps.Marker(arrMarker);

        var x = 0 - arrGmap["marker-pos-left"];
        var y = 0 - arrGmap["marker-pos-top"];
        if (!isEmpty(arrGmap["marker-pos"]) && $(window).width() > 768) {
            var p = arrGmap["marker-pos"];
            if (p == 'col-md-6-right') x = x - 1 * $(".col-md-6").outerWidth() / 2;
            else if (p == 'col-md-6-left') x = x + $(".col-md-6").outerWidth() / 2;
            else if (p == 'center-bottom') y = y - 1 * $(obj).outerHeight() / 4 - 30;
            else if (p == 'center-top') y = y + $(obj).outerHeight() / 2 - 60;
        }
        if (device_screen_size != "device-xs" || isEmpty(p)) GMap.panBy(x, y);
    }
    function initializeGMapByAdress(obj) {
        var arrGmap = getAttrs(obj);
        geocoder = new google.maps.Geocoder();
        geocoder.geocode({ 'address': arrGmap["address"] }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                $(obj).attr("data-coords", results[0].geometry.location.lat() + "," + results[0].geometry.location.lng());
                initializeGMap(obj);
            }
        });
    }
    $(document).ready(function () {
        $(".google-map").each(function (index) {
            if ($(this).attr("data-trigger") != "manual") $(this).googleMap();
        });
    });
    var arrSkins = {
        "gray": [{ "featureType": "water", "elementType": "geometry", "stylers": [{ "color": "#e9e9e9" }, { "lightness": 17 }] }, { "featureType": "landscape", "elementType": "geometry", "stylers": [{ "color": "#f5f5f5" }, { "lightness": 20 }] }, { "featureType": "road.highway", "elementType": "geometry.fill", "stylers": [{ "color": "#ffffff" }, { "lightness": 17 }] }, { "featureType": "road.highway", "elementType": "geometry.stroke", "stylers": [{ "color": "#ffffff" }, { "lightness": 29 }, { "weight": 0.2 }] }, { "featureType": "road.arterial", "elementType": "geometry", "stylers": [{ "color": "#ffffff" }, { "lightness": 18 }] }, { "featureType": "road.local", "elementType": "geometry", "stylers": [{ "color": "#ffffff" }, { "lightness": 16 }] }, { "featureType": "poi", "elementType": "geometry", "stylers": [{ "color": "#f5f5f5" }, { "lightness": 21 }] }, { "featureType": "poi.park", "elementType": "geometry", "stylers": [{ "color": "#dedede" }, { "lightness": 21 }] }, { "elementType": "labels.text.stroke", "stylers": [{ "visibility": "on" }, { "color": "#ffffff" }, { "lightness": 16 }] }, { "elementType": "labels.text.fill", "stylers": [{ "saturation": 36 }, { "color": "#333333" }, { "lightness": 40 }] }, { "elementType": "labels.icon", "stylers": [{ "visibility": "off" }] }, { "featureType": "transit", "elementType": "geometry", "stylers": [{ "color": "#f2f2f2" }, { "lightness": 19 }] }, { "featureType": "administrative", "elementType": "geometry.fill", "stylers": [{ "color": "#fefefe" }, { "lightness": 20 }] }, { "featureType": "administrative", "elementType": "geometry.stroke", "stylers": [{ "color": "#fefefe" }, { "lightness": 17 }, { "weight": 1.2 }] }],
        "black": [{ "featureType": "all", "elementType": "labels.text.fill", "stylers": [{ "saturation": 36 }, { "color": "#000000" }, { "lightness": 40 }] }, { "featureType": "all", "elementType": "labels.text.stroke", "stylers": [{ "visibility": "on" }, { "color": "#000000" }, { "lightness": 16 }] }, { "featureType": "all", "elementType": "labels.icon", "stylers": [{ "visibility": "off" }] }, { "featureType": "administrative", "elementType": "geometry.fill", "stylers": [{ "color": "#000000" }, { "lightness": 20 }] }, { "featureType": "administrative", "elementType": "geometry.stroke", "stylers": [{ "color": "#000000" }, { "lightness": 17 }, { "weight": 1.2 }] }, { "featureType": "landscape", "elementType": "geometry", "stylers": [{ "color": "#000000" }, { "lightness": 20 }] }, { "featureType": "poi", "elementType": "geometry", "stylers": [{ "color": "#000000" }, { "lightness": 21 }] }, { "featureType": "road.highway", "elementType": "geometry.fill", "stylers": [{ "color": "#000000" }, { "lightness": 17 }] }, { "featureType": "road.highway", "elementType": "geometry.stroke", "stylers": [{ "color": "#000000" }, { "lightness": 29 }, { "weight": 0.2 }] }, { "featureType": "road.arterial", "elementType": "geometry", "stylers": [{ "color": "#000000" }, { "lightness": 18 }] }, { "featureType": "road.local", "elementType": "geometry", "stylers": [{ "color": "#000000" }, { "lightness": 16 }] }, { "featureType": "transit", "elementType": "geometry", "stylers": [{ "color": "#000000" }, { "lightness": 19 }] }, { "featureType": "water", "elementType": "geometry", "stylers": [{ "color": "#000000" }, { "lightness": 17 }] }],
        "green": [{ "featureType": "water", "elementType": "geometry", "stylers": [{ "visibility": "on" }, { "color": "#aee2e0" }] }, { "featureType": "landscape", "elementType": "geometry.fill", "stylers": [{ "color": "#abce83" }] }, { "featureType": "poi", "elementType": "geometry.fill", "stylers": [{ "color": "#769E72" }] }, { "featureType": "poi", "elementType": "labels.text.fill", "stylers": [{ "color": "#7B8758" }] }, { "featureType": "poi", "elementType": "labels.text.stroke", "stylers": [{ "color": "#EBF4A4" }] }, { "featureType": "poi.park", "elementType": "geometry", "stylers": [{ "visibility": "simplified" }, { "color": "#8dab68" }] }, { "featureType": "road", "elementType": "geometry.fill", "stylers": [{ "visibility": "simplified" }] }, { "featureType": "road", "elementType": "labels.text.fill", "stylers": [{ "color": "#5B5B3F" }] }, { "featureType": "road", "elementType": "labels.text.stroke", "stylers": [{ "color": "#ABCE83" }] }, { "featureType": "road", "elementType": "labels.icon", "stylers": [{ "visibility": "off" }] }, { "featureType": "road.local", "elementType": "geometry", "stylers": [{ "color": "#A4C67D" }] }, { "featureType": "road.arterial", "elementType": "geometry", "stylers": [{ "color": "#9BBF72" }] }, { "featureType": "road.highway", "elementType": "geometry", "stylers": [{ "color": "#EBF4A4" }] }, { "featureType": "transit", "stylers": [{ "visibility": "off" }] }, { "featureType": "administrative", "elementType": "geometry.stroke", "stylers": [{ "visibility": "on" }, { "color": "#87ae79" }] }, { "featureType": "administrative", "elementType": "geometry.fill", "stylers": [{ "color": "#7f2200" }, { "visibility": "off" }] }, { "featureType": "administrative", "elementType": "labels.text.stroke", "stylers": [{ "color": "#ffffff" }, { "visibility": "on" }, { "weight": 4.1 }] }, { "featureType": "administrative", "elementType": "labels.text.fill", "stylers": [{ "color": "#495421" }] }, { "featureType": "administrative.neighborhood", "elementType": "labels", "stylers": [{ "visibility": "off" }] }]
    };

}(jQuery));

