<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_Table_Schedules extends WP_List_Table {

    var $current_destination = '';

    public function __construct() {

        parent::__construct(array(
            'singular' => __('item', 'woocommerce-pickingpal'),
            'plural' => __('items', 'woocommerce-pickingpal'),
            'ajax' => true
        ));
    }

    /**
     * Output the report
     */
    public function output() {
        $this->prepare_items();
        ?>

        <div class="wp-wrap">
            <?php
            $this->display();
            ?>
        </div>
        <?php
    }

    public function display_tablenav($which) {
        if ('top' != $which)
            return;
        ?>
        <div>
            <input type="button" class="button-secondary" value="<?php _e('Add Schedule','woocommerce-order-export');?>" id="add_schedule">
        </div>
        <?php
    }

    public function prepare_items() {


        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();

        $this->_column_headers = array($columns, $hidden, $sortable);

//		$this->items = array(
//			0 => array( 'recurrence' => 2 ),
//		);
        $this->items = get_option('woocommerce-order-export-cron', array());

        foreach ($this->items as $index => $item) {
            $this->items[$index]['id'] = $index;
        }
//		var_dump( $this->items );
    }

    public function get_columns() {
        $columns = array();
        $columns['recurrence'] = __('Recurrence', 'woocommerce-order-export');
        $columns['destination'] = __('Destination', 'woocommerce-order-export');
        $columns['destination_details'] = __('Destination Details', 'woocommerce-order-export');
        $columns['next_event'] = __('Next event', 'woocommerce-order-export');
        $columns['actions'] = __('Actions', 'woocommerce-order-export');
        return $columns;
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'recurrence':
                $r = '';
                if (isset($item['schedule'])) {
                    if ($item['schedule']['type'] == 'schedule-1') {
                        $r = __('Run ', 'woocommerce-order-export');
                        if (isset($item['schedule']['weekday'])) {
                            $days = array_keys($item['schedule']['weekday']);
                            $r .= __(" on ", 'woocommerce-order-export') . implode(', ', $days);
                        }
                        if (isset($item['schedule']['run_at'])) {
                            $r .= __('  at ', 'woocommerce-order-export') . $item['schedule']['run_at'];
                        }
                    } else {
                        if ($item['schedule']['interval'] == 'custom') {
                            $r = sprintf(__("to run every %s minute(s)", 'woocommerce-order-export'), $item['schedule']['custom_interval']);
                        } else {
                            foreach (wp_get_schedules() as $name => $schedule) {
                                if ($item['schedule']['interval'] == $name)
                                    $r = $schedule['display'];
                            }
                        }
                    }
                }
                return $r;
            case 'destination':
                $this->current_destination = $item['destination']['type'];
                $al = array('ftp' => __('Ftp', 'woocommerce-order-export'), 'http' => __('Http post', 'woocommerce-order-export'), 'email' => __('Email', 'woocommerce-order-export') );
                if (isset($item['destination']['type'])) {
                    return $al[$item['destination']['type']];
                }
                return '';
            case 'destination_details':
                if ($this->current_destination == 'http')
                    return esc_html($item['destination']['http_post_url']);
                if ($this->current_destination == 'email')
                    return __('Subject: ', 'woocommerce-order-export') . esc_html($item['destination']['email_subject']) . "<br>" . __('To: ', 'woocommerce-order-export') . esc_html($item['destination']['email_recipients']);
                if ($this->current_destination == 'ftp')
                    return esc_html($item['destination']['ftp_user']) . "@" . esc_html($item['destination']['ftp_server']) . $item['destination']['ftp_path'];

                //print_r($item);
                return '';
            case 'next_event':
                if ($item['schedule']['type'] == 'schedule-1')
                    return WC_Order_Export_Admin::next_event_for_schedule_weekday(array_keys($item['schedule']['weekday']), $item['schedule']['run_at']);
                else {
                    $timestamp = wp_next_scheduled('wc_export_cron_job', array('job_id' => intval($item['id'])));
                    if ($timestamp && $timestamp > 0)
                        return date("D M j Y G:i:s", $timestamp);
                    else
                        return __("At next page refresh", 'woocommerce-order-export');
                }
            case 'actions':
                return '<div class="btn-edit button-secondary" data-id="' . $item['id'] . '"><span class="dashicons dashicons-edit"></span></div>' .
                        '<div class="btn-trash button-secondary" data-id="' . $item['id'] . '"><span class="dashicons dashicons-trash"></span></div>';
                break;
            default:

                return isset($item[$column_name]) ? $item[$column_name] : '';
        }
    }

}
