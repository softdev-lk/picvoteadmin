<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Table_block_model extends CI_Model
{


    public function block_user($blocked_by_user_fk, $blocked_user_fk)
    {

        $data['blocked_by_user_fk'] = $blocked_by_user_fk;
        $data['blocked_user_fk'] = $blocked_user_fk;

        $created_time = date('Y-m-d H:i:s');

        $data['block_created_time'] = $created_time;

        $this->db->where('blocked_by_user_fk', $blocked_by_user_fk);
        $this->db->where('blocked_user_fk', $blocked_user_fk);
        $q = $this->db->get('table_block_user');

        if ($q->num_rows() > 0) {

            $this->db->where('blocked_by_user_fk', $blocked_by_user_fk);
            $this->db->where('blocked_user_fk', $blocked_user_fk);
            $this->db->update('table_block_user', $data);

            $response['success'] = '1';
            $response['message'] = 'updated';

        } else {
            $this->db->insert('table_block_user', $data);

            $response['success'] = '1';
            $response['message'] = 'inserted';
        }


        return $response;

    }

    public function get_tags()
    {

        $tags = $this->db->limit(100)->group_by('tag_name')->get('table_poll_tags')->result();

        return $tags;
    }

    public function get_blocked_users($user_pk)
    {

        $query = "select user_pk,username,profile_pic from table_block_user JOIN `table_user` ON user_pk = blocked_user_fk WHERE blocked_by_user_fk = '$user_pk' ORDER BY block_created_time DESC";


        $all_users = $this->db->query($query)->result_array();

        return $all_users;

    }


    public function unblock($user_pk,$other_user_fk)
    {

        $this->db->where('blocked_by_user_fk', $user_pk);
        $this->db->where('blocked_user_fk', $other_user_fk);
        $q = $this->db->get('table_block_user');

        if ($q->num_rows() > 0) {

            $this->db->where('blocked_by_user_fk', $user_pk);
            $this->db->where('blocked_user_fk', $other_user_fk);
            $this->db->delete('table_block_user');

            $response = 'User Unblocked';

        } else {

            $response = 'error, Something went wrong.';
        }

        return $response;

    }


}