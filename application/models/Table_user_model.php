<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Table_user_model extends CI_Model
{


    public function get_user_data($other_user_fk)
    {

        $query = "SELECT user_pk,username,email,gender,profile_pic FROM table_user WHERE user_pk = '$other_user_fk'";
        $user_data = $this->db->query($query)->first_row('array');

        return $user_data;

    }

    public function get_single_user_data($user_fk)
    {

        return $this->db->get_where('table_user', array('user_pk' => $user_fk))->first_row('array');

    }

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function register()
    {

        $response = array();

        $data['username'] = $this->input->post('username');
        $data['email'] = $this->input->post('email');
        $data['medium'] = $this->input->post('medium');
        $data['password'] = md5($this->input->post('password'));
        $data['gender'] = $this->input->post('gender');
        $data['firebase_id'] = $this->input->post('firebase_id');


        $user_check = $this->db->select(user_pk)->get_where('table_user', array('email' => $data['email'], 'password' => $data['password']))->first_row('array');

        if (count($user_check) > 0) {


            $response['success'] = '0';
            $response['message'] = 'Already registered';

        } else {

            $this->db->insert('table_user', $data);
            $id = $this->db->insert_id();

            if ($_FILES['img1']['tmp_name'] == null) {

                $profile_pic = '';
            } else {

                $profile_pic = $this->crud_model->file_up_new("img1", "users", "user", $id, '', '', '.png');

                $upload_image1 = 'uploads/users_image/' . $profile_pic;

                $resized_image = new ImageResize($upload_image1);
                $resized_image->quality_jpg = 90;
                $resized_image->resizeToBestFit('600', '600');
                $resized_image->save($upload_image1, 2, 90);
            }


            $data['profile_pic'] = $profile_pic;

            $token = crypt(substr(md5(rand()), 0, 7));

            $data['token'] = $token;

            $this->db->where('user_pk', $id);
            $flag = $this->db->update('table_user', $data);

//            recache();


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


            if ($flag == 1) {

                $response = array();
                $response['success'] = '1';
                $response['user'] = $user;
                $response['message'] = 'registered';
            } else {

                $response = array();
                $response['success'] = '0';
                $response['message'] = 'not registered';
            }

        }

        return $response;
    }

    public function update_profile()
    {

        $response = array();

        $data['user_pk'] = $this->input->post('user_pk');
        $data['username'] = $this->input->post('username');
        $data['email'] = $this->input->post('email');
        $data['gender'] = $this->input->post('gender');


        $this->db->update('table_user', $data);
        $id = $this->generateRandomString(4);

        if ($_FILES['img1']['tmp_name'] == null) {

            $profile_pic = '';
        } else {

            $profile_pic = $this->crud_model->file_up_new("img1", "users", "user", $id, '', '', '.png');

            $data['profile_pic'] = $profile_pic;

            $upload_image1 = 'uploads/users_image/' . $profile_pic;

            $resized_image = new ImageResize($upload_image1);
            $resized_image->quality_jpg = 90;
            $resized_image->resizeToBestFit('600', '600');
            $resized_image->save($upload_image1, 2, 90);
        }




        $this->db->where('user_pk', $data['user_pk']);
        $flag = $this->db->update('table_user', $data);

        recache();


        $user = $this->db
            ->select('user_pk')
            ->select('username')
            ->select('email')
            ->select('gender')
            ->select('token')
            ->select('profile_pic')
            ->get_where('table_user', array(
                'email' => $data['email']
            ))->first_row('array');

        if ($flag == 1) {

            $response = array();
            $response['success'] = '1';
            $response['user'] = $user;
            $response['message'] = 'registered';
        } else {

            $response = array();
            $response['success'] = '0';
            $response['message'] = 'not registered';
        }


        return $response;
    }


    public function change_password($user_pk, $password)
    {

        $data['password'] = md5($password);

        $this->db->where('user_pk', $user_pk);
        $flag = $this->db->update('table_user', $data);

        return $flag;

    }


    public function leaderboard()
    {

        $query = "select distinct user_pk,username, 
(SELECT COUNT(`poll_pk`) from `table_poll` WHERE user_fk = user_pk AND is_approved = 1) as polls,
(SELECT COUNT(`polling_pk`) from `table_polling` WHERE user_fk = user_pk) as polled,
(SELECT COUNT(`comment_pk`) from `table_comments` WHERE user_fk = user_pk) as comments
from table_user JOIN table_poll ON table_poll.user_fk = table_user.user_pk";


        $user_data = $this->db->query($query)->result_array();

        return $user_data;

    }


    public function add_friend($fr_requested_user_fk, $fr_request_to_user_fk)
    {


        $this->db->where('fr_requested_user_fk', $fr_requested_user_fk);
        $this->db->where('fr_request_to_user_fk', $fr_request_to_user_fk);

        $q = $this->db->get('friend_requests');


        if ($q->num_rows() > 0) {

            $data['fr_requested_user_fk'] = $fr_requested_user_fk;
            $data['fr_request_to_user_fk'] = $fr_request_to_user_fk;
            $data['accept_status'] = '0';

            $this->db->where('fr_requested_user_fk', $fr_requested_user_fk);
            $this->db->where('fr_request_to_user_fk', $fr_request_to_user_fk);
            $this->db->update('friend_requests', $data);

            $flag['first'] = 'Updated';

        } else {
            $data['fr_requested_user_fk'] = $fr_requested_user_fk;
            $data['fr_request_to_user_fk'] = $fr_request_to_user_fk;
            $data['accept_status'] = '0';

            $this->db->insert('friend_requests', $data);

            $flag['first'] = 'Inserted';
        }

        return $flag;


    }


    public function friend_requests($user_pk)
    {

        $query = "select fr_pk,username,profile_pic,accept_status 
from friend_requests JOIN table_user ON table_user.user_pk = friend_requests.fr_requested_user_fk 
WHERE fr_request_to_user_fk = '$user_pk' AND accept_status = '0'";

        $user_data = $this->db->query($query)->result_array();

        return $user_data;
    }


    public function friend_request_status($user_pk, $other_user_pk)
    {

        $query = "select accept_status 
from friend_requests WHERE fr_requested_user_fk = '$user_pk' AND fr_request_to_user_fk = '$other_user_pk'";

        $user_data = $this->db->query($query)->first_row('array');

        if ($user_data['accept_status'] != null)
            return $user_data['accept_status'];
        else
            return '401';
    }

    public function accept_request($user_pk, $fr_pk, $accept_status)
    {

        $data['fr_pk'] = $fr_pk;
        $data['fr_request_to_user_fk'] = $user_pk;
        $data['accept_status'] = $accept_status;


        $this->db->where('fr_pk', $fr_pk);
        $flag = $this->db->update('friend_requests', $data);

        return $flag;

    }

    public function friends_list($user_pk)
    {

        $query = "select user_pk,username,profile_pic from friend_requests 
JOIN table_user ON table_user.user_pk = friend_requests.fr_request_to_user_fk 
WHERE fr_requested_user_fk = '$user_pk' AND accept_status = 1
UNION ALL
select user_pk,username,profile_pic from friend_requests 
JOIN table_user ON table_user.user_pk = friend_requests.fr_requested_user_fk 
WHERE fr_request_to_user_fk = '$user_pk' AND accept_status = 1";

        $user_data = $this->db->query($query)->result_array();

        return $user_data;

    }

    public function add_chatroom($ch_sender_fk, $ch_receiver_fk)
    {


        $this->db->where('ch_sender_fk', $ch_sender_fk);
        $this->db->where('ch_receiver_fk', $ch_receiver_fk);
        $q = $this->db->get('chat_history');


        if ($q->num_rows() > 0) {

            $data['ch_sender_fk'] = $ch_sender_fk;
            $data['ch_receiver_fk'] = $ch_receiver_fk;
            $data['ch_room_name'] = $ch_sender_fk . '-' . $ch_receiver_fk;

            $this->db->where('ch_sender_fk', $ch_sender_fk);
            $this->db->where('ch_receiver_fk', $ch_receiver_fk);
            $this->db->update('chat_history', $data);

            $flag['first'] = 'Updated';

        } else {
            $data['ch_sender_fk'] = $ch_sender_fk;
            $data['ch_receiver_fk'] = $ch_receiver_fk;
            $data['ch_room_name'] = $ch_sender_fk . '-' . $ch_receiver_fk;

            $this->db->insert('chat_history', $data);

            $flag['first'] = 'Inserted';
        }

        $data = array();

        $this->db->where('ch_sender_fk', $ch_receiver_fk);
        $this->db->where('ch_receiver_fk', $ch_sender_fk);
        $q2 = $this->db->get('chat_history');


        if ($q2->num_rows() > 0) {

            $data1['ch_sender_fk'] = $ch_receiver_fk;
            $data1['ch_receiver_fk'] = $ch_sender_fk;
            $data1['ch_room_name'] = $ch_receiver_fk . '-' . $ch_sender_fk;

            $this->db->where('ch_sender_fk', $ch_receiver_fk);
            $this->db->where('ch_receiver_fk', $ch_sender_fk);
            $this->db->update('chat_history', $data1);

            $flag['second'] = 'Updated';

        } else {

            $data1['ch_sender_fk'] = $ch_receiver_fk;
            $data1['ch_receiver_fk'] = $ch_sender_fk;
            $data1['ch_room_name'] = $ch_receiver_fk . '-' . $ch_sender_fk;

            $this->db->insert('chat_history', $data1);

            $flag['second'] = 'Inserted';
        }

        return $flag;

    }


    public function list_chat_rooms($user_pk)
    {

        $query = "select user_pk,username,profile_pic,last_message,ch_datetime from chat_history 
JOIN table_user ON user_pk = ch_receiver_fk 
WHERE ch_sender_fk = '$user_pk'";

        $user_data = $this->db->query($query)->result_array();

        return $user_data;

    }

    public function add_message($ch_sender_fk, $ch_receiver_fk, $message)
    {

        $this->db->where('ch_sender_fk', $ch_sender_fk);
        $this->db->where('ch_receiver_fk', $ch_receiver_fk);
        $q = $this->db->get('chat_history');

        if ($q->num_rows() > 0) {

            $data['ch_sender_fk'] = $ch_sender_fk;
            $data['ch_receiver_fk'] = $ch_receiver_fk;
            $data['ch_room_name'] = $ch_sender_fk . '-' . $ch_receiver_fk;
            $data['last_message'] = $message;

            $this->db->where('ch_sender_fk', $ch_sender_fk);
            $this->db->where('ch_receiver_fk', $ch_receiver_fk);
            $this->db->update('chat_history', $data);

            $flag['first'] = 'Updated';

        }


        $this->db->where('ch_sender_fk', $ch_receiver_fk);
        $this->db->where('ch_receiver_fk', $ch_sender_fk);
        $q2 = $this->db->get('chat_history');


        if ($q2->num_rows() > 0) {

            $data1['ch_sender_fk'] = $ch_receiver_fk;
            $data1['ch_receiver_fk'] = $ch_sender_fk;
            $data1['ch_room_name'] = $ch_receiver_fk . '-' . $ch_sender_fk;
            $data1['last_message'] = $message;

            $this->db->where('ch_sender_fk', $ch_receiver_fk);
            $this->db->where('ch_receiver_fk', $ch_sender_fk);
            $this->db->update('chat_history', $data1);

            $flag['second'] = 'Updated';

        }

        return $flag;


    }

}