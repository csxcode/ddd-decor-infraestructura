function InitDataTable() {
    if ($('#data-table-fixed-header tbody tr').hasClass('odd')) {

        $('#data-table-search').DataTable({
            lengthMenu: [20, 40, 60],
            fixedHeader: {
                header: true,
                headerOffset: $('#header').height()
            },
            responsive: true,
            paging: false,
            searching: false,
            info: false,
            bSort: false
        });
    }
}

function SearchData() {
    var model = $("#frmFilter").serialize();
    var url = $('#btnSearch').data('request-url');
    $.get(url, model, function (res) {        
        $("#TableDataContainer").html(res);
        InitDataTable();
    });
}

function ExportData() {
    var model = $("#frmFilter").serialize();
    var _url = $('#btnExport').data('export-url');    
    window.location = _url + '?' + model;  
}


/* --------------------------------------------- */
/*          [Dataentry] Modal: Type              */
/* --------------------------------------------- */

function ShowTypeModal(id, readonly){        
    
    UtilsForAjaxMessages();

    var url = route_show_type;    
    var action = null;
    var prefix = prefix_modal_type;

    if(id == 0){
        action = actionType.CREATE;
    } else {
        if(readonly)
            action = actionType.VIEW;
        else 
            action = actionType.EDIT;
    }    
    
    url = url.replace('PARAM_ID', id);
    url = url.replace('PARAM_ACTION', action); 
    
    $.ajax({
        url: url,
        type: "GET",        
        success: function (response) {

            $('#container_'+prefix).html(response.html);
            $('#'+prefix).modal('show');                                    
            
        },
        error: function () {            
            showErrorMessage();            
        }        
    });        
}

function SaveTypeModal(id) {   

    CloseAllAlters();

    if(!id)
        id = 0;    

    var prefix = prefix_modal_type;
    var form = $('#'+prefix+"_form");
    var errors = form.validator('validate').has('.has-error').length;

    if(errors === 0){                
        var url = form.attr('action').replace(':PARAM_ID', id);
        var data = form.serialize();             
        
        $.ajax({
            url: url,
            type: "POST",  
            data,      
            success: function (response) {       

                // Reload search
                SearchData();
                
                var message = document.createElement("span");
                message.innerHTML = response.message;

                swal({
                    title: response.title,
                    content: message,
                    icon: 'success',
                    buttons: {
                        ok: {
                            text: 'Ok',
                            value: null,
                            visible: true,
                            className: 'btn btn-success',
                            closeModal: true,
                        }
                    }
                }).then((value) => {                    
                    // Remove and hide modal
                    $("#"+prefix).modal('hide').on('hidden.bs.modal', function () {
                        $("#"+prefix).html('');
                    });                    
                });                

            },
            error: function (response) {
                if(response.status == 400){
                    ShowAlertErrors(response.responseJSON.errors, 'container_'+prefix);
                }else{
                    showErrorMessage();     
                }                       
            }        
        });        
    }

}

function DeleteType(id){
    var row = $('.row-type-'+id);
    var value = row.data('value');
    var prefix = prefix_modal_type;

    var text = document.createElement("span");
    text.innerHTML = 'Estas seguro de eliminar el tipo <b>' + value + '</b>?',

    swal({
        title: 'Eliminar Tipo',
        content: text,
        icon: 'warning',
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                className: 'btn btn-default',
                closeModal: true,
            },
            confirm: {
                text: 'Eliminar',
                value: true,
                visible: true,
                className: 'btn btn-warning',
                closeModal: true
            }
        }
    }).then((value) => {

        if (value) {

            CloseAllAlters();
            
            var form = $('#'+prefix+'_form_delete');
            var data = form.serialize();             
            var url = form.attr('action').replace(':PARAM_ID', id);            

            $.ajax({
                url: url,
                type: "POST",  
                data,      
                success: function (response) {                                                           
                    // Reload search
                    SearchData();                                 
                },
                error: function (response) {            
                    if(response.status == 400){                        
                        showMessage("Validación", response.responseJSON.errors, "error", "btn-danger")
                    }else{
                        showErrorMessage();     
                    }                             
                }        
            });                    
        }

    });

}


/* --------------------------------------------- */
/*       [Dataentry] Modal: Subtype              */
/* --------------------------------------------- */

function ShowSubtypeModal(id, type_id, readonly){        
    
    UtilsForAjaxMessages();

    var url = route_show_subtype;    
    var action = null;
    var prefix = prefix_modal_subtype;

    if(id == 0){
        action = actionType.CREATE;
    } else {
        if(readonly)
            action = actionType.VIEW;
        else 
            action = actionType.EDIT;
    }    
    
    url = url.replace('PARAM_ID', id);
    url = url.replace('TYPE_ID', type_id);
    url = url.replace('PARAM_ACTION', action); 
    
    $.ajax({
        url: url,
        type: "GET",        
        success: function (response) {

            $('#container_'+prefix).html(response.html);
            $('#'+prefix).modal('show');                                    
            
        },
        error: function () {            
            showErrorMessage();            
        }        
    });        
}

function SaveSubtypeModal(id) {   

    CloseAllAlters();

    if(!id)
        id = 0;    

    var prefix = prefix_modal_subtype;
    var form = $('#'+prefix+"_form");
    var errors = form.validator('validate').has('.has-error').length;

    if(errors === 0){                
        var url = form.attr('action').replace(':PARAM_ID', id);
        var data = form.serialize();             
        
        $.ajax({
            url: url,
            type: "POST",  
            data,      
            success: function (response) {       

                // Reload search
                SearchData();
                
                var message = document.createElement("span");
                message.innerHTML = response.message;

                swal({
                    title: response.title,
                    content: message,
                    icon: 'success',
                    buttons: {
                        ok: {
                            text: 'Ok',
                            value: null,
                            visible: true,
                            className: 'btn btn-success',
                            closeModal: true,
                        }
                    }
                }).then((value) => {

                    // Remove and hide modal
                    $("#"+prefix).modal('hide').on('hidden.bs.modal', function () {
                        $("#"+prefix).html('');
                    });
                  
                });                

            },
            error: function (response) {
                if(response.status == 400){
                    ShowAlertErrors(response.responseJSON.errors, 'container_'+prefix);
                }else{
                    showErrorMessage();     
                }                       
            }        
        });        
    }

}

function DeleteSubtype(id){
    var row = $('.row-subtype-'+id);    
    var value = row.data('value');
    var prefix = prefix_modal_subtype;

    var text = document.createElement("span");
    text.innerHTML = 'Estas seguro de eliminar el subtipo <b>' + value + '</b>?',

    swal({
        title: 'Eliminar Subtipo',
        content: text,
        icon: 'warning',
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                className: 'btn btn-default',
                closeModal: true,
            },
            confirm: {
                text: 'Eliminar',
                value: true,
                visible: true,
                className: 'btn btn-warning',
                closeModal: true
            }
        }
    }).then((value) => {

        if (value) {

            CloseAllAlters();
            
            var form = $('#'+prefix+'_form_delete');
            var data = form.serialize();             
            var url = form.attr('action').replace(':PARAM_ID', id);            

            $.ajax({
                url: url,
                type: "POST",  
                data,      
                success: function (response) {                                                           
                    // Reload search
                    SearchData();                              
                },
                error: function (response) {            
                    if(response.status == 400){                        
                        showMessage("Validación", response.responseJSON.errors, "error", "btn-danger")
                    }else{
                        showErrorMessage();     
                    }                             
                }        
            });                    
        }

    });

}



/* --------------------------------------------- */
/*       [Dataentry] Modal: Item                 */
/* --------------------------------------------- */

function ShowItemModal(id, subtype_id, readonly){        
    
    UtilsForAjaxMessages();

    var url = route_show_item;    
    var action = null;
    var prefix = prefix_modal_item;

    if(id == 0){
        action = actionType.CREATE;
    } else {
        if(readonly)
            action = actionType.VIEW;
        else 
            action = actionType.EDIT;
    }                   

    url = url.replace('PARAM_ID', id);
    url = url.replace('SUBTYPE_ID', subtype_id);
    url = url.replace('PARAM_ACTION', action);     
    
    $.ajax({
        url: url,
        type: "GET",        
        success: function (response) {

            $('#container_'+prefix).html(response.html);
            $('#'+prefix).modal('show');                                    
            
        },
        error: function () {            
            showErrorMessage();            
        }        
    });        
}

function SaveItemModal(id) {   

    CloseAllAlters();

    if(!id)
        id = 0;    

    var prefix = prefix_modal_item;
    var form = $('#'+prefix+"_form");
    var errors = form.validator('validate').has('.has-error').length;

    if(errors === 0){                
        var url = form.attr('action').replace(':PARAM_ID', id);
        var data = form.serialize();             
        
        $.ajax({
            url: url,
            type: "POST",  
            data,      
            success: function (response) {       

                // Reload search
                SearchData();
                
                var message = document.createElement("span");
                message.innerHTML = response.message;

                swal({
                    title: response.title,
                    content: message,
                    icon: 'success',
                    buttons: {
                        ok: {
                            text: 'Ok',
                            value: null,
                            visible: true,
                            className: 'btn btn-success',
                            closeModal: true,
                        }
                    }
                }).then((value) => {

                    // Remove and hide modal
                    $("#"+prefix).modal('hide').on('hidden.bs.modal', function () {
                        $("#"+prefix).html('');
                    });
                  
                });                

            },
            error: function (response) {
                if(response.status == 400){
                    ShowAlertErrors(response.responseJSON.errors, 'container_'+prefix);
                }else{
                    showErrorMessage();     
                }                       
            }        
        });        
    }

}

function DeleteItem(id){
    var row = $('.row-item-'+id);    
    var value = row.data('value');
    var prefix = prefix_modal_item;

    var text = document.createElement("span");
    text.innerHTML = 'Estas seguro de eliminar el item <b>' + value + '</b>?',

    swal({
        title: 'Eliminar Item',
        content: text,
        icon: 'warning',
        buttons: {
            cancel: {
                text: 'Cancelar',
                value: null,
                visible: true,
                className: 'btn btn-default',
                closeModal: true,
            },
            confirm: {
                text: 'Eliminar',
                value: true,
                visible: true,
                className: 'btn btn-warning',
                closeModal: true
            }
        }
    }).then((value) => {

        if (value) {

            CloseAllAlters();
            
            var form = $('#'+prefix+'_form_delete');
            var data = form.serialize();             
            var url = form.attr('action').replace(':PARAM_ID', id);            

            $.ajax({
                url: url,
                type: "POST",  
                data,      
                success: function (response) {                                                           
                    // Reload search
                    SearchData();                               
                },
                error: function (response) {            
                    if(response.status == 400){                        
                        showMessage("Validación", response.responseJSON.errors, "error", "btn-danger")
                    }else{
                        showErrorMessage();     
                    }                             
                }        
            });                    
        }

    });

}

