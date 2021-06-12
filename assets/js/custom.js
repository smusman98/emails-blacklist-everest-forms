jQuery( document ).ready( function(){

    //On change, Change fields of forms
    jQuery( '#forms' ).change( function () {
        var selectedFormID = jQuery('#forms').val();

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'select_form',
                form_id: selectedFormID
            },
            success:function( data ) {
                data = JSON.parse( data );
                var counter = 0;
                jQuery("#field").html('');
                jQuery.each( data, function () {
                    jQuery("#field").append('<option value="'+ data[counter][0] +'">'+ data[counter][1] +'</option>');
                    counter++;
                } )
            }
        });
    } );

    //Blacklist a form
    jQuery( document ).on( 'click', '#save', function(){
        var selectedForm = jQuery( "#forms option:selected" ).text();
        var formID = jQuery( '#forms' ).val();
        var field = jQuery( '#field' ).val();
        var selectedField = jQuery( "#field option:selected" ).text();
        var value = jQuery( '#value' ).val();
        var by = jQuery('input[name="by"]:checked').val();

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data:{
                action: 'blacklist_form',
                form_id: formID,
                field: field,
                value: value,
                by: by
            },
            success:function( data ) {
                if ( data == 'Empty' )
                {
                    alert( 'All Fields Are Required!' );
                    return false;
                }

                jQuery( '#blacklisted-forms' ).after(
                    '<tr>' +
                        '<th>'+selectedForm+'</th>' +
                        '<th>'+selectedField+'</th>' +
                        '<th>'+value+'</th>' +
                        '<th><input type="button" class="delete button button-secondary" data-id="'+formID+'" value="Delete"></th>' +
                    '</tr>'
                );
            }
        })
    } );

    //Delete Blacklisted
    jQuery( document ).on( 'click', '.delete', function(){
        var formID = jQuery( this ).data( 'id' );

        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data:{
                action: 'delete',
                form_id: formID
            },
            success:function( data ) {
                if ( data == 'deleted' )
                {
                    alert( 'Deleted' );
                    location.reload();
                }
            }
        })
    } )
} )
