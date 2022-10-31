<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Table_follow_model extends CI_Model
{


    public function is_user_followed($user_pk, $other_user_fk)
    {

        $query = "SELECT * FROM table_follow WHERE user_fk = '$user_pk' AND other_user_fk = '$other_user_fk'";
        $is_follow = $this->db->query($query)->row();

        return $is_follow;
    }

    public function follow($user_fk, $other_user_fk, $follow)
    {

        $data['user_fk'] = $user_fk;
        $data['other_user_fk'] = $other_user_fk;
        $data['follow'] = $follow;

        $this->db->where('user_fk', $user_fk);
        $this->db->where('other_user_fk', $other_user_fk);
        $q = $this->db->get('table_follow');

        if ($q->num_rows() > 0) {
            $this->db->where('user_fk', $user_fk);
            $this->db->where('other_user_fk', $other_user_fk);
            $this->db->update('table_follow', $data);


            $response['message'] = 'Updated';

        } else {

            $this->db->insert('table_follow', $data);

            $response['message'] = 'Inserted';
        }

        if ($follow == 1) {

            if ($user_fk != $other_user_fk) {

                $other_user_data = $this->db->get_where('table_user', array('user_pk' => $other_user_fk))->first_row('array');

                $user_data = $this->db->get_where('table_user', array('user_pk' => $user_fk))->first_row('array');

                $datanotif['message'] = $user_data['username'] . ' Followed you';
                $datanotif['to_user_fk'] = $other_user_fk;
                $datanotif['from_user_fk'] = $user_fk;
                $datanotif['created_time'] = $created_time = date('Y-m-d H:i:s');

                $this->db->insert('table_notification', $datanotif);


                if (!empty($other_user_data['firebase_id'])) {

                    $this->push_notification($other_user_data['firebase_id'], $datanotif['message']);

                }

            }

        }


        $response['success'] = '1';
        $response['message'] = 'followed';


        return $response;


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

}