<?php

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    if (session_id() == '') session_start();
} else {
    if (session_status() == PHP_SESSION_NONE) session_start();
}


if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Admin extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->database();
//        $this->load->library('paypal');
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        //$this->crud_model->ip_data();
        $this->config->cache_query();
    }

    /* index of the admin. Default: Dashboard; On No Login Session: Back to login page. */

    public function index()
    {
        if ($this->session->userdata('admin_login') == 'yes') {
            $page_data['page_name'] = "dashboard";
            $this->load->view('back/index', $page_data);
        } else {
            $page_data['control'] = "admin";
            $this->load->view('back/login', $page_data);
        }
    }

    function polls($para1 = '', $para2 = '')
    {

        if ($this->session->userdata('admin_login') == 'yes') {

            if ($para1 == 'list') {
                $this->db->order_by('poll_pk', 'desc');
                $query = "SELECT * FROM table_poll WHERE is_approved = '1'";
                $page_data['all_polls'] = $this->db->query($query)->result_array();
                $this->load->view('back/admin/polls_list', $page_data);
            } else if ($para1 == 'delete') {
                $this->db->where('poll_pk', $para2);
                $this->db->delete('table_poll');
                recache();
            } else if ($para1 == 'unapprove') {

                $data['is_approved'] = '0';

                $this->db->where('poll_pk', $para2);
                $this->db->update('table_poll', $data);

                $page_data['page_name'] = "polls";
                $query = "SELECT * FROM table_poll WHERE is_approved = '1'";
                $page_data['all_polls'] = $this->db->query($query)->result_array();

//            print_r($page_data);
                $this->load->view('back/index', $page_data);

            } else {
                $page_data['page_name'] = "polls";
                $query = "SELECT * FROM table_poll WHERE is_approved = '1'";
                $page_data['all_polls'] = $this->db->query($query)->result_array();
                $this->load->view('back/index', $page_data);
            }

        } else {
            $page_data['control'] = "admin";
            $this->load->view('back/login', $page_data);
        }
    }


    function reported_polls($para1 = '', $para2 = '')
    {

        if ($this->session->userdata('admin_login') == 'yes') {


            if ($para1 == 'list') {
//            $this->db->order_by('poll_pk', 'desc');

                $query = "SELECT * FROM table_poll JOIN table_report_poll ON table_report_poll.poll_fk = table_poll.poll_pk ORDER BY poll_pk DESC";

                $page_data['all_polls'] = $this->db->query($query)->result_array();

//            print_r($page_data);
                $this->load->view('back/admin/report_polls_list', $page_data);
            } elseif ($para1 == 'delete') {
                $this->db->where('poll_pk', $para2);
                $this->db->delete('table_poll');
                recache();
            } else {
                $page_data['page_name'] = "reported_polls";
                $query = "SELECT * FROM table_poll JOIN table_report_poll ON table_report_poll.poll_fk = table_poll.poll_pk ORDER BY poll_pk DESC";
                $page_data['all_polls'] = $this->db->query($query)->result_array();

//            print_r($page_data);
                $this->load->view('back/index', $page_data);
            }

        } else {
            $page_data['control'] = "admin";
            $this->load->view('back/login', $page_data);
        }


    }


    function for_review($para1 = '', $para2 = '')
    {

        if ($this->session->userdata('admin_login') == 'yes') {

            if ($para1 == 'list') {
//            $this->db->order_by('poll_pk', 'desc');

                $query = "SELECT * FROM table_poll WHERE is_approved = '0' ORDER BY poll_pk DESC";

                $page_data['all_polls'] = $this->db->query($query)->result_array();

//            print_r($page_data);
                $this->load->view('back/admin/for_review_list', $page_data);
            } else if ($para1 == 'delete') {
                $this->db->where('poll_pk', $para2);
                $this->db->delete('table_poll');
                recache();
            } else if ($para1 == 'approve') {

                $data['is_approved'] = '1';

                $this->db->where('poll_pk', $para2);
                $this->db->update('table_poll', $data);

                $page_data['page_name'] = "for_review";
                $query = "SELECT * FROM table_poll WHERE is_approved = '0' ORDER BY poll_pk DESC";
                $page_data['all_polls'] = $this->db->query($query)->result_array();

//            print_r($page_data);
                $this->load->view('back/index', $page_data);

            } else {
                $page_data['page_name'] = "for_review";
                $query = "SELECT * FROM table_poll WHERE is_approved = '0' ORDER BY poll_pk DESC";
                $page_data['all_polls'] = $this->db->query($query)->result_array();

//            print_r($page_data);
                $this->load->view('back/index', $page_data);
            }

        } else {
            $page_data['control'] = "admin";
            $this->load->view('back/login', $page_data);
        }
    }


    function users($para1 = '', $para2 = '')
    {

        if ($this->session->userdata('admin_login') == 'yes') {

            if ($para1 == 'list') {
                $this->db->order_by('user_pk', 'desc');
                $page_data['all_users'] = $this->db->get('table_user')->result_array();
                $this->load->view('back/admin/users_list', $page_data);
            } elseif ($para1 == 'delete') {
                $this->db->where('user_pk', $para2);
                $this->db->delete('table_user');
                recache();
            } else {
                $page_data['page_name'] = "users";
                $page_data['all_users'] = $this->db->get('table_user')->result_array();
                $this->load->view('back/index', $page_data);
            }

        } else {
            $page_data['control'] = "admin";
            $this->load->view('back/login', $page_data);
        }
    }

    /* Login into Admin panel */
    function login($para1 = '')
    {
        if ($para1 == 'forget_form') {
            $page_data['control'] = 'vendor';
            $this->load->view('back/forget_password', $page_data);
        } else if ($para1 == 'forget') {

            $this->load->library('form_validation');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            if ($this->form_validation->run() == FALSE) {
                echo validation_errors();
            } else {
                $query = $this->db->get_where('admin', array(
                    'email' => $this->input->post('email')
                ));
                if ($query->num_rows() > 0) {
                    $admin_id = $query->row()->admin_id;
                    $password = substr(hash('sha512', rand()), 0, 12);
                    $data['password'] = sha1($password);
                    $this->db->where('admin_id', $admin_id);
                    $this->db->update('admin', $data);
                    if ($this->email_model->password_reset_email('admin', $admin_id, $password)) {
                        echo 'email_sent';
                    } else {
                        echo 'email_not_sent';
                    }
                } else {
                    echo 'email_nay';
                }
            }
        } else {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'required');

            if ($this->form_validation->run() == FALSE) {
                echo validation_errors();
            } else {
                $login_data = $this->db->get_where('admin', array(
                    'email' => $this->input->post('email'),
                    'password' => sha1($this->input->post('password'))
                ));
                if ($login_data->num_rows() > 0) {
                    foreach ($login_data->result_array() as $row) {
                        $this->session->set_userdata('login', 'yes');
                        $this->session->set_userdata('admin_login', 'yes');
                        $this->session->set_userdata('admin_id', $row['admin_id']);
                        $this->session->set_userdata('admin_name', $row['name']);
                        $this->session->set_userdata('title', 'admin');
                        echo 'lets_login';
                    }
                } else {
                    echo 'login_failed';
                }
            }
        }
    }

    /* Loging out from Admin panel */
    function logout()
    {
        $this->session->sess_destroy();
        redirect(base_url() . 'index.php/admin', 'refresh');
    }


    /* Checking Login Stat */
    function is_logged()
    {
        if ($this->session->userdata('admin_login') == 'yes') {
            echo 'yah!good';
        } else {
            echo 'nope!bad';
        }
    }

    /* Manage Admin Settings */
    function manage_admin($para1 = "")
    {
        if ($this->session->userdata('admin_login') != 'yes') {
            redirect(base_url() . 'index.php/admin');
        }
        if ($para1 == 'update_password') {
            $user_data['password'] = $this->input->post('password');
            $account_data = $this->db->get_where('admin', array(
                'admin_id' => $this->session->userdata('admin_id')
            ))->result_array();
            foreach ($account_data as $row) {
                if (sha1($user_data['password']) == $row['password']) {
                    if ($this->input->post('password1') == $this->input->post('password2')) {
                        $data['password'] = sha1($this->input->post('password1'));
                        $this->db->where('admin_id', $this->session->userdata('admin_id'));
                        $this->db->update('admin', $data);
                        echo 'updated';
                    }
                } else {
                    echo 'pass_prb';
                }
            }
        } else if ($para1 == 'update_profile') {
            $this->db->where('admin_id', $this->session->userdata('admin_id'));
            $this->db->update('admin', array(
                'name' => $this->input->post('name'),
                'email' => $this->input->post('email'),
                'address' => $this->input->post('address'),
                'phone' => $this->input->post('phone')
            ));
        } else {
            $page_data['page_name'] = "manage_admin";
            $this->load->view('back/index', $page_data);
        }
    }


}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */