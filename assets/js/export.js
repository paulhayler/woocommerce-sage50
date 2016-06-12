
function bind_events() {

    jQuery('#attributes').change(function() {

        jQuery('#text_attributes').attr('disabled', 'disabled');
        var data = {
            'attr' : jQuery(this).val(),
            method: "get_products_attributes_values",
            action: "order_exporter"
        };

        jQuery.post(ajaxurl, data, function(response) {
            jQuery('#text_attributes').remove();
            if (response) {
                var options = '';
                jQuery.each(response, function(index, value) {
                    options += '<option>' + value + '</option>';
                });
                jQuery('<select id="text_attributes" style="margin-top: 0px;margin-right: 6px;">' + options + '</select>').insertBefore(jQuery('#add_attributes'));
            }
            else {
                jQuery('<input type="text" id="text_attributes" style="margin-right: 8px;">').insertBefore(jQuery('#add_attributes'));
            }
        }, 'json');
    });

    jQuery('#add_attributes').click(function() {

        var val = jQuery("#text_attributes").val();
        var val2 = jQuery('#attributes').val();
        if (val != null && val2 != null && val.length && val2.length) {
            val =  val2 + '=' + val;

            var f = true;
            jQuery('#attributes_check').next().find('ul li').each(function() {
               if (jQuery(this).attr('title') == val) {
                   f = false;
               }
            });

            if (f) {

                jQuery('#attributes_check').append('<option selected="selected" value="' + val + '">' + val + '</option>');
                jQuery('#attributes_check').select2();

                jQuery('#attributes_check option').each(function() {
                    jQuery('#attributes_check option[value=\"' + jQuery(this).val() + '\"]:not(:last)').remove();
                });

                jQuery("input#text_attributes").val('');
            }
        }

        return false;
    });

    jQuery('#add_taxonomies').click(function() {

        var val = jQuery("#text_taxonomies").val();
        var val2 = jQuery('#taxonomies').val();
        if (val != null && val2 != null && val.length && val2.length) {
            val =  val2 + '=' + val;

            var f = true;
            jQuery('#taxonomies_check').next().find('ul li').each(function() {
               if (jQuery(this).attr('title') == val) {
                   f = false;
               }
            });

            if (f) {

                jQuery('#taxonomies_check').append('<option selected="selected" value="' + val + '">' + val + '</option>');
                jQuery('#taxonomies_check').select2();

                jQuery('#taxonomies_check option').each(function() {
                    jQuery('#taxonomies_check option[value=\"' + jQuery(this).val() + '\"]:not(:last)').remove();
                });

                jQuery("#text_taxonomies").val('');
            }
        }

        return false;
    });
	
    jQuery('#orders_add_custom_field').click(function() {
        jQuery("#fields_control > div").hide();
        jQuery("#fields_control .div_custom").show();

        //add_custom_field(jQuery("#order_fields"),'products','CSV');
        return false;
    });
    jQuery('#orders_add_custom_meta').click(function() {
        jQuery("#fields_control > div").hide();
        jQuery("#fields_control .div_meta").show();

        //add_custom_field(jQuery("#order_fields"),'products','CSV');
        return false;
    });

    jQuery('.button_cancel').click(function() {
        reset_field_contorls();
        return false;
    });

///*CUSTOM FIELDS BINDS
    jQuery('#button_custom_field').click(function() {
        var colname = jQuery('#colname_custom_field').val();
        var value = jQuery('#value_custom_field').val();

        add_custom_field(jQuery("#order_fields"), 'orders', output_format, colname, value);
        reset_field_contorls();
        return false;
    });

    jQuery('#button_custom_meta').click(function() {
        var label = jQuery('#select_custom_meta').val();
        var colname = jQuery('#colname_custom_meta').val();
        add_custom_meta(jQuery("#order_fields"), 'orders', output_format, label, colname);
        reset_field_contorls();
        return false;
    });

/////////////END CUSTOM FIELDS BINDS

    jQuery('#shipping_locations').change(function() {

        jQuery('#text_locations').attr('disabled', 'disabled');
        var data = {
            'item' : jQuery(this).val(),
            method: "get_products_shipping_values",
            action: "order_exporter"
        };

        jQuery.post(ajaxurl, data, function(response) {
            jQuery('#text_locations').remove();
            if (response) {
                var options = '';
                jQuery.each(response, function(index, value) {
                    options += '<option>' + value + '</option>';
                });
                jQuery('<select id="text_locations" style="margin-top: 0px;margin-right: 6px;">' + options + '</select>').insertBefore(jQuery('#add_locations'));
            }
            else {
                jQuery('<input type="text" id="text_locations" style="margin-right: 8px;">').insertBefore(jQuery('#add_locations'));
            }
        }, 'json');
    });

    jQuery('#add_locations').click(function() {

        var val = jQuery("#text_locations").val();
        var val2 = jQuery('#shipping_locations').val();
        if (val != null && val2 != null && val.length && val2.length) {
            val =  val2 + '=' + val;

            var f = true;
            jQuery('#locations_check').next().find('ul li').each(function() {
                if (jQuery(this).attr('title') == val) {
                    f = false;
                }
            });

            if (f) {

                jQuery('#locations_check').append('<option selected="selected" value="' + val + '">' + val + '</option>');
                jQuery('#locations_check').select2();

                jQuery('#locations_check option').each(function() {
                    jQuery('#locations_check option[value=\"' + jQuery(this).val() + '\"]:not(:last)').remove();
                });

                jQuery("input#text_locations").val('');
            }
        }
        return false;
    });
}

function add_bind_for_custom_fields(prefix, output_format, $to) {
    jQuery('#button_custom_meta_' + prefix + '').off();
    jQuery('#button_custom_field_' + prefix + '').off();
    jQuery('#button_custom_field_' + prefix + '').click(function() {
        var colname = jQuery('#colname_custom_field_' + prefix + '').val();
        var value = jQuery('#value_custom_field_' + prefix + '').val();
        jQuery('#colname_custom_field_' + prefix + '').val("");
        jQuery('#value_custom_field_' + prefix + '').val("");
        add_custom_field($to, prefix, output_format, colname, value);
        return false;
    });

    jQuery('#button_custom_meta_' + prefix + '').click(function() {
        var type = jQuery('#select_custom_meta_' + prefix + '').val() != '' ? 'meta' : 'taxonomies';
        type = type + '_' + prefix;
        var label = jQuery('#select_custom_' + type + '').val();
        var colname = jQuery('#colname_custom_meta_' + prefix + '').val();
        if (colname == undefined || colname == '') {
            colname = label;
        }
        console.log(label);
        add_custom_meta($to, prefix, output_format, label, colname);
        jQuery('#select_custom_' + type + '').val("");
        jQuery('#colname_custom_meta_' + prefix + '').val("");
        return false;
    });
}

function reset_field_contorls() {
    jQuery('#fields_control').find('input').val('');
    jQuery("#fields_control > div").hide();
    jQuery("#fields_control .div1").show();
    jQuery("#fields_control .div2").show();
}

function formatItem(item) {
    var markup = '<div class="clearfix">' +
            '<div>';
    if (typeof item.photo_url !== "undefined")
        markup += '<img src="' + item.photo_url + '" style="width: 20%;float:left;" />';
    markup += '<div style="width:75%;float:left;  padding: 5px;">' + item.text + '</div>' +
            '</div>' +
            '</div><div style="clear:both"></div>';

    return markup;
}

function add_custom_field(to, index_p, format, colname, value) {
    var count = (jQuery('input[name*=' + index_p + '\\[label\\]\\[custom_field]')).length;
    console.log();
    var row = '<li class="mapping_row segment_modal_' + index_p + '">\
                                                        <div class="mapping_col_1">\
                                                                <input type=hidden name=' + index_p + '[exported][custom_field_' + count + ']  value="0">\
                                                                <input type=checkbox name=' + index_p + '[exported][custom_field_' + count + ']  value="1" checked>\
                                                                <input class="mapping_fieldname" type=hidden name=' + index_p + '[segment][custom_field_' + count + '] value="misc">\
                                                                <input class="mapping_fieldname" type=hidden name=' + index_p + '[label][custom_field_' + count + '] value="' + colname + '">\
                                                        </div>\
                                                        <div class="mapping_col_2">' + colname + '<a href="#" onclick="remove_custom_field(this);" style="float: right;"><span class="ui-icon ui-icon-trash"></span></a></div>\
                                                        <div class="mapping_col_3"><input class="mapping_fieldname" type=input name=' + index_p + '[colname][custom_field_' + count + '] value="' + colname + '"></div>\
                                                        <div class="mapping_col_3"><input class="mapping_fieldname" type=input name=' + index_p + '[value][custom_field_' + count + '] value="' + value + '"></div>\
                                                </li>\
                        ';
    to.append(row);
}

function add_custom_meta(to, index_p, format, label, colname) {
    var count = (jQuery('input[name*=' + index_p + '\\[label\\]\\[' + format + '\\]\\[custom_meta]')).length;
    console.log();
    var row = '<li class="mapping_row segment_modal_' + index_p + '">\
                                                        <div class="mapping_col_1">\
                                                                <input type=hidden name=' + index_p + '[exported][' + label + ']   value="0">\
                                                                <input type=checkbox name=' + index_p + '[exported][' + label + ']   value="1">\
                                                                <input class="mapping_fieldname" type=hidden name=' + index_p + '[label][' + label + '] value="' + label + '">\
                                                        </div>\
                                                        <div class="mapping_col_2">' + label + '</div>\
                                                        <div class="mapping_col_3"><input class="mapping_fieldname" type=input name=' + index_p + '[colname][' + label + '] value="' + colname + '"></div>\
                                                </li>\
                        ';
    to.append(row);
}

function formatItemSelection(item) {
    return item.text;
}

function select2_inits()
{
    jQuery("#statuses").select2();
    jQuery("#shipping_locations").select2();
    jQuery("#attributes").select2({
        width: 150
    });
    jQuery("#attributes_check").select2();
	
    jQuery("#taxonomies").select2({
        width: 150
    });
    jQuery("#taxonomies_check").select2();
	
    jQuery("#shipping_locations").select2({
        width: 150
    });
    jQuery("#locations_check").select2();



    jQuery("#product_categories").select2({
        ajax: {
            url: ajaxurl,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    method: "get_categories",
                    action: "order_exporter"
                };
            },
            processResults: function(data, page) {
                // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data
                return {
                    results: data
                };
            },
            cache: true
        },
        escapeMarkup: function(markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 3,
        templateResult: formatItem, // omitted for brevity, see the source of this page
        templateSelection: formatItemSelection // omitted for brevity, see the source of this page
    });

    jQuery("#products").select2({
        ajax: {
            url: ajaxurl,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term, // search term
                    page: params.page,
                    method: "get_products",
                    action: "order_exporter"
                };
            },
            processResults: function(data, page) {
                // parse the results into the format expected by Select2.
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data
                return {
                    results: data
                };
            },
            cache: true
        },
        escapeMarkup: function(markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 3,
        templateResult: formatItem, // omitted for brevity, see the source of this page
        templateSelection: formatItemSelection // omitted for brevity, see the source of this page
    });
}
