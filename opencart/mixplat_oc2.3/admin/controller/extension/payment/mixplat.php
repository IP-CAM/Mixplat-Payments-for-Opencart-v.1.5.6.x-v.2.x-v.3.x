<?php
class ControllerExtensionPaymentMixplat extends Controller {
    private $error   = array();
    private $pname   = 'mixplat';
    private $ver     = '1.0.0';
    private $shopver = '2.3.x';

    public function index($payname = 'mixplat') {
        $pname               = isset($payname['name']) ? $payname['name'] : $this->pname;
        $data['version']     = $this->ver;
        $data['shopversion'] = $this->shopver;
        $this->install();
        $data += $this->load->language('extension/payment/mixplatpro');
        $data += $this->load->language('extension/payment/' . $pname);
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate($pname))) {
            $this->model_setting_setting->editSetting($pname, $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        $this->load->model('extension/payment/mixplat');
        $data['twostage_show'] = $this->model_extension_payment_mixplat->getTwostage($pname);

        $seterrs     = $this->model_extension_payment_mixplat->getErrSettings();
        $seterrsLang = $this->model_extension_payment_mixplat->getErrSettingsLang();
        foreach ($seterrs as $seterr) {

            if (isset($this->error[$seterr])) {
                $data['error_' . $seterr] = $this->error[$seterr];
            } else {
                $data['error_' . $seterr] = '';
            }

        }

        $this->load->model('localisation/language');
        $languages         = $this->model_localisation_language->getLanguages();
        $setlangs          = $this->model_extension_payment_mixplat->getLangSettings();
        $data['languages'] = $languages;

        foreach ($languages as $language) {

            foreach ($setlangs as $setlang) {

                if (isset($this->request->post[$pname . '_' . $setlang . '_' . $language['language_id']])) {
                    $data['mixplatpro_' . $setlang . '_' . $language['language_id']] = $this->request->post[$pname . '_' . $setlang . '_' . $language['language_id']];
                } else {
                    $data['mixplatpro_' . $setlang . '_' . $language['language_id']] = $this->config->get($pname . '_' . $setlang . '_' . $language['language_id']);
                }
            }

            foreach ($seterrsLang as $seterrLang) {

                if (isset($this->error[$seterrLang . '_' . $language['language_id']])) {
                    $data['error_' . $seterrLang . '_' . $language['language_id']] = $this->error[$seterrLang . '_' . $language['language_id']];
                } else {
                    $data['error_' . $seterrLang . '_' . $language['language_id']] = '';
                }

            }
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['pname'] = $pname;
        $setpros       = $this->model_extension_payment_mixplat->getSettings();

        foreach ($setpros as $setpro) {

            if (isset($this->request->post[$pname . '_' . $setpro])) {
                $data['mixplatpro_' . $setpro] = $this->request->post[$pname . '_' . $setpro];
            } else {
                $data['mixplatpro_' . $setpro] = $this->config->get($pname . '_' . $setpro);
            }
        }

        if (isset($this->request->post[$pname . '_classes'])) {
            $data['mixplatpro_classes'] = $this->request->post[$pname . '_classes'];
        } elseif ($this->config->get($pname . '_classes') && isset($this->config->get($pname . '_classes')[0][$pname . '_nalog'])) {
            $data['mixplatpro_classes'] = $this->config->get($pname . '_classes');
        } else {
            $data['mixplatpro_classes'] = array(
                array(
                    $pname . '_nalog'    => 1,
                    $pname . '_tax_rule' => 1,
                ),
            );
        }

        $data['tax_rules'] = array(
            array(
                'id'   => 1,
                'name' => $this->language->get('entry_nds_important_1'),
            ),
            array(
                'id'   => 2,
                'name' => $this->language->get('entry_nds_important_2'),
            ),
            array(
                'id'   => 3,
                'name' => $this->language->get('entry_nds_important_3'),
            ),
            array(
                'id'   => 4,
                'name' => $this->language->get('entry_nds_important_4'),
            ),
            array(
                'id'   => 5,
                'name' => $this->language->get('entry_nds_important_5'),
            ),
            array(
                'id'   => 6,
                'name' => $this->language->get('entry_nds_important_6'),
            ),
        );

        $this->load->model('localisation/tax_class');
        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $data['methodcode'] = $this->model_extension_payment_mixplat->getPaymentType($pname);

        $data['manypoles'] = $this->model_extension_payment_mixplat->getPoles($pname);

        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/' . $pname, 'token=' . $this->session->data['token'], true),
        );

        $data['action'] = $this->url->link('extension/payment/' . $pname, 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/mixplat', $data));

    }

    public function install() {

        $query = $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "mixplat (pay_id INT(11) AUTO_INCREMENT, num_order INT(8), sum DECIMAL(15,2), user TEXT, email TEXT, status INT(1), date_created DATETIME, date_enroled DATE, sender TEXT, label TEXT, label2 TEXT, label3 TEXT, label4 TEXT, label5 TEXT, label6 TEXT, label7 INT(15), label8 INT(15), label9 TEXT, PRIMARY KEY (pay_id)) DEFAULT CHARACTER SET utf8, ENGINE=MyISAM");
    }

    private function validate($pname) {

        if (!$this->user->hasPermission('modify', 'extension/payment/' . $pname)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ($this->request->post[$pname . '_name_attach']) {
            $this->load->model('localisation/language');

            $languages = $this->model_localisation_language->getLanguages();

            foreach ($languages as $language) {
                if (empty($this->request->post[$pname . '_name_' . $language['language_id']])) {
                    $this->error['name_' . $language['language_id']] = $this->language->get('error_name');
                }
            }
        }

        if (!$this->request->post[$pname . '_project_id']) {
            $this->error['project_id'] = $this->language->get('error_project_id');
        }

        if (!$this->request->post[$pname . '_payment_form_id']) {
            $this->error['payment_form_id'] = $this->language->get('error_payment_form_id');
        }

        if (!$this->request->post[$pname . '_password']) {
            $this->error['password'] = $this->language->get('error_password');
        }

        if ($this->request->post[$pname . '_fixen']) {
            if (!$this->request->post[$pname . '_fixen_amount']) {
                $this->error['fixen'] = $this->language->get('error_fixen');
            }
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function status() {

        $this->load->language('extension/payment/mixplatpro');
        $this->document->setTitle($this->language->get('status_title') . ' ' . $this->language->get('heading_title_status'));
        $data['heading_title'] = $this->language->get('heading_title_status');
        $data['status_title']  = $this->language->get('status_title');

        $data['id']           = $this->language->get('id');
        $data['num_order']    = $this->language->get('num_order');
        $data['sum']          = $this->language->get('sum');
        $data['label']        = $this->language->get('label');
        $data['status']       = $this->language->get('status');
        $data['user']         = $this->language->get('user');
        $data['email']        = $this->language->get('email');
        $data['date_created'] = $this->language->get('date_created');
        $data['date_enroled'] = $this->language->get('date_enroled');
        $data['sender']       = $this->language->get('sender');
        $data['info']         = $this->language->get('info');

        $this->load->model('extension/payment/mixplat');

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        $olimits = array(
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin'),
        );

        $data['viewstatuses'] = array();

        $total_statuses = $this->model_extension_payment_mixplat->getTotalStatus($olimits);

        $viewstatuses = $this->model_extension_payment_mixplat->getStatus($olimits);

        $pagination        = new Pagination();
        $pagination->total = $total_statuses;
        $pagination->page  = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->text  = $this->language->get('text_pagination');
        $pagination->url   = $this->url->link('extension/payment/mixplat/status', 'token=' . $this->session->data['token'] . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $info    = $this->url->link('extension/payment/mixplat/info', 'token=' . $this->session->data['token'], true);
        $capture = $this->url->link('extension/payment/mixplat/capture', 'token=' . $this->session->data['token'], true);

        foreach ($viewstatuses as $viewstatus) {
            $info                   = $info . '&order_id=' . $viewstatus['num_order'];
            $capture_href           = $capture . '&order_id=' . $viewstatus['num_order'];
            $data['viewstatuses'][] = array(
                'pay_id'       => $viewstatus['pay_id'],
                'num_order'    => $viewstatus['num_order'],
                'sum'          => $viewstatus['sum'],
                'label'        => $viewstatus['label'],
                'status'       => $this->language->get('status_list_' . $viewstatus['status']),
                'user'         => $viewstatus['user'],
                'email'        => $viewstatus['email'],
                'date_created' => $viewstatus['date_created'],
                'date_enroled' => $viewstatus['date_enroled'],
                'sender'       => $viewstatus['sender'],
                'info'         => $info,
            );
        }

        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_order'),
            'href' => $this->url->link('sale/order', 'token=' . $this->session->data['token'], true),
        );

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/mixplat_view_status.tpl', $data));

    }

    private function curlito($data, $rname) {
        $server = 'https://api.mixplat.com/' . $rname;

        $this->load->model('extension/payment/mixplat');
        $this->load->model('sale/order');
        $order_info = $this->model_sale_order->getOrder($data['order_id']);
        $payment_id = $this->model_extension_payment_mixplat->getPaymentIdByNum($order_info['order_id']);

        $request = array(
            'api_version'         => 3,
            'payment_id'          => $payment_id,
            'signature'           => md5($payment_id . $this->config->get($order_info['payment_code'] . '_password')),
        );

        $request = json_encode($request);

        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $server);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            $result = curl_exec($curl);
            curl_close($curl);

            return $result;

        } else {
            $this->log->write('maxiplat error: No curl');
            exit();
        }
    }

    private function nameChecker($key, $value) {

        if ($key == 'status') {
            $value = $this->language->get('stat_val_' . $value) . ' (' . $value . ')';
        }

        if ($key == 'status_extended') {
            $value = $this->language->get('stat_extended_val_' . $value) . ' (' . $value . ')';
        }

        if ($value === true) {$value = $this->language->get('text_status_true');}
        if ($value === false) {$value = $this->language->get('text_status_false');}

        return $value;

    }

    public function capture() {

        if ($this->user->hasPermission('access', 'sale/order')) {

            $curlito = array('order_id' => (int) $this->request->get['order_id']);
            $jsons   = $this->curlito($curlito, 'authorize_dms_payment');
            $json    = json_decode(stripslashes($jsons), true);

            $this->load->language('extension/payment/mixplatpro');

            if (isset($json['result']) && $json['result'] == 'ok') {
                $this->session->data['status_success'] = $this->language->get('text_capture_success');
                $this->load->model('extension/payment/mixplat');
                $this->model_extension_payment_mixplat->changeStatus($this->request->get['order_id'], 1);

            } else {
                $this->log->write('mixpalt error: CAPTURE PAYMENT '.$json['result'] . ' - ' . $json['error_description']);
                $this->session->data['status_error'] = $this->language->get('text_capture_error');
            }

        } else {
            $this->session->data['status_error'] = $this->language->get('text_capture_error_perrmission');
        }

        if (isset($this->request->get['capture'])) {
            $this->response->redirect($this->url->link('extension/payment/mixplat/status', 'token=' . $this->session->data['token'], true));
        } else {
            $this->response->redirect($this->url->link('extension/payment/mixplat/info', 'order_id=' . (int) $this->request->get['order_id'] . '&token=' . $this->session->data['token'], true));
        }

    }

    public function cancel() {

        if ($this->user->hasPermission('access', 'sale/order')) {

            $curlito = array('order_id' => (int) $this->request->get['order_id']);
            $jsons   = $this->curlito($curlito, 'cancel_dms_payment');
            $json    = json_decode(stripslashes($jsons), true);

            $this->load->language('extension/payment/mixplatpro');

            if (isset($json['result']) && $json['result'] == 'ok') {
                $this->session->data['status_success'] = $this->language->get('text_cancel_success');
                $this->load->model('extension/payment/mixplat');
                $this->model_extension_payment_mixplat->changeStatus($this->request->get['order_id'], 3);

            } else {
                $this->log->write('mixpalt error: CANCEL PAYMENT '.$json['result'] . ' - ' . $json['error_description']);
                $this->session->data['status_error'] = $this->language->get('text_cancel_error');
            }

        } else {
            $this->session->data['status_error'] = $this->language->get('text_cancel_error_perrmission');
        }

        $this->response->redirect($this->url->link('extension/payment/mixplat/info', 'order_id=' . (int) $this->request->get['order_id'] . '&token=' . $this->session->data['token'], true));

    }

    public function info() {

        $this->load->language('extension/payment/mixplatpro');

        if (isset($this->session->data['status_success'])) {
            $data['success'] = $this->session->data['status_success'];
            unset($this->session->data['status_success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->session->data['status_error'])) {
            $data['error_warning'] = $this->session->data['status_error'];
            unset($this->session->data['status_error']);
        } else {
            $data['error_warning'] = '';
        }

        $curlito = array('order_id' => (int) $this->request->get['order_id']);
        $json    = $this->curlito($curlito, 'get_payment');
        $json    = json_decode(stripslashes($json), true);
        if (is_array($json)) {
            $payment_status = $this->model_extension_payment_mixplat->getPaymentStatus($this->request->get['order_id']);
            if (isset($payment_status['status']) && $payment_status['status'] == 2) {
            //if (isset($json['status']) && $json['status'] == 'waiting') {
                $data['capture']      = $this->url->link('extension/payment/mixplat/capture', 'order_id=' . (int) $this->request->get['order_id'] . '&token=' . $this->session->data['token'], true);
                $data['cancel']       = $this->url->link('extension/payment/mixplat/cancel', 'order_id=' . (int) $this->request->get['order_id'] . '&token=' . $this->session->data['token'], true);
                $data['text_capture'] = $this->language->get('text_capture');
                $data['text_cancel']  = $this->language->get('text_cancel');
            }
            $info = array();
            foreach ($json as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $key2 => $value2) {
                        if (is_array($value2)) {
                            foreach ($value2 as $key3 => $value3) {
                                $info[$this->language->get('stat_' . $key3) . ' (' . $key3 . ')'] = $this->nameChecker($key3, $value3);
                            }
                        } else {
                            $info[$this->language->get('stat_' . $key2) . ' (' . $key2 . ')'] = $this->nameChecker($key2, $value2);
                        }
                    }
                } else {
                    $info[$this->language->get('stat_' . $key) . ' (' . $key . ')'] = $this->nameChecker($key, $value);
                }
            }
        }
        if (isset($info)) {
            $data['statuses'] = $info;
        } else {
            $data['statuses'] = array($this->language->get('status_nodata') => '');
        }

        $this->document->setTitle($this->language->get('heading_title_capture'));

        $data['heading_title'] = $this->language->get('heading_title_capture');

        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_order'),
            'href' => $this->url->link('sale/order', 'token=' . $this->session->data['token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_status'),
            'href' => $this->url->link('extension/payment/mixplat/status', 'token=' . $this->session->data['token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_status_info'),
            'href' => $this->url->link('extension/payment/mixplat/info', 'order_id=' . (int) $this->request->get['order_id'] . '&token=' . $this->session->data['token'], true),
        );

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/mixplat_info.tpl', $data));

    }
}
