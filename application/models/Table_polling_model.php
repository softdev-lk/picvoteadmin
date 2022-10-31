<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Table_polling_model extends CI_Model
{


    public function get_answered_count($poll_pk)
    {

        $query = "SELECT * FROM table_polling WHERE poll_fk = '$poll_pk'";
        $poll_count = $this->db->query($query)->num_rows();

        return $poll_count;

    }

    public function get_choice_count($poll_pk, $choice)
    {

        $query = "SELECT * FROM table_polling WHERE poll_fk = '$poll_pk' AND choice = '$choice'";
        $choice_count = $this->db->query($query)->num_rows();

        return $choice_count;
    }


    public function get_choice_gender_count($poll_pk, $choice, $gender)
    {

        $query = "SELECT * FROM table_polling 
JOIN table_user ON user_pk = user_fk
WHERE poll_fk = '$poll_pk' AND choice = '$choice' AND gender = '$gender'";
        $choice_gender = $this->db->query($query)->num_rows();

        return $choice_gender;
    }

    public function is_user_answered($poll_pk, $user_pk)
    {

        $query = "SELECT * FROM table_polling WHERE poll_fk = '$poll_pk' AND user_fk = '$user_pk'";
        $is_answered = $this->db->query($query)->row();

        return $is_answered;
    }

    public function user_polled_count($other_user_fk)
    {

        $query = "SELECT * FROM table_polling WHERE user_fk = '$other_user_fk'";
        $poll_count = $this->db->query($query)->num_rows();

        return $poll_count;


    }

    public function vote_poll($user_fk,$poll_fk,$choice){

        $data['poll_fk'] = $poll_fk;
        $data['user_fk'] = $user_fk;
        $data['choice'] = $choice;

        $this->db->where('poll_fk', $poll_fk);
        $this->db->where('user_fk', $user_fk);
        $q = $this->db->get('table_polling');

        if ($q->num_rows() > 0) {
            $this->db->where('poll_fk', $poll_fk);
            $this->db->where('user_fk', $user_fk);
            $this->db->update('table_polling', $data);

            return 'Updated';

        } else {
            $this->db->set('poll_fk', $poll_fk);
            $this->db->set('user_fk', $user_fk);
            $this->db->insert('table_polling', $data);

            return 'Inserted';
        }
    }

}