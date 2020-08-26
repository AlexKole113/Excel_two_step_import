jQuery(function($){

    $('#excel-two-step-import').on( 'change', '.file-upload-input', function(){

        let img_type = $(this).data('img-type');
        let row      = $(this).data('row');
        let data     = new FormData();

        let files = $(this)[0].files;
        $.each( files, function( key, value ){
            data.append( key, value );
        });
        data.append( 'action', 'excel_two_step_import' );


        $.ajax({
            url: ajaxurl,
            type: "POST",
            cache: false,
            contentType: false,
            processData: false,
            // dataType : "json",
            data: data,
            beforeSend: function() {
                $('#'+img_type+'_'+row).html('<img style="max-width: 50px; display: block; margin: auto;" src="' + excel_two_step_import_object.plugin_url + '/Excel_two_step_import/assets/img/load.gif" >');
            },
            success: function( data ) {
                data = JSON.parse( data );
                if( data.src ) {
                    let images = '<div class="img-collection">';
                    for(let i = 0; i < data.src.length; i++  ){
                        images += '<div class="attach-ajax-img">' +
                                  '<span class="attach-ajax-delete" data-attachid="' + data.id[ i ] + '">&times;</span>' +
                                  '<img src="' + data.src[ i ] + '" />' +
                                  '<input type="hidden" name="excel_two_step_import_'+img_type+'['+row+'][]"  value="' + data.id[ i ] + '"/>' +
                                  '</div>';
                    }
                    images += '</div>';
                    $('#'+img_type+'_'+row).html( images );
                }
            }
        });
    });


    $('#excel-two-step-import').on( 'click', '.attach-ajax-delete', function(){

        let parent = $(this).parent();

        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: { 'excel_two_step_delete_attach' : $(this).data('attachid'),
                    'action' : 'excel_two_step_import'
            },
            beforeSend: function() {
                parent.html('<img style="max-width: 50px; display: block; margin: auto;" src="' + excel_two_step_import_object.plugin_url + '/excel_two_step_import/assets/img/load.gif" >');
            },
            success: function( data ) {

                if( true === Boolean(data) ) {
                    parent.remove();
                } else {
                    console.log( data)
                }

            }
        });

    });


    // delete row
    $('#excel-two-step-import').on( 'click', '.unset-product', function(){
        let product = $(this).data('product')
        $('#'+product).fadeOut(350);
        setTimeout(()=>{
            $('#'+product).remove();
        },350)
    })


    // add col
    $('#add-col').on('click', function(){
        let select_block = $('.add_col_thead').prev().clone();
        let col_num = Number( $(this)[0].getAttribute('data-col') );

        col_num += 1;
        $(this)[0].setAttribute('data-col', col_num );

        select_block.find('select').attr('name', 'excel_two_step_import_prod_attrs['+col_num +']' );


        $('.add_col_thead').before( select_block );
        $('.add_col_tbody').each(function(){
            let row_num = Number( $(this)[0].getAttribute('data-row') );
            let col_num = Number( $(this)[0].getAttribute('data-col') );

            col_num += 1;

            $(this)[0].setAttribute('data-row', row_num );
            $(this)[0].setAttribute('data-col', col_num );

            $(this).before('<td><input name="excel_two_step_import_all_data['+row_num+']['+col_num+']" type="text" value=""></td>' );
        })
    })
})