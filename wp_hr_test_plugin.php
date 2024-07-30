<?php

namespace NamePlugin;

class NameApi {
    protected $api_url;

    public function __construct($api_url) {
        $this->api_url = $api_url;
    }

    public function list_vacancies($post, int $vid = 0) {
        if (!is_object($post)) {
            return false;
        }

        $ret = [];
        $page = 0;
        $found = false;

        do {
            $params = [
                'status' => 'all',
                'id_user' => $this->self_get_option('superjob_user_id'),
                'with_new_response' => 0,
                'order_field' => 'date',
                'order_direction' => 'desc',
                'page' => $page,
                'count' => 100,
            ];

            $res = $this->api_send($params);

            if ($res === false || !is_object($res) || !isset($res->objects)) {
                return false;
            }

            $ret = array_merge($ret, $res->objects);

            if ($vid > 0) { // Для конкретной вакансии, иначе возвращаем все
                foreach ($res->objects as $value) {
                    if ($value->id == $vid) {
                        $found = $value;
                        break;
                    }
                }
            }
            $page++;

        } while ($found === false && $res->more)

        return is_object($found) ? $found : $ret;
    }

    protected function api_send($params) {        
        $response = wp_remote_get($this->api_url . '/hr/vacancies/?' . http_build_query($params));
        if ($response['response']['code'] === 200) {
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body );
            return $data;
        }
        return false;
    }

    protected function self_get_option($option_name) {
        return get_option($option_name);
    }
}