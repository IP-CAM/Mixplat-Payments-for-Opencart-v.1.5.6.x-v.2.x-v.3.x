<?php
class ModelExtensionPaymentMixplat extends Model {
    private $pname = 'mixplat';

    public function getMethod($address, $total) {

        $method_data = $this->secondmodel($address, $total, $this->pname);
        return $method_data;

    }

    public function secondmodel($address, $total, $pname) {

        $method_data = array();

        $this->load->language('extension/payment/mixplatpro');
        $this->load->language('extension/payment/' . $pname);

        if ($total > 0) {
            $status = true;

            if ($this->config->get($pname . '_status')) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get($pname . '_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

                if (!$this->config->get($pname . '_geo_zone_id')) {
                    $status = true;

                } elseif ($query->num_rows) {
                    $status = true;
                } else {
                    $status = false;
                }
            } else {
                $status = false;
            }
        } else {
            $status = false;
        }

        if ($status) {
            
            $metname = htmlspecialchars_decode($this->config->get($pname . '_name_' . $this->config->get('config_language_id')));

            $method_data = array(
                'code'       => $pname,
                'title'      => $metname,
                'terms'      => '',
                'sort_order' => $this->config->get($pname . '_sort_order'),
            );
        }

        return $method_data;
    }

    public function getOrderStatus($order_status_id) {

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int) $order_status_id . "' AND language_id = '" . (int) $this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function setPaymentStatus($order_info, $invoiceId = 0, $status = 0, $orderSumAmount = 0, $orderCreatedDatetime = 0) {

        $query = $this->db->query("INSERT INTO `" . DB_PREFIX . "mixplat` SET `num_order` = '" . (int) $order_info['order_id'] . "' , `sum` = '" . $this->db->escape($orderSumAmount) . "' , `date_enroled` = '" . $this->db->escape($orderCreatedDatetime) . "', `date_created` = '" . $this->db->escape($order_info['date_added']) . "', `user` = '" . $this->db->escape($order_info['payment_firstname']) . " " . $this->db->escape($order_info['payment_lastname']) . "', `email` = '" . $this->db->escape($order_info['email']) . "', `status` = '" . (int) $status . "', `sender` = '" . $this->db->escape($invoiceId) . "' ");

    }

    public function getPaymentStatus($order_id) {

        $query = $this->db->query("SELECT `status` FROM " . DB_PREFIX . "mixplat WHERE num_order = '" . (int) $order_id . "' ");

        return $query->row;
    }

    public function getPaymentIdByNum($num) {
        $query = $this->db->query("SELECT `sender` FROM " . DB_PREFIX . "mixplat WHERE num_order = '" . (int)$num . "' ");

        return $query->row['sender'];
    }

    public function getPaymentAcc($order_id) {

        $query = $this->db->query("SELECT `payment_code`, `order_status_id` FROM " . DB_PREFIX . "order WHERE order_id = '" . (int) $order_id . "' ");

        return $query->row;
    }

    public function getCustomName($product_id, $place) {

        $query = $this->db->query("SELECT `" . $this->db->escape($place) . "` FROM " . DB_PREFIX . "product where product_id='" . (int) $product_id . "' ");
        if ($query->row) {
            return $query->row[$place];
        }

    }

    public function getProc($out_summ, $order_info) {

        if ($this->config->get($order_info['payment_code'] . '_komis')) {

            $totalrub['sum']   = number_format($out_summ * ($this->config->get($order_info['payment_code'] . '_komis') / 100) + $out_summ, 2, '.', '');
            $totalrub['comis'] = $totalrub['sum'] - number_format($out_summ, 2, '.', '');

        } else {
            $totalrub['sum']   = $out_summ;
            $totalrub['comis'] = 0;
        }

        return $totalrub;
    }

    public function getCur($out_summ, $order_info) {

        $currencyData = $this->currencyData($order_info);

        return $this->currency->format($out_summ, $currencyData['currency_code'], $currencyData['currency_value'], false);

    }

    public function currencyData($order_info) {

        $currencyData = array();
        if ($order_info['currency_code'] == 'RUB') {
            $currencyData['currency_code']  = $order_info['currency_code'];
            $currencyData['currency_value'] = $order_info['currency_value'];
        } else {
            $currencyData['currency_code']  = 'RUB';
            $currencyData['currency_value'] = $this->currency->getValue('RUB');
        }

        return $currencyData;
    }

    public function getndsnum($nds) {
        $name = '0';

        if ($nds == 'none') {
            $name = '0';
        }

        if ($nds == 'vat20') {
            $name = '1';
        }

        if ($nds == 'vat120') {
            $name = '2';
        }

        if ($nds == 'vat10') {
            $name = '3';
        }

        if ($nds == 'vat110') {
            $name = '4';
        }

        if ($nds == 'vat0') {
            $name = '5';
        }

        if ($nds == 'none') {
            $name = '6';
        }

        return $name;
    }

    public function getndscode($nds) {
        $name = 'none';

        if ($nds == '0') {
            $name = 'none';
        }

        if ($nds == '1') {
            $name = 'vat20';
        }

        if ($nds == '2') {
            $name = 'vat120';
        }

        if ($nds == '3') {
            $name = 'vat10';
        }

        if ($nds == '4') {
            $name = 'vat110';
        }

        if ($nds == '5') {
            $name = 'vat0';
        }

        if ($nds == '6') {
            $name = 'none';
        }

        return $name;
    }

    public function getCustomFields($order_info, $varabliesd) {

        $instros = explode('$', $varabliesd);
        $instroz = "";
        $pname   = $order_info['payment_code'];

        $currency = $this->currencyData($order_info);

        if ($this->config->get($pname . '_fixen')) {
            if ($this->config->get($pname . '_fixen') == 'fix') {
                $out_summ = $this->config->get($pname . '_fixen_amount');
            } else {
                $out_summ = $order_info['total'] * $this->config->get($pname . '_fixen_amount') / 100;
            }
        } else {
            $out_summ = $order_info['total'];
        }

        foreach ($instros as $instro) {
            if ($instro == 'pay_link' || $instro == 'gopay' || $instro == 'koplate' || $instro == 'order_id' || $instro == 'amount' || $instro == 'fee' || $instro == 'amount_fee' || $instro == 'sum_fee') {

                if ($instro == 'pay_link') {
                    $action       = $order_info['store_url'] . 'index.php?route=extension/payment/mixplat/go';
                    $instro_other = $action . '&order_id=' . $order_info['order_id'] . '&code=' . $this->getSecureCode($order_info['order_id']);
                }

                if ($instro == 'gopay') {
                    $instro_other = $this->url->link('extension/payment/mixplat/pay', 'order_id=' . $order_info['order_id'] . '&code=' . $this->getSecureCode($order_info['order_id']), true);
                }

                if ($instro == 'koplate') {
                    $out_summs    = $this->getCur($out_summ, $order_info);
                    $proc         = $this->getProc($out_summs, $order_info);
                    $instro_other = $proc['sum'];
                }

                if ($instro == 'order_id') {
                    $instro_other = $order_info['order_id'];
                }

                if ($instro == 'amount') {
                    $instro_other = $this->currency->format($out_summ, $currency['currency_code'], $currency['currency_value'], true);
                }

                if ($instro == 'fee') {
                    if ($this->config->get($pname . '_komis')) {
                        $instro_other = $this->config->get($pname . '_komis') . '%';
                    } else { $instro_other = '';}
                }

                if ($instro == 'amount_fee') {
                    if ($this->config->get($pname . '_komis')) {
                        $instro_other = $this->currency->format($out_summ * $this->config->get($pname . '_komis') / 100, $currency['currency_code'], $currency['currency_value'], true);
                    } else { $instro_other = '';}
                }

                if ($instro == 'sum_fee') {
                    if ($this->config->get($pname . '_komis')) {
                        $instro_other = $this->currency->format($out_summ + ($out_summ * $this->config->get($pname . '_komis') / 100), $currency['currency_code'], $currency['currency_value'], true);
                    } else { $instro_other = '';}
                }

                if (isset($order_info[$instro])) {
                    $instro_other = $order_info[$instro];
                }

            } else {
                $instro_other = htmlspecialchars_decode($instro);
            }
            $instroz .= $instro_other;
        }
        return $instroz;
    }

    public function getSecureCode($order_id) {

        return substr(md5($order_id . $this->config->get('config_encryption')), 0, 12);
    }
}
