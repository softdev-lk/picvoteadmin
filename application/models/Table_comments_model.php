<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Table_comments_model extends CI_Model
{


    public function get_comments($poll_fk)
    {

        $query = "SELECT comment_pk, 
       poll_fk, 
       user_fk,
        comment,
       username, 
       profile_pic 
FROM   table_comments 
       JOIN table_user 
         ON table_user.user_pk = table_comments.user_fk WHERE poll_fk = '$poll_fk'";

        $comments = $this->db->query($query)->result_array();

        return $comments;
    }


    public function get_comments_count($poll_pk)
    {

        $query = "SELECT comment_pk 
FROM   table_comments 
       WHERE poll_fk = '$poll_pk'";

        $comments = $this->db->query($query)->result_array();

        return count($comments);
    }




    public function add_comment($user_fk, $poll_fk, $comment)
    {

        $data['user_fk'] = $user_fk;
        $data['poll_fk'] = $poll_fk;
        $data['comment'] = $comment;


        $this->db->set('poll_fk', $poll_fk);
        $this->db->set('user_fk', $user_fk);
        $this->db->set('comment', $comment);
        $this->db->insert('table_comments', $data);

        return 'Inserted';

    }

}