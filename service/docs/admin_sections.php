<table class="docs-groups-tbl">
<tbody>
<?php if (is_array($sections) && count($sections)) { ?>

<?php 
$npp = 0;
foreach ($sections as $section) { $npp++; $num = $npp;?>
                <tr id="admin_section_line_<?= $section['id']; ?>">
<? if(isset($id) && $id == $section['id']) {
    include('admin_section_line_delete.php');
}else{
    include('admin_section_line.php');
}
?>
            </tr>
<?php } ?>


<?php } ?>
</tbody>
</table>