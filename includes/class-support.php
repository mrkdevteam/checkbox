<?php

namespace Checkbox;

class Support
{
    /**
     * Preprocess receipt footer
     *
     * @param WC_Order $order instance of order
     * @param string $message
     * @return string
     */
    public static function processReceiptFooter(\WC_Order $order, string $message): string
    {
        if (stripos($message, '[order_id]') !== false) {
            $order_id = $order->id;
            if (!empty($order_id)) {
                $message = str_replace('[order_id]', $order_id, $message);
            } else {
                $message = str_replace('[order_id]', '', $message);
            }
        }
        if (stripos($message, '[website_title]') !== false) {
            $website_title = get_bloginfo('name');
            if (!empty($website_title)) {
                $message = str_replace('[website_title]', $website_title, $message);
            } else {
                $message = str_replace('[website_title]', '', $message);
            }
        }
        if (stripos($message, '[order_created_date]') !== false) {
            $created_date = new \DateTime($order->get_date_created());
            if (!empty($created_date)) {
                $message = str_replace('[order_created_date]', $created_date->format('d-m-Y H:i:s'), $message);
            } else {
                $message = str_replace('[order_created_date]', '', $message);
            }
        }
        if (stripos($message, '[order_paid_date]') !== false) {
            $paid_date = new \DateTime($order->get_date_paid());
            if (!empty($paid_date)) {
                $message = str_replace('[order_paid_date]', $paid_date->format('d-m-Y H:i:s'), $message);
            } else {
                $message = str_replace('[order_paid_date]', '', $message);
            }
        }

        return $message;
    }
}
