<div>
    <p>
        <input type="file" name="<?php echo esc_attr($this->file_name) ?>" id="excel_two_step_import_file" />
        <label for="excel_two_step_import_file"><?php esc_html_e('Upload file','excel-two-step-import' ) ?></label>
    </p>
</div>
<div class="d-flex">

</div>
<div class="table-wrapper">
    <table id="table" border="1">
        <thead>
            <tr>
                <td class="delete"></td>
                <?php for( $col_num = 0 ;  $col_num < $cols_cont;  $col_num++) { ?>
                    <td>
                        <?php
                            $this->multiple_val( $col_num );
                            $this->show_prod_props( $col_num );
                        ?>
                    </td>
                <?php } ?>
                <td class="add_col add_col_thead">
                    <span data-col="<?php echo esc_attr($cols_cont-1) ?>" id="add-col">+</span>
                </td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $fields as $row => $cols ) { ?>
                <tr id="row-<?php echo esc_attr( $row ); ?>">
                    <td class="delete">
                        <span class="unset-product" data-product="row-<?php echo esc_attr( $row ); ?>">&times;</span>
                    </td>
                    <?php

                        for(  $col_num = 0 ;  $col_num < $cols_cont;  $col_num++ ) {

                        if ( isset( $cols[ $col_num ] ) ) {
                            echo '<td>';
                            $this->input_text( $cols[ $col_num ], $row, $col_num );
                            echo '</td>';
                        } else {
                            echo "<td><input name='{$this->data_name}[$row][$col_num]' type='text' value=''></td>";
                        }

                    }
                    ?>
                    <td data-row="<?php echo esc_attr( $row ) ?>" data-col="<?php echo esc_attr($cols_cont-1) ?>" class="add-col add_col_tbody"></td>
                    <?php  $this->form_product_images ( $row ); ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
