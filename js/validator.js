/**
 * Created by PhpStorm.
 * User: roman
 * Date: 4/3/18
 * Time: 11:03 PM
 */
var validate_class_name = {
    'required': /^[\S]*$/,
    'validate_number_int': /^[0-9]*$/,
    'validate_number_float': /^[0-9]*\.?[0-9]*$/,
    'validate_num_txt': /^[\s0-9a-zA-Zа-яА-ЯЁё\u0530-\u058F-]*$/,
    'validate_name': /^[\sa-zA-Zа-яА-ЯЁё\u0530-\u058F-"]*$/,
    'validate_name_arm': /^[\s\u0530-\u058F-"]*$/,
    'validate_name_rus': /^[\sа-яА-ЯЁё"-]*$/,
    'validate_name_eng': /^[\sa-zA-Z-"]*$/,
    'validate_arm_num': /^[\s\u0530-\u058F0-9-./",]*$/,
    'validate_rus_num': /^[\sа-яА-ЯЁё0-9-./",]*$/,
    'validate_eng_num': /^[\sa-zA-Z0-9-./",]*$/,
    'validate_email': /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/,
    // 'validate_pass': /^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&amp;*()_+}&quot;:;'?/&gt;.&lt;,])(?=\S+$).{8,}$/,
    'validate_pass': /(?=^.{6,}$)(?=.*\d)(?=.*[a-zа-яё\u0560-\u058F])(?=.*[A-ZА-ЯЁ\u0530-\u0559])(?=.*[\\=!@#$%^&amp;*()_+}{&quot;:;'?/&gt;.&lt;,~`-|])(?!.*\s).*$/,
    'validate_even_number': /^\d*[02468]$/,
    'validate_odd_number': /^\d*[13579]$/,
    'validate_number_int_double_point': /^[0-9:-]*$/,
    'validate_number_int_point': /^[0-9.]*$/,
    'validate_number_int_slash': /^[0-9/]*$/

};

var error_message = {
    'validate_number_int': 'Միայն ամբողջ թվեր',
    'validate_number_float': 'Միայն թվեր',
    'validate_num_txt': 'Միայն տառեր և թվեր',
    'validate_name': 'Միայն տառեր',
    'validate_name_arm': 'Միայն հայերեն տառեր',
    'validate_name_rus': 'Միայն ռուսերեն տառեր',
    'validate_name_eng': 'Միայն անգլերեն տառեր',
    'validate_arm_num': 'Միայն հայերեն տառեր և . / - ",',
    'validate_rus_num': 'Միայն ռուսերեն տառեր և . / - ",',
    'validate_eng_num': 'Միայն անգլերեն տառեր և . / - ",',
    'required': 'Պարտադիր է լրացնել',
    'length': 'Նիշերի քանակը չի համապատասխանում',
    'value': 'Արժեքը չի համապատասխանում',
    'validate_email': 'Միայն էլ-փոստ',
    'validate_pass': 'Պարտադիր է պարունակի մեծատառ փոքրատառ թիվ և սինվոլ',
    'validate_even_number': 'Միայն զույգ թվեր',
    'validate_odd_number': 'Միայն կենտ թվեր',
    'validate_number_int_double_point': 'Միայն թվեր և ։ -',
    'validate_number_int_point': 'Միայն թվեր և .',
    'validate_number_int_slash': 'Միայն թվեր և /'
};

function check_length(this_input) {

    var this_val = this_input.val();
    // this_val = Number(this_val);


    if (this_val.length == 0) {
        return false;
    }

    if (this_input.attr('data-length')) {

        if (this_input.attr('data-length') == this_val.length) {
            this_input.removeClass("inp_invalid").addClass("inp_valid");
        } else {
            this_input.next().find('.error_small_red').remove()
            this_input.after('<p class="error_small_red"><span>' + error_message.length + ' (x=' + this_input.attr('data-length') + ')</span></p>')
            this_input.removeClass("inp_valid").addClass("inp_invalid");
        }
    } else {
        var min_length = this_input.attr('data-minlength') ? this_input.attr('data-minlength') : 0;
        var max_length = this_input.attr('data-maxlength') ? this_input.attr('data-maxlength') : 99999;

        min_length = Number(min_length);
        max_length = Number(max_length);


        var error_length_message = min_length != 0 ? ' ' + min_length + "< x" : '';
        error_length_message += error_length_message == '' ? 'x' : '';
        error_length_message += max_length != 99999 ? ' <' + max_length + '' : '';
        //
        // this_input.removeClass("inp_valid").addClass("inp_invalid");
        // this_input.removeClass("inp_invalid").addClass("inp_valid");
        // $(this).removeClass("inp_valid").removeClass("inp_invalid");

        this_input.parent().find('.error_small_red').remove()

        if (min_length < this_val.length && this_val.length < max_length) {

            this_input.removeClass("inp_invalid").addClass("inp_valid");
        } else {
            this_input.after('<p class="error_small_red"><span>' + error_message.length + ' (' + error_length_message + ')</span></p>');
            this_input.removeClass("inp_valid").addClass("inp_invalid");
        }
    }
}

function check_value(this_input) {

    var this_val = this_input.val();


    if (this_val == '') {
        return false;
    }


    // this_val = Number(this_val);

    if (!isNaN(this_val)) {
        this_input.val(this_val);
    }

    var min_value = this_input.attr('data-minval') ? this_input.attr('data-minval') : 0;
    var max_value = this_input.attr('data-maxval') ? this_input.attr('data-maxval') : 2147483647;

    min_value = Number(min_value);
    max_value = Number(max_value);

    console.log(this_val);
    var error_value_message = min_value != 0 ? ' ' + min_value + "<= x" : '';
    error_value_message += error_value_message == '' ? 'x' : '';
    error_value_message += max_value != 2147483647 ? ' <=' + max_value + '' : '';

    this_input.parent().find('.error_small_red').remove();

    // console.log(min_value, this_val, max_value);

    if (min_value <= this_val && this_val <= max_value) {

        this_input.removeClass("inp_invalid").addClass("inp_valid");
    } else {
        this_input.after('<p class="error_small_red"><span>' + error_message.value + ' (' + error_value_message + ')</span></p>');
        this_input.removeClass("inp_valid").addClass("inp_invalid");
    }
}

function set_valid_or_not(this_inp, key) {

    // console.log(this_inp.attr('disabled'));

    if (this_inp.hasClass('required') && this_inp.val().length < 1 && this_inp.attr('disabled') != 'disabled') {
        this_inp.removeClass("inp_valid").addClass("inp_invalid");
        this_inp.parent().find('.error_small_red').remove();
        this_inp.after('<p class="error_small_red"><span>' + error_message['required'] + '</span></p>');

    } else if (validate_class_name[key].test(this_inp.val()) && this_inp.val().length > 0) {
        this_inp.removeClass("inp_invalid").addClass("inp_valid");
        this_inp.parent().find('.error_small_red').remove();
    } else if (!validate_class_name[key].test(this_inp.val()) && this_inp.val().length > 0) {
        this_inp.removeClass("inp_valid").addClass("inp_invalid");
        this_inp.parent().find('.error_small_red').remove();
        this_inp.after('<p class="error_small_red"><span>' + error_message[key] + '</span></p>');
    } else {
        this_inp.removeClass("inp_valid").removeClass("inp_invalid");
        this_inp.parent().find('.error_small_red').remove()
    }

}

var check_valid = null;

function startValidate() {

    $(".check_length").on('focusout', function () {
        check_length($(this))
    });

    $.each(validate_class_name, function (key, re) {
        var c = '.' + key;
        $(c).focusout(function () {
            set_valid_or_not($(this), key)
        });
    });

    $(".validate_number_int").on('keypress', function (e) {

        var charCode = (e.which) ? e.which : e.keyCode;

        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }

    });

    $(".validate_number_float").on('keypress', function (e) {

        var charCode = (e.which) ? e.which : e.keyCode;

        if (charCode > 31 && ((charCode < 48 || charCode > 57) && charCode != 46)) {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }

    });

    $(".validate_num_txt").on('keypress', function (e) {
        var charCode = (e.which) ? e.which : e.keyCode;

        if ((charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90) ||
            (charCode >= 1377 && charCode <= 1415) || (charCode >= 1329 && charCode <= 1366) ||
            (charCode >= 1040 && charCode <= 1071) || (charCode >= 1072 && charCode <= 1103) || charCode == 1025 || charCode == 1105 ||
            (charCode > 48 && charCode < 57) || charCode == 45 || charCode == 32) {
        } else {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }
    });

    $(".validate_name").on('keypress', function (e) {
        var charCode = (e.which) ? e.which : e.keyCode;
        console.log(charCode);
        if ((charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90) ||
            (charCode >= 1377 && charCode <= 1416) || (charCode >= 1329 && charCode <= 1366) ||
            (charCode >= 1040 && charCode <= 1071) || (charCode >= 1072 && charCode <= 1103) || charCode == 1025 || charCode == 1105 ||
            charCode == 45 || charCode == 32 || charCode == 34) {
        } else {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }
    });

    $(".validate_name_arm").on('keypress', function (e) {
        var charCode = (e.which) ? e.which : e.keyCode;

        if ((charCode >= 1377 && charCode <= 1415) || (charCode >= 1329 && charCode <= 1366) ||
            charCode == 45 || charCode == 32 || charCode == 34) {
        } else {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }
    });

    $(".validate_name_rus").on('keypress', function (e) {
        var charCode = (e.which) ? e.which : e.keyCode;

        if ((charCode >= 1040 && charCode <= 1071) || (charCode >= 1072 && charCode <= 1103) || charCode == 1025 || charCode == 1105 ||
            charCode == 45 || charCode == 32 || charCode == 34) {
        } else {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }
    });

    $(".validate_name_eng").on('keypress', function (e) {
        var charCode = (e.which) ? e.which : e.keyCode;

        if ((charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90) ||
            charCode == 45 || charCode == 32 || charCode == 34) {
        } else {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }
    });

    $(".validate_arm_num").on('keypress', function (e) {
        var charCode = (e.which) ? e.which : e.keyCode;
        console.log(charCode);
        if ((charCode >= 1377 && charCode <= 1415) || (charCode >= 1329 && charCode <= 1366) ||
            charCode == 44 || charCode == 45 || charCode == 46 || charCode == 47 || charCode == 8228 || charCode == 32 || charCode == 34 ||
            (charCode >= 48 && charCode <= 57)
        ) {
        } else {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }
    });

    $(".validate_rus_num").on('keypress', function (e) {
        var charCode = (e.which) ? e.which : e.keyCode;

        if ((charCode >= 1040 && charCode <= 1071) || (charCode >= 1072 && charCode <= 1103) || charCode == 1025 || charCode == 1105 ||
            charCode == 44 || charCode == 45 || charCode == 46 || charCode == 47 || charCode == 32 || charCode == 34 ||
            (charCode >= 48 && charCode <= 57)
        ) {
        } else {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }
    });

    $(".validate_eng_num").on('keypress', function (e) {
        var charCode = (e.which) ? e.which : e.keyCode;

        if ((charCode >= 97 && charCode <= 122) || (charCode >= 65 && charCode <= 90) ||
            charCode == 44 || charCode == 45 || charCode == 46 || charCode == 47 || charCode == 32 || charCode == 34 ||
            (charCode >= 48 && charCode <= 57)
        ) {
        } else {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }
    });

    $(".validate_number_int_double_point").on('keypress', function (e) {

        var charCode = (e.which) ? e.which : e.keyCode;

        console.log(charCode);

        if (charCode > 31 && (charCode < 48 || charCode > 58) && charCode != 45) {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }

    });

    $(".validate_number_int_point").on('keypress', function (e) {

        var charCode = (e.which) ? e.which : e.keyCode;

        console.log(charCode);

        if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }

    });

    $(".validate_number_int_slash").on('keypress', function (e) {

        var charCode = (e.which) ? e.which : e.keyCode;

        console.log(charCode);

        if (charCode > 31 && (charCode < 47 || charCode > 57)) {
            e.preventDefault();
        }
        if (charCode == 18) {
            e.preventDefault();
        }

    });

    var check_value_num_int = $(".check_value_num_int");
    check_value_num_int.addClass('validate_number_int');
    check_value_num_int.on('focusout', function () {
        check_value($(this))
    });

    var check_value_num_float = $(".check_value_num_float");
    check_value_num_float.addClass('validate_number_float');
    check_value_num_float.on('focusout', function () {
        check_value($(this))
    });


    function validateForm(this_form) {
        var this_form_inp;
        $.each(validate_class_name, function (key, re) {
            // console.log(this_form.find("." + key));
            this_form_inp = this_form.find("." + key);

            if (this_form_inp.length > 0) {
                this_form_inp.each(function () {
                    // console.log($(this));
                    set_valid_or_not($(this), key);
                })
            }
        });


        this_form.find(".check_length").each(function () {
            check_length($(this));
        });

        this_form.find(".check_value_num").each(function () {
            check_value($(this));
        });

        if ($('.inp_invalid').length > 0) {

            var $container = $("html,body");
            var $scrollTo = $('.inp_invalid');

            $container.animate({
                scrollTop: $scrollTo.offset().top - $container.offset().top - 50,
                scrollLeft: 0
            }, 500);

            return false;
        }
        return true;
    }

    $("form").on('submit', function (event) {
        // event.preventDefault();
        var this_form = $(this);
        if (!validateForm(this_form)) {
            event.preventDefault();
        }
    });

    check_valid = function checkValid(idName) {
        var this_form = $('#' + idName);
        return validateForm(this_form)
    };

}


function restartValidate() {

    $(".check_length").off('focusout');
    $(".check_value_num_int").off('focusout');
    $(".check_value_num_float").off('focusout');
    $(".validate_number_int").off('keypress');
    $(".validate_number_float").off('keypress');
    $(".validate_num_txt").off('keypress');
    $(".validate_name").off('keypress');
    $(".validate_name_arm").off('keypress');
    $(".validate_name_rus").off('keypress');
    $(".validate_name_eng").off('keypress');
    $(".validate_arm_num").off('keypress');
    $(".validate_rus_num").off('keypress');
    $(".validate_eng_num").off('keypress');
    $(".validate_number_int_double_point").off('keypress');
    $(".validate_number_int_point").off('keypress');
    $(".validate_number_int_slash").off('keypress');
    startValidate();
}

startValidate();
