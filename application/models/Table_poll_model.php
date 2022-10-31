<?php

require_once APPPATH. '/lib/Autoloader.php';

use Snipe\BanBuilder\CensorWords;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Table_poll_model extends CI_Model
{


    public function get_all_polls($user_pk,$limit_from, $limit_count)
    {

        $query = "SELECT poll_pk, 
       title, 
       choice1, 
       choice2, 
       user_fk, 
       username, 
       profile_pic,
       feed_type,
       (SELECT COUNT(block_pk) from table_block_user WHERE blocked_by_user_fk = $user_pk AND blocked_user_fk = user_fk) AS block_count,
       created_time FROM table_poll JOIN table_user ON table_user.user_pk = table_poll.user_fk WHERE is_approved = '1' HAVING (block_count != 1) ORDER BY poll_pk DESC limit $limit_from,$limit_count";


        $this->db->order_by('poll_pk', 'desc');

        $all_polls = $this->db->query($query)->result_array();

        return $all_polls;

    }


    public function get_trending_polls($limit_from, $limit_count)
    {

        $query = "SELECT poll_pk, 
       title, 
       choice1, 
       choice2, 
       user_fk, 
       username, 
       profile_pic,
       feed_type,
       created_time,
       (SELECT Count(*) 
        FROM   `table_polling` 
        WHERE  poll_fk = poll_pk) AS count 
       FROM table_poll JOIN table_user ON table_user.user_pk = table_poll.user_fk WHERE is_approved = '1' ORDER BY count DESC limit $limit_from,$limit_count";

        $this->db->order_by('poll_pk', 'desc');

        $all_polls = $this->db->query($query)->result_array();

        return $all_polls;

    }


    public function get_following_poll($user_pk, $limit_from, $limit_count)
    {

        $query = "SELECT poll_pk, 
       title, 
       choice1, 
       choice2, 
       table_poll.user_fk, 
       username, 
       profile_pic, 
       feed_type, 
       table_poll.created_time 
FROM   table_poll 
       JOIN table_user 
         ON table_user.user_pk = table_poll.user_fk 
       JOIN table_follow 
         ON table_follow.other_user_fk = table_user.user_pk 
WHERE  table_follow.user_fk = '$user_pk' AND table_follow.follow = '1' AND is_approved = '1'
ORDER  BY poll_pk ASC limit $limit_from,$limit_count";


        $this->db->order_by('poll_pk', 'desc');

        $all_polls = $this->db->query($query)->result_array();

        return $all_polls;

    }


    public function get_searched_poll($tag, $limit_from, $limit_count)
    {

        $query = "SELECT poll_pk, 
       title, 
       choice1, 
       choice2, 
       user_fk, 
       username, 
       profile_pic,
       feed_type,
       table_poll.created_time FROM table_poll 
       JOIN table_user ON table_user.user_pk = table_poll.user_fk
       JOIN table_poll_tags ON table_poll_tags.poll_fk = table_poll.poll_pk
       WHERE table_poll_tags.tag_name = '$tag' AND is_approved = '1'
       ORDER BY poll_pk DESC limit $limit_from,$limit_count";


        $this->db->order_by('poll_pk', 'desc');

        $all_polls = $this->db->query($query)->result_array();

        return $all_polls;

    }

    public function get_top_poll($limit_from, $limit_count)
    {

        $query = "SELECT poll_pk, 
       title, 
       choice1, 
       choice2, 
       user_fk, 
       username, 
       profile_pic,
       feed_type,
       created_time FROM table_poll JOIN table_user ON table_user.user_pk = table_poll.user_fk WHERE is_approved = '1' ORDER BY poll_pk DESC limit $limit_from,$limit_count";

        $this->db->order_by('poll_pk', 'desc');

        $all_polls = $this->db->query($query)->result_array();

        return $all_polls;

    }


    public function get_top_picvoters_poll($limit_from, $limit_count)
    {

        $query = "SELECT poll_pk, 
       title, 
       choice1, 
       choice2, 
       user_fk, 
       username, 
       profile_pic,
       feed_type,
       created_time FROM table_poll JOIN table_user ON table_user.user_pk = table_poll.user_fk WHERE is_approved = '1' GROUP BY user_pk ORDER BY poll_pk DESC limit $limit_from,$limit_count";

        $this->db->order_by('poll_pk', 'desc');

        $all_data = $this->db->query($query)->result_array();

        return $all_data;

    }


    public function get_single_poll($poll_pk)
    {

        $query = "SELECT poll_pk, 
       title, 
       choice1, 
       choice2, 
       user_fk, 
       username, 
       profile_pic,
       feed_type,
       created_time FROM table_poll JOIN table_user ON table_user.user_pk = table_poll.user_fk WHERE poll_pk = '$poll_pk'";

        $this->db->order_by('poll_pk', 'desc');

        $single_poll = $this->db->query($query)->first_row('array');

        return $single_poll;

    }

    public function get_user_poll($other_user_fk, $limit_from, $limit_count)
    {

        $query = "SELECT poll_pk, 
       title, 
       choice1, 
       choice2, 
       user_fk, 
       username, 
       profile_pic,
       feed_type,
       created_time FROM table_poll JOIN table_user ON table_user.user_pk = table_poll.user_fk WHERE user_fk = '$other_user_fk' AND is_approved = '1' ORDER BY poll_pk DESC limit $limit_from,$limit_count";

        $this->db->order_by('poll_pk', 'desc');

        $all_polls = $this->db->query($query)->result_array();

        return $all_polls;


    }


    public function user_poll_count($other_user_fk)
    {

        $query = "SELECT * FROM table_poll WHERE user_fk = '$other_user_fk' AND is_approved = '1'";
        $poll_count = $this->db->query($query)->num_rows();

        return $poll_count;

    }

    public function get_single_poll_data($poll_fk)
    {

        return $this->db->get_where('table_poll', array('poll_pk' => $poll_fk))->first_row('array');
    }

    public function add_poll()
    {

        $is_contain_porn1 = 0;
        $is_contain_porn2 = 0;
        $image1_score = 0;
        $image2_score = 0;

        $censor = new CensorWords;

//        $this->load->library("ImageFilter");
//        $filter = new ImageFilter;

        $input_title = $censor->censorString($this->input->post('title'));

        $data['title'] = $input_title['clean'];
        $data['user_fk'] = $this->input->post('user_fk');
        $data['feed_type'] = $this->input->post('feed_type');
        $tags = $this->input->post('tags');

        $created_time = date('Y-m-d H:i:s');

        $data['created_time'] = $created_time;

        $this->db->insert('table_poll', $data);
        $id = $this->db->insert_id();


        if ($_FILES['img1']['tmp_name'] != null) {


            $choice1 = $this->crud_model->file_up_new("img1", "choices", "choice1", $id, '', '', '.png');
            $upload_image1 = 'uploads/choices_image/' . $choice1;
            $resized_image = new ImageResize($upload_image1);
            $resized_image->quality_jpg = 90;
            $resized_image->resizeToBestFit('600', '600');
            $resized_image->save($upload_image1, 2, 90);

//            $score = $filter->GetScore($upload_image1);

            $quant1 = new Image_FleshSkinQuantifier($upload_image1);

            $image1_score =  $quant1->isPorn();;

        }

        if ($_FILES['img2']['tmp_name'] != null) {

            $choice2 = $this->crud_model->file_up_new("img2", "choices", "choice2", $id, '', '', '.png');
            $upload_image2 = 'uploads/choices_image/' . $choice2;

            $resized_image = new ImageResize($upload_image2);
            $resized_image->quality_jpg = 90;
            $resized_image->resizeToBestFit('600', '600');
            $resized_image->save($upload_image2, 2, 90);

//            $score = $filter->GetScore($upload_image2);

            $quant2 = new Image_FleshSkinQuantifier($upload_image2);

            $image2_score =  $quant2->isPorn();
        }


        if($image1_score || $image2_score ){

            $data['is_approved'] = '0';

            $is_contain_porn1 = 1;
            $is_contain_porn2 = 1;
        }

        else{

            $data['is_approved'] = '1';
        }


        $data['choice1'] = $choice1;
        $data['choice2'] = $choice2;

        $data['image1_score'] = $image1_score;
        $data['image2_score'] = $image2_score;


        $this->db->where('poll_pk', $id);
        $this->db->update('table_poll', $data);

        $occassion = urldecode(str_replace("\\", "", $tags));
        $json_object_occassions = json_decode($occassion, TRUE);

        foreach ($json_object_occassions as $param) {

            $input_tag = $censor->censorString($param['tag']);

            $data1['poll_fk'] = $id;
            $data1['tag_name'] = $input_tag['clean'];
            $this->db->insert('table_poll_tags', $data1);

        }

        if($is_contain_porn1==0 && $is_contain_porn2 ==0){

            $response = array();
            $response['status'] = 'passed';
            $response['image1_score'] = $image1_score;
            $response['image2_score'] = $image2_score;

            return $response;

        }
        else{

            $response = array();
            $response['status'] = 'failed';
            $response['image1_score'] = $image1_score;
            $response['image2_score'] = $image2_score;

            return $response;

        }

        recache();

    }


    public function test_porn2()
    {

        $upload_image1 = 'uploads/choices_image/choice1_220.png';

        $quant = new Image_FleshSkinQuantifier($upload_image1);

        if($quant->isPorn())
            echo 'This image contains a lot of skin colors, thus might contain some adult content';
        else
            echo 'This image does not contain many skin colors, thus is not likely to contain adult content';

    }

}