<?php
class ModelExtensionPaymentMixplat extends Model {
    private $key;
    private $iv;

    public function getSettings() {

        $setpro = array('status', 'server', 'debug', 'password', 'project_id', 'payment_form_id', 'twostage', 'otlog', 'komis', 'fixen', 'fixen_amount', 'button_later', 'geo_zone_id', 'sort_order', 'cart', 'tax_system_code', 'nds_important', 'nds', 'customShip', 'show_free_shipping', 'shipping_tax', 'payment_mode_default', 'payment_mode_source', 'payment_subject_default', 'payment_subject_source', 'customName', 'createorder_or_notcreate', 'success_alert_admin', 'success_alert_customer', 'start_status_id', 'on_status_id', 'order_status_id', 'instruction_attach', 'mail_instruction_attach', 'success_comment_attach', 'hrefpage_text_attach', 'success_page_text_attach', 'waiting_page_text_attach', 'fail_page_text_attach');
        return $setpro;
    }

    public function getLangSettings() {

        $setpro = array('instruction', 'mail_instruction', 'success_comment', 'hrefpage_text', 'success_page_text', 'waiting_page_text', 'fail_page_text');
        return $setpro;
    }

    public function getErrSettings() {

        $setpro = array('warning', 'project_id', 'password', 'fixen');
        return $setpro;
    }

    public function getErrSettingsLang() {

        $setpro = array('name');
        return $setpro;
    }

    public function getTwostage($paymentType) {

        if ($paymentType === 'mixplat') {
            $pt = true;
        } else {
            $pt = false;
        }

        return $pt;
    }

    public function getPaymentType($paymentType) {

        if ($paymentType == 'mixplat') {
            $pt = 'all';
        }

        return $pt;
    }

    public function changeStatus($order_id, $status) {

        $this->db->query("UPDATE " . DB_PREFIX . "mixplat SET `status` = '" . (int) $status . "' where num_order='" . (int) $order_id . " '");

    }

    public function getPaymentStatus($order_id) {

        $query = $this->db->query("SELECT `status` FROM " . DB_PREFIX . "mixplat WHERE num_order = '" . (int) $order_id . "' ");

        return $query->row;
    }

    public function getTotalStatus() {

        $sql = "SELECT COUNT(pay_id) AS total FROM " . DB_PREFIX . "mixplat WHERE `status` = 1 OR `status` = 2 OR `status` = 3";

        $query = $this->db->query($sql);

        return $query->row['total'];

    }

    public function getStatus($data) {

        $sql = "SELECT * FROM `" . DB_PREFIX . "mixplat` WHERE `status` = 1 OR `status` = 2 OR `status` = 3 ORDER BY `pay_id` DESC";
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getPayMethods() {

        $yu       = array('mixplat');
        $yu_codes = array();
        foreach ($yu as $yucode) {if ($this->config->get($yucode . '_status')) {$yu_codes[] = $yucode;}}

        return $yu_codes;
    }

    public function getPoles() {

        $pt = array();

        $pt['payment_mode_default'] = array('full_prepayment', 'partial_prepayment', 'advance', 'full_payment', 'partial_payment', 'credit', 'credit_payment');
        $pt['payment_mode_source']  = array(0, 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location');

        $pt['payment_subject_default'] = array('commodity', 'excise', 'job', 'service', 'gambling_bet', 'gambling_prize', 'lottery', 'lottery_prize', 'intellectual_activity', 'payment', 'agent_commission', 'composite', 'another');
        $pt['payment_subject_source']  = array(0, 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location');
        $pt['customName']              = array(0, 'upc', 'ean', 'jan', 'isbn', 'mpn', 'location');

        $pt['tax'] = array('1', '2', '3', '4', '5', '6');

        return $pt;

    }

    public function getPaymentIdByNum($num) {
        $query = $this->db->query("SELECT `sender` FROM " . DB_PREFIX . "mixplat WHERE num_order = '" . (int)$num . "' ");

        return $query->row['sender'];
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
