indexCustom = {

    /**
     * Initialisation des fonctions sur toutes les pages twig.
     */
    init: function() {
        this.main();
    },

    main: function() {
        let inputValuesSelector = $('input.input-values');
        let authorizedNumbersOperators = [
            '0', "1", "2", "3", "4", "5", "6", "7", "8", "9", ".", "/", "*", "+", "-"
        ];
        let operatorsArray = ['+', '-', '*', '/', '.'];

        $('input.input-value').click(function() {

            inputValuesSelector.val(inputValuesSelector.val() + $(this).val());
        });

        $('input.input-operator').click(function() {
            let valueInputValues = inputValuesSelector.val();
            let previousOperator = valueInputValues.substr(valueInputValues.length - 1);
            let strToInsert = $(this).val();

            if ($.inArray(String($(this).val()), operatorsArray) > -1 && $.inArray(String(previousOperator), operatorsArray) > -1 ) {
                strToInsert = '';
            }


            if (previousOperator !== $(this).val()) {
                inputValuesSelector.val(inputValuesSelector.val() + strToInsert);
            }

        });

        $('input.input-reset').click(function() {
            let valInputToCalculate = $('input.input-values');

            valInputToCalculate.val('');
            valInputToCalculate.text('');
        });

        document.querySelector('input.input-values').addEventListener("keypress", function(e) {

            let valueInputValues = inputValuesSelector.val();
            let previousOperator = valueInputValues.substr(valueInputValues.length - 1);


            if ($.inArray(String(e.key), authorizedNumbersOperators) === -1) {
                e.preventDefault();
            } else if ($.inArray(String(e.key), operatorsArray) > -1 && previousOperator === e.key) {
                e.preventDefault();
            } else if ($.inArray(String(e.key), operatorsArray) > -1 && $.inArray(String(previousOperator), operatorsArray) > -1 ) {
                e.preventDefault();
            }
        });


        $('input.input-equal').click(function() {
            let url = $(this).attr('data-url-calculate');
            let valInputToCalculate = $('input.input-values');
            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    'inputToCalculate': valInputToCalculate.val()
                },
                dataType: 'json',
                success: function (response) {
                    if ($.isNumeric(response)) {
                        $('div.history-container').append( "<p>"+valInputToCalculate.val()+"<span> = </span><span>"+response+"</span></p>");
                    }

                    valInputToCalculate.val(response);
                }
            });
        });
    },
};




$(function(){
    indexCustom.init();
});