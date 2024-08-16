$(function(){
    //remove red border
    //propertychange change keyup paste input
    $("input[type=text]").on('propertychange change keyup paste input', function() {
        $(this).removeClass("error");
    });

    $("select").on('change propertychange paste', function() {
        $(this).removeClass("error");
    });

    $("textarea").on('input propertychange paste', function() {
        $(this).removeClass("error");
    });

    $("input[type=radio]").change(function(){
        $(this).closest("div.radio-group").removeClass("error");
    });

    // SwiftNumeric.prepare('.swiftInteger');
    // SwiftNumeric.prepare('.swiftFloat');

    // $('.swiftDate').datepicker({
    //     language: 'en',
    //     dateFormat: 'dd-mm-yyyy',
    //     autoClose: true,
    //     onSelect: function(formattedDate, date, inst) {
    //         $(inst.el).trigger('change');
    //         $(inst.el).removeClass('error');
    //     }
    // })

    // //Allow user to select only year from datepicker
    // $('.swiftYear').datepicker({
    //     language: 'en',
    //     dateFormat: "yyyy", 
    //     autoClose:true,
    //     showOn: "button",
    //     minView: 'years',
    //     view:"years",
    //     onSelect: function(formattedDate, date, inst) {
    //         $(inst.el).trigger('change');
    //         $(inst.el).removeClass('error');
    //     }
    // })

    // var m = moment("29/02/2004", "DD-MM-YYYY");
    // //alert(m.isValid());
    
    // var a = moment("29/12/2004", "DD-MM-YYYY");
    // var b = moment("27/12/2004", "DD-MM-YYYY");
    
    // var diffDuration = moment.duration(a.diff(b));
    
    // alert(diffDuration.years()); // 8 years
    // alert(diffDuration.months()); // 5 months
    // alert(diffDuration.days()); // 2 days

    function validationRule() {
        if ($("input[name=mobileNo]").val() != $("input[name=reMobileNo]").val()) {
            $.sweetModal({
                content: 'Mobile No. did not match.',
                icon: $.sweetModal.ICON_WARNING
            });
            $("input[name=mobileNo]").addClass('error');
            $("input[name=reMobileNo]").addClass('error');

            return false;
        }


        var checked = $('#DeclarationApproval').is(':checked');
        if (!checked) {
            $.sweetModal({
                content: 'Please provide your consent in the declaration section.',
                icon: $.sweetModal.ICON_WARNING
            });
            return false;
        }
        return true;
    }

    var $icon = $('.m-icons.login');
    var $submitButton = $('button[type="submit"]');

    function beforeSend(){
        $submitButton.attr("disabled", "disabled");
        $icon.html('autorenew').addClass('spinner');
    }
    
    function onSuccess(response){
        // console.log(response);
        if(response.issuccess){
            $icon.removeClass('spinner').html("done").css("color", "green");
            window.location = response.redirecturl;
        }
        else{
            $.sweetModal({
                content: response.message,
                icon: $.sweetModal.ICON_WARNING
            });
            $submitButton.removeAttr('disabled');
            $icon.removeClass('spinner').html("login");
        }
    }
    
    $('form').formstar();

}); //Document.ready//