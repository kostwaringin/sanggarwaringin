<?php
/**
 *
 * comments.php
 *
 * The comments template. Used to display post or page comments and comment form.
 * 
 * Additional settings are available under the Appearance -> Theme Options -> Comments.
 *
 */
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die('Please do not load this page directly. Thanks!');
	
if (post_password_required()) {
?>
    <div class="art-comments">
        <h2 class="art-postheader nocomments"><?php _e('This post is password protected. Enter the password to view any comments.', THEME_NS) ?></h2>
    </div>
<?php
	return;
}
if (have_comments()) {
?>
    <div class="art-comments">
        <h2 class="art-postheader comments"><?php printf(
            _n('One Response to %2$s', '%1$s Responses to %2$s', get_comments_number(), THEME_NS),
            number_format_i18n(get_comments_number()),  
            get_the_title()
        ); ?></h2>
<?php	
    theme_ob_start();
    paginate_comments_links();
    $pagination = theme_stylize_pagination(theme_ob_get_clean());
    echo $pagination;		
?>
        <ul id="comments-list">
            <?php wp_list_comments('type=all&callback=theme_comment'); ?>
        </ul>
<?php echo $pagination; ?>
    </div>
<?php
}
if (!comments_open()) {
    return;
}
/* comment form */
theme_ob_start();
$args = array();
if (theme_get_option('theme_comment_use_smilies')) {

	function theme_comment_form_field_comment($form_field) {
		theme_include_lib('smiley.php');
		return theme_get_smilies_js() . '<p class="smilies">' . theme_get_smilies() . '</p>' . $form_field;
	}

	add_filter('comment_form_field_comment', 'theme_comment_form_field_comment');
}
comment_form();
echo str_replace(
    array('id="respond"', '<h3', 'id="reply-title"', '</h3>', 'logged-in-as', 'type="submit"'), 
    array('id="respond" class="art-commentsform"', '<h2', 'id="reply-title" class="art-postheader"', '</h2>', 'art-postcontent logged-in-as', 'class="art-button" type="submit"'), 
    theme_ob_get_clean());