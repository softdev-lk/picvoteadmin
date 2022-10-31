<div class="panel-body" id="demo_s">
    <table id="demo-table" class="table table-striped" data-pagination="true" data-show-refresh="true"
           data-ignorecol="0,2" data-show-toggle="true" data-show-columns="false" data-search="true">

        <thead>
        <tr>
            <th><?php echo translate('no'); ?></th>
            <th><?php echo translate('name'); ?></th>
            <th><?php echo translate('choice1'); ?></th>
<!--            <th>--><?php //echo translate('image1 score').'<br>(Skin color porn score)'; ?><!--</th>-->
            <th><?php echo translate('choice2'); ?></th>
<!--            <th>--><?php //echo translate('image2 score').'<br>(Skin color porn score)'; ?><!--</th>-->
            <th class="text-right"><?php echo translate('options'); ?></th>
        </tr>
        </thead>

        <tbody>
        <?php
        $i = 0;
        foreach ($all_polls as $row) {
            $i++;
            ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo $row['title']; ?></td>

                <td>
                    <img class="img-md2"
                         src="<?php echo(base_url() . "uploads/choices_image/" . $row['choice1']); ?>"/>
                </td>

<!--                <td>-->
<!--                    <p class="text-left">--><?php //echo $row['image1_score']; ?><!--</p>-->
<!--                </td>-->

                <td>
                    <img class="img-md2"
                         src="<?php echo(base_url() . "uploads/choices_image/" . $row['choice2']); ?>"/>
                </td>

<!--                <td>-->
<!--                    <p class="text-left">--><?php //echo $row['image2_score']; ?><!--</p>-->
<!--                </td>-->

                <td class="text-right">

                    <a onclick="approve_confirm('<?php echo $row['poll_pk']; ?>')"
                       class="btn btn-success btn-xs btn-labeled fa fa-check" data-toggle="tooltip"
                       data-original-title="Delete" data-container="body">
                        <?php echo translate('Approve'); ?>
                    </a>

                    <a onclick="delete_confirm('<?php echo $row['poll_pk']; ?>','<?php echo translate('really_want_to_delete_this?'); ?>')"
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


