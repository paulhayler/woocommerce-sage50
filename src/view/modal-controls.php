<div id='fields_control_products' class='fields_control_style' style="display: none;">
    <div class='div_meta' >
        <label><?php _e('Meta key', 'woocommerce-order-export')?>:</label><select id='select_custom_meta_products'>
            <option></option>
                <?php
                foreach (WC_Order_Export_Data_Extractor::get_all_product_custom_meta_fields() as $meta_id => $meta_name) {
                    echo "<option value=$meta_name >$meta_name</option>";
                };
                ?>
            </select>
        <label><?php _e('Taxonomies', 'woocommerce-order-export')?>:</label><select id='select_custom_taxonomies_products'>
            <option></option>
            <?php
            foreach (WC_Order_Export_Data_Extractor::get_product_taxonomies() as $tax_id => $tax_name) {
                echo "<option value=__$tax_name >__$tax_name</option>";
            };
            ?>
        </select>
        <label><?php _e('Column Name', 'woocommerce-order-export')?>:</label><input type='text' id='colname_custom_meta_products'/>
        <div style="text-align: right;">
            <button  id='button_custom_meta_products' class='button-secondary'><?php _e('Add Field', 'woocommerce-order-export')?></button>
        </div>
    </div>
    <div class='div_custom'> 
        <label><?php _e('Column Name', 'woocommerce-order-export')?>:</label><input type='text' id='colname_custom_field_products'/>
        <label><?php _e('Value', 'woocommerce-order-export')?>:</label><input type='text' id='value_custom_field_products'/>
        <div style="text-align: right;">
            <button  id='button_custom_field_products' class='button-secondary'><?php _e('Add Static Field', 'woocommerce-order-export')?></button>
        </div>
    </div>           
</div>

<div id='fields_control_coupons' class='fields_control_style' style="display: none;">
    <div class='div_meta' >
        <label><?php _e('Meta key', 'woocommerce-order-export')?>:</label><select id='select_custom_meta_coupons'>
                <?php
                foreach (WC_Order_Export_Data_Extractor::get_all_coupon_custom_meta_fields() as $meta_id => $meta_name) {
                    echo "<option value=$meta_name >$meta_name</option>";
                };
                ?>
            </select>
        <label><?php _e('Column Name', 'woocommerce-order-export')?>:</label><input type='text' id='colname_custom_meta_coupons'/></label>
        <div style="text-align: right;">
            <button  id='button_custom_meta_coupons' class='button-secondary'><?php _e('Add Field', 'woocommerce-order-export')?></button>
        </div>
    </div>
    <div class='div_custom'> 
        <label><?php _e('Column Name', 'woocommerce-order-export')?>:</label><input type='text' id='colname_custom_field_coupons'/></label>
        <label><?php _e('Value', 'woocommerce-order-export')?>:</label><input type='text' id='value_custom_field_coupons'/></label>
        <div style="text-align: right;">
            <button  id='button_custom_field_coupons' class='button-secondary'><?php _e('Add Static Field', 'woocommerce-order-export')?></button>
        </div>
    </div>           
</div>