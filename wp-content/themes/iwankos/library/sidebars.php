<?php
global $theme_sidebars;
$theme_sidebars = array();

function theme_add_sidebar($group, $id, $name, $description) {
    global $theme_sidebars;
    $theme_sidebars[$id] = array('group' => $group, 'id' => $id, 'name' => $name, 'description' => $description);
}

theme_add_sidebar('default', 'primary-widget-area', __('Primary Widget Area', THEME_NS), __("This is the default sidebar, visible on 2 or 3 column layouts. If no widgets are active, the default theme widgets will be displayed instead.", THEME_NS));

theme_add_sidebar('header', 'header-widget-area', __('Header Widget Area', THEME_NS), __("The header widget area. Use the unique widget ids to control the design and position of individual widgets with CSS code.", THEME_NS)); 

theme_add_sidebar('nav', 'first-nav-widget-area', __('First Navigation Widget Area', THEME_NS), __("This sidebar is displayed before the horizontal menu.", THEME_NS));
theme_add_sidebar('nav', 'second-nav-widget-area', __('Second Navigation Widget Area', THEME_NS), __("This sidebar is displayed after the horizontal menu.", THEME_NS));
theme_add_sidebar('top', 'first-top-widget-area', __('First Top Widget Area', THEME_NS), __("This sidebar is displayed above the main content.", THEME_NS));
theme_add_sidebar('top', 'second-top-widget-area', __('Second Top Widget Area', THEME_NS), __("This sidebar is displayed above the main content.", THEME_NS));
theme_add_sidebar('bottom', 'first-bottom-widget-area', __('First Bottom Widget Area', THEME_NS), __("This sidebar is displayed below the main content.", THEME_NS));
theme_add_sidebar('bottom', 'second-bottom-widget-area', __('Second Bottom Widget Area', THEME_NS), __("This sidebar is displayed below the main content.", THEME_NS));
if (theme_get_option('theme_override_default_footer_content')) {
    theme_add_sidebar('footer', 'first-footer-widget-area', __('First Footer Widget Area', THEME_NS), __("The first footer widget area. You can add a text widget for custom footer text.", THEME_NS));
    theme_add_sidebar('footer', 'second-footer-widget-area', __('Second Footer Widget Area', THEME_NS), __("The second footer widget area.", THEME_NS));
    theme_add_sidebar('footer', 'third-footer-widget-area', __('Third Footer Widget Area', THEME_NS), __("The third footer widget area.", THEME_NS));
    theme_add_sidebar('footer', 'fourth-footer-widget-area', __('Fourth Footer Widget Area', THEME_NS), __("The fourth footer widget area.", THEME_NS));
} else {

}

global $theme_widget_args;

$theme_widget_args = array(
    'before_widget' => '<widget id="%1$s" name="%1$s" class="widget %2$s">',
    'before_title' => '<title>',
    'after_title' => '</title>',
    'after_widget' => '</widget>'
);

foreach ($theme_sidebars as $sidebar) {
    register_sidebar(array_merge($sidebar, $theme_widget_args));
}

function theme_is_displayed_widget($widget) {
    $id = $widget['name'];
    $show_on = theme_get_widget_meta_option($id, 'theme_widget_show_on');

    $page_ids = explode(',', theme_get_widget_meta_option($id, 'theme_widget_page_ids_list'));
    $page_ids = array_map('trim', $page_ids);
    $page_ids = array_filter($page_ids, 'is_numeric');
    $page_ids = array_map('intval', $page_ids);
    if ('all' != $show_on) {
        $selected = (theme_get_widget_meta_option($id, 'theme_widget_front_page') && is_front_page()) ||
                (theme_get_widget_meta_option($id, 'theme_widget_single_post') && is_single()) ||
                (theme_get_widget_meta_option($id, 'theme_widget_single_page') && is_page()) ||
                (theme_get_widget_meta_option($id, 'theme_widget_posts_page') && is_home()) ||
                (theme_get_widget_meta_option($id, 'theme_widget_page_ids') && !empty($page_ids) && is_page($page_ids));
        if ((!$selected && 'selected' == $show_on) ||
                ($selected && 'none_selected' == $show_on)) {
            return false;
        }
    }
    return true;
}

function theme_get_dynamic_sidebar_data($sidebar_id) {
    global $theme_widget_args, $theme_sidebars;
    $sidebar_style = theme_get_option('theme_sidebars_style_' . $theme_sidebars[$sidebar_id]['group']);
    theme_ob_start();
    $success = dynamic_sidebar($sidebar_id);
    $content = theme_ob_get_clean();
    if (!$success)
        return false;
    extract($theme_widget_args);
    $data = explode($after_widget, $content);
    $widgets = array();
    $heading = theme_get_option('theme_' . (is_home() ? 'posts' : 'single') . '_widget_title_tag');
    for ($i = 0; $i < count($data); $i++) {
        $widget = $data[$i];
        if (theme_is_empty_html($widget))
            continue;
        $id = null;
        $name = null;
        $class = null;
        $style = $sidebar_style;
        $title = null;
        if (preg_match('/<widget(.*?)>/', $widget, $matches)) {
            if (preg_match('/id="(.*?)"/', $matches[1], $ids)) {
                $id = $ids[1];
            }
            if (preg_match('/name="(.*?)"/', $matches[1], $names)) {
                $name = $names[1];
            }
            if (preg_match('/class="(.*?)"/', $matches[1], $classes)) {
                $class = $classes[1];
            }
            if ($name) {
                $style = theme_get_widget_style($name, $style);
            }
            $widget = preg_replace('/<widget[^>]+>/', '', $widget);
            if (preg_match('/<title>(.*)<\/title>/', $widget, $matches)) {
                $title = $matches[1];
                $widget = preg_replace('/<title>.*?<\/title>/', '', $widget);
            }
        }
        $widgets[] = array(
            'id' => $id,
            'name' => $name,
            'class' => $class,
            'style' => $style,
            'title' => $title,
            'heading' => $heading,
            'content' => $widget
        );
    }
    return array_filter($widgets, 'theme_is_displayed_widget');
}

function theme_print_widget($widget) {
    if (!is_array($widget))
        return false;
    $widget_name = theme_get_array_value($widget, 'name', '');
    if ($widget_name) {
        echo theme_get_widget_meta_option($widget_name, 'theme_widget_styling');
        theme_wrapper(theme_get_array_value($widget, 'style', 'block'), $widget);
    } else {
        echo $widget['content'];
    }
    return true;
}
function theme_print_sidebar($sidebar_id, $before = '', $after = '') {
    $widgets = theme_get_dynamic_sidebar_data($sidebar_id);
    if (!is_array($widgets) || count($widgets) < 1)
        return false;
    echo $before;
    foreach ($widgets as $widget) {
        theme_print_widget($widget);
    }
    echo $after;
    return true;
}
