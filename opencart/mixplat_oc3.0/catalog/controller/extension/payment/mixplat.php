<?php
class ControllerExtensionPaymentmixplat extends Controller {
    private $pname = 'mixplat';

    public function index($payname = array('name' => 'mixplat')) {

        $pname = isset($payname['name']) ? $payname['name'] : $this->pname;
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/mixplat');
        $this->language->load('extension/payment/mixplatpro');
        $this->language->load('extension/payment/' . $pname);
        $data['instructionat'] = $this->config->get('payment_'.$pname . '_instruction_attach');
        $data['btnlater']      = $this->config->get('payment_'.$pname . '_button_later');
        $order_info            = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $paymentredir          = htmlspecialchars_decode($this->model_extension_payment_mixplat->getCustomFields($order_info, 'gopay') . '&first=1');

        $data['continue'] = $this->url->link('checkout/success', '', 'SSL');

        if ($this->config->get('payment_'.$pname . '_createorder_or_notcreate')) {
            if ($this->config->get('payment_'.$pname . '_otlog') == 'stock') {
                if ($this->cart->hasStock()) {
                    $data['notcreate'] = 'notcreate';
                }
            } else {
                $data['notcreate'] = 'notcreate';
            }
        }

        if ($this->config->get('payment_'.$pname . '_otlog') == 'stock') {
            if ($this->cart->hasStock()) {
                $data['pay_url'] = $paymentredir;
            } else {
                $data['pay_url'] = $this->url->link('checkout/success');
            }

        } else if ($this->config->get('payment_'.$pname . '_otlog') == 'pay') {
            $data['pay_url'] = $this->url->link('checkout/success');
        } else {
            $data['pay_url'] = $paymentredir;
        }

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['payment_url']    = $this->url->link('checkout/success');
        $data['button_later']   = $this->language->get('button_pay_later');
        $data['text_loading']   = $this->language->get('text_loading');

        if ($this->config->get('payment_'.$pname . '_instruction_attach')) {
            $data['text_instruction'] = $this->language->get('text_instruction');
            $data['mixplati']         = $this->model_extension_payment_mixplat->getCustomFields($order_info, $this->config->get('payment_'.$pname . '_instruction_' . $this->config->get('config_language_id')));
        }

        $data['pname'] = $pname;

        return $this->load->view('extension/payment/mixplat', $data);
    }

    public function confirm($payname = array('name' => 'mixplat')) {

        if (strpos($this->session->data['payment_method']['code'], 'mixplat') !== false) {
            $pname   = isset($payname['name']) ? $payname['name'] : $this->pname;
            $comment = '';
            $this->language->load('extension/payment/mixplatpro');
            $this->language->load('extension/payment/' . $pname);
            $this->load->model('extension/payment/mixplat');
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            if ($this->config->get('payment_'.$pname . '_otlog') == 'stock') {
                if ($this->cart->hasStock()) {
                    $ostatus = $this->config->get('payment_'.$pname . '_on_status_id');
                    $comment = sprintf($this->language->get('stock'), $this->model_extension_payment_mixplat->getCustomFields($order_info, 'pay_link'));
                } else {
                    $ostatus = $this->config->get('payment_'.$pname . '_start_status_id');
                    $comment = $this->language->get('no_stock');
                }

            } else if ($this->config->get('payment_'.$pname . '_otlog') == 'pay') {
                $ostatus = $this->config->get('payment_'.$pname . '_start_status_id');
            } else {
                $ostatus = $this->config->get('payment_'.$pname . '_on_status_id');
            }

            if ($this->config->get('payment_'.$pname . '_mail_instruction_attach')) {

                $instroz = $this->model_extension_payment_mixplat->getCustomFields($order_info, $this->config->get('payment_'.$pname . '_mail_instruction_' . $this->config->get('config_language_id')));

                $comment1 = $instroz;
                $comment .= htmlspecialchars_decode($comment1);
            }

            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $ostatus, $comment, true);
        }
    }

    public function pay() {

        if (!isset($this->request->get['code']) && !isset($this->request->get['order_id'])) {
            $this->response->redirect($this->url->link('error/not_found'));
        }

        if (!$this->currency->has('RUB')) {
            echo 'No currency RUB';
            return;
        }

        $this->load->model('checkout/order');
        $this->load->model('extension/payment/mixplat');
        $order_info = $this->model_checkout_order->getOrder($this->request->get['order_id']);

        $platp = $this->model_extension_payment_mixplat->getSecureCode($order_info['order_id']);
        if ($this->request->get['code'] != $platp) {
            $this->response->redirect($this->url->link('error/not_found'));
        }

        if (strpos($order_info['payment_code'], 'mixplat') === false) {
            echo 'error: no payment method';
            return;
        }

        if (!$this->config->get('payment_'.$order_info['payment_code'] . '_createorder_or_notcreate')) {
            if (isset($this->session->data['order_id'])) {
                if ($this->request->get['order_id'] == $this->session->data['order_id']) {
                    $this->cart->clear();
                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                    unset($this->session->data['guest']);
                    unset($this->session->data['comment']);
                    unset($this->session->data['order_id']);
                    unset($this->session->data['coupon']);
                    unset($this->session->data['reward']);
                    unset($this->session->data['voucher']);
                    unset($this->session->data['vouchers']);
                }
            }
        }

        if (isset($this->request->get['first'])) {
            $first = '&first=1';
        } else {
            $first = '';
        }

        $this->language->load('extension/payment/mixplatpro');
        $this->language->load('extension/payment/' . $order_info['payment_code']);

        $result = json_decode($this->createPaymentForm($order_info, $first));

        if (isset($result->redirect_url)) {
            if ($this->config->get('payment_'.$order_info['payment_code'] . '_debug')) {
                echo '<br><br><b>RESULT:</b><br><a href="' . $result->redirect_url . '">' . $result->redirect_url . '</a><br><br>';
            } else {
                $this->response->redirect($result->redirect_url);
            }
        } else {
            $result = (array) $result;
            $this->log->write('mixplat error: code=' . implode(' - ', $result));
            echo '<br><br><b>RESULT:</b><br>mixplat error: ' . implode(' - ', $result);
        }
    }

    private function createPaymentForm($order_info, $first = '') {

        $method     = 'create_payment_form';
        $credintals = $this->getCredintals($order_info['payment_code']);
        $return_url = $this->returnUrl($order_info, $first);
        $request_id = $order_info['order_id'] . time();

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_twostage')) {
            $scheme = 'dms';
        } else {
            $scheme = 'sms';
        }

        $amount = $this->model_extension_payment_mixplat->getCustomFields($order_info, 'koplate');

        $request = array(
            'api_version'         => $credintals['api_version'],
            'request_id'          => $request_id,
            'project_id'          => $credintals['project_id'],
            'merchant_payment_id' => $order_info['order_id'],
            'test'                => $this->testMode($order_info['payment_code']),
            'description'         => sprintf($this->language->get('pay_order_text_target'), $order_info['order_id']),
            'currency'            => 'RUB',
            'user_email'          => $order_info['email'],
            'user_phone'          => preg_replace('/[^0-9]/', '', $order_info['telephone']),
            'url_success'         => $return_url['success'],
            'url_failure'         => $return_url['fail'],
            'notify_url'          => $return_url['notify_url'],
            'payment_form_id'     => $credintals['payment_form_id'],
            'amount'              => $amount * 100,
            'payment_scheme'      => $scheme,
            'signature'           => md5($request_id . $credintals['project_id'] . $order_info['order_id'] . $credintals['api_key']),
        );

        if (!$this->config->get('payment_'.$order_info['payment_code'] . '_cart')) {
            $request['items'] = $this->getReceipt($order_info, $amount);
        }

        return $this->getRequest($request, $method, $order_info['payment_code']);
    }

    private function getRequest($request, $method, $pay_code) {

        $request = json_encode($request);

        if ($this->config->get('payment_'.$pay_code . '_debug')) {
            echo '<br><br><b>REQUEST:</b><br>';
            var_dump($request);
        }

        $server = 'https://api.mixplat.com/' . $method;

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $server);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            $result = curl_exec($curl);
            curl_close($curl);

            if ($this->config->get('payment_'.$pay_code . '_debug')) {
                echo '<br><br><b>ANSWER:</b><br>';
                var_dump($result);
            }

            return $result;

        } else {
            $this->log->write('maxiplat error: No curl');
            exit();
        }

    }

    private function returnUrl($order_info, $first = '') {

        $return_url['notify_url'] = $this->url->link('extension/payment/mixplat/callback', '', true);

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_returnpage')) {
            $return_url['success'] = $this->url->link('checkout/success');
            $return_url['fail']    = $this->url->link('common/home');
        } else {
            $platp                 = $this->model_extension_payment_mixplat->getSecureCode($order_info['order_id']);
            $return_url['success'] = htmlspecialchars_decode($this->url->link('extension/payment/mixplat/success', 'order=' . $order_info['order_id'] . '&code=' . $platp . $first, true));
            $return_url['fail']    = htmlspecialchars_decode($this->url->link('extension/payment/mixplat/fail', 'order=' . $order_info['order_id'] . '&code=' . $platp . $first, true));
        }

        return $return_url;

    }

    private function testMode($pay_code) {

        if ($this->config->get('payment_'.$pay_code . '_server')) {
            return 0;
        } else {
            return 1;
        }
    }

    private function getCredintals($pay_code) {

        $credintals['api_version']     = 3;
        $credintals['project_id']      = $this->config->get('payment_'.$pay_code . '_project_id');
        $credintals['payment_form_id'] = $this->config->get('payment_'.$pay_code . '_payment_form_id');
        $credintals['api_key']         = $this->config->get('payment_'.$pay_code . '_password');
        return $credintals;
    }

    public function callback() {

        $res     = file_get_contents('php://input');
        $resdata = json_decode($res, true);

        if (!isset($resdata['request']) && !isset($resdata['status']) && !isset($resdata['signature'])) {
            echo 'CALLBACK IT\'S HERE';
            return;
        }

        if ($resdata['request'] != 'payment_status') {
            $this->jAnswer('ok');
            return;
        }
        else {      
            if ($resdata['status'] == 'success' && $resdata['status_extended'] == 'success_success') {
                $status = 1;
            }
            else{
                if ($resdata['status'] == 'pending' && $resdata['status_extended'] == 'pending_authorized'){
                    $status = 2;
                }
                else{
                    $this->jAnswer('ok');
                    return;
                }
            }
        }

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($resdata['merchant_payment_id']);

        if (!$order_info['order_id'] || $order_info['order_id'] <= 0) {
            $this->debug('CALLBACK', $res, 'CALLBACK NO ORDER');
            return;
        }

        $mysignature = md5($resdata['payment_id'] . $this->config->get('payment_'.$order_info['payment_code'] . '_password'));
        if ($resdata['signature'] != $mysignature) {
            $this->debug('CALLBACK', $res, 'WRONG SIGNATURE FOR ORDER ' . $order_info['order_id']);
            return;
        }

        $this->load->model('extension/payment/mixplat');
        $paystat = $this->model_extension_payment_mixplat->getPaymentStatus((int) $order_info['order_id']);
        if (!isset($paystat['status'])) {$paystat['status'] = 0;}
        if ($paystat['status'] == 1 || $paystat['status'] == 2 || $paystat['status'] == 3) {
            $this->jAnswer('ok');
            return;
        }

        $out_summ = $this->model_extension_payment_mixplat->getCustomFields($order_info, 'koplate');
        if ($resdata['amount'] != $out_summ * 100) {
            $this->debug('CALLBACK', $res, 'WRONG AMOUNT FOR ORDER ' . $order_info['order_id']);
            return;
        }

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_twostage')) {
            $status = 2;
        } else {
            $status = 1;
        }

        $this->model_extension_payment_mixplat->setPaymentStatus($order_info, $resdata['payment_id'], $status, $out_summ, $resdata['date_created']);

        $this->language->load('extension/payment/mixplatpro');
        $this->language->load('extension/payment/' . $order_info['payment_code']);

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_createorder_or_notcreate') && $order_info['order_status_id'] != $this->config->get('payment_'.$order_info['payment_code'] . '_on_status_id')) {

            if (!$this->config->get('payment_'.$order_info['payment_code'] . '_mail_instruction_attach')) {

                $comment = $this->language->get('text_instruction') . "\n\n";
                $comment .= $this->model_extension_payment_mixplat->getCustomFields($order_info, $this->config->get('payment_'.$order_info['payment_code'] . '_mail_instruction_' . $order_info['language_id']));
                $comment = htmlspecialchars_decode($comment);
                $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_'.$order_info['payment_code'] . '_order_status_id'), $comment, true);
            } else {
                $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_'.$order_info['payment_code'] . '_order_status_id'), '', true);
            }

            if (!$this->config->get('payment_'.$order_info['payment_code'] . '_success_alert_customer')) {
                if ($this->config->get('payment_'.$order_info['payment_code'] . '_success_comment_attach')) {
                    $message = $this->model_extension_payment_mixplat->getCustomFields($order_info, $this->config->get('payment_'.$order_info['payment_code'] . '_success_comment_' . $order_info['language_id']));
                    $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_'.$order_info['payment_code'] . '_order_status_id'), $message, true);
                } else {
                    $message = '';
                    $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_'.$order_info['payment_code'] . '_order_status_id'), $message, true);
                }
            }

        } else {

            if (!$this->config->get('payment_'.$order_info['payment_code'] . '_success_alert_customer')) {
                if ($this->config->get('payment_'.$order_info['payment_code'] . '_success_comment_attach')) {
                    $message = $this->model_extension_payment_mixplat->getCustomFields($order_info, $this->config->get('payment_'.$order_info['payment_code'] . '_success_comment_' . $order_info['language_id']));
                    $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_'.$order_info['payment_code'] . '_order_status_id'), $message, true);
                } else {
                    $message = '';
                    $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_'.$order_info['payment_code'] . '_order_status_id'), $message, true);
                }
            } else {
                $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('payment_'.$order_info['payment_code'] . '_order_status_id'), '', false);

            }

        }

        if (!$this->config->get('payment_'.$order_info['payment_code'] . '_success_alert_admin')) {

            $subject = sprintf(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $order_info['order_id']);
            $text    = sprintf($this->language->get('success_admin_alert'), $order_info['order_id']) . "\n";

            $this->mailAlert($subject, $text, $this->config->get('config_email'), $order_info['store_name'], true);

        }

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_debug')) {
            $this->debug('CALLBACK', $res);
        }

        $this->jAnswer('ok');

    }

    private function mailAlert($subject, $text, $email, $sender, $additional = false) {

        $mail = new Mail($this->config->get('config_mail_engine'));
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
        $mail->setTo($email);
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($sender);
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
        $mail->send();

        if ($additional) {
            $emails = explode(',', $this->config->get('config_mail_alert_email'));

            foreach ($emails as $email) {
                if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }
        }
    }

    public function jAnswer($data) {

        $json           = array();
        $json['result'] = $data;
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function debug($method, $res = '', $error = false) {

        $this->log->write('MIXPLAT DEBUG---------------------' . $method . ' START---------------------------------');
        $this->log->write('POST:');
        $this->log->write($this->request->post);
        $this->log->write('GET:');
        $this->log->write($this->request->get);
        $this->log->write('DATA:');
        $this->log->write($res);
        $this->log->write('MIXPLAT DEBUG---------------------' . $method . ' END---------------------------------');
        if ($error) {
            $this->log->write('mixplat error: ' . $error);
            $this->jAnswer('error');
        }
    }

    public function go() {

        if (!isset($this->request->get['code']) && !isset($this->request->get['order_id'])) {
            echo "No data";
            return;
        }

        $this->load->model('checkout/order');
        $this->load->model('extension/payment/mixplat');
        $order_info = $this->model_checkout_order->getOrder($this->request->get['order_id']);
        $platp      = $this->model_extension_payment_mixplat->getSecureCode($order_info['order_id']);
        if ($this->request->get['code'] != $platp) {
            $this->response->redirect($this->url->link('error/not_found'));
        }
        if ($order_info['order_id'] == 0) {$this->response->redirect($this->url->link('error/not_found'));}
        if (!$this->customer->isLogged()) {
            $data['back'] = $this->url->link('common/home');
        } else {
            $data['back'] = $this->url->link('account/order');
        }

        $data['merchant_url'] = $this->model_extension_payment_mixplat->getCustomFields($order_info, 'gopay');

        $paystat = $this->model_extension_payment_mixplat->getPaymentStatus($order_info['order_id']);
        if (!isset($paystat['status'])) {$paystat['status'] = 0;}

        $this->language->load('extension/payment/mixplatpro');
        $this->load->language('extension/payment/' . $order_info['payment_code']);
        $data['button_pay']    = $this->language->get('button_pay');
        $data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($this->language->get('heading_title'));

        if ($paystat['status'] == 0) {


            $data['paystat'] = 0;

            if (strpos($order_info['payment_code'], 'mixplat') === false) {
                $this->response->redirect($this->url->link('error/not_found'));
            }

            if (!$this->config->get('payment_'.$order_info['payment_code'] . '_status')) {
                $this->response->redirect($this->url->link('error/not_found'));
            }

            if ($this->config->get('payment_'.$order_info['payment_code'] . '_hrefpage_text_attach')) {
                $data['send_text'] = $this->model_extension_payment_mixplat->getCustomFields($order_info, $this->config->get('payment_'.$order_info['payment_code'] . '_hrefpage_text_' . $this->config->get('config_language_id')));
            } else {
                $data['send_text'] = sprintf($this->language->get('send_text'), $order_info['order_id'], $this->model_extension_payment_mixplat->getCustomFields($order_info, 'koplate'));
            }

        } else {
            $data['paystat'] = 1;
            $data['send_text'] = $this->language->get('oplachen');
        }

        $data['column_left']    = $this->load->controller('common/column_left');
        $data['column_right']   = $this->load->controller('common/column_right');
        $data['content_top']    = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/payment/mixplat_go', $data));

    }

    private function getReceipt($order_info, $totalcheck) {

        $this->load->model('extension/payment/mixplat');
        $amount = $totalcheck * 100;
        $currency = $this->model_extension_payment_mixplat->currencyData($order_info);

        $okassacheck = '';
        $okassa = array();

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_customShip') != '') {

            $order_info['shipping_method'] = $this->config->get('payment_'.$order_info['payment_code'] . '_customShip');

        }

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_customName')) {

            $customname = $this->config->get('payment_'.$order_info['payment_code'] . '_customName');

        }

        $this->load->model('account/order');
        $cart_products = $this->model_account_order->getOrderProducts($order_info['order_id']);

        //vouchers
        $vouchersbuy = $this->model_account_order->getOrderVouchers($order_info['order_id']);
        foreach ($vouchersbuy as $voucherbuy) {
            $cart_products[] = array(
                'quantity'   => 1,
                'name'       => $voucherbuy['description'],
                'price'      => $voucherbuy['amount'],
                'product_id' => 0,
                'model'      => 'voucher',
            );

        }
        //vouchers end

        $totals   = $this->model_account_order->getOrderTotals($order_info['order_id']);
        $tax      = 0;
        $voucher  = 0;
        $shipping = 0;
        $subtotal = 0;
        $coupon   = 0;
        foreach ($totals as $total) {
            switch ($total['code']) {
                case 'tax':$tax = $total['value'];
                    break;
                case 'shipping':$shipping = $total['value'];
                    break;
                case 'sub_total':$subtotal = $total['value'];
                    break;
                case 'coupon':$coupon = $total['value'];
                    break;
                case 'voucher':$voucher = $total['value'];
                    break;
            }
        }


        $ndsship = 'none';
        if ($this->config->get('payment_'.$order_info['payment_code'] . '_tax_system_code') == 1 && $this->config->get('payment_'.$order_info['payment_code'] . '_shipping_tax')) {
            $ndsship = $this->model_extension_payment_mixplat->getndscode($this->config->get('payment_'.$order_info['payment_code'] . '_shipping_tax'));
        }

        $paymentMethod = $this->config->get('payment_'.$order_info['payment_code'] . '_payment_mode_default');

        // coupon free shipping
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon_history` WHERE order_id = '" . (int) $order_info['order_id'] . "'");

        if (isset($query->rows)) {
            foreach ($query->rows as $row) {
                $sipcoup = $this->db->query("SELECT `shipping` FROM `" . DB_PREFIX . "coupon` WHERE coupon_id = '" . (int) $row['coupon_id'] . "'");
                if ($sipcoup->row['shipping'] == 1) {
                    $couponship = true;
                }
            }
        }

        if (isset($couponship)) {
            $shipping = 0;
        }

        // coupon free shipping end

        $ndsval = 'none';

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_tax_system_code') == 1 && $this->config->get('payment_'.$order_info['payment_code'] . '_nds_important')) {
            $ndsval = $this->model_extension_payment_mixplat->getndscode($this->config->get('payment_'.$order_info['payment_code'] . '_nds_important'));
        }

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_tax_system_code') == 1 && $this->config->get('payment_'.$order_info['payment_code'] . '_nds') == 'tovar') {
            $ndson = true;
            $this->load->model('catalog/product');
        }

        $paymentMethod = $this->config->get('payment_'.$order_info['payment_code'] . '_payment_mode_default');
        $paymentObject = $this->config->get('payment_'.$order_info['payment_code'] . '_payment_subject_default');
        if ($this->config->get('payment_'.$order_info['payment_code'] . '_payment_mode_source')){
            $pms = $this->config->get('payment_'.$order_info['payment_code'] . '_payment_mode_source');
        }
        if ($this->config->get('payment_'.$order_info['payment_code'] . '_payment_subject_source')){
            $pss = $this->config->get('payment_'.$order_info['payment_code'] . '_payment_subject_source');
        }

        $moden       = ($totalcheck - $this->currency->format($shipping, $currency['currency_code'], $currency['currency_value'], false)) / $this->currency->format($subtotal, $currency['currency_code'], $currency['currency_value'], false);
        $alldiscount = false;

        foreach ($cart_products as $cart_product) {

            if (isset($customname)) {
                $res = $this->model_extension_payment_mixplat->getCustomName($cart_product['product_id'], $customname);
                if ($res != '') {
                    $cart_product['name'] = $res;
                }
            }

            if (isset($pms)) {
                $paymet = $this->model_extension_payment_mixplat->getCustomName($cart_product['product_id'], $pms);
                if ($paymet != '') {
                    $cart_product['pms'] = $paymet;
                }
                else{
                    $cart_product['pms'] = $paymentMethod;
                }
            }
            else {
                $cart_product['pms'] = $paymentMethod;
            }

            if (isset($pss)) {
                $payob = $this->model_extension_payment_mixplat->getCustomName($cart_product['product_id'], $pss);
                if ($payob != '') {
                    $cart_product['pss'] = $payob;
                }
                else{
                    $cart_product['pss'] = $paymentObject;
                }
            }
            else {
                $cart_product['pss'] = $paymentObject;
            }

            $tovprice = number_format($this->currency->format($cart_product['price'], $currency['currency_code'], $currency['currency_value'], false) * $moden, 2, '.', '');
            if ($tovprice < 0) {
                $alldiscount = true;
                break;
            }

            $tovprice = $tovprice*100;

            $ndsvalue = $ndsval;
            if (isset($ndson)) {
                foreach ($this->config->get('payment_'.$order_info['payment_code'] . '_classes') as $tax_rule) {

                    $product_info = $this->model_catalog_product->getProduct($cart_product['product_id']);
                    if (isset($tax_rule[$order_info['payment_code'] . '_nalog']) && isset($product_info['tax_class_id']) && $tax_rule[$order_info['payment_code'] . '_nalog'] == $product_info['tax_class_id']) {
                        $ndsvalue = $this->model_extension_payment_mixplat->getndscode($tax_rule[$order_info['payment_code'] . '_tax_rule']);
                    }
                }
            }

            $okassa['cartItems'][] = array(
                'name'           => mb_substr(addslashes(stripslashes(str_replace("'", '', htmlspecialchars_decode($cart_product['name'])))), 0, 100, 'UTF-8'),
                'quantity'       => $cart_product['quantity'],
                'sum'            => $tovprice*$cart_product['quantity'],
                'vat'            => $ndsvalue,
                'payment_method' => $cart_product['pms'],
                'payment_object' => $cart_product['pss'],
            );

        }

        if ($alldiscount == true) {

            $posnum = 0;
            $moden  = $totalcheck / ($this->currency->format($subtotal, $currency['currency_code'], $currency['currency_value'], false) + $this->currency->format($shipping, $currency['currency_code'], $currency['currency_value'], false));

            foreach ($cart_products as $cart_product) {

                if (isset($customname)) {
                    $res = $this->model_extension_payment_mixplat->getCustomName($cart_product['product_id'], $customname);
                    if ($res != '') {
                        $cart_product['name'] = $res;
                    }
                }

                if (isset($pms)) {
                    $paymet = $this->model_extension_payment_mixplat->getCustomName($cart_product['product_id'], $pms);
                    if ($paymet != '') {
                        $cart_product['pms'] = $paymet;
                    }
                    else{
                        $cart_product['pms'] = $paymentMethod;
                    }
                }
                else {
                    $cart_product['pms'] = $paymentMethod;
                }

                if (isset($pss)) {
                    $payob = $this->model_extension_payment_mixplat->getCustomName($cart_product['product_id'], $pss);
                    if ($payob != '') {
                        $cart_product['pss'] = $payob;
                    }
                    else{
                        $cart_product['pss'] = $paymentObject;
                    }
                }
                else {
                    $cart_product['pss'] = $paymentObject;
                }

                $tovprice = number_format($this->currency->format($cart_product['price'], $currency['currency_code'], $currency['currency_value'], false) * $moden, 2, '.', '');

                $tovprice = $tovprice*100;

                $ndsvalue = $ndsval;
                if (isset($ndson)) {
                    foreach ($this->config->get('payment_'.$order_info['payment_code'] . '_classes') as $tax_rule) {

                        $product_info = $this->model_catalog_product->getProduct($cart_product['product_id']);
                        if (isset($tax_rule[$order_info['payment_code'] . '_nalog']) && isset($product_info['tax_class_id']) && $tax_rule[$order_info['payment_code'] . '_nalog'] == $product_info['tax_class_id']) {
                            $ndsvalue = $this->model_extension_payment_mixplat->getndscode($tax_rule[$order_info['payment_code'] . '_tax_rule']);
                        }
                    }
                }

                $okassa['cartItems'][] = array(
                    'name'       => mb_substr(addslashes(stripslashes(str_replace("'", '', htmlspecialchars_decode($cart_product['name'])))), 0, 100, 'UTF-8'),
                    'quantity'       => $cart_product['quantity'],
                    'sum'            => $tovprice*$cart_product['quantity'],
                    'vat'            => $ndsvalue,
                    'payment_method' => $cart_product['pms'],
                    'payment_object' => $cart_product['pss'],
                );

            }

            if ($shipping > 0 && $order_info['shipping_code'] != '' || $this->config->get('payment_'.$order_info['payment_code'] . '_show_free_shipping') && $order_info['shipping_code'] != '') {
                $posnum += 1;

                $shipping1 = number_format($this->currency->format($shipping, $currency['currency_code'], $currency['currency_value'], false) * $moden, 2, '.', '');

                $shipping1 = $shipping1*100;

                $okassa['cartItems'][] = array(
                    'name'       => mb_substr(addslashes(stripslashes(str_replace("'", '', htmlspecialchars_decode($order_info['shipping_method'])))), 0, 100, 'UTF-8'),
                    'quantity'    => 1,
                    'sum'  => $shipping1,
                    'vat'            => $ndsship,
                    'payment_method' => $paymentMethod,
                    'payment_object' => '4',
                );
            }

        }

        //kopeyka wars
        $checkitogo = 0;
        $quantity = 0;
        foreach ($okassa['cartItems'] as $okas) {

            $checkitogo += $okas['sum'];
            $quantity += $okas['quantity'];

        }

        if ($alldiscount == true) {

            $proverkacheck = $amount - $checkitogo;

        } else {

            $shipping1 = number_format($this->currency->format($shipping, $currency['currency_code'], $currency['currency_value'], false), 2, '.', '');

            $shipping1 = $shipping1*100;

            $proverkacheck = ($amount - $shipping1) - $checkitogo;

        }

        if ($proverkacheck != 0.00) {
            $correctsum = $proverkacheck;
            
            $itemnum = -1;
            $kopwar  = false;
            foreach ($okassa['cartItems'] as $item) {
                $itemnum += 1;
                if ($item['quantity'] == 1 && $item['sum'] > 0) {
                    $okassa['cartItems'][$itemnum]['sum'] = $okassa['cartItems'][$itemnum]['sum'] + $correctsum;
                    $kopwar = true;
                    break;
                }

            }

            if ($kopwar == false) {
                $orderNotCorrect = false;
                $itemnum = -1;
                foreach ($okassa['cartItems'] as $item) {
                    if ($item['sum'] > 0) {
                        $itemnum += 1;
                        $itemPrice = $okassa['cartItems'][$itemnum]['sum']/$okassa['cartItems'][$itemnum]['quantity'] + $correctsum;
                        if ($itemPrice >= 0) {
                        $okassa['cartItems'][$itemnum]['sum'] = $okassa['cartItems'][$itemnum]['sum'] - $okassa['cartItems'][$itemnum]['sum']/$okassa['cartItems'][$itemnum]['quantity'];
                        $okassa['cartItems'][$itemnum]['quantity'] -= 1;
                        $copyprod[] = array(
                            'name'       => $okassa['cartItems'][$itemnum]['name'],
                            'quantity'   => 1,
                            'sum'  => $itemPrice,
                            'vat'        => $okassa['cartItems'][$itemnum]['vat'],
                            'payment_method' => $okassa['cartItems'][$itemnum]['payment_method'],
                            'payment_object' => $okassa['cartItems'][$itemnum]['payment_object'],
                        );
                        array_splice($okassa['cartItems'], 1, 0, $copyprod);
                        $kopwar = true;
                        break;
                        }
                        else{
                            $this->log->write('mixplat error: Positions in order '.$order_info['order_id'].' may be INCORRECT, check you order in product price and sum product price and product quantity.');
                            $orderNotCorrect = true;
                            break;
                        }
                    }
                }
            }
        }
        //kopeyka wars end

        if ($shipping > 0 && $alldiscount == false && $order_info['shipping_code'] != '' || $this->config->get('payment_'.$order_info['payment_code'] . '_show_free_shipping') && $alldiscount == false && $order_info['shipping_code'] != '') {

            $shipping1 = number_format($this->currency->format($shipping, $currency['currency_code'], $currency['currency_value'], false), 2, '.', '');

            $shipping1 = $shipping1*100;

            $okassa['cartItems'][] = array(
                'name'       => mb_substr(addslashes(stripslashes(str_replace("'", '', htmlspecialchars_decode($order_info['shipping_method'])))), 0, 100, 'UTF-8'),
                'quantity'   => 1,
                'sum'  => $shipping1,
                'vat'        => $ndsship,
                'payment_method' => $paymentMethod,
                'payment_object' => '4',
            );
        }
        

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_debug')) {
            echo '<br/>--------------Товары---------------------------------------<br/>';
            var_dump($cart_products);
            echo '<br/><br/>--------------Учитывать-в-заказе---------------------------<br/>';
            var_dump($totals);
            echo '<br/><br/>--------------В-чек----------------------------------------<br/>';
            var_dump($okassa);
            echo '<br/><br/>-----------------------------------------------------------<br/>';

            echo '<br/>--------------Онлайн Чек (Позиции для отладки)-------------<br/>';

            $numpos = 0;
            $itogo  = 0;

            $ondsrules = array(
                array(
                    'id'   => 0,
                    'name' => 'Без НДС',
                ),
                array(
                    'id'   => 1,
                    'name' => 'НДС 20%',
                ),
                array(
                    'id'   => 2,
                    'name' => 'НДС 20/120',
                ),
                array(
                    'id'   => 3,
                    'name' => 'НДС 10%',
                ),
                array(
                    'id'   => 4,
                    'name' => 'НДС 10/110',
                ),
                array(
                    'id'   => 5,
                    'name' => 'НДС 0%',
                ),
                array(
                    'id'   => 6,
                    'name' => 'Без НДС',
                ),
            );

            echo '<table>';

            foreach ($okassa['cartItems'] as $okas) {
                $numpos += 1;
                $itogo += $okas['sum'];
                $okas['vat'] = $this->model_extension_payment_mixplat->getndsnum($okas['vat']);
                $otax = $ondsrules[$okas['vat']]['name'];
                echo '<tr><td>';
                echo $numpos . '.</td><td>' . $okas['name'] . '</td><td>' . $okas['quantity'] . ' * ' . $okas['sum'] / $okas['quantity'] . '</td><td>' . '   =   ' . $okas['sum'] . '</td></tr>';
                echo '<tr><td></td><td>' . $otax . '</td></tr>';
            }
            echo '<tr></tr><tr><td></td><td>ИТОГ в Копейках: </td><td></td><td> = ' . $itogo . '</td></tr>';
            echo '<tr></tr><tr><td></td><td>ИТОГ: </td><td></td><td> = ' . ($itogo / 100) . '</td></tr>';
            echo '</table>';

        }

        return $okassa['cartItems'];

    }

    public function success() {

        if (isset($this->request->get['order'])) {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->request->get['order']);
        } else {
            echo 'No order';
            return;
        }

        if ($this->request->get['order'] != $order_info['order_id']) {
            echo "No data";
            return;
        }

        $this->load->model('extension/payment/mixplat');

        $platp = $this->model_extension_payment_mixplat->getSecureCode($order_info['order_id']);
        if ($this->request->get['code'] != $platp) {
            $this->response->redirect($this->url->link('error/not_found'));
        }

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_debug')) {
            $this->debug('SUCCESS');
        }

        $data['success_text'] = '';

        $this->load->language('extension/payment/mixplatpro');
        $this->load->language('extension/payment/' . $order_info['payment_code']);
        $data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($this->language->get('heading_title'));
        $data['button_ok'] = $this->language->get('button_ok');

        if ($order_info['order_status_id'] == $this->config->get('payment_'.$order_info['payment_code'] . '_order_status_id')) {

            if (isset($this->request->get['first'])) {
                $data['success_text'] .= $this->language->get('success_text_first');
            }

            if ($this->config->get('payment_'.$order_info['payment_code'] . '_createorder_or_notcreate') && isset($this->request->get['first'])) {

                $this->cart->clear();

                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
                unset($this->session->data['guest']);
                unset($this->session->data['comment']);
                unset($this->session->data['order_id']);
                unset($this->session->data['coupon']);
                unset($this->session->data['reward']);
                unset($this->session->data['voucher']);
                unset($this->session->data['vouchers']);
            }

            if ($this->config->get('payment_'.$order_info['payment_code'] . '_success_page_text_attach')) {

                $data['success_text'] .= htmlspecialchars_decode($this->model_account_yandexur->getCustomFields($order_info, $this->config->get('payment_'.$order_info['payment_code'] . '_success_page_text_' . $this->config->get('config_language_id')), $order_info['payment_code']));
            } else {
                $data['success_text'] .= sprintf($this->language->get('success_text'), $order_info['order_id']);
            }
        } else {

            if (isset($this->request->get['first']) && $order_info['order_status_id'] == $this->config->get('payment_'.$order_info['payment_code'] . '_on_status_id')) {
                $data['success_text'] .= $this->language->get('success_text_first');
            }
            if ($this->config->get('payment_'.$order_info['payment_code'] . '_waiting_page_text_attach')) {

                $data['success_text'] .= htmlspecialchars_decode($this->model_account_yandexur->getCustomFields($order_info, $this->config->get('payment_'.$order_info['payment_code'] . '_waiting_page_text_' . $this->config->get('config_language_id')), $order_info['payment_code']));
            } else {

                $online_url = $this->model_extension_payment_mixplat->getCustomFields($order_info, 'pay_link');

                if ($order_info['order_status_id'] == $this->config->get('payment_'.$order_info['payment_code'] . '_on_status_id')) {
                    $data['success_text'] .= sprintf($this->language->get('success_text_wait'), $order_info['order_id'], $online_url);
                } else {
                    $data['success_text'] .= sprintf($this->language->get('success_text_wait_noorder'), $online_url);
                }
            }
        }

        if ($this->customer->isLogged()) {

            if (!$this->config->get('payment_'.$order_info['payment_code'] . '_createorder_or_notcreate')) {
                $data['success_text'] .= sprintf($this->language->get('success_text_loged'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/order/info&order_id=' . $order_info['order_id'], '', 'SSL'));
            } else {
                if ($order_info['order_status_id'] == $this->config->get('payment_'.$order_info['payment_code'] . '_order_status_id')) {
                    $data['success_text'] .= sprintf($this->language->get('success_text_loged'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/order/info&order_id=' . $order_info['order_id'], '', 'SSL'));
                }
            }
            if ($order_info['order_status_id'] != $this->config->get('payment_'.$order_info['payment_code'] . '_order_status_id')) {
                if ($order_info['order_status_id'] == $this->config->get('payment_'.$order_info['payment_code'] . '_on_status_id')) {
                    $data['success_text'] .= sprintf($this->language->get('waiting_text_loged'), $this->url->link('account/order', '', 'SSL'));
                }
            }

        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home'),
        );

        if (isset($this->request->get['first'])) {
            $this->language->load('checkout/success');
            $data['breadcrumbs'][] = array(
                'href' => $this->url->link('checkout/cart'),
                'text' => $this->language->get('text_basket'),
            );

            $data['breadcrumbs'][] = array(
                'href' => $this->url->link('checkout/checkout', '', 'SSL'),
                'text' => $this->language->get('text_checkout'),
            );
            $data['button_ok_url'] = $this->url->link('common/home');
        } else {
            if ($this->customer->isLogged()) {
                $data['breadcrumbs'][] = array(
                    'text' => $this->language->get('lich'),
                    'href' => $this->url->link('account/account', '', 'SSL'),
                );

                $data['breadcrumbs'][] = array(
                    'text' => $this->language->get('history'),
                    'href' => $this->url->link('account/order', '', 'SSL'),
                );
                $data['button_ok_url'] = $this->url->link('account/order', '', 'SSL');
            } else {
                $data['button_ok_url'] = $this->url->link('common/home');
            }
        }

        $data['column_left']    = $this->load->controller('common/column_left');
        $data['column_right']   = $this->load->controller('common/column_right');
        $data['content_top']    = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/payment/mixplat_success', $data));
    }

    public function fail() {

        if (isset($this->request->get['order'])) {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->request->get['order']);
        } else {
            echo 'No order';
            return;
        }

        if ($this->request->get['order'] != $order_info['order_id']) {
            echo "No data";
            return;
        }

        $this->load->model('extension/payment/mixplat');

        $platp = $this->model_extension_payment_mixplat->getSecureCode($order_info['order_id']);
        if ($this->request->get['code'] != $platp) {
            $this->response->redirect($this->url->link('error/not_found'));
        }

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_debug')) {
            $this->debug('FAIL');
        }

        $this->load->language('extension/payment/mixplatpro');
        $this->load->language('extension/payment/' . $order_info['payment_code']);
        $data['heading_title'] = $this->language->get('heading_title_fail');
        $this->document->setTitle($this->language->get('heading_title'));
        $data['button_ok'] = $this->language->get('button_ok');
        $data['fail_text'] = '';

        if (isset($this->request->get['first']) && $order_info['order_status_id'] == $this->config->get('payment_'.$order_info['payment_code'] . '_on_status_id')) {
            $data['fail_text'] .= $this->language->get('fail_text_first');
        }

        if ($this->config->get('payment_'.$order_info['payment_code'] . '_fail_page_text_attach')) {

            $data['fail_text'] .= htmlspecialchars_decode($this->model_extension_payment_mixplat->getCustomFields($order_info, $this->config->get('payment_'.$order_info['payment_code'] . '_fail_page_text_' . $this->config->get('config_language_id')), $order_info['payment_code']));
        } else {

            $online_url = $this->model_extension_payment_mixplat->getCustomFields($order_info, 'pay_link');

            if ($order_info['order_status_id'] == $this->config->get('payment_'.$order_info['payment_code'] . '_on_status_id')) {
                $data['fail_text'] .= sprintf($this->language->get('fail_text'), $order_info['order_id'], $online_url, $online_url);
            } else {
                $data['fail_text'] .= sprintf($this->language->get('fail_text_noorder'), $online_url);
            }
        }

        if ($this->customer->isLogged()) {

            if ($order_info['order_status_id'] == $this->config->get('payment_'.$order_info['payment_code'] . '_on_status_id')) {
                $data['fail_text'] .= sprintf($this->language->get('fail_text_loged'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/order/info&order_id=' . $order_info['order_id'], '', 'SSL'), $this->url->link('account/order', '', 'SSL'));
            }

        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home'),
        );

        if (isset($this->request->get['first'])) {
            $this->language->load('checkout/success');
            $data['breadcrumbs'][] = array(
                'href' => $this->url->link('checkout/cart'),
                'text' => $this->language->get('text_basket'),
            );

            $data['breadcrumbs'][] = array(
                'href' => $this->url->link('checkout/checkout', '', 'SSL'),
                'text' => $this->language->get('text_checkout'),
            );
            $data['button_ok_url'] = $this->url->link('common/home');
        } else {
            if ($this->customer->isLogged()) {
                $data['breadcrumbs'][] = array(
                    'text' => $this->language->get('lich'),
                    'href' => $this->url->link('account/account', '', 'SSL'),
                );

                $data['breadcrumbs'][] = array(
                    'text' => $this->language->get('history'),
                    'href' => $this->url->link('account/order', '', 'SSL'),
                );
                $data['button_ok_url'] = $this->url->link('account/order', '', 'SSL');
            } else {
                $data['button_ok_url'] = $this->url->link('common/home');
            }
        }

        $data['column_left']    = $this->load->controller('common/column_left');
        $data['column_right']   = $this->load->controller('common/column_right');
        $data['content_top']    = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/payment/mixplat_fail', $data));

    }

    public function amail(&$route, &$args) {

        if (isset($args[0])) {
            $order_id = $args[0];
        } else {
            $order_id = 0;
        }

        if (isset($args[1])) {
            $order_status_id = $args[1];
        } else {
            $order_status_id = 0;
        }

        if (isset($args[3])) {
            $notify = $args[3];
        } else {
            $notify = '';
        }

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if ($order_info) {
            if ($order_info['order_status_id'] && $order_status_id) {
                $this->load->model('extension/payment/mixplat');
                if ($this->config->get('payment_mixplat_on_status_id') == $order_status_id && $order_info['payment_code'] == 'mixplat') {
                    $this->load->language('extension/payment/yandexurpro');
                    $this->load->language('extension/payment/' . $order_info['payment_code']);
                    $merchant_url = $this->model_extension_payment_mixplat->getCustomFields($order_info, 'pay_link', $order_info['payment_code']);
                    $merchant_url = "<a href=' " . $merchant_url . "'>" . $merchant_url . "</a>";
                    $merchant_url = strip_tags(html_entity_decode($merchant_url, ENT_QUOTES, 'UTF-8'));
                    $message      = sprintf($this->language->get('text_stat'), $merchant_url);

                    $args[2] = $message . $args[2];
                }
            }
        }

    }
}
