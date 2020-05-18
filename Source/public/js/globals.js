const actionType = {
    CREATE : 1,
    EDIT : 2,
    VIEW : 3,
    DELETE : 4
}

const ChecklistStatus = {
    APPROVED : 2,
    REJECTED : 3,    
}

function clearForm() {
    $(':input').not(':button, :submit, :reset, :hidden, :checkbox, :radio').val('');
    $(':checkbox, :radio').prop('checked', false);
}

function LoadAndSetEventsUtilsForStoreAndBranches(){

    var uti_a_sb = $('#uti_a_sb').val().split(',');

    // Set Event
    $('#role_id').change(function(e){
        if(uti_a_sb.includes(this.value)){
            $('#section_sb_list').slideDown();
        }else{
            $('#section_sb_list').slideUp();
        }
    });


    // Show visible or not in Load
    var value = $("#role_id option:selected").val();

    if(uti_a_sb.includes(value)){
        $('#section_sb_list').slideDown();
    }else{
        $('#section_sb_list').slideUp();
    }

}

function SetEmptyDatatable(text){
    var html = "<tr> <td colspan='10' class='text-center height-100' style='line-height: 100px;'>"+text+'</td>';
    $('#datatable-body').html(html);
}


function UtilsForAjaxMessages(){
    $('.alert-messages-close').click(function(){
        $('.alert-messages-error').css('display', 'none');
        $('.alert-messages-success').css('display', 'none');
    });
}

function CloseAllAlters(){
    $('.alert-messages-error').css('display', 'none');
    $('.alert-messages-success').css('display', 'none');
}

function ShowAlertErrors(errors, childContainerID){

    if(childContainerID == null)
        childID = '';
    else
        childID = '#' + childContainerID + ' ';

    $(childID + '.alert-messages-error').css('display', '');
    $(childID + '.alert-messages-success').css('display', 'none');

    var htmlErrors = '';

    $.each(errors, function(key, value){
        htmlErrors += '<li>' + value + '</li>'
    });

    $(childID + '.flash-message').html(htmlErrors);
}

function ShowAlertSuccess(message, childContainerID){

    if(childContainerID == null)
        childID = '';
    else
        childID = '#' + childContainerID + ' ';

    $(childID + '.alert-messages-error').css('display', 'none');
    $(childID + '.alert-messages-success').css('display', '');

    $(childID + '.flash-message').html(message);
}

function showErrorMessage() {
    swal({
        title: 'Error',
        text: 'Se ha producido un error. Por favor vuelva a intentarlo.',
        icon: 'error',
        buttons: {
            ok: {
                text: 'Ok',
                value: null,
                visible: true,
                className: 'btn btn-danger',
                closeModal: true,
            }
        }
    });
}

function showMessage(title, text, icon, btnColor) {

    var _text = document.createElement("span");
    _text.innerHTML = text;

    swal({
        title: title,
        content: _text,
        icon: icon,
        buttons: {
            ok: {
                text: 'Ok',
                value: null,
                visible: true,
                className: 'btn ' + btnColor,
                closeModal: true,
            }
        }
    });  
}

function isEmpty(val) {

    // test results
    //---------------
    // []        true, empty array
    // {}        true, empty object
    // null      true
    // undefined true
    // ""        true, empty string
    // ''        true, empty string
    // 0         false, number
    // true      false, boolean
    // false     false, boolean
    // Date      false
    // function  false

    if (val === undefined)
        return true;

    if (typeof (val) == 'function' || typeof (val) == 'number' || typeof (val) == 'boolean' || Object.prototype.toString.call(val) === '[object Date]')
        return false;

    if (val == null || val.length === 0)        // null or 0 length array
        return true;

    if (typeof (val) == "object") {
        // empty object

        var r = true;

        for (var f in val)
            r = false;

        return r;
    }

    return false;
}