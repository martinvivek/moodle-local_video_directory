<?php

require_once('init.php');

$tags = optional_param('tag',0, PARAM_RAW);
if ($tags != '') {
	$SESSION->video_tags = explode(',',$tags);
} else {
	$SESSION->video_tags = 'Gurnisht';
}

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('list','local_video_directory'));
$PAGE->set_title(get_string('list','local_video_directory'));
$PAGE->set_url('/local/video_directory/list.php');
$PAGE->navbar->add(get_string('pluginname','local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('list','local_video_directory'));

$PAGE->requires->js('/local/video_directory/jquery/DataTables/jquery.dataTables.min.js');
$PAGE->requires->css("/local/video_directory/jquery/DataTables/jquery.dataTables.css");
$PAGE->requires->js(new moodle_url('/local/video_directory/js/list.js'));
$PAGE->requires->css('/local/video_directory/style.css');


//Table headers
$PAGE->requires->strings_for_js(array('id','filename','private','streaming_url', 'owner','orig_filename','convert_status'),
	'local_video_directory');

echo $OUTPUT->header();

//Menu
include('menu.php');

echo '<div id="tools"><button id="datatable_ajax_reload">' . get_string('reload','local_video_directory') . '</button>';

echo '<button id="datatable_ajax_clear_tags">' . get_string('show_all','local_video_directory') . '</button>';

echo '<existing_tags>' . get_string('existing_tags','local_video_directory').':';
//find all movies tags
$alltags=$DB->get_records_sql('select distinct name from {tag_instance} ti LEFT JOIN {tag} t on ti.tagid=t.id where itemtype="local_video_directory" order by name');

echo '<span class="tag_list hideoverlimit videos">
    <ul class="inline-list">';

foreach ($alltags as $key => $value) {
	echo '<li>
                <a href="' . $CFG->wwwroot . '/local/video_directory/tag.php?action=add&tag=' . $key . '" class="label label-info ">+ ' . $key . '</a>
          </li>	'; 
}
echo '</ul></span></existing_tags>';

if (is_array($SESSION->video_tags)) {
	echo '<selected_tags>' . get_string('selected_tags','local_video_directory').':';
	echo '<span class="tag_list hideoverlimit videos">
    <ul class="inline-list">';

	foreach ($SESSION->video_tags as $key => $value) {
		echo '<li>
                <a href="' . $CFG->wwwroot . '/local/video_directory/tag.php?action=remove&tag=' . $value . '" class="label label-info ">X ' . $value . '</a>
          </li>	'; 
	}
	echo '</ul></span></selected_tags>';
}

?>

</div>
<table id="video_table" class="display" cellspacing="0">
	<thead>
		<tr>
<?php
$list_strings = array('actions', 'thumb', 'id', 'owner', 'orig_filename', 'filename', 'length', 'convert_status', 'private', 'streaming_url', 'tags');

foreach($list_strings as $list_string) {
?>
			<th><?= get_string($list_string, 'local_video_directory') ?></th>
<?php
}
?>
		</tr>
<?php

?>
	</thead>
	<tbody></tbody>
</table>

<div id='video_player' style='display:none'>
	<a href=# class='close' onclick='close_player();'>
		X <?php echo get_string('close','local_video_directory'); ?>
	</a><br>

<video id="my-video" controls preload="auto"  width="800px">
</video>

</div>

<?php

echo $OUTPUT->footer();

?>