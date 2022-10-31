<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Auth_model extends CI_Model
{

    var $client_service = "App-Client";
    var $auth_key = "PicVote";

    public function check_auth_client()
    {
        $client_service = $this->input->get_request_header('Client-Service', TRUE);
        $auth_key = $this->input->get_request_header('Auth-Key', TRUE);

        if ($client_service == $this->client_service && $auth_key == $this->auth_key) {
            return true;
        } else {

            return json_output(401, array('status' => 401, 'message' => 'Unauthorized.'));
        }
    }

    public function auth()
    {
        $users_id = $this->input->get_request_header('User-Id', TRUE);
        $token = $this->input->get_request_header('Auth-Code', TRUE);

        $q = $this->db->select('token')->from('table_user')->where('user_pk', $users_id)->where('token', $token)->get()->row();

        if ($q == "") {
            return json_output(401, array('status' => 401, 'message' => 'Unauthorized.'));
        } else {

            return array('status' => 200, 'message' => 'Authorized.');
        }
    }

}