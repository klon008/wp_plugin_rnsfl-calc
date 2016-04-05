/**
 * Created by User on 005 05.04.16.
 */
jQuery(document).ready(function ($) {
    var vant_update = false;
    var potential_victims = [];
    $("#rnsfl_add_row").click(function () {
        $('#rnsfl_table tr:last').after("<tr ><td>x</td><td contenteditable=\"true\"></td><td contenteditable=\"true\">0.00</td><td contenteditable=\"true\">0.00</td><td contenteditable=\"true\">0.00</td><td contenteditable=\"true\">0.00</td><td><img class=\"del_icon\" src=\""+rnsflDelIcon+"\"></td></td>");
        var refl = $('#rnsfl_table tr:last');
        refl.hide().fadeIn(300);
        refl.find('img').click(function (e) {
            refl.fadeOut( 300 , function () {
              refl.remove();
            })
        });
        vant_update = true;
    });
    $('#rnsfl_table tr').each(function(index, obj){
        var refl = $(obj);
        refl.find('img').click(function (e) {
            refl.fadeOut( 300 , function () {
                var id = refl[0].firstChild.innerHTML;
                potential_victims.push(id);
                refl.remove();
                vant_update = true;
            })
        });
    });

    //AJAX
    $("#rnsfl_submit").click(function () {
        $('body').addClass("loading");
        sending_ajax();
    });

    var sending_ajax = function(){

        var dataArray = [];
        $("#rnsfl_table tbody tr").each(function (index, obj) {
            var temp_data = $(obj).children('td');
            var temp_result = {};
            var id = temp_data[0].innerHTML;
            if (id == 'x') {
                id = 'null'
            };
            temp_result.id = id;
            var name = temp_data[1].innerHTML;
            if (typeof name === "undefined" || name === null || name === "") return;
            temp_result.name = name;
            var stencil_price = temp_data[2].innerHTML;
            if (typeof stencil_price === "undefined" || stencil_price === null || stencil_price === "") return;
            temp_result.stencil_price = stencil_price;
            var applic_price_20 = temp_data[3].innerHTML;
            if (typeof applic_price_20 === "undefined" || applic_price_20 === null || applic_price_20 === "") return;
            temp_result.applic_price_20 = applic_price_20;
            var applic_price_100 = temp_data[4].innerHTML;
            if (typeof applic_price_100 === "undefined" || applic_price_100 === null || applic_price_100 === "") return;
            temp_result.applic_price_100 = applic_price_100;
            var applic_price_z = temp_data[5].innerHTML;
            if (typeof applic_price_z === "undefined" || applic_price_z === null || applic_price_z === "") return;
            temp_result.applic_price_z = applic_price_z;

            dataArray.push(temp_result);
        });
        var ardditional_data = {
            design_price: $("#frb1").val(),
            background_fill: $("#frb2").val(),
            color_price: $("#color_price").val(),
            currency: $("#currency").val()
        }
        var obj = {
            new_data: dataArray,
            removed_data: potential_victims
        };
        var data = {
            action: 'rnsfl_update_table',
            new_data: dataArray,
            removed_data: potential_victims,
            additional_data: ardditional_data
        };
        $.post( ajaxurl, data, function(response) {
            $('body').removeClass("loading");
            location.reload(false);
        });
    }
});