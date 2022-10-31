<?php


//require_once APPPATH. '/lib/Autoloader.php';

//require APPPATH.'/vendor/autoload.php';
use Snipe\BanBuilder\CensorWords;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

define('API_ACCESS_KEY', 'AAAAGnPu1io:APA91bEnib7k5nyM6Wyd5pzyC_cW4BhAYHIC7HZvZRzPXYgfrCoN9GZnV5QrtyNMwf_xhXFZqwVna1DW1p56BMCCMzJwa0bh9w8u9neGkPEASCyboxdlZE8upWgYeKoUhYTYGAIJU5-e');

class Api extends CI_Controller
{
    /*
     *
     *  Date    : 14 July, 2015
     *
     *
     */

    function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json; charset=utf-8');

        $this->load->database();
//        $this->load->library('paypal');

        $this->load->helper('json_output');

        $this->faker = Faker\Factory::create();

        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        //$this->crud_model->ip_data();
        $this->config->cache_query();
    }


    public function test_auth()
    {
        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();

            print_r($check_auth_client);

            if ($check_auth_client == true) {

                $response = $this->auth_model->auth();
                if ($response['status'] == 200) {

//                    print_r($response);

                } else {


                }


            }
        }
    }

    /* index of the admin. Default: Dashboard; On No Login Session: Back to login page. */
    public function add_poll()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {

            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $response = $this->auth_model->auth();

                if ($response['status'] == 200) {

                    $this->load->model('Table_poll_model');

                    $returned_string = $this->Table_poll_model->add_poll();

                    $response = array();
                    $response['success'] = '1';
                    $response['message'] = 'Uploaded';
                    $response['return'] = $returned_string;

                    echo json_encode($response);

                }
            }
        }


    }

    public function vote_poll()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();

            if ($check_auth_client == true) {

                $response = $this->auth_model->auth();

                if ($response['status'] == 200) {

                    $this->load->model('Table_poll_model');
                    $this->load->model('Table_user_model');
                    $this->load->model('Table_polling_model');

                    $response = array();

                    $user_fk = $this->input->post('user_fk');
                    $poll_fk = $this->input->post('poll_fk');
                    $choice = $this->input->post('choice');

                    $response['message'] = $this->Table_polling_model->vote_poll($user_fk, $poll_fk, $choice);

                    $poll_data = $this->Table_poll_model->get_single_poll_data($poll_fk);

                    $from_user_data = $this->Table_user_model->get_single_user_data($user_fk);

                    $to_user_data = $this->Table_user_model->get_single_user_data($poll_data['user_fk']);

                    $datanotif['message'] = $from_user_data['username'] . ' voted your poll';
                    $datanotif['to_user_fk'] = $to_user_data['user_pk'];
                    $datanotif['from_user_fk'] = $user_fk;
                    $datanotif['ref_fk'] = $poll_fk;
                    $datanotif['created_time'] = $created_time = date('Y-m-d H:i:s');


                    $this->db->insert('table_notification', $datanotif);


                    if (!empty($to_user_data['firebase_id'])) {


                        try {

                            $this->push_notification($to_user_data['firebase_id'], $datanotif['message']);

                        } catch (Exception $e) {

                        }


                    }

                    $response['success'] = '1';


                    echo json_encode($response);

                }
            }
        }


    }

    public function register()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $this->load->model('Table_user_model');

                $response = $this->Table_user_model->register();

                echo json_encode($response);
            }
        }


    }

    public function update_profile()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $this->load->model('Table_user_model');

                $response = $this->Table_user_model->update_profile();

                echo json_encode($response);
            }
        }


    }

    public function login()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $data['email'] = $this->input->post('email');
                $data['password'] = md5($this->input->post('password'));
                $firebase_id = $this->input->post('firebase_id');


                $user_check = $this->db->select(user_pk)->get_where('table_user', array('email' => $data['email'], 'password' => $data['password']))->first_row('array');

                if (count($user_check) > 0) {

                    $token = crypt(substr(md5(rand()), 0, 7));

                    $update_token['token'] = $token;

                    $this->db->where('user_pk', $user_check['user_pk']);
                    $flag = $this->db->update('table_user', $update_token);


                    $user = $this->db
                        ->select('user_pk')
                        ->select('username')
                        ->select('email')
                        ->select('gender')
                        ->select('token')
                        ->select('profile_pic')
                        ->get_where('table_user', array(
                            'email' => $data['email'],
                            'password' => $data['password']
                        ))->first_row('array');


                    if (!empty($firebase_id)) {

                        $data_update['firebase_id'] = $firebase_id;

                        $this->db->where('user_pk', $user['user_pk']);
                        $flag = $this->db->update('table_user', $data_update);

                    }


                    if (empty($user)) {

                        $response['success'] = '0';
                        $response['message'] = 'Login Failed';
                        echo json_encode($response);


                    } else {
                        $response['success'] = '1';
                        $response['user'] = $user;
                        $response['message'] = 'Login Success';
//                echo json_encode($response);

                        json_output(200, $response);
                    }

                } else {

                    $response['success'] = '0';
                    $response['message'] = 'Login Failed';
                    echo json_encode($response);

                }
            }
        }


    }

    public function forgot_password()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                if ($this->email_model->password_reset_email2()) {
                    echo 'email_sent';
                } else {
                    echo 'email_not_sent';
                }
            }
        }


    }

    public function login_other()
    {

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $data['email'] = $this->input->post('email');
                $firebase_id = $this->input->post('firebase_id');


                $user = $this->db
                    ->select('user_pk')
                    ->select('username')
                    ->select('email')
                    ->select('gender')
                    ->select('profile_pic')
                    ->get_where('table_user', array(
                        'email' => $data['email']
                    ))->first_row('array');


                if (!empty($firebase_id) & !empty($user)) {

                    $token = crypt(substr(md5(rand()), 0, 7));

                    $data_update['token'] = $token;

                    $data_update['firebase_id'] = $firebase_id;

                    $this->db->where('user_pk', $user['user_pk']);
                    $flag = $this->db->update('table_user', $data_update);

                }

                $user = $this->db
                    ->select('user_pk')
                    ->select('username')
                    ->select('token')
                    ->select('email')
                    ->select('gender')
                    ->select('profile_pic')
                    ->get_where('table_user', array(
                        'email' => $data['email']
                    ))->first_row('array');


                if (empty($user)) {

                    $response['success'] = '0';
                    $response['message'] = 'Login Failed';
                    echo json_encode($response);


                } else {
                    $response['success'] = '1';
                    $response['user'] = $user;
                    $response['message'] = 'Login Success';
                    echo json_encode($response);

                }
            }
        }


    }

    public function follow()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $response = $this->auth_model->auth();

                if ($response['status'] == 200) {


                    $this->load->model('Table_follow_model');

                    $user_fk = $this->input->post('user_fk');
                    $other_user_fk = $this->input->post('other_user_fk');
                    $follow = $this->input->post('follow');

                    if ($user_fk == $other_user_fk) {

                        $response['success'] = '0';
                        $response['message'] = 'you not able to follow yourself';


                        echo json_encode($response);

                    } else {


                        $response = $this->Table_follow_model->follow($user_fk, $other_user_fk, $follow);
                        echo json_encode($response);


                    }

                }
            }
        }


    }

    public function trending()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $response = $this->auth_model->auth();

                if ($response['status'] == 200) {

                    $this->load->model('Table_poll_model');
                    $this->load->model('Table_polling_model');
                    $this->load->model('Table_follow_model');
                    $this->load->model('Table_comments_model');

                    $limit_count = 30;

                    $user_pk = $this->input->post('user_pk');
                    $limit_from = $this->input->post('limit_from');

                    if (empty($limit_from))
                        $limit_from = 0;

                    $response = array();
                    $poll_data = array();

                    $all_polls = $this->Table_poll_model->get_trending_polls($limit_from, $limit_count);


                    foreach ($all_polls as $single_poll) {

                        $poll_pk = $single_poll['poll_pk'];

                        $poll['poll_pk'] = $single_poll['poll_pk'];
                        $poll['title'] = $single_poll['title'];
                        $poll['choice1'] = $single_poll['choice1'];
                        $poll['choice2'] = $single_poll['choice2'];
                        $poll['user_fk'] = $single_poll['user_fk'];
                        $poll['username'] = $single_poll['username'];
                        $poll['profile_pic'] = $single_poll['profile_pic'];
                        $poll['feed_type'] = $single_poll['feed_type'];
                        $poll['created_time'] = $this->get_timeago(strtotime($single_poll['created_time']));

                        $is_answered = $this->Table_polling_model->is_user_answered($poll_pk, $user_pk);

                        if (empty($is_answered)) {

                            $poll['selected_choice'] = 0;

                        } else {

                            $poll['selected_choice'] = $is_answered->choice;

                        }

                        $other_user_fk = $single_poll['user_fk'];

                        $is_follow = $this->Table_follow_model->is_user_followed($user_pk, $other_user_fk);

                        if (empty($is_follow)) {

                            $poll['follow'] = 0;

                        } else {

                            $poll['follow'] = $is_follow->follow;

                        }

                        $poll_count = $this->Table_polling_model->get_answered_count($poll_pk);

                        $poll['answered_poll'] = $poll_count;

                        $choice1_count = $this->Table_polling_model->get_choice_count($poll_pk, '1');

                        $choice2_count = $this->Table_polling_model->get_choice_count($poll_pk, '2');

                        $comments_count = $this->Table_comments_model->get_comments_count($poll_pk);

                        if ($poll_count > 0) {

                            $choice1_percentage = ($choice1_count / $poll_count) * 100;
                            $choice2_percentage = ($choice2_count / $poll_count) * 100;
                        } else {

                            $choice1_percentage = 0;
                            $choice2_percentage = 0;

                        }


                        $poll['choice1_percentage'] = round($choice1_percentage);
                        $poll['choice2_percentage'] = round($choice2_percentage);
                        $poll['choice1_count'] = $choice1_count;
                        $poll['choice2_count'] = $choice2_count;
                        $poll['comments_count'] = $comments_count;


                        array_push($poll_data, $poll);


                    }


                    $query = "SELECT user_pk,username,email,gender,profile_pic FROM table_user LIMIT 10";
                    $users = $this->db->query($query)->result_array();

                    $response['success'] = '1';
                    $response['poll_data'] = $poll_data;

                    $response['top_users'] = $users;


                    echo json_encode($response);

                }
            }
        }


    }

    public function delete_poll()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $user_fk = $this->input->post('user_fk');
                $poll_pk = $this->input->post('poll_pk');


                $this->db->where('user_fk', $user_fk);
                $this->db->where('poll_pk', $poll_pk);
                $response['flag'] = $this->db->delete('table_poll');

                $response['success'] = "1";

                echo json_encode($response);
            }
        }

    }

    public function report_poll()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {

            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

//                $response = $this->auth_model->auth();

//                if ($response['status'] == 200) {

                $this->load->model('Table_report_model');

                $user_fk = $this->input->post('user_fk');
                $poll_fk = $this->input->post('poll_fk');

                $response = $this->Table_report_model->add_report_poll($user_fk, $poll_fk);

                echo json_encode($response);

//                }
            }
        }


    }

    public function block_user()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {

            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

//                $response = $this->auth_model->auth();

//                if ($response['status'] == 200) {

                $this->load->model('Table_block_model');

                $blocked_by_user_fk = $this->input->post('blocked_by_user_fk');
                $blocked_user_fk = $this->input->post('blocked_user_fk');

                $response = $this->Table_block_model->block_user($blocked_by_user_fk, $blocked_user_fk);

                echo json_encode($response);

//                }
            }
        }


    }


    public function view_all_poll()
    {

        $this->load->model('Table_poll_model');
        $this->load->model('Table_polling_model');
        $this->load->model('Table_follow_model');
        $this->load->model('Table_comments_model');

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $response = $this->auth_model->auth();

                if ($response['status'] == 200) {

                    $limit_count = 30;

                    $user_pk = $this->input->post('user_pk');
                    $limit_from = $this->input->post('limit_from');

                    if (empty($limit_from))
                        $limit_from = 0;

                    $response = array();
                    $poll_data = array();

                    $all_polls = $this->Table_poll_model->get_all_polls($user_pk, $limit_from, $limit_count);

                    foreach ($all_polls as $single_poll) {

                        $poll_pk = $single_poll['poll_pk'];

                        $poll['poll_pk'] = $single_poll['poll_pk'];
                        $poll['title'] = $single_poll['title'];
                        $poll['choice1'] = $single_poll['choice1'];
                        $poll['choice2'] = $single_poll['choice2'];
                        $poll['user_fk'] = $single_poll['user_fk'];
                        $poll['username'] = $single_poll['username'];
                        $poll['profile_pic'] = $single_poll['profile_pic'];
                        $poll['feed_type'] = $single_poll['feed_type'];
                        $poll['created_time'] = $this->get_timeago(strtotime($single_poll['created_time']));


                        $is_answered = $this->Table_polling_model->is_user_answered($poll_pk, $user_pk);

                        if (empty($is_answered)) {

                            $poll['selected_choice'] = 0;

                        } else {

                            $poll['selected_choice'] = $is_answered->choice;

                        }

                        $other_user_fk = $single_poll['user_fk'];

                        $is_follow = $this->Table_follow_model->is_user_followed($user_pk, $other_user_fk);

                        if (empty($is_follow)) {

                            $poll['follow'] = 0;

                        } else {

                            $poll['follow'] = $is_follow->follow;

                        }

                        $poll_count = $this->Table_polling_model->get_answered_count($poll_pk);

                        $poll['answered_poll'] = $poll_count;

                        $choice1_count = $this->Table_polling_model->get_choice_count($poll_pk, '1');


                        $choice2_count = $this->Table_polling_model->get_choice_count($poll_pk, '2');

                        $comments_count = $this->Table_comments_model->get_comments_count($poll_pk);

                        if ($poll_count > 0) {

                            $choice1_percentage = ($choice1_count / $poll_count) * 100;
                            $choice2_percentage = ($choice2_count / $poll_count) * 100;
                        } else {

                            $choice1_percentage = 0;
                            $choice2_percentage = 0;

                        }


                        $poll['choice1_percentage'] = round($choice1_percentage);
                        $poll['choice2_percentage'] = round($choice2_percentage);
                        $poll['choice1_count'] = $choice1_count;
                        $poll['choice2_count'] = $choice2_count;
                        $poll['comments_count'] = $comments_count;


                        array_push($poll_data, $poll);


                    }


                    $response['success'] = '1';
                    $response['poll_data'] = $poll_data;


                    echo json_encode($response);

                } else {


                }


            }
        }


    }


    public
    function view_my_following_poll()
    {


        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $this->load->model('Table_poll_model');
                $this->load->model('Table_polling_model');
                $this->load->model('Table_follow_model');
                $this->load->model('Table_comments_model');

                $limit_count = 30;

                $user_pk = $this->input->post('user_pk');
                $limit_from = $this->input->post('limit_from');

                if (empty($limit_from))
                    $limit_from = 0;

                $response = array();
                $poll_data = array();

                $all_polls = $this->Table_poll_model->get_following_poll($user_pk, $limit_from, $limit_count);

//        $this->db->close();

                foreach ($all_polls as $single_poll) {

                    $poll_pk = $single_poll['poll_pk'];

                    $poll['poll_pk'] = $single_poll['poll_pk'];
                    $poll['title'] = $single_poll['title'];
                    $poll['choice1'] = $single_poll['choice1'];
                    $poll['choice2'] = $single_poll['choice2'];
                    $poll['user_fk'] = $single_poll['user_fk'];
                    $poll['username'] = $single_poll['username'];
                    $poll['profile_pic'] = $single_poll['profile_pic'];
                    $poll['feed_type'] = $single_poll['feed_type'];
                    $poll['created_time'] = $this->get_timeago(strtotime($single_poll['created_time']));


                    $is_answered = $this->Table_polling_model->is_user_answered($poll_pk, $user_pk);

                    if (empty($is_answered)) {

                        $poll['selected_choice'] = 0;

                    } else {

                        $poll['selected_choice'] = $is_answered->choice;

                    }

                    $other_user_fk = $single_poll['user_fk'];

                    $is_follow = $this->Table_follow_model->is_user_followed($user_pk, $other_user_fk);

                    if (empty($is_follow)) {

                        $poll['follow'] = 0;

                    } else {

                        $poll['follow'] = $is_follow->follow;

                    }

                    $poll_count = $this->Table_polling_model->get_answered_count($poll_pk);

                    $poll['answered_poll'] = $poll_count;


                    $choice1_count = $this->Table_polling_model->get_choice_count($poll_pk, '1');


                    $choice2_count = $this->Table_polling_model->get_choice_count($poll_pk, '2');

                    $comments_count = $this->Table_comments_model->get_comments_count($poll_pk);

                    if ($poll_count > 0) {

                        $choice1_percentage = ($choice1_count / $poll_count) * 100;
                        $choice2_percentage = ($choice2_count / $poll_count) * 100;
                    } else {

                        $choice1_percentage = 0;
                        $choice2_percentage = 0;

                    }


                    $poll['choice1_percentage'] = round($choice1_percentage);
                    $poll['choice2_percentage'] = round($choice2_percentage);
                    $poll['choice1_count'] = $choice1_count;
                    $poll['choice2_count'] = $choice2_count;
                    $poll['comments_count'] = $comments_count;


                    array_push($poll_data, $poll);


                }

                $response['success'] = '1';
                $response['poll_data'] = $poll_data;


                echo json_encode($response);

            }
        }

    }


    public
    function view_search_tag_poll()
    {


        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $this->load->model('Table_poll_model');
                $this->load->model('Table_polling_model');
                $this->load->model('Table_follow_model');
                $this->load->model('Table_comments_model');

                $limit_count = 30;

                $user_pk = $this->input->post('user_pk');
                $tag = $this->input->post('tag');
                $limit_from = $this->input->post('limit_from');

                if (empty($limit_from))
                    $limit_from = 0;

                $response = array();
                $poll_data = array();

                $all_polls = $this->Table_poll_model->get_searched_poll($tag, $limit_from, $limit_count);

//        $this->db->close();

                foreach ($all_polls as $single_poll) {

                    $poll_pk = $single_poll['poll_pk'];

                    $poll['poll_pk'] = $single_poll['poll_pk'];
                    $poll['title'] = $single_poll['title'];
                    $poll['choice1'] = $single_poll['choice1'];
                    $poll['choice2'] = $single_poll['choice2'];
                    $poll['user_fk'] = $single_poll['user_fk'];
                    $poll['username'] = $single_poll['username'];
                    $poll['profile_pic'] = $single_poll['profile_pic'];
                    $poll['feed_type'] = $single_poll['feed_type'];
                    $poll['created_time'] = $this->get_timeago(strtotime($single_poll['created_time']));

                    $is_answered = $this->Table_polling_model->is_user_answered($poll_pk, $user_pk);

                    if (empty($is_answered)) {

                        $poll['selected_choice'] = 0;

                    } else {

                        $poll['selected_choice'] = $is_answered->choice;

                    }

                    $other_user_fk = $single_poll['user_fk'];

                    $is_follow = $this->Table_follow_model->is_user_followed($user_pk, $other_user_fk);

                    if (empty($is_follow)) {

                        $poll['follow'] = 0;

                    } else {

                        $poll['follow'] = $is_follow->follow;

                    }

                    $poll_count = $this->Table_polling_model->get_answered_count($poll_pk);

                    $poll['answered_poll'] = $poll_count;


                    $choice1_count = $this->Table_polling_model->get_choice_count($poll_pk, '1');


                    $choice2_count = $this->Table_polling_model->get_choice_count($poll_pk, '2');

                    $comments_count = $this->Table_comments_model->get_comments_count($poll_pk);

                    if ($poll_count > 0) {

                        $choice1_percentage = ($choice1_count / $poll_count) * 100;
                        $choice2_percentage = ($choice2_count / $poll_count) * 100;
                    } else {

                        $choice1_percentage = 0;
                        $choice2_percentage = 0;

                    }


                    $poll['choice1_percentage'] = round($choice1_percentage);
                    $poll['choice2_percentage'] = round($choice2_percentage);
                    $poll['choice1_count'] = $choice1_count;
                    $poll['choice2_count'] = $choice2_count;
                    $poll['comments_count'] = $comments_count;


                    array_push($poll_data, $poll);

                }

                $response['success'] = '1';
                $response['poll_data'] = $poll_data;


                echo json_encode($response);

            }
        }

    }


    public
    function top_polls()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $this->load->model('Table_poll_model');
                $this->load->model('Table_polling_model');
                $this->load->model('Table_follow_model');


                $limit_count = 30;

                $user_pk = $this->input->post('user_pk');
                $limit_from = $this->input->post('limit_from');

                if (empty($limit_from))
                    $limit_from = 0;

                $response = array();
                $poll_data = array();

                $all_polls = $this->Table_poll_model->get_top_poll($limit_from, $limit_count);

                foreach ($all_polls as $single_poll) {

                    $poll_pk = $single_poll['poll_pk'];

                    $poll['poll_pk'] = $single_poll['poll_pk'];
                    $poll['title'] = $single_poll['title'];
                    $poll['choice1'] = $single_poll['choice1'];
                    $poll['choice2'] = $single_poll['choice2'];
                    $poll['user_fk'] = $single_poll['user_fk'];
                    $poll['username'] = $single_poll['username'];
                    $poll['profile_pic'] = $single_poll['profile_pic'];
                    $poll['feed_type'] = $single_poll['feed_type'];
                    $poll['created_time'] = $this->get_timeago(strtotime($single_poll['created_time']));


                    $is_answered = $this->Table_polling_model->is_user_answered($poll_pk, $user_pk);

                    if (empty($is_answered)) {

                        $poll['selected_choice'] = 0;

                    } else {

                        $poll['selected_choice'] = $is_answered->choice;

                    }

                    $other_user_fk = $single_poll['user_fk'];

                    $is_follow = $this->Table_follow_model->is_user_followed($user_pk, $other_user_fk);

                    if (empty($is_follow)) {

                        $poll['follow'] = 0;

                    } else {

                        $poll['follow'] = $is_follow->follow;

                    }

                    $poll_count = $this->Table_polling_model->get_answered_count($poll_pk);

                    $poll['answered_poll'] = $poll_count;


                    $choice1_count = $this->Table_polling_model->get_choice_count($poll_pk, '1');


                    $choice2_count = $this->Table_polling_model->get_choice_count($poll_pk, '2');

                    if ($poll_count > 0) {

                        $choice1_percentage = ($choice1_count / $poll_count) * 100;
                        $choice2_percentage = ($choice2_count / $poll_count) * 100;
                    } else {

                        $choice1_percentage = 0;
                        $choice2_percentage = 0;

                    }


                    $poll['choice1_percentage'] = round($choice1_percentage);
                    $poll['choice2_percentage'] = round($choice2_percentage);
                    $poll['choice1_count'] = $choice1_count;
                    $poll['choice2_count'] = $choice2_count;


                    array_push($poll_data, $poll);


                }

                $response['success'] = '1';
                $response['poll_data'] = $poll_data;


                echo json_encode($response);

            }
        }

    }

    public
    function top_picvoters()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $this->load->model('Table_poll_model');

                $limit_from = $this->input->post('limit_from');

                if (empty($limit_from))
                    $limit_from = 0;

                $limit_count = 30;

                $user_pk = $this->input->post('user_pk');

                $top_users = $this->Table_poll_model->get_top_picvoters_poll($limit_from, $limit_count);

                $response['success'] = '1';
                $response['users'] = $top_users;

                echo json_encode($response);

            }
        }
    }


    public
    function view_single_poll()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $this->load->model('Table_poll_model');
                $this->load->model('Table_polling_model');
                $this->load->model('Table_follow_model');
                $this->load->model('Table_comments_model');

                $limit_count = 30;

                $user_pk = $this->input->post('user_pk');
                $poll_pk = $this->input->post('poll_pk');
                $limit_from = $this->input->post('limit_from');

                if (empty($limit_from))
                    $limit_from = 0;

                $response = array();
                $poll_data = array();

                $single_poll = $this->Table_poll_model->get_single_poll($poll_pk);


                $poll_pk = $single_poll['poll_pk'];

                $poll['poll_pk'] = $single_poll['poll_pk'];
                $poll['title'] = $single_poll['title'];
                $poll['choice1'] = $single_poll['choice1'];
                $poll['choice2'] = $single_poll['choice2'];
                $poll['user_fk'] = $single_poll['user_fk'];
                $poll['username'] = $single_poll['username'];
                $poll['profile_pic'] = $single_poll['profile_pic'];
                $poll['feed_type'] = $single_poll['feed_type'];
                $poll['created_time'] = $this->get_timeago(strtotime($single_poll['created_time']));


                $is_answered = $this->Table_polling_model->is_user_answered($poll_pk, $user_pk);

                if (empty($is_answered)) {

                    $poll['selected_choice'] = 0;

                } else {

                    $poll['selected_choice'] = $is_answered->choice;

                }

                $other_user_fk = $single_poll['user_fk'];

                $is_follow = $this->Table_follow_model->is_user_followed($user_pk, $other_user_fk);

                if (empty($is_follow)) {

                    $poll['follow'] = 0;

                } else {

                    $poll['follow'] = $is_follow->follow;

                }


                $poll_count = $this->Table_polling_model->get_answered_count($poll_pk);

                $poll['answered_poll'] = $poll_count;


                $choice1_count = $this->Table_polling_model->get_choice_count($poll_pk, '1');


                $choice1_male = $this->Table_polling_model->get_choice_gender_count($poll_pk, '1', 'male');

                $choice1_female = $this->Table_polling_model->get_choice_gender_count($poll_pk, '1', 'female');

                $choice2_count = $this->Table_polling_model->get_choice_count($poll_pk, '2');

                $choice2_male = $this->Table_polling_model->get_choice_gender_count($poll_pk, '2', 'male');

                $choice2_female = $this->Table_polling_model->get_choice_gender_count($poll_pk, '2', 'female');

                $comments_count = $this->Table_comments_model->get_comments_count($poll_pk);

                $comments = $this->Table_comments_model->get_comments($poll_pk);

                if ($poll_count > 0) {

                    $choice1_percentage = ($choice1_count / $poll_count) * 100;
                    $choice2_percentage = ($choice2_count / $poll_count) * 100;
                } else {

                    $choice1_percentage = 0;
                    $choice2_percentage = 0;

                }


                $poll['choice1_percentage'] = round($choice1_percentage);
                $poll['choice2_percentage'] = round($choice2_percentage);
                $poll['choice1_count'] = $choice1_count;
                $poll['choice2_count'] = $choice2_count;
                $poll['choice1_male'] = $choice1_male;
                $poll['choice2_male'] = $choice2_male;

                $poll['choice1_female'] = $choice1_female;
                $poll['choice2_female'] = $choice2_female;
                $poll['comments_count'] = $comments_count;

                $response['success'] = '1';
                $response['poll_data'] = $poll;
                $response['comments'] = $comments;


                echo json_encode($response);

            }
        }

    }

    public
    function view_others_poll()
    {
        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $this->load->model('Table_poll_model');
                $this->load->model('Table_polling_model');
                $this->load->model('Table_follow_model');
                $this->load->model('Table_user_model');
                $this->load->model('Table_comments_model');

                $limit_count = 30;

                $user_pk = $this->input->post('user_pk');
                $other_user_fk = $this->input->post('other_user_fk');
                $limit_from = $this->input->post('limit_from');

                if (empty($limit_from))
                    $limit_from = 0;

                $response = array();
                $poll_data = array();

                $all_polls = $this->Table_poll_model->get_user_poll($other_user_fk, $limit_from, $limit_count);

                foreach ($all_polls as $single_poll) {

                    $poll_pk = $single_poll['poll_pk'];

                    $poll['poll_pk'] = $single_poll['poll_pk'];
                    $poll['title'] = $single_poll['title'];
                    $poll['choice1'] = $single_poll['choice1'];
                    $poll['choice2'] = $single_poll['choice2'];
                    $poll['user_fk'] = $single_poll['user_fk'];
                    $poll['username'] = $single_poll['username'];
                    $poll['profile_pic'] = $single_poll['profile_pic'];
                    $poll['feed_type'] = $single_poll['feed_type'];
                    $poll['created_time'] = $this->get_timeago(strtotime($single_poll['created_time']));;

                    $is_answered = $this->Table_polling_model->is_user_answered($poll_pk, $user_pk);

                    if (empty($is_answered)) {

                        $poll['selected_choice'] = 0;

                    } else {

                        $poll['selected_choice'] = $is_answered->choice;

                    }

                    $other_user_fk = $single_poll['user_fk'];

                    $is_follow = $this->Table_follow_model->is_user_followed($user_pk, $other_user_fk);

                    if (empty($is_follow)) {

                        $poll['follow'] = 0;

                    } else {

                        $poll['follow'] = $is_follow->follow;

                    }

                    $poll_count = $this->Table_polling_model->get_answered_count($poll_pk);

                    $poll['answered_poll'] = $poll_count;


                    $choice1_count = $this->Table_polling_model->get_choice_count($poll_pk, '1');


                    $choice2_count = $this->Table_polling_model->get_choice_count($poll_pk, '2');

                    $comments_count = $this->Table_comments_model->get_comments_count($poll_pk);

                    if ($poll_count > 0) {

                        $choice1_percentage = ($choice1_count / $poll_count) * 100;
                        $choice2_percentage = ($choice2_count / $poll_count) * 100;
                    } else {

                        $choice1_percentage = 0;
                        $choice2_percentage = 0;

                    }


                    $poll['choice1_percentage'] = round($choice1_percentage);
                    $poll['choice2_percentage'] = round($choice2_percentage);
                    $poll['choice1_count'] = $choice1_count;
                    $poll['choice2_count'] = $choice2_count;
                    $poll['comments_count'] = $comments_count;


                    array_push($poll_data, $poll);


                }

                $user_data = $this->Table_user_model->get_user_data($other_user_fk);

                $poll_count = $this->Table_poll_model->user_poll_count($other_user_fk);

                $user_data['total_posts'] = $poll_count;

                $poll_count = $this->Table_polling_model->user_polled_count($other_user_fk);

                $user_data['total_poll'] = $poll_count;

                $is_follow = $this->Table_follow_model->is_user_followed($user_pk, $other_user_fk);

                $user_data['is_friend'] = $this->Table_user_model->friend_request_status($user_pk, $other_user_fk);

                if (empty($is_follow)) {

                    $user_data['follow'] = 0;

                } else {

                    $user_data['follow'] = $is_follow->follow;

                }

                $response['success'] = '1';
                $response['user_data'] = $user_data;
                $response['poll_data'] = $poll_data;


                echo json_encode($response);

            }
        }

    }

    public
    function view_my_poll()
    {


        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $this->load->model('Table_poll_model');
                $this->load->model('Table_polling_model');
                $this->load->model('Table_follow_model');
                $this->load->model('Table_user_model');
                $this->load->model('Table_comments_model');


                $limit_count = 30;

                $user_pk = $this->input->post('user_pk');
                $limit_from = $this->input->post('limit_from');

                if (empty($limit_from))
                    $limit_from = 0;

                $response = array();
                $poll_data = array();

                $all_polls = $this->Table_poll_model->get_user_poll($user_pk, $limit_from, $limit_count);

                foreach ($all_polls as $single_poll) {

                    $poll_pk = $single_poll['poll_pk'];

                    $poll['poll_pk'] = $single_poll['poll_pk'];
                    $poll['title'] = $single_poll['title'];
                    $poll['choice1'] = $single_poll['choice1'];
                    $poll['choice2'] = $single_poll['choice2'];
                    $poll['user_fk'] = $single_poll['user_fk'];
                    $poll['username'] = $single_poll['username'];
                    $poll['profile_pic'] = $single_poll['profile_pic'];
                    $poll['feed_type'] = $single_poll['feed_type'];
                    $poll['created_time'] = $this->get_timeago(strtotime($single_poll['created_time']));;

                    $is_answered = $this->Table_polling_model->is_user_answered($poll_pk, $user_pk);

                    if (empty($is_answered)) {

                        $poll['selected_choice'] = 0;

                    } else {

                        $poll['selected_choice'] = $is_answered->choice;

                    }

                    $other_user_fk = $single_poll['user_fk'];

                    $is_follow = $this->Table_follow_model->is_user_followed($user_pk, $other_user_fk);

                    if (empty($is_follow)) {

                        $poll['follow'] = 0;

                    } else {

                        $poll['follow'] = $is_follow->follow;

                    }

                    $poll_count = $this->Table_polling_model->get_answered_count($poll_pk);

                    $poll['answered_poll'] = $poll_count;


                    $choice1_count = $this->Table_polling_model->get_choice_count($poll_pk, '1');


                    $choice2_count = $this->Table_polling_model->get_choice_count($poll_pk, '2');

                    $comments_count = $this->Table_comments_model->get_comments_count($poll_pk);

                    if ($poll_count > 0) {

                        $choice1_percentage = ($choice1_count / $poll_count) * 100;
                        $choice2_percentage = ($choice2_count / $poll_count) * 100;
                    } else {

                        $choice1_percentage = 0;
                        $choice2_percentage = 0;

                    }


                    $poll['choice1_percentage'] = round($choice1_percentage);
                    $poll['choice2_percentage'] = round($choice2_percentage);
                    $poll['choice1_count'] = $choice1_count;
                    $poll['choice2_count'] = $choice2_count;
                    $poll['comments_count'] = $comments_count;


                    array_push($poll_data, $poll);

                }

                $user_data = $this->Table_user_model->get_user_data($user_pk);

                $poll_count = $this->Table_poll_model->user_poll_count($user_pk);

                $user_data['total_posts'] = $poll_count;

                $poll_count = $this->Table_polling_model->user_polled_count($other_user_fk);

                $user_data['total_poll'] = $poll_count;

                $response['success'] = '1';
                $response['user_data'] = $user_data;
                $response['poll_data'] = $poll_data;


                echo json_encode($response);

            }
        }

    }

    public
    function view_all_notifications()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $all_notification = array();

                $limit_count = 30;

                $user_pk = $this->input->post('user_pk');
                $limit_from = $this->input->post('limit_from');


                $query = "SELECT notification_pk, 
       message, 
       from_user_fk, 
       username, 
       email, 
       gender,
       ref_fk,
       profile_pic,
       table_notification.created_time
        
FROM   table_notification 
       JOIN table_user 
         ON table_user.user_pk = table_notification.from_user_fk WHERE to_user_fk = '$user_pk' ORDER BY notification_pk DESC limit $limit_from,$limit_count";

                $notification_data = $this->db->query($query)->result_array();

                foreach ($notification_data as $single_notification) {

                    $notification = $single_notification;
                    $notification['created_time'] = $this->get_timeago(strtotime($notification['created_time']));

                    array_push($all_notification, $notification);

                }

                $response['success'] = '1';
                $response['notifications'] = $all_notification;

                echo json_encode($response);

            }
        }

    }

    public
    function get_tags()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $this->load->model('Table_poll_tags_model');

                $tags = $this->Table_poll_tags_model->get_tags();

                $response['success'] = '1';
                $response['tags'] = $tags;

                echo json_encode($response);

            }
        }

    }


    public
    function get_tags_polls()
    {
        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $this->load->model('Table_poll_tags_model');

                $tag_data = $this->Table_poll_tags_model->get_tags_polls();

                $response['success'] = '1';
                $response['tags'] = $tag_data;

                echo json_encode($response);

            }
        }

    }


    public function add_comment()
    {
        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $user_fk = $this->input->post('user_fk');
                $comment = $this->input->post('comment');
                $poll_fk = $this->input->post('poll_fk');

                $this->load->model('Table_comments_model');
                $this->load->model('Table_user_model');
                $this->load->model('Table_poll_model');

                $resp = $this->Table_comments_model->add_comment($user_fk, $poll_fk, $comment);


                $from_user_data = $this->Table_user_model->get_single_user_data($user_fk);

                $poll_data = $this->Table_poll_model->get_single_poll_data($poll_fk);

                $to_user_data = $this->Table_user_model->get_single_user_data($poll_data['user_fk']);

                $datanotif['message'] = $from_user_data['username'] . ' commented on your poll';
                $datanotif['to_user_fk'] = $to_user_data['user_pk'];
                $datanotif['from_user_fk'] = $user_fk;
                $datanotif['ref_fk'] = $poll_fk;
                $datanotif['created_time'] = $created_time = date('Y-m-d H:i:s');

                //print_r($datanotif);
                //exit();

                $this->db->insert('table_notification', $datanotif);


                if ($user_fk != $poll_data['user_fk']) {

                    if (!empty($to_user_data['firebase_id'])) {


                        try {

                            $this->push_notification($to_user_data['firebase_id'], $datanotif['message']);

                        } catch (Exception $e) {

                        }


                    }
                }


                $response['success'] = '1';
                $response['status'] = $resp;

                echo json_encode($response);

            }
        }

    }


    public
    function timetest()
    {

        $this->push_reupload('fDuBk5hGBWg:APA91bEAwqa3roMwb5t7kCr5_y-qUGgPt2-m4oDXiLvTsL1g0cu2HQTmSyV9RV2wtjmEj-FbBjxlGue_aTsFSXvaglPUJnLr4VkQ7jxk6sCsThHGt6ihtFbSSb62ljjmIqQQW6YGKZJ4');

        echo $this->get_timeago(strtotime("2018-03-18 00:00:00"));
    }


    function get_timeago($ptime)
    {
        $estimate_time = time() - $ptime;

        if ($estimate_time < 1) {
            return 'less than 1 second ago';
        }

        $condition = array(
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60 => 'month',
            24 * 60 * 60 => 'day',
            60 * 60 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($condition as $secs => $str) {
            $d = $estimate_time / $secs;

            if ($d >= 1) {
                $r = round($d);
                return 'about ' . $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
            }
        }
    }

    function push_notification($token, $message)
    {

        $registrationIds = array($token);
        $msg = array
        (
            'message' => $message,
            'priority' => 'high',
            'title' => 'PicVote',
            'body' => $message,
            'flag' => 'notification'
            //'title'		=> 'Mobile Sync App',
            //'subtitle'	=> 'This is a subtitle. subtitle',
            //'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
            //'vibrate'	=> 1,
            //'sound'		=> 1,
            //'largeIcon'	=> 'large_icon',
            //'smallIcon'	=> 'small_icon'
        );


        $fields = array
        (
            'priority' => 'high',
            'content_available' => true,
            'notification' => $msg,
            'registration_ids' => $registrationIds,
            'data' => $msg
        );

        $headers = array
        (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        curl_close($ch);


//        echo $result;


    }

    function push_notification_test($token, $message)
    {

        $registrationIds = array($token);
        $msg = array
        (
            'message' => $message,
            'priority' => 'high',
            'title' => 'PicVote',
            'body' => $message,
            //'title'		=> 'Mobile Sync App',
            //'subtitle'	=> 'This is a subtitle. subtitle',
            //'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
            //'vibrate'	=> 1,
            //'sound'		=> 1,
            //'largeIcon'	=> 'large_icon',
            //'smallIcon'	=> 'small_icon'
        );


        $fields = array
        (
            'priority' => 'high',
            'content_available' => true,
            'notification' => $msg,
            'registration_ids' => $registrationIds,
            'data' => $msg
        );

        $headers = array
        (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        curl_close($ch);


        echo $result;


    }

    public function test_push()
    {

        try {

            $this->push_notification_test('fNwa5mp16o4:APA91bHNwsfl9hRVEBsZqH9X16YCrxOjAYmRwZq22JoVmJSjN1dlt-604y-694YEIpf8oV_b2v_S5P2cdj77TbRkA2UQjpQsce-tMRdxVL0z_1Fn-uQRnxBf1uNaw8lK6dJZsFTBKn-M', 'test');

        } catch (Exception $e) {

        }

    }


    function push_notification_requests($token, $message)
    {

        $registrationIds = array($token);
        $msg = array
        (
            'message' => $message,
            'priority' => 'high',
            'title' => 'PicVote',
            'body' => $message,
            'flag' => 'friend_request'
            //'title'		=> 'Mobile Sync App',
            //'subtitle'	=> 'This is a subtitle. subtitle',
            //'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
            //'vibrate'	=> 1,
            //'sound'		=> 1,
            //'largeIcon'	=> 'large_icon',
            //'smallIcon'	=> 'small_icon'
        );


        $fields = array
        (
            'priority' => 'high',
            'content_available' => true,
            'notification' => $msg,
            'registration_ids' => $registrationIds,
            'data' => $msg
        );

        $headers = array
        (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        curl_close($ch);


//        echo $result;


    }

    function push_notification_chat($sender_pk, $token, $message)
    {

        $registrationIds = array($token);
        $msg = array
        (
            'message' => $message,
            'priority' => 'high',
            'title' => 'PicVote',
            'body' => $message,
            'flag' => 'chat',
            'sender_pk' => $sender_pk,
            'ttl' => 3600
            //'title'		=> 'Mobile Sync App',
            //'subtitle'	=> 'This is a subtitle. subtitle',
            //'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
            //'vibrate'	=> 1,
            //'sound'		=> 1,
            //'largeIcon'	=> 'large_icon',
            //'smallIcon'	=> 'small_icon'
        );


        $fields = array
        (
            'priority' => 'high',
            'content_available' => true,
            'notification' => $msg,
            'registration_ids' => $registrationIds,
            'data' => $msg
        );

        $headers = array
        (
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        curl_close($ch);

        $data1['message'] = json_encode($msg);
        $data1['response'] = stripslashes($result);
        $data1['fb_created_time'] = date('Y-m-d H:i:s');

        $this->db->insert('firebase_log', $data1);


    }


    public
    function dummy()
    {

        $data['username'] = $this->faker->unique()->userName;
        $data['email'] = $this->faker->unique()->email;
        $data['medium'] = 'direct';
        $data['password'] = md5($this->faker->unique()->password('4', '6'));

        $a = array("male", "female");

        $randIndex = array_rand($a);

        $random_keys = $a[$randIndex];


        $data['gender'] = $random_keys;
        $this->db->insert('table_user', $data);

        $id = $this->db->insert_id();

        $profile_pic = $id . '.jpg';

        $data['profile_pic'] = $profile_pic;

        $this->db->where('user_pk', $id);
        $flag = $this->db->update('table_user', $data);

        recache();

        echo $flag;
    }

    public
    function image_compress()
    {

//        $this->load->library('imageresize');
        $images = $this->db->get('table_user')->result_array();


        foreach ($images as $single_image) {


            $userimg = 'uploads/users_image/' . $single_image['profile_pic'];
            $resized_userimg = 'uploads/users_image/resized_' . $single_image['profile_pic'];
            $resized_image = new ImageResize($userimg);
            $resized_image->resizeToBestFit('500', '500');
            $resized_image->save($userimg);

        }
//        echo $resized_image;
//        $resized_image->crop(100, 100, ImageResize::CROPCENTER);
//        $resized_image->save('' . 'resized1_' . $userimg);

    }

    public
    function image_compress_poll()
    {

//        $this->load->library('imageresize');
        $images = $this->db->get('table_poll')->result_array();


        foreach ($images as $single_image) {

//            print_r($single_image);

            $userimg = 'uploads/choices_image/' . $single_image['choice1'];

            $resized_image = new ImageResize($userimg);
            $resized_image->resizeToBestFit('600', '600');
            $resized_image->save($userimg);

        }
//        echo $resized_image;
//        $resized_image->crop(100, 100, ImageResize::CROPCENTER);
//        $resized_image->save('' . 'resized1_' . $userimg);


//        $userimg = 'uploads/choices_image/choice1_124.png';
//        $resized_image = new ImageResize($userimg);
//        $resized_image->resizeToBestFit('600', '600');
//        $resized_image->save($userimg);
    }

    public
    function reset_password()
    {

        $email = $this->input->post('email');

        if (!empty($email)) {

            $user = $this->db
                ->select('user_pk')
                ->get_where('table_user', array(
                    'email' => $email
                ))->first_row('array');

            if (!empty($user)) {

                $password = substr(hash('sha512', rand()), 0, 5);

                $data_update['password'] = md5($password);

                $this->db->where('user_pk', $user['user_pk']);
                $flag = $this->db->update('table_user', $data_update);

                $this->send_email($email, $password);

                $response['success'] = '1';
                $response['insert status'] = $flag;
                $response['message'] = 'If the entered email is correct, you will receive an email with the resetted password.';
                echo json_encode($response);
            } else {

                $response['success'] = '0';
                $response['message'] = 'Password reset failure. User email not exist';
                echo json_encode($response);
            }


        }
    }

    public
    function send_email($email, $password)
    {

        $this->load->library("PhpMailerLib");
        $mail = $this->phpmailerlib->load();

        try {
            //Server settings
            $mail->SMTPDebug = 0;                                 // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.googlemail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'youremail@gmail.com';                 // SMTP username
            $mail->Password = 'yourpassword';                           // SMTP password
            $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465;                                    // TCP port to connect to
            //Recipients
            $mail->setFrom('Your Email', 'Your Company Name');
//            $mail->addAddress($email, 'PicVote');     // Add a recipient
            $mail->addAddress($email, '');     // Add a recipient

            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Forgot Password';
            $mail->Body = 'Here is your resetted password : ' . $password;
//                $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }


    public function leaderboard()
    {
        $this->load->model('Table_user_model');

        $user_data = $this->Table_user_model->leaderboard();

        $response['success'] = '1';
        $response['user_data'] = $user_data;

        echo json_encode($response);


    }


    public function add_friend()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $fr_requested_user_fk = $this->input->post('fr_requested_user_fk');
                $fr_request_to_user_fk = $this->input->post('fr_request_to_user_fk');

                $this->load->model('Table_user_model');

                $user_data = $this->Table_user_model->add_friend($fr_requested_user_fk, $fr_request_to_user_fk);


                $to_user_data = $this->Table_user_model->get_single_user_data($fr_request_to_user_fk);

                if (!empty($to_user_data['firebase_id'])) {


                    try {

                        $this->push_notification_requests($to_user_data['firebase_id'], 'You received a friend request!');

                    } catch (Exception $e) {

                    }


                }

                $response['success'] = '1';
                $response['user_data'] = $user_data;

                echo json_encode($response);
            }
        }

    }

    public function friend_requests()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $user_pk = $this->input->post('user_pk');

                $this->load->model('Table_user_model');

                $user_data = $this->Table_user_model->friend_requests($user_pk);

                $response['success'] = '1';
                $response['user_data'] = $user_data;

                echo json_encode($response);
            }
        }

    }


    public function accept_request()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $this->load->model('Table_user_model');

                $user_pk = $this->input->post('user_pk');
                $fr_pk = $this->input->post('fr_pk');
                $accept_status = $this->input->post('accept_status');


                $user_data = $this->Table_user_model->accept_request($user_pk, $fr_pk, $accept_status);

                $response['success'] = '1';
                $response['flag'] = $user_data;

                echo json_encode($response);

            }
        }


    }


    public function friends_list()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $user_pk = $this->input->post('user_pk');

                $this->load->model('Table_user_model');

                $user_data = $this->Table_user_model->friends_list($user_pk);

                $response['success'] = '1';
                $response['friends'] = $user_data;

                echo json_encode($response);

            }
        }

    }


    public function list_chat_rooms()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $user_pk = $this->input->post('user_pk');

                $this->load->model('Table_user_model');

                $data = $this->Table_user_model->list_chat_rooms($user_pk);

                $response['success'] = '1';
                $response['chat_rooms'] = $data;

                echo json_encode($response);

            }
        }


    }

    public function add_chat_room()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $ch_sender_fk = $this->input->post('ch_sender_fk');
                $ch_receiver_fk = $this->input->post('ch_receiver_fk');

                $this->load->model('Table_user_model');

                $data = $this->Table_user_model->add_chatroom($ch_sender_fk, $ch_receiver_fk);

                $response['success'] = '1';
                $response['status'] = $data;

                echo json_encode($response);

            }
        }

    }

    public function temp()
    {


        $this->load->model('Table_user_model');

        $data = $this->Table_user_model->friend_request_status(11, 11);

        $response['success'] = '1';
        $response['status'] = $data;

        echo json_encode($response);
    }

    public function add_message()
    {

        $sender_fk = $this->input->post('sender_fk');
        $receiver_fk = $this->input->post('receiver_fk');
        $message = $this->input->post('message');

        $this->load->model('Table_user_model');

        $data = $this->Table_user_model->add_message($sender_fk, $receiver_fk, $message);

        $to_user_data = $this->Table_user_model->get_single_user_data($receiver_fk);

        if (!empty($to_user_data['firebase_id'])) {

            try {

                $this->push_notification_chat($sender_fk, $to_user_data['firebase_id'], 'You received a message');

            } catch (Exception $e) {

            }

        }

        $response['success'] = '1';
        $response['status'] = $data;

        echo json_encode($response);


    }

    public function change_password()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $user_pk = $this->input->post('user_pk');
                $old_password = $this->input->post('old_password');
                $new_password = $this->input->post('new_password');

                $this->load->model('Table_user_model');

                $user_check = $this->db->select(user_pk)->get_where('table_user', array('user_pk' => $user_pk, 'password' => md5($old_password)))->first_row('array');

                if (count($user_check) > 0) {

                    $data = $this->Table_user_model->change_password($user_pk, $new_password);

                    $response['success'] = '1';
                    $response['status'] = $data;
                    $response['message'] = 'Password Changed';

                    echo json_encode($response);

                } else {

                    $response['success'] = '0';
                    $response['message'] = 'Old password not matching the database.';

                    echo json_encode($response);

                }
            }
        }


    }

    public function get_blocked_users()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {
                $user_pk = $this->input->post('user_pk');

                $this->load->model('Table_block_model');

                $data = $this->Table_block_model->get_blocked_users($user_pk);

                $response['success'] = '1';
                $response['users'] = $data;

                echo json_encode($response);
            }
        }

    }

    public function unblock()
    {

        $method = $_SERVER['REQUEST_METHOD'];


        if ($method != 'POST') {
            json_output(400, array('status' => 400, 'message' => 'Bad request.'));
        } else {

            $check_auth_client = $this->auth_model->check_auth_client();


            if ($check_auth_client == true) {

                $user_pk = $this->input->post('user_pk');
                $other_user_fk = $this->input->post('other_user_fk');

                $this->load->model('Table_block_model');

                $data = $this->Table_block_model->unblock($user_pk, $other_user_fk);

                $response['success'] = '1';
                $response['message'] = $data;

                echo json_encode($response);
            }
        }


    }


    public function test()
    {

        $censor = new CensorWords;

        $input_title = $censor->censorString($this->input->post('title'));

        $vari = $input_title['clean'];

        $data1['poll_fk'] = 100;
        $data1['tag_name'] = $vari;
        $this->db->insert('table_poll_tags', $data1);

        echo $vari;


    }


//    public function test_porn2()
//    {
//
//        $upload_image1 = 'uploads/choices_image/choice1_220.png';
//
//        $quant = new Image_FleshSkinQuantifier($upload_image1);
//
//        if($quant->isPorn())
//            echo 'This image contains a lot of skin colors, thus might contain some adult content';
//        else
//            echo 'This image does not contain many skin colors, thus is not likely to contain adult content';
//
//    }
//
//    public function test_porn3()
//    {
//
//        $this->load->model('Table_poll_model');
//
//        $this->Table_poll_model->test_porn2();
//
//    }


}
