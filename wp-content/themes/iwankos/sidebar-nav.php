<?php
global $theme_sidebars;
$i = 0;
foreach ($theme_sidebars as $sidebar){
    if ($sidebar['group'] !== 'nav')
        continue;
    $i++;
    theme_print_sidebar($sidebar['id'], "<div class=\"art-hmenu-extra$i\">", '</div>');
}
