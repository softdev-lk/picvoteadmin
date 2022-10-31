<div class="panel-body" id="demo_s">
    <table id="demo-table" class="table table-striped" data-pagination="true" data-show-refresh="true"
           data-ignorecol="0,2" data-show-toggle="true" data-show-columns="false" data-search="true">

        <thead>
        <tr>
            <th>No</th>
            <th>Username</th>
            <th>Profile Pic</th>
            <th>Email</th>
            <th>gender</th>
            <th class="text-right"><?php echo translate('options'); ?></th>
        </tr>
        </thead>

        <tbody>
        <?php
        $i = 0;
        foreach ($all_users as $row) {
            $i++;
            ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo $row['username']; ?></td>

                <td>
                    <img class="img-md2"
                         src="<?php echo(base_url() . "uploads/users_image/".$row['profile_pic']); ?>"/>
                </td>

                <td><?php echo $row['email']; ?></td>

                <td><?php echo $row['gender']; ?></td>


                <td class="text-right">

                    <a onclick="delete_confirm('<?php echo $row['user_pk']; ?>','<?php echo translate('really_want_to_delete_this?'); ?>')"
                       class="btn btn-danger btn-xs btn-labeled fa fa-trash" data-toggle="tooltip"
                       data-original-title="Delete" data-container="body">
                        <?php echo translate('delete'); ?>
                    </a>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>

