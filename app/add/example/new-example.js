$(function(){

    $("#dateOnSchedule").change(function(){       
        var value = $(this).val();
        var dateValue = moment(value, "DD-MM-YYYY");
        $('select[name=month]').val(dateValue.month()+1);
        $('select[name=year]').val(dateValue.year());
      });

    $('select[name="visitor[]"]').selectize({
        plugins: ["restore_on_backspace", "clear_button"],
        delimiter: " - ",
        persist: false,
        maxItems: null,
        hideSelected: true,
        // valueField: "email",
        // labelField: "name",
        // searchField: ["name", "email"],
        // options: [
        //   { email: "selectize@risadams.com", name: "Ris Adams" },
        //   { email: "someone@gmail.com", name: "Someone" },
        //   { email: "someone-else@yahoo.com", name: "Someone Else" },
        // ],
      });

    $('form#add-new-word').formstar({onSuccessMessage: function(message){
        //do later.
    }});


    // Show the .addDate modal
    $(document).on("click", ".addDate", function(e){
        e.preventDefault(); 
        let button = $(this);
        $("input[type=hidden][name=visitId]").val($(this).attr('data-visitId'));
        $("input[type=text][name=visitDate]").val('');
        let trId =  button.closest('tr').attr('id');
        $("input[type=hidden][name=trId]").val(trId);
        let visitNo = button.closest('tr').find('td.visitNo').text();
        $('#addVisitDate').find('.modal-title').text(visitNo);
        $('#addVisitDate').show();
    });
    
    $('.cancelModal').click(function(e){
        $(this).closest('.modal').hide();
    });

    // Show the modal
    $('.add-schedule').click(function(e){
        e.preventDefault();       
        $('#addMonthYear').show();
    });
    
    
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



    $(".number").swiftNumericInput({ allowFloat: false, allowNegative: false });

    var $icon = $('.buttonIcon');
    var $submitButton = $('.form-submit-button');

    function beforeSend(){
        $icon.addClass('spinner').html("autorenew").css("color", "#A3B9D8");
        $submitButton.attr('disabled', 'disabled');
    }

    function success(response){
        if(response.issuccess){
            $icon.removeClass('spinner').html("done").css("color", "#A3B9D8");
            window.location = response.redirecturl;
        }
        else{
            $.sweetModal({
                content: response.message,
                icon: $.sweetModal.ICON_WARNING
            });
            $submitButton.removeAttr('disabled');
            $icon.removeClass('spinner').html("arrow_forward").css("color", "#A3B9D8");
        }
    }

    function error(a,b,c){
        $.sweetModal({
            content: 'Failed to communicate with server',
            icon: $.sweetModal.ICON_WARNING
        });

        console.log(b + ", " + c);
        $submitButton.removeAttr('disabled');
        $icon.removeClass('spinner').html("arrow_forward").css("color", "#A3B9D8");
    }

    function validationRule() {
        var checked = false;
        if (!checked) {
            $.sweetModal({
                content: 'Please provide your consent in the declaration section.',
                icon: $.sweetModal.ICON_WARNING
            });
            return false;
        }
        return true;
    }

}); //Document.ready//