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

    //automatically submit the form when eiin has six digits
    $("input[type=text][name=eiin]").on('propertychange change paste input', function(e){
        let value = $(this).val();
        if(value.length == 6){
            let form =  $('#eiinSearch');
            form.trigger( "submit" );
        }
    });

    // FormStar --->
    $('form#eiinSearch').formstar({
        ajax:false,
        beforeSend: function(form){
            form.find('.buttonIcon').addClass('spinner').html('autorenew');
        },
    });

    $('#frmVisitDate').formstar({
        beforeSuccessMessage:function(response, form){
            form.closest('.modal').hide();
        },
        afterSuccessMessage: function(response, form){
            let submitButton = $(form).find('button[type=submit]');
            let trId = $(form).find("input[type=hidden][name=trId]").val();
            let tr = $('tr#'+ trId);
            let visitDate = $(form).find("[name=visitDate]").val();
            let reportingDate = $(form).find("[name=reportingDate]").val();
            tr.find('td.visitSchedule').html(visitDate);
            tr.find('td.reportingDate').html(reportingDate);
            tr.find('td.buttonTd').html('');
            submitButton.text("Ok");
            tr.css("color", "#41f32d");
            setTimeout(function(){
                tr.animate({opacity:0}, 2000,function(){
                    tr.css("color", "#A3B9D8").css("opacity","100%");
                });
            }, 5000);
        }
    });

    $('form#updateNothi').formstar();


    $('form#create-schedule').formstar({
        beforeSuccessMessage:function(response, form){
            form.closest('.modal').hide();
        },
        afterSuccessMessage: function(response, form){
            let submitButton = $(form).find('button[type=submit]');

            //Create new table if not exists-
            if($('div#tableContainer').find('table').length == 0){
                $('div#tableContainer').html('<table class="visits"><tbody></tbody></table>');
            }

            let monthName = $("select[name=month] option:selected").text();
            let year = $("select[name=year]").val();
            let dateOnSchedule = $(form).find('[name=dateOnSchedule]').val();

            let visitSchedule = "";
            let addDateButton = "";
            if(dateOnSchedule != ""){
                visitSchedule = dateOnSchedule;
                addDateButton = '';
            }
            else{
                visitSchedule = monthName + ', ' + year;
                addDateButton = '<span class="addDate" data-visitId='+ response.visitId + '>Add Date</span>';
            }

            let trId =  $('table').find('tr').length + 1;
            let newTr = '<tr id='+ trId +'><td class="visitNo">'+ response.ordinal +' Visit</td><td>Refresh to show</td><td class="visitSchedule">'+ visitSchedule +'</td><td class="reportingDate"></td><td class="buttonTd">'+ addDateButton +'</td></tr>';
            $('table').prepend(newTr);
            let addedTr = $('tr#'+ trId);
            addedTr.css("color", "#41f32d");
            setTimeout(function(){
                addedTr.animate({opacity:0}, 500,function(){
                    addedTr.css("color", "#A3B9D8").css("opacity","100%");
                });
            }, 5000);
        }
    });
    //<---- FormStar

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

    $('.swiftDate').datepicker({
        language: 'en',
        dateFormat: 'dd-mm-yyyy',
        autoClose: true,
        onSelect: function(formattedDate, date, inst) {
            $(inst.el).trigger('change');
            $(inst.el).removeClass('error');
        }
    })

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