var currency_settings = function () {

    var elements = [
        document.getElementsByName('subscribe')[0],
        document.getElementsByName('exchange_rates')[0],
        document.getElementsByName('crypto_rates')[0],
        document.getElementsByName('display_crypto')[0]
    ];

    handle_checkboxes(elements);
    add_listeners(elements);

};

var add_listeners = function (elements) {
    for (var i=0; i < elements.length; i++) {
        elements[i].addEventListener('click', function() {
            handle_checkboxes(elements);
        });
    }
};

var handle_checkboxes = function (elements) {
    if(elements[0].checked) {
        elements[1].removeAttribute('disabled');
        elements[2].removeAttribute('disabled');
        elements[3].removeAttribute('disabled');

    } else {
        elements[1].disabled = true;
        elements[1].checked = false;
        elements[2].disabled = true;
        elements[2].checked = false;
        elements[3].disabled = true;
        elements[3].checked = false;
    }
};