<link rel="stylesheet" href="<?php echo base_url(); ?>template/back//amcharts/style.css" type="text/css">
<script src="<?php echo base_url(); ?>template/back/amcharts/amcharts.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>template/back/amcharts/serial.js" type="text/javascript"></script>
<script src="http://www.google.com/jsapi"></script>
<script type="text/javascript"
        src="http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerclusterer/1.0/src/markerclusterer.js"></script>
<script src="<?php echo base_url(); ?>template/back/plugins/gauge-js/gauge.min.js"></script>

<div id="content-container">
    <div id="page-title">
        <h1 class="page-header text-overflow"><?php echo translate('dashboard'); ?></h1>
    </div>
    <div id="page-content">


        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="col-md-4 col-lg-4">
                    <div class="panel panel-bordered panel-dark" style="height:205px;">
                        <div class="panel-heading">
                            <h3 class="panel-title">Total Users</h3>
                        </div>
                        <div class="panel-body">
                            <div class="text-center">
                                <h1>
                                    <?php echo $this->db->get('table_user')->num_rows(); ?>
                                </h1>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-bordered panel-dark" style="height:205px;">
                        <div class="panel-heading">
                            <h3 class="panel-title">Total Polls Approved</h3>
                        </div>
                        <div class="panel-body">
                            <div class="text-center">
                                <h1>
                                    <?php echo $this->db->where('is_approved', '1')->get('table_poll')->num_rows(); ?>
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-lg-4">
                    <div class="panel panel-bordered panel-dark" style="height:205px;">
                        <div class="panel-heading">
                            <h3 class="panel-title">Polls in Review</h3>
                        </div>
                        <div class="panel-body">
                            <div class="text-center">
                                <h1>
                                    <?php echo $this->db->where('is_approved', '0')->get('table_poll')->num_rows(); ?>
                                </h1>
                            </div>
                        </div>
                    </div>
                    <div class="panel panel-bordered panel-dark" style="height:205px;">
                        <div class="panel-heading">
                            <h3 class="panel-title">Reported Polls</h3>
                        </div>
                        <div class="panel-body">
                            <div class="text-center">
                                <h1>
                                    <?php

                                    $query = "SELECT * FROM table_poll JOIN table_report_poll ON table_report_poll.poll_fk = table_poll.poll_pk";

                                    echo $this->db->query($query)->num_rows(); ?>
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>

        <div class="row">

            <div class="col-md-4 col-lg-4">
                <div class="panel panel-bordered panel-grad">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo translate('Month Statistics - User'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <div class="text-center">
                            <div class="col-md-12 col-lg-12">
                                <div class="panel-body">
                                    <div id="chartdiv5" style="width: 100%; height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 col-lg-4">
                <div class="panel panel-bordered panel-grad">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo translate('Month Statistics - Polls'); ?></h3>
                    </div>
                    <div class="panel-body">
                        <div class="text-center">
                            <div class="col-md-12 col-lg-12">
                                <div class="panel-body">
                                    <div id="chartdiv6" style="width: 100%; height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>


    </div>
</div>

<script>

    <?php

    $query = "SELECT
    MONTHNAME(user_created_time) as monthname, COUNT(user_pk) as user_count
    FROM
    table_user
    GROUP
    BY
    YEAR(user_created_time), MONTH(user_created_time) ORDER BY MONTH(user_created_time) DESC LIMIT 4";

    $month_user = $this->db->query($query)->result_array();
    ?>



    <?php

    $poll_query = "SELECT
    MONTHNAME(created_time) as monthname, COUNT(poll_pk) as poll_count
    FROM
    table_poll
    GROUP
    BY
    YEAR(created_time), MONTH(created_time) ORDER BY MONTH(created_time) DESC LIMIT 4";

    $month_poll = $this->db->query($poll_query)->result_array();
    ?>



    var pl_txt = 'Month / New Users';

    var chartData5 = [

        <?php

        foreach($month_user as $single_month) {

        ?>
        {
            "country": "<?php echo $single_month['monthname']; ?>",
            "visits": "<?php echo $single_month['user_count']; ?>",
            "color": "#458fd2"
        },

        <?php

        }

        ?>
    ];

    var pl_txt2 = 'Month / New Polls';

    var chartData6 = [

        <?php

        foreach($month_poll as $single_poll) {

        ?>
        {
            "country": "<?php echo $single_poll['monthname']; ?>",
            "visits": "<?php echo $single_poll['poll_count']; ?>",
            "color": "#458fd2"
        },

        <?php

        }

        ?>
    ];

</script>


<script src="<?php echo base_url(); ?>template/back/js/custom/dashboard.js"></script>
<style>
    #map-container {
        padding: 6px;
        border-width: 1px;
        border-style: solid;
        border-color: #ccc #ccc #999 #ccc;
        -webkit-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px;
        -moz-box-shadow: rgba(64, 64, 64, 0.5) 0 2px 5px;
        box-shadow: rgba(64, 64, 64, 0.1) 0 2px 5px;
        width: 100%;
    }

    #map {
        width: 100%;
        height: 400px;
    }

    #map1 {
        width: 100%;
        height: 400px;
    }

    #actions {
        list-style: none;
        padding: 0;
    }

    #inline-actions {
        padding-top: 10px;
    }

    .item {
        margin-left: 20px;
    }
</style>