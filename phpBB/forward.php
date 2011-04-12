<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// Grab only parameters needed here
$post_id = request_var('p', 0);
$forum_id = request_var('f', 0);
$topic_id = request_var('t', 0);

$emails = utf8_normalize_nfc(request_var('foremails', '', true));
$submit = (isset($_GET['forward'])) ? true : false;

if ($submit) {
  page_header('Forward Response');

  // check input format, if it fails, exit to template.
  $emails_string = trim($emails);
  $emails_string = trim($emails_string, ' ');
  $emails_array = explode(';', $emails_string);
  foreach($emails_array as $email)
  {
     if (!valid_email($email))
     {
        $error[] = $user->lang['INVALID_EMAIL'];
     }
  }

  // check $error variable
  if (sizeof($error)) {
      $template->assign_vars(array('ERROR' => implode('<br />', $error),
                                   'FORUMID'=>$forum_id,
                                   'POSTID'=>$post_id,
                                   'TOPICID'=>$topic_id
                                  )
                            );
  } else {
      $url = generate_board_url() . "/viewtopic.$phpEx?f=$forum_id&p=$post_id";
      send_femails($emails_string, $url);

      $template->assign_vars(array('MSG'=>'Your post has been forwarded to ' . implode(';', $emails_array) . '.',
                                   'URL'=> $url
                                  )
                            );

      // add item to post list
      post_reply('reply', 'Emailed Faculty', $forum_id, $topic_id, 'Aims emailed to ' . implode(';', $emails_array) . '.');
      //add_email_reply($sql_data, $db);
      # send items to function
  }

} else {
  page_header('Forward Post');
  $template->assign_vars(array('FORUMID'=>$forum_id,
                               'POSTID'=>$post_id,
                               'TOPICID'=>$topic_id
                              )
                        );

}

$template->set_filenames(array(
  'body' => 'forward_body.html',
));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();
?>