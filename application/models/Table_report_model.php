<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Table_report_model extends CI_Model
{


    public function add_report_poll($user_fk, $poll_fk)
    {

        $data['poll_fk'] = $poll_fk;
        $data['user_fk'] = $user_fk;

        $created_time = date('Y-m-d H:i:s');

        $data['report_created_time'] = $created_time;


        $this->db->where('poll_fk', $poll_fk);
        $this->db->where('user_fk', $user_fk);
        $q = $this->db->get('table_report_poll');

        if ($q->num_rows() > 0) {
            $this->db->where('poll_fk', $poll_fk);
            $this->db->where('user_fk', $user_fk);
            $this->db->update('table_report_poll', $data);

            $response['success'] = '1';
            $response['message'] = 'updated';

        } else {
            $this->db->insert('table_report_poll', $data);

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


}