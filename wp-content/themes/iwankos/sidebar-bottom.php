<?php
global $theme_sidebars;
$places = array();
foreach ($theme_sidebars as $sidebar){
    if ($sidebar['group'] !== 'bottom')
        continue;
    $widgets = theme_get_dynamic_sidebar_data($sidebar['id']);
    if (!is_array($widgets) || count($widgets) < 1)
        continue;
    $places[$sidebar['id']] = $widgets;
}
$place_count = count($places);
$needLayout = ($place_count > 1);
if ($place_count > 0) {
    if ($needLayout) {
?>
<div class="art-content-layout">
    <div class="art-content-layout-row">
        <?php 
    }
            foreach ($places as $widgets) { 
                if ($needLayout) {
            ?>
            <div class="art-layout-cell art-layout-cell-size<?php echo $place_count; ?>">
               <?php 
               }
               foreach ($widgets as $widget) {
                    theme_print_widget($widget);
               } 
               if ($needLayout) {
               ?>
            </div>
        <?php 
            }
        } 
    if ($needLayout) {    
        ?>
    </div>
</div>
<?php
    }
}
