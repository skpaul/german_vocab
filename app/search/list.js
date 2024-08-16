$(document).ready(function() {



    $("input[type=radio][name=searchType]").change(function(){
        let selectedValue = $(this).val();
        if(selectedValue == "instituteSearch"){
            $("div.visitCount").removeClass("hidden");
            $("div.visitDetails").addClass("hidden");

            table.showColumn('visitCount');
            table.hideColumn('tentativeMonth');
            table.hideColumn('tentativeYear');
            table.hideColumn('actualVisitDate');
            table.hideColumn('visitorName');
        }
        else{
            table.hideColumn('visitCount');
            $("div.visitCount").addClass("hidden");
            $("div.visitDetails").removeClass("hidden");

            table.hideColumn('visitCount');
            table.showColumn('tentativeMonth');
            table.showColumn('tentativeYear');
            table.showColumn('actualVisitDate');
            table.showColumn('visitorName');
        }
    });

    $(".number").swiftNumericInput({ allowFloat: false, allowNegative: false });

    $('.swiftDate').datepicker({
        language: 'en',
        dateFormat: 'dd-mm-yy',
        autoClose: true,
        // onSelect: function(formattedDate, date, inst) {
        //     $(inst.el).trigger('change');
        //     $(inst.el).removeClass('error');
        // }
    })

    $('.swiftYear').datepicker({
        language: 'en',
        dateFormat: 'yy',
        autoClose: true,
        // onSelect: function(formattedDate, date, inst) {
        //     $(inst.el).trigger('change');
        //     $(inst.el).removeClass('error');
        // }
    })

    //define column header menu as column visibility toggle
    var headerMenu = function(){
        var menu = [];
        var columns = this.getColumns();

        for(let column of columns){

            //create checkbox element using font awesome icons
            let icon = document.createElement("i");
            icon.classList.add("fas");
            icon.classList.add(column.isVisible() ? "fa-check-square" : "fa-square");

            //build label
            let label = document.createElement("span");
            let title = document.createElement("span");

            title.textContent = " " + column.getDefinition().title;

            label.appendChild(icon);
            label.appendChild(title);

            //create menu item
            menu.push({
                label:label,
                action:function(e){
                    //prevent menu closing
                    e.stopPropagation();

                    //toggle current column visibility
                    column.toggle();

                    //change menu item icon
                    if(column.isVisible()){
                        icon.classList.remove("fa-square");
                        icon.classList.add("fa-check-square");
                    }else{
                        icon.classList.remove("fa-check-square");
                        icon.classList.add("fa-square");
                    }
                }
            });
        }

        return menu;
    };

    var table = new Tabulator("#dataTable", {
        height:"500px",
        // layout:"fitColumns",
        layout:"fitDataFill",
        // progressiveLoad:"scroll",
        // progressiveLoadScrollMargin:20,
        paginationSize:1000,
        // progressiveLoad:"load", //enable progressive loading
        // progressiveLoadDelay:200, //wait 200 milliseconds between each request
        placeholder:"No Data Available",
        // ajaxURL: baseUrl +  "/app/admins/list/list-data.php",
        // ajaxParams:{token: "123" },
        ajaxParams: function(){
                        let searchParameters = {};
                        searchParameters.searchType =  $("input[type=radio][name=searchType]:checked").val();
                        searchParameters.visitCount = $("#visitCount").val();
                        searchParameters.instName = $("#instName").val();
                        searchParameters.instNameBangla = $("#instNameBangla").val();
                        searchParameters.thanaName = $("#thanaName").val();
                        searchParameters.district = $("#district").val();
                        searchParameters.level = $("#level").val();
                        searchParameters.type = $("#type").val();
                        searchParameters.management = $("#management").val();
                        searchParameters.mpo = $("#mpo").val();
                        searchParameters.forWhom = $("#forWhom").val();
                        searchParameters.area = $("#area").val();
                        searchParameters.geography = $("#geography").val();
                        searchParameters.nothiNumber = $("#nothiNumber").val();
                        searchParameters.reportStatus = $("#reportStatus").val();
                        searchParameters.visitorId = $("#visitorId").val();
                        searchParameters.tentativeMonth = $("#tentativeMonth").val();
                        searchParameters.tentativeYear = $("#tentativeYear").val();
                        searchParameters.actualVisitDateFrom = $("#actualVisitDateFrom").val();
                        searchParameters.actualVisitDateTo = $("#actualVisitDateTo").val();

                        return searchParameters;
                    },
        columns:[
            {title:"Sl.", formatter:"rownum",visible:true, hozAlign:"center", headerMenu:headerMenu},
            {title:"EIIN", field:"eiin",visible:true},


            // {title:"Roll", field:"roll", hozAlign:"center", formatter:function(cell, formatterParams, onRendered){
            //         var roll = cell.getValue();
            //         var row = cell.getRow();
            //         var batch = row.getData().programBatch;
            //         if(batch > 1){
            //             return roll;
            //         }
            //         else{
            //             return "";
            //         }
            //         -- How to work with date :
            //         var value = cell.getValue();
            //         value = moment(value).format("DD-MM-YYYY");
            //         return value;
            //     } 
            // },

            {title:"Inst. Name", field:"instName", formatter:function(cell, formatterParams, onRendered){
                    return cell.getValue().toLowerCase(); //'text-transform: capitalize;' applied in css rule.
                } 
            },
            {title:"Inst. Name (Bangla)", field:"instNameBangla"},
            {title:"Thana", field:"thanaName", formatter:function(cell, formatterParams, onRendered){
                    return cell.getValue().toLowerCase(); //'text-transform: capitalize;' applied in css rule.
                } 
            },
            {title:"District", field:"district", formatter:function(cell, formatterParams, onRendered){
                    return cell.getValue().toLowerCase(); //'text-transform: capitalize;' applied in css rule.
                } 
            },
            {title:"Level", field:"level", formatter:function(cell, formatterParams, onRendered){
                    return cell.getValue().toLowerCase(); //'text-transform: capitalize;' applied in css rule.
                } 
            },
            {title:"Type", field:"type", formatter:function(cell, formatterParams, onRendered){
                    return cell.getValue().toLowerCase(); //'text-transform: capitalize;' applied in css rule.
                } 
            },
            {title:"Management", field:"management", formatter:function(cell, formatterParams, onRendered){
                    return cell.getValue().toLowerCase(); //'text-transform: capitalize;' applied in css rule.
                } 
            },
            {title:"MPO", field:"mpo", formatter:function(cell, formatterParams, onRendered){
                    return cell.getValue().toLowerCase(); //'text-transform: capitalize;' applied in css rule.
                } 
            },
            {title:"For whom", field:"forWhom", formatter:function(cell, formatterParams, onRendered){
                    return cell.getValue().toLowerCase(); //'text-transform: capitalize;' applied in css rule.
                } 
            },
            {title:"Area", field:"area", formatter:function(cell, formatterParams, onRendered){
                    return cell.getValue().toLowerCase(); //'text-transform: capitalize;' applied in css rule.
                } 
            },
            {title:"Geography", field:"geography", formatter:function(cell, formatterParams, onRendered){
                    return cell.getValue().toLowerCase(); //'text-transform: capitalize;' applied in css rule.
                } 
            },
            {title:"Nothi No.", field:"nothiNumber", formatter:function(cell, formatterParams, onRendered){
                    return cell.getValue();
                } 
            },
            {title:"Visitor", field:"visitorName", formatter:function(cell, formatterParams, onRendered){
                    var value = cell.getValue();
                    if(value == null){
                        return "";
                    }
                    else{
                        return cell.getValue().toLowerCase(); //'text-transform: capitalize;' applied in css rule.
                    }
                   
                } 
            },
            {title:"Date", field:"actualVisitDate", formatter:function(cell, formatterParams, onRendered){
                    var value = cell.getValue();
                    if(value == null){
                        return "Pending";
                    }
                    else{
                        value = moment(value).format("DD-MM-YYYY");
                        return value;
                    }
                } 
            },

            {title:"Visit Count", field:"visitCount",visible:true, hozAlign:"center"},
            {title:"Month", field:"tentativeMonth",visible:true, hozAlign:"center"},
            {title:"Year", field:"tentativeYear",visible:true, hozAlign:"center"},
        ],
        //Right-click menu --->
        rowContextMenu: rowContextMenuItems,  //<---- Right-click menu

        //Left-click menu ---->
        rowClickMenu:[
            {
                label:"Say Hi",
                action:function(e, row){
                   alert("Hi");
                }
            },
        ], //<----- Left-click menu//

    });

    table.on("tableBuilt", () => {
        table.setPage(1);

        table.hideColumn('tentativeMonth');
        table.hideColumn('tentativeYear');
        table.hideColumn('actualVisitDate');
        table.hideColumn('visitorName');
    });

    $("#search").click(function(){
        $(this).html('Searching ...');
        table.setData(baseUrl +  "/app/search/list-data.php?session=" + encSessionId).then(function(){
            $("#search").html("Search again");
        }).catch(function(error){
            alert(error);
        });
       
    });

    // table.on("rowClick", function(e, row){
    //    let id = row.getData().cinfoId;
    //     window.open(baseUrl +  "/app/admins/details/details.php?session=" + encSessionId + "&id=" + id, '_blank');
    // });

    $("#download").click(function(){
        table.download("csv", "data.csv", {bom:true}); //include BOM in output
    });

    //refer to google doc for more 
    $(".autoComplete").autocomplete({
        minLength:0,   
        delay:0,  
        source: function(request, response){
            let columnName = $(this.element).data('column');
            let iconSpan = $(this.element).closest('.field').find('.auto-complete-loader');
            iconSpan.addClass('spinner').removeClass('hidden');
            let url = baseUrl + '/app/search/auto-complete-data.php?session='+ encSessionId +'&column=' + columnName;
            
            $.get(url,  
                    {
                        term:request.term
                    }, 
                    function(data){
                        response(data);
                        iconSpan.addClass('hidden').removeClass('spinner');
                    },
                    'json'
                );
        }
    }).focus(function(){
        $(this).autocomplete('search', $(this).val())
    });

    $("#district").autocomplete({
        minLength:0,   
        delay:0,  
        source: districts,
    }).focus(function(){
        $(this).autocomplete('search', $(this).val())
    }).change(function(e){
        $("#thanaName").val('');
    });

    $("#thanaName").autocomplete({
        minLength:0,   
        delay:0,  
        source: function(request, response){
            let columnName = $(this.element).data('column');
            let iconSpan = $(this.element).closest('.field').find('.auto-complete-loader');
            let district = $("#district").val();
            iconSpan.addClass('spinner').removeClass('hidden');
            let url = baseUrl + '/app/search/auto-complete-data.php?session='+ encSessionId +'&column=' + columnName + '&district=' + district;
            
            $.get(url,  
                    {
                        term:request.term
                    }, 
                    function(data){
                        response(data);
                        iconSpan.addClass('hidden').removeClass('spinner');
                    },
                    'json'
                );
        }
    }).focus(function(){
        $(this).autocomplete('search', $(this).val())
    });

});//document.ready()