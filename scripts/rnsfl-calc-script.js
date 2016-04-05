jQuery(document).ready(function ($) {
    /**
     * Приводит id к виду плагина
     * @param inName
     * @returns {*|jQuery|HTMLElement}
     */
    function getCalcPluginName(inName) {
        return $('#' + rnsflCalculatorPluginPrefix + "-" + inName);
    }

    /**
     * Переводит массив id к виду плагина
     * @param inNames array[]
     */
    function getCalcPluginsName(inNames) {
        inNames = inNames.map(function (item) {
            return '#' + rnsflCalculatorPluginPrefix + "-" + item;
        });
        return $(inNames.join(","));
    }

    /**
     * Биндинг инпутов на перерасчет
     */
    getCalcPluginsName(['counter', 'color', 'size', 'frb1', 'frb2']).each(function (index, obj) {
        $(obj).bind("change", function (inChange) {
            //Вызов функции перерасчета
            calculateRnsflPluginPrice();
        });
    });
    var calculateRnsflPluginPrice = function () {
        var calculateInputs = {};
        getCalcPluginsName(['counter', 'color', 'size', 'frb1:checked', 'frb2:checked']).each(function (index, obj) {
            calculateInputs[obj.id] = obj.value;
        });
        var priceStencil = calculateInputs[rnsflCalculatorPluginPrefix + "-size"];
        var frb1 = parseFloat(calculateInputs[rnsflCalculatorPluginPrefix + "-frb1"]) || 0;
        var frb2 = parseFloat(calculateInputs[rnsflCalculatorPluginPrefix + "-frb2"]) || 0;
        var counter = parseInt(calculateInputs[rnsflCalculatorPluginPrefix + "-counter"]) || 0;
        var color = parseFloat(calculateInputs[rnsflCalculatorPluginPrefix + "-color"]) || 0;
        //Калькуляция трафарета
        var selectedStencilPrice = prices.filter(function (eachItem) {
            return eachItem.id == priceStencil;
        })[0];
        var costStencil = parseFloat(selectedStencilPrice.stencil_price) + frb1;
        var costStencilFormated = accounting.formatMoney(costStencil, options_cur_rur);
        getCalcPluginName('final-cost1').find('span').html('<span>' + costStencilFormated + '</span>');
        /**
         * Калькуляция нанесений
         * @param inVal
         */
        var setFincalCost2 = function (inVal) {
            getCalcPluginName('final-cost2').find('span').html('<span>' + accounting.formatMoney(inVal, options_cur_rur)
                + '</span>');
        };
        var cost = 0;
        if (counter < 20) {
            cost = selectedStencilPrice.applic_price_20 * counter;

        } else if (counter >= 20 && counter <= 100) {
            cost = selectedStencilPrice.applic_price_100 * counter;

        } else if (counter > 100) {
            cost = selectedStencilPrice.applic_price_z * counter;
        } else {
            setFincalCost2(0);
            return;
        }
        var additionalPercent = (color / 100) * cost;
        cost += additionalPercent;
        var additionalBackgroundFillPrice = (frb2 / 100) * cost;
        cost += additionalBackgroundFillPrice;
        setFincalCost2(cost);
        /**
         * Итого
         */
        getCalcPluginName('final-cost3').find('span').html('<span>' + accounting.formatMoney((cost+costStencil), options_cur_rur)
            + '</span>');

    };

    getCalcPluginName('counter').bind("change keyup input click", function () {
        if (this.value.match(/[^0-9]/g)) {
            this.value = this.value.replace(/[^0-9]/g, '');
        }
    });

    var prices = $.parseJSON(rnsflDataPrices);
    /**
     * Уберем из prices дробную часть .00
     */
    var removeDecimalPart = function (inVal) {
        var res = inVal.split(".")[0];
        return res;
    };
    for (var i = 0; i < prices.length; i++) {
        prices[i].applic_price_20 = removeDecimalPart(prices[i].applic_price_20);
        prices[i].applic_price_100 = removeDecimalPart(prices[i].applic_price_100);
        prices[i].applic_price_z = removeDecimalPart(prices[i].applic_price_z);
        prices[i].stencil_price = removeDecimalPart(prices[i].stencil_price);
    }

    /**
     * Генерация селекта "Формат трафарета"
     */
    var sizeSelector = getCalcPluginName('size');
    for (var index = 0; index < prices.length; ++index) {
        sizeSelector.append("<option value=" + prices[index].id + ">" + prices[index].name + "</option>");
    }
    var additionalValues = $.parseJSON(rnsflAdditionalValues);
    var keysValues = {};
    for (var i = 0; i < additionalValues.length; i++) {
        var objName = additionalValues[i]['key_t'];
        var objValue = additionalValues[i]['value'];
        keysValues[objName] = objValue;
    }
    /**
     * Настройка для форматирования вывода Цены
     * @type {{symbol: (c.settings.currency|{symbol, format, decimal, thousand, precision, grouping}), precision: number, thousand: string, decima: string, format: {pos: string, neg: string, zero: string}}}
     */
    var options_cur_rur = {
        symbol: keysValues.currency,
        precision: 0,
        thousand: " ",
        decima: ".",
        format: {
            pos: "%v %s",
            neg: "(%v) %s",
            zero: "-- %s"
        }
    }
    /**
     * заполнение Value для checkBox
     * и Value для Кол-во Цветов
     */
    getCalcPluginName('frb1').val(keysValues.design_price);
    getCalcPluginName('frb2').val(keysValues.background_fill);
    getCalcPluginName('color').find('option').each(function (index, obj) {
        obj.value = parseInt(obj.value) * (parseInt(keysValues.color_price));
    });
    /**
     * Генерация таблицы "Стоимость нанесений"
     */
    var buildApplyPaintTable = (function () {
        var applyPaintTable = getCalcPluginName('apply-paint');
        var applyPaintTableBody = applyPaintTable.find('tbody');
        for (var i = 0; i < prices.length; i++) {
            var obj = prices[i];
            applyPaintTableBody.append("<tr><td>" + obj.name + "</td><td >" + accounting.formatMoney(obj.applic_price_20, options_cur_rur) + "/шт</td><td>" + accounting.formatMoney(obj.applic_price_100, options_cur_rur) + "/шт</td><td>" + accounting.formatMoney(obj.applic_price_z, options_cur_rur) + "/шт</td></tr>");
        }
    })();
    /**
     * Генерация таблицы "Изготовление трафарета"
     */
    var buildApplyPaintTable = (function () {
        var applyPaintTable = getCalcPluginName('building-stencil');
        var applyPaintTableBody = applyPaintTable.find('tbody');
        for (var i = 0; i < prices.length; i++) {
            var obj = prices[i];
            applyPaintTableBody.append("<tr><td>" + obj.name + "</td><td>" + accounting.formatMoney(obj.stencil_price, options_cur_rur) + "</td></tr>");
        }
    })();
    getCalcPluginName('label_frb1').html(accounting.formatMoney(keysValues.design_price, options_cur_rur));
    getCalcPluginName('label_color').html(keysValues.color_price);
    getCalcPluginName('label_frb2').html(keysValues.background_fill);
    /**
     * AJAX
     */
    var sendMailToAdmin = function(){
        //Получение данных о расчете трафарета
        var priceStencil = getCalcPluginName('size').val();
        var selectedStencilPrice = prices.filter(function (eachItem) {
            return eachItem.id == priceStencil;
        })[0];
        var costStencil = parseFloat(selectedStencilPrice.stencil_price);
        var costStencilFormated = accounting.formatMoney(costStencil, options_cur_rur);
        //
        var formatted_data = {
            size: getCalcPluginName('size option:selected').text() + " по стоимости "
            + costStencilFormated, //Формат Имя
            design_price: accounting.formatMoney(getCalcPluginName('frb1:checked').val()||'Не нужен', options_cur_rur), //Дизайн Макета
            colors: ((getCalcPluginName('color').val()/30)+1) +" каждый цвет увеличивает стоимость на "
            +keysValues.color_price, //Количество цветов
            background_fill: getCalcPluginName('frb2:checked').val()|| 'Не нужна',
            count: getCalcPluginName('counter').val(),
            final_cost1: getCalcPluginName('final-cost1 span').html(),
            final_cost2: getCalcPluginName('final-cost2 span').html(),
            final_cost3: getCalcPluginName('final-cost3 span').html()
        }
        var formData = getCalcPluginName('calculate').serializeArray();

        for (var i = 0; i < formData.length; i++) {
            var name = formData[i]['name'];
            var value = formData[i]['value'];
            formatted_data[name] = value;
        }
        var data = {
            action: 'rnsfl_mail_action',
            data: formatted_data
        };
        $("#overlay .overlay_loading").show();
        $("#overlay").fadeIn();
        $.post( rnsfl_ajax.url,
            data,
            function(response) {
                $("#overlay .overlay_success h1").html(response);
                $("#overlay .overlay_loading").fadeOut(300, function () {
                    $("#overlay .overlay_success").fadeIn();
                    $("#overlay").click(function () {
                        $("#overlay").fadeOut();
                        $("#overlay").unbind( "click" );
                    })
                });
            }
        );
        return false;
    };
    getCalcPluginName('calculate').submit(sendMailToAdmin);

    /**
     * Добавление подложки для модалок
     */
    $("#page").append("<div id=\"overlay\">" +
        "<div class=\"overlay_loading\" style=\"" + rnsflModalStyle + "\"></div>" +
        "<div class=\"overlay_success\">" + "<h1></h1></div>" +
        "</div>");
});