<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_Order_Export_Admin {

    var $text_domain = 'woocommerce-order-export';
    var $settings_name_now = 'woocommerce-order-export-now';
    var $settings_name_cron = 'woocommerce-order-export-cron';
    var $tempfile_prefix = 'woocommerce-order-file-';
    var $step = 30;
    public static $formats = array('XLS', 'CSV', 'XML','Sage', 'JSON');
    public static $export_types = array('EMAIL', 'FTP', 'HTTP');
    public $url_plugin;
    public $path_plugin;

    public function __construct() {
        $this->url_plugin = dirname(plugin_dir_url(__FILE__)) . '/';
        $this->path_plugin = dirname(plugin_dir_path(__FILE__)) . '/';
        $this->path_views_default = dirname(plugin_dir_path(__FILE__)) . "/view/";

        if (is_admin()) { // admin actions
            add_action('admin_menu', array($this, 'add_menu'));
            add_action('plugins_loaded', array($this, 'load_textdomain'));

            add_action('admin_enqueue_scripts', array($this, 'thematic_enqueue_scripts'));
            add_action('wp_ajax_order_exporter', array($this, 'ajax_gate'));
            add_filter('cron_schedules', array($this, 'create_custom_schedules'), 10, 1);
            add_action('wc_export_cron_job', array($this, 'wc_export_cron_job_f'));
            add_action('wc_export_cron_global', array($this, 'wc_export_cron_global_f'));
        }
    }

    public function install() {
        wp_schedule_event(time(), 'wc_export_30min_global', 'wc_export_cron_global');
    }

    public function uninstall() {
        wp_clear_scheduled_hook("wc_export_cron_global");
    }

    function load_textdomain() {
        load_plugin_textdomain($this->text_domain, false, dirname(plugin_basename(__FILE__)) . '/../i18n/languages');
    }

    public function add_menu() {
        add_submenu_page('woocommerce', __('Export Orders', $this->text_domain), __('Export Orders', $this->text_domain), 'export', 'wc-order-export', array($this, 'render_menu'));
    }

    public function render_menu() {
        //var_dump(wp_next_scheduled('wc_export_cron_job', array('job_id' => intval(2))));
        //die();
        //$this->next_event_for_schedule_weekday(array("Thu","Sat"),"5:00");
        //wp_clear_scheduled_hook('wc_export_cron_job',array('job_id' => 6));
        //$this->init_cron_jobs();
        $this->render('main', array('WC_Order_Export' => $this, 'ajaxurl' => admin_url('admin-ajax.php')));
        $active_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'export';
        if (method_exists($this, 'render_tab_' . $active_tab)) {
            $this->{'render_tab_' . $active_tab}();
        }
    }

    public function render_tab_export() {
        $this->render('export', array('ajaxurl' => admin_url('admin-ajax.php'), 'WC_Order_Export' => $this));
    }

    public function render_tab_schedules() {
        $wc_oe = isset($_REQUEST['wc_oe']) ? $_REQUEST['wc_oe'] : '';
        $ajaxurl = admin_url('admin-ajax.php');
        $all_jobs = get_option($this->settings_name_cron, array());
        switch ($wc_oe) {
            case 'add_schedule':
                $show = array('date_filter' => false, 'export_button' => false, 'destinations' => true, 'schedule' => true,);
                end($all_jobs);
                $next_id = key($all_jobs) + 1;
                $this->render('settings-form', array('mode' => 'cron', 'id' => $next_id, 'WC_Order_Export' => $this, 'ajaxurl' => $ajaxurl, 'show' => $show));
                return;
                break;

            case 'edit_schedule':
                $schedule_id = isset($_REQUEST['schedule_id']) ? $_REQUEST['schedule_id'] : '';
                if ($schedule_id) {
                    $show = array('date_filter' => false, 'export_button' => false, 'destinations' => true, 'schedule' => true,);
                    $this->render('settings-form', array('mode' => 'cron', 'id' => $schedule_id, 'WC_Order_Export' => $this, 'ajaxurl' => $ajaxurl, 'show' => $show));
                }
                return;
                break;
            case 'delete_schedule':
                $schedule_id = isset($_REQUEST['schedule_id']) ? $_REQUEST['schedule_id'] : '';
                if ($schedule_id) {
                    unset($all_jobs[$schedule_id]);
                    update_option($this->settings_name_cron, $all_jobs);
                }
                break;
        }
        $this->render('schedules', array('ajaxurl' => $ajaxurl, 'WC_Order_Export' => $this));
    }

    public function get_export_settings($mode, $id = 0) {
        if ($mode == 'now' OR ! $id) {
            $settings = get_option($this->settings_name_now, array());
        } elseif ($mode == 'cron') {
            $all_jobs = get_option($this->settings_name_cron, array());
            if (isset($all_jobs[$id]))
                $settings = $all_jobs[$id];
            else
                $settings = array();
        }

        $defaults = array('statuses' => array(), 'from_date' => '', 'to_date' => '', 'shipping_locations' => array(), 'product_categories' => array(), 'products' => array(), 'product_attributes' => array(), 'product_taxonomies' => array(),
            'format' => 'XLS',
            'format_xls_display_column_names' => 1, 'format_xls_populate_other_columns_product_rows' => 0,
            'format_csv_delimiter' => ',', 'format_csv_linebreak' => '\r\n', 'format_csv_display_column_names' => 1, 'format_csv_add_utf8_bom' => 0, 'format_csv_populate_other_columns_product_rows' => 0,
            'format_xml_root_tag' => 'Orders', 'format_xml_order_tag' => 'Order', 'format_xml_product_tag' => 'Product', 'format_xml_coupon_tag' => 'Coupon',
            'format_sage_root_tag' => 'Orders', 'format_sage_order_tag' => 'Order', 'format_sage_product_tag' => 'Product', 'format_sage_coupon_tag' => 'Coupon',
        );

        if (!isset($settings['format']))
            $settings['format'] = 'XLS';

        if (!isset($settings['order_fields']))
            $settings['order_fields'] = array();

        $settings['order_fields'] = $settings['order_fields'] + WC_Order_Export_Data_Extractor::get_order_fields($settings['format']);
        if (!isset($settings['order_product_fields']))
            $settings['order_product_fields'] = array();
        $settings['order_product_fields'] = $settings['order_product_fields'] + WC_Order_Export_Data_Extractor::get_order_product_fields($settings['format']);

        if (!isset($settings['order_coupon_fields']))
            $settings['order_coupon_fields'] = array();
        $settings['order_coupon_fields'] = $settings['order_coupon_fields'] + WC_Order_Export_Data_Extractor::get_order_coupon_fields($settings['format']);
        return array_merge($defaults, $settings);
    }

    public function save_export_settings($mode, $id, $options) {

        if ($mode == 'now') {
            update_option($this->settings_name_now, $options);
        } elseif ($mode == 'cron') {
            $all_jobs = get_option($this->settings_name_cron, array());
            if ($id)
                $all_jobs[$id] = $options;
            else
                $all_jobs[] = $options; // new job

            wp_clear_scheduled_hook('wc_export_cron_job', array('job_id' => intval($id)));
            update_option($this->settings_name_cron, $all_jobs);
        }
        return $id;
    }

    public function thematic_enqueue_scripts() {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
        //wp_enqueue_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.min.js', array( 'jquery' ), '3.5.2' );
        wp_enqueue_script('select22', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js', array('jquery'), '3.5.2');
        //wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
        wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css', array(), WC_VERSION);
        wp_enqueue_script('export', $this->url_plugin . 'assets/js/export.js');
        wp_enqueue_style('export', $this->url_plugin . 'assets/css/export.css');
    }

    public function render($view, $params = array(), $path_views = null) {

        extract($params);
        if ($path_views) {
            include $path_views . "$view.php";
        } else {
            include $this->path_views_default . "$view.php";
        }
    }

    public function get_value($arr, $name) {
        $arr_name = explode(']', $name);
        $arr_name = array_map(function($name) {
            if (substr($name, 0, 1) == '[') {
                $name = substr($name, 1);
            }
            return trim($name);
        }, $arr_name);
        $arr_name = array_filter($arr_name);

        foreach ($arr_name as $value) {
            $arr = isset($arr[$value]) ? $arr[$value] : "";
        }
        return $arr;
    }

    // AJAX part
    // calls ajax_action_XXXX
    public function ajax_gate() {
        $url = plugins_url('../', __FILE__);
        if (isset($_REQUEST['method'])) {
            $method = 'ajax_action_' . $_REQUEST['method'];
            if (method_exists($this, $method))
                $this->$method();
        }
        die();
    }

    private function make_new_settings($in) {
        $in = stripslashes_deep($in);
        $new_settings = $in['settings'];

        // UI don't pass empty multiselects
        $multiselects = array('statuses', 'product_categories', 'products', 'shipping_locations', 'product_attributes', 'product_taxonomies');
        foreach ($multiselects as $m_select) {
            if (!isset($new_settings[$m_select]))
                $new_settings[$m_select] = array();
        }

        $settings = $this->get_export_settings($in['mode'], $in['id']);
        // setup new values for same keys
        foreach ($new_settings as $key => $val)
            $settings[$key] = $val;

        $sections = array('orders' => 'order_fields', 'products' => 'order_product_fields', 'coupons' => 'order_coupon_fields');
        foreach ($sections as $section => $fieldset) {
            $new_order_fields = array();
            $in_sec = $in[$section];

            if ($in_sec['colname'])
                foreach ($in_sec['colname'] as $field => $colname) {
                    $opts = array("checked" => $in_sec['exported'][$field], "colname" => $colname, "label" => $in_sec['label'][$field]);
                    // for products & coupons
                    if (isset($in_sec['repeat'][$field]))
                        $opts["repeat"] = $in_sec['repeat'][$field];
                    //for orders
                    if (isset($in_sec['segment'][$field]))
                        $opts["segment"] = $in_sec['segment'][$field];
                    //for static fields
                    if (isset($in_sec['value'][$field]))
                        $opts["value"] = $in_sec['value'][$field];
                    $new_order_fields[$field] = $opts;
                }

            $settings[$fieldset] = $new_order_fields;
        }
        return $settings;
    }

    public function ajax_action_save_settings() {
        $settings = $this->make_new_settings($_POST);
        //print_r(array($_POST['mode'], $_POST['id'], $settings));
        $this->save_export_settings($_POST['mode'], $_POST['id'], $settings);
        //_e("Settings Updated", 'woocommerce-order-export');
    }

    public function ajax_action_get_products() {
        global $wpdb;
        $like = $wpdb->esc_like($_REQUEST['q']);
        $query = "
                SELECT      post.ID as id,post.post_title as text,att.ID as photo_id,att.guid as photo_url
                FROM        " . $wpdb->posts . " as post
                LEFT JOIN  " . $wpdb->posts . " AS att ON post.ID=att.post_parent AND att.post_type='attachment'
                WHERE       post.post_title LIKE '%{$like}%'
                AND         post.post_type = 'product'
                GROUP BY    post.ID
                ORDER BY    post.post_title
                LIMIT 0,5
                ";
        $products = $wpdb->get_results($query);
        foreach ($products as $key => $product) {
            if ($product->photo_id) {
                $photo = wp_get_attachment_image_src($product->photo_id, 'thumbnail');
                $products[$key]->photo_url = $photo[0]; //debug
            }
        }
        echo json_encode($products);
    }

    public function ajax_action_get_categories() {
        $cat = array();
        foreach (get_terms('product_cat', 'hide_empty=0&hierarchical=1&name__like=' . $_REQUEST['q'] . '&number=10') as $term) {
            $cat[] = array("id" => $term->term_id, "text" => $term->name);
        }
        echo json_encode($cat);
    }

    public function ajax_action_test_destination() {
        $settings = $this->make_new_settings($_POST);
        // use unsaved settings
        $file = WC_Order_Export_Engine::build_file($settings, 'preview', 'file');
        $result = WC_Order_Export_Engine::export($settings, $file);
        echo $result;
    }

    public function ajax_action_preview() {
        $settings = $this->make_new_settings($_POST);
        // use unsaved settings
        WC_Order_Export_Engine::build_file($settings, 'preview', 'browser');
    }

    public function ajax_action_get_products_attributes_values() {

        $data = false;

        $attrs = wc_get_attribute_taxonomies();

        foreach ($attrs as $item) {
            if ($item->attribute_label == $_POST['attr'] && $item->attribute_type != 'select') {
                break;
            } elseif ($item->attribute_label == $_POST['attr']) {

                $name = wc_attribute_taxonomy_name($item->attribute_name);

                $values = get_terms($name, array('hide_empty' => false));
                if (is_array($values)) {
                    $data = array_map(function( $elem ) {
                        return $elem->slug;
                    }, $values);
                } else {
                    $data = array();
                }
                break;
            }
        }
        echo json_encode($data);
    }

    public function ajax_action_get_products_shipping_values() {

        global $wpdb;

        $data = false;

        $query = $wpdb->prepare('SELECT meta_value FROM ' . $wpdb->postmeta . ' WHERE meta_key = %s', array('_shipping_' . strtolower($_POST['item'])));
        $results = $wpdb->get_results($query);
        $data = array_filter(array_unique(array_map(function( $elem ) {
                            return $elem->meta_value;
                        }, $results)), function( $elem ) {
            return !empty($elem);
        });

        echo json_encode($data);
    }

    public function send_headers($format) {
        switch ($format) {
            case 'XLS':
                header('Content-type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename="orders.xlsx"');
                break;
            case 'CSV':
                header('Content-type: text/csv');
                header('Content-Disposition: attachment; filename="orders.csv"');
                break;
            case 'JSON':
                header('Content-type: application/json');
                header('Content-Disposition: attachment; filename="orders.json"');
                break;
            case 'XML':
                header('Content-type: text/xml');
                header('Content-Disposition: attachment; filename="orders.xml"');
                break;
            case 'Sage':
                header('Content-type: application/atom+xml');
                header('Content-Disposition: attachment; filename="orders.xml"');
                break;
        }
    }

    public function ajax_action_export_start() {
        $settings = $this->make_new_settings($_POST);

        $filename = tempnam("/tmp", "orders");
        file_put_contents($filename, '');
        $total = WC_Order_Export_Engine::build_file($settings, 'estimate', 'file', 0, 0, $filename);

        $file_id = current_time('timestamp');
        set_transient($this->tempfile_prefix . $file_id, $filename, 60);
        echo json_encode(array('total' => $total, 'file_id' => $file_id));
    }

    private function get_temp_file_name() {
        $filename = get_transient($this->tempfile_prefix . $_REQUEST['file_id']);
        if ($filename === false) {
            echo json_encode(array('error' => __('Can not find exported file', 'woocommerce-order-export')));
            die();
        }
        set_transient($this->tempfile_prefix . $_REQUEST['file_id'], $filename, 60);
        return $filename;
    }

    public function ajax_action_export_part() {
        $settings = $this->make_new_settings($_POST);

        WC_Order_Export_Engine::build_file($settings, 'partial', 'file', intval($_POST['start']), $this->step, $this->get_temp_file_name());
        echo json_encode(array('start' => $_POST['start'] + $this->step));
    }

    public function ajax_action_export_finish() {
        $settings = $this->make_new_settings($_POST);
        WC_Order_Export_Engine::build_file($settings, 'finish', 'file', 0, 0, $this->get_temp_file_name());
    }

    public function ajax_action_export_download() {

        $format = basename($_GET['format']);
        $filename = $this->get_temp_file_name();
        delete_transient($this->tempfile_prefix . $_GET['file_id']);

        $this->send_headers($format);
        readfile($filename);
        unlink($filename);
    }

    public function create_custom_schedules($schedules) {

        $schedules['wc_export_30min_global'] = array(
            'interval' => 1800,
            'display' => 'Once Every 30 Minutes'
        );
        $items = get_option('woocommerce-order-export-cron', array());
        foreach ($items as $key => $value) {
            if ($value['schedule']['type'] == 'schedule-2')
                if (!empty($value['schedule']['custom_interval']))
                    $schedules['wc_export_interval_' . $value['schedule']['custom_interval']] = array(
                        'interval' => $value['schedule']['custom_interval'] * 60,
                        'display' => 'Every ' . $value['schedule']['custom_interval'] . ' Minutes'
                    );
        }

        return $schedules;
    }

    public function init_cron_jobs() {
        $items = get_option('woocommerce-order-export-cron', array());
        foreach ($items as $key => $value) {
            $schedule = wp_get_schedule('wc_export_cron_job', array('job_id' => $key));
            if (!$schedule) {
                //              die('tut');
                if ($value['schedule']['type'] == 'schedule-2')
                    if (!empty($value['schedule']['custom_interval']))
                        wp_schedule_event(time(), 'wc_export_interval_' . $value['schedule']['custom_interval'], 'wc_export_cron_job', array('job_id' => $key));
                    else
                        wp_schedule_event(time(), $value['schedule']['interval'], 'wc_export_cron_job', array('job_id' => $key));
            }
        }
    }

    public function wc_export_cron_job_f($job_id) {
        ///do cron job here
    }

    public function wc_export_cron_global_f() {
        $items = get_option('woocommerce-order-export-cron', array());
        foreach ($items as $key => $item) {
            if ($item['schedule']['type'] == 'schedule-1')
                if (in_array(date('D'), array_keys($item['schedule']['weekday']))) {//ÐºÑ€Ð¾Ð½ Ð²Ð¾Ñ€Ð´Ð¿Ñ€ÐµÑ�Ñ�Ð° Ð²Ñ�Ðµ Ð¿Ð¾Ñ€Ñ‚Ð¸Ñ‚.Ð¿Ð¾ÐºÐ° Ð´Ñ€ÑƒÐ³Ð¾Ð³Ð¾ Ð² Ð³Ð¾Ð»Ð¾Ð²Ñƒ Ð½Ðµ Ð¿Ñ€Ð¸ÑˆÐ»Ð¾. Ñ„Ð»Ð°Ð³Ð¸?
                    $sec = strtotime($item['schedule']['run_at']);
                    $sec_now = strtotime('now');
                    if (($sec < $sec_now) && (($sec_now - $sec) < 1800)) {
                        //do cron job
                    }
                }
        }
    }

    public static function next_event_for_schedule_weekday($weekdays, $runat, $timestamp = false) {
        for ($index = 0; $index < 7; $index++) {
            if (in_array(date("D", strtotime("+{$index} day")), $weekdays)) {
                if ($timestamp)
                    return strtotime(date("M j Y", strtotime("+{$index} day")) . " " . $runat);
                else
                    return date("D M j Y", strtotime("+{$index} day")) . " at " . $runat;
            }
        }
    }

}
