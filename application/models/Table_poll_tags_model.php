<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Table_poll_tags_model extends CI_Model
{


    public function get_tags_polls()
    {

        $query = "SELECT poll_tag_pk, 
       tag_name AS singletag, 
       (SELECT Count(*) 
        FROM   `table_poll_tags` 
        WHERE  tag_name = singletag) AS count 
FROM   table_poll_tags 
GROUP  BY tag_name 
ORDER  BY count DESC ";

        $tag_data = $this->db->query($query)->result_array();

        return $tag_data;

    }

    public function get_tags(){

        $tags = $this->db->limit(100)->group_by('tag_name')->get('table_poll_tags')->result();

        return $tags;
    }


}