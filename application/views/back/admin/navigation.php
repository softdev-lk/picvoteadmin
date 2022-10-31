<nav id="mainnav-container">
    <div id="mainnav">
        <!--Menu-->
        <div id="mainnav-menu-wrap">
            <div class="nano">
                <div class="nano-content" style="overflow-x:auto;">
                    <ul id="mainnav-menu" class="list-group">
                        <!--Category name-->
                        <li class="list-header"></li>
                        <!--Menu list item-->
                        <li <?php if ($page_name == "dashboard") { ?> class="active-link" <?php } ?>
                                style="border-top:1px solid rgba(69, 74, 84, 0.7);">
                            <a href="<?php echo base_url(); ?>index.php/admin/">
                                <i class="fa fa-tachometer"></i>
                                <span class="menu-title">
									Dashboard
                                </span>
                            </a>
                        </li>

                        <li <?php if ($page_name == "users") { ?> class="active-link" <?php } ?>
                                style="border-top:1px solid rgba(69, 74, 84, 0.7);">
                            <a href="<?php echo base_url(); ?>index.php/admin/users">
                                <i class="fa fa-tachometer"></i>
                                <span class="menu-title">
									Users
                                </span>
                            </a>
                        </li>

                        <li <?php if ($page_name == "polls") { ?> class="active-link" <?php } ?>
                                style="border-top:1px solid rgba(69, 74, 84, 0.7);">
                            <a href="<?php echo base_url(); ?>index.php/admin/polls">
                                <i class="fa fa-tachometer"></i>
                                <span class="menu-title">
									Polls
                                </span>
                            </a>
                        </li>

                        <li <?php if ($page_name == "for_review") { ?> class="active-link" <?php } ?>
                                style="border-top:1px solid rgba(69, 74, 84, 0.7);">
                            <a href="<?php echo base_url(); ?>index.php/admin/for_review">
                                <i class="fa fa-tachometer"></i>
                                <span class="menu-title">
									Polls For Review
                                </span>
                            </a>
                        </li>

                        <li <?php if ($page_name == "reported_polls") { ?> class="active-link" <?php } ?>
                                style="border-top:1px solid rgba(69, 74, 84, 0.7);">
                            <a href="<?php echo base_url(); ?>index.php/admin/reported_polls">
                                <i class="fa fa-tachometer"></i>
                                <span class="menu-title">
									Reported Polls
                                </span>
                            </a>
                        </li>


                </div>
            </div>
        </div>
    </div>
</nav>
<style>
    .activate_bar {
        border-left: 3px solid #1ACFFC;
        transition: all .6s ease-in-out;
    }

    .activate_bar:hover {
        border-bottom: 3px solid #1ACFFC;
        transition: all .6s ease-in-out;
        background: #1ACFFC !important;
        color: #000 !important;
    }
</style>