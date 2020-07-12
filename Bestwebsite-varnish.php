<?php
/*
Plugin Name: Bestwebsite Varnish
Plugin URI: http://github.com/Bestwebsite/varnish-wp-cache
Version: 1.0
Author: Bestwebsite
Description: A plugin for purging Varnish cache when content is published or edited.
*/

class BestwebsiteVarnish {
  public $Bestwebsite_addr_optname;
  public $Bestwebsite_port_optname;
  public $Bestwebsite_secret_optname;
  public $Bestwebsite_timeout_optname;
  public $Bestwebsite_update_pagenavi_optname;
  public $Bestwebsite_update_commentnavi_optname;
  public $Bestwebsite_purgeactions;

  function BestwebsiteVarnish() {
    global $post;

    $this->Bestwebsite_addr_optname = "BestwebsiteVarnish_addr";
    $this->Bestwebsite_port_optname = "BestwebsiteVarnish_port";
    $this->Bestwebsite_secret_optname = "BestwebsiteVarnish_secret";
    $this->Bestwebsite_timeout_optname = "BestwebsiteVarnish_timeout";
    $this->BestwebsiteVarnish_purge_url_optname = "BestwebsiteVarnish_purge_url";
    $this->Bestwebsite_update_pagenavi_optname = "BestwebsiteVarnish_update_pagenavi";
    $this->Bestwebsite_update_commentnavi_optname = "BestwebsiteVarnish_update_commentnavi";
    $this->Bestwebsite_use_adminport_optname = "BestwebsiteVarnish_use_adminport";
    $this->Bestwebsite_vversion_optname = "BestwebsiteVarnish_vversion";
    $this->Bestwebsite_purgeactions = array();
    $Bestwebsite_addr_optval = array ("127.0.0.1");
    $Bestwebsite_port_optval = array (80);
    $Bestwebsite_secret_optval = array ("");
    $Bestwebsite_timeout_optval = 5;
    $Bestwebsite_update_pagenavi_optval = 0;
    $Bestwebsite_update_commentnavi_optval = 0;
    $Bestwebsite_use_adminport_optval = 0;
    $Bestwebsite_vversion_optval = 2;

    if ( (get_option($this->Bestwebsite_addr_optname) == FALSE) ) {
      add_option($this->Bestwebsite_addr_optname, $Bestwebsite_addr_optval, '', 'yes');
    }

    if ( (get_option($this->Bestwebsite_port_optname) == FALSE) ) {
      add_option($this->Bestwebsite_port_optname, $Bestwebsite_port_optval, '', 'yes');
    }

    if ( (get_option($this->Bestwebsite_secret_optname) == FALSE) ) {
      add_option($this->Bestwebsite_secret_optname, $Bestwebsite_secret_optval, '', 'yes');
    }

    if ( (get_option($this->Bestwebsite_timeout_optname) == FALSE) ) {
      add_option($this->Bestwebsite_timeout_optname, $Bestwebsite_timeout_optval, '', 'yes');
    }

    if ( (get_option($this->Bestwebsite_update_pagenavi_optname) == FALSE) ) {
      add_option($this->Bestwebsite_update_pagenavi_optname, $Bestwebsite_update_pagenavi_optval, '', 'yes');
    }

    if ( (get_option($this->Bestwebsite_update_commentnavi_optname) == FALSE) ) {
      add_option($this->Bestwebsite_update_commentnavi_optname, $Bestwebsite_update_commentnavi_optval, '', 'yes');
    }

    if ( (get_option($this->Bestwebsite_use_adminport_optname) == FALSE) ) {
      add_option($this->Bestwebsite_use_adminport_optname, $Bestwebsite_use_adminport_optval, '', 'yes');
    }

    if ( 
        (get_option($this->Bestwebsite_vversion_optname) == FALSE) ) {
      add_option($this->Bestwebsite_vversion_optname, $Bestwebsite_vversion_optval, '', 'yes');
    }

    // Localization init
    add_action('init', array($this, 'BestwebsiteVarnishLocalization'));

    // Add Administration Interface
    add_action('admin_menu', array($this, 'BestwebsiteVarnishAdminMenu'));

    // Add Purge Links to Admin Bar
    add_action('admin_bar_menu', array($this, 'BestwebsiteVarnishAdminBarLinks'), 100);

    // When posts/pages are published, edited or deleted
    // 'edit_post' is not used as it is also executed when a comment is changed,
    // causing the plugin to purge several URLs (BestwebsiteVarnishPurgeCommonObjects)
    // that do not need purging.
    
    // When a post or custom post type is published, or if it is edited and its status is "published".
    add_action('publish_post', array($this, 'BestwebsiteVarnishPurgePost'), 99);
    add_action('publish_post', array($this, 'BestwebsiteVarnishPurgeCommonObjects'), 99);
    // When a page is published, or if it is edited and its status is "published".
    add_action('publish_page', array($this, 'BestwebsiteVarnishPurgePost'), 99);
    add_action('publish_page', array($this, 'BestwebsiteVarnishPurgeCommonObjects'), 99);
    // When an attachment is updated.
    add_action('edit_attachment', array($this, 'BestwebsiteVarnishPurgePost'), 99);
    add_action('edit_attachment', array($this, 'BestwebsiteVarnishPurgeCommonObjects'), 99);
    // Runs just after a post is added via email.
    add_action('publish_phone', array($this, 'BestwebsiteVarnishPurgePost'), 99);
    add_action('publish_phone', array($this, 'BestwebsiteVarnishPurgeCommonObjects'), 99);
    // Runs when a post is published via XMLRPC request, or if it is edited via XMLRPC and its status is "published".
    add_action('xmlrpc_publish_post', array($this, 'BestwebsiteVarnishPurgePost'), 99);
    add_action('xmlrpc_publish_post', array($this, 'BestwebsiteVarnishPurgeCommonObjects'), 99);
    // Runs when a future post or page is published.
    add_action('publish_future_post', array($this, 'BestwebsiteVarnishPurgePost'), 99);
    add_action('publish_future_post', array($this, 'BestwebsiteVarnishPurgeCommonObjects'), 99);
    // When post status is changed
    add_action('transition_post_status', array($this, 'BestwebsiteVarnishPurgePostStatus'), 99, 3);
    add_action('transition_post_status', array($this, 'BestwebsiteVarnishPurgeCommonObjectsStatus'), 99, 3);
    // When posts, pages, attachments are deleted
    add_action('deleted_post', array($this, 'BestwebsiteVarnishPurgePost'), 99);
    add_action('deleted_post', array($this, 'BestwebsiteVarnishPurgeCommonObjects'), 99);

    // When comments are made, edited or deleted
    // See: http://codex.wordpress.org/Plugin_API/Action_Reference#Comment.2C_Ping.2C_and_Trackback_Actions
    add_action('comment_post', array($this, 'BestwebsiteVarnishPurgePostComments'),99);
    add_action('edit_comment', array($this, 'BestwebsiteVarnishPurgePostComments'),99);
    add_action('deleted_comment', array($this, 'BestwebsiteVarnishPurgePostComments'),99);
    add_action('trashed_comment', array($this, 'BestwebsiteVarnishPurgePostComments'),99);
    add_action('pingback_post', array($this, 'BestwebsiteVarnishPurgePostComments'),99);
    add_action('trackback_post', array($this, 'BestwebsiteVarnishPurgePostComments'),99);
    add_action('wp_set_comment_status', array($this, 'BestwebsiteVarnishPurgePostCommentsStatus'),99);

    // When Theme is changed, Thanks dupuis
    add_action('switch_theme',array($this, 'BestwebsiteVarnishPurgeAll'), 99);

    // When a new plugin is loaded
    // this was added due to Issue #12, but, doesn't do what was intended
    // commenting this out gets rid of the incessant purging.
    //add_action('plugins_loaded',array($this, 'BestwebsiteVarnishPurgeAll'), 99);

    // Do the actual purges only on shutdown to ensure a single URL is only purged once. IOK 2016-01-20
    add_action('shutdown',array($this,'BestwebsiteVarnishPurgeOnExit'),99);
  }

  function BestwebsiteVarnishLocalization() {
    load_plugin_textdomain('Bestwebsite-varnish', false, dirname(plugin_basename( __FILE__ ) ) . '/lang/');
  }

    // BestwebsiteVarnishPurgeAll - Using a regex, clear all blog cache. Use carefully.
    function BestwebsiteVarnishPurgeAll() {
        $this->BestwebsiteVarnishPurgeObject('/.*');
    }

    // BestwebsiteVarnishPurgeURL - Using a URL, clear the cache
    function BestwebsiteVarnishPurgeURL($Bestwebsite_purl) {
        $Bestwebsite_purl = preg_replace( '#^https?://[^/]+#i', '', $Bestwebsite_purl );
        $this->BestwebsiteVarnishPurgeObject($Bestwebsite_purl);
    }

    //wrapper on BestwebsiteVarnishPurgeCommonObjects for transition_post_status
    function BestwebsiteVarnishPurgeCommonObjectsStatus($old, $new, $post) {
        if ( $old != $new ) {
            if ( $old == 'publish' || $new == 'publish' ) {
                $this->BestwebsiteVarnishPurgeCommonObjects($post->ID);
            }
        }
    }

    // Purge related objects
    function BestwebsiteVarnishPurgeCommonObjects($post_id) {

        $post = get_post($post_id);
        // We need a post object in order to generate the archive URLs which are
        // related to the post. We perform a few checks to make sure we have a
        // post object.
        if ( ! is_object($post) || ! isset($post->post_type) || ! in_array( get_post_type($post), array('post') ) ) {
            // Do nothing for pages, attachments.
            return;
        }
        
        // NOTE: Policy for archive purging
        // By default, only the first page of the archives is purged. If
        // 'Bestwebsite_update_pagenavi_optname' is checked, then all the pages of each
        // archive are purged.
        if ( get_option($this->Bestwebsite_update_pagenavi_optname) == 1 ) {
            // Purge all pages of the archive.
            $archive_pattern = '(?:page/[\d]+/)?$';
        } else {
            // Only first page of the archive is purged.
            $archive_pattern = '$';
        }

        // Front page (latest posts OR static front page)
        $this->BestwebsiteVarnishPurgeObject( '/' . $archive_pattern );

        // Static Posts page (Added only if a static page used as the 'posts page')
        if ( get_option('show_on_front', 'posts') == 'page' && intval(get_option('page_for_posts', 0)) > 0 ) {
            $posts_page_url = preg_replace( '#^https?://[^/]+#i', '', get_permalink(intval(get_option('page_for_posts'))) );
            $this->BestwebsiteVarnishPurgeObject( $posts_page_url . $archive_pattern );
        }

        // Feeds
        $this->BestwebsiteVarnishPurgeObject( '/feed/(?:(atom|rdf)/)?$' );

        // Category, Tag, Author and Date Archives

        // We get the URLs of the category and tag archives, only for
        // those categories and tags which have been attached to the post.

        // Category Archive
        $category_slugs = array();
        foreach( get_the_category($post->ID) as $cat ) {
            $category_slugs[] = $cat->slug;
        }
        if ( ! empty($category_slugs) ) {
            if ( count($category_slugs) > 1 ) {
                $cat_slug_pattern = '(' . implode('|', $category_slugs) . ')';
            } else {
                $cat_slug_pattern = implode('', $category_slugs);
            }
            $this->BestwebsiteVarnishPurgeObject( '/' . get_option('category_base', 'category') . '/' . $cat_slug_pattern . '/' . $archive_pattern );
        }

        // Tag Archive
        $tag_slugs = array();
        foreach( get_the_tags($post->ID) as $tag ) {
            $tag_slugs[] = $tag->slug;
        }
        if ( ! empty($tag_slugs) ) {
            if ( count($tag_slugs) > 1 ) {
                $tag_slug_pattern = '(' . implode('|', $tag_slugs) . ')';
            } else {
                $tag_slug_pattern = implode('', $tag_slugs);
            }
            $this->BestwebsiteVarnishPurgeObject( '/' . get_option('tag_base', 'tag') . '/' . $tag_slug_pattern . '/' . $archive_pattern );
        }

        // Author Archive
        $author_archive_url = preg_replace('#^https?://[^/]+#i', '', get_author_posts_url($post->post_author) );
        $this->BestwebsiteVarnishPurgeObject( $author_archive_url . $archive_pattern );

        // Date based archives
        $archive_year = mysql2date('Y', $post->post_date);
        $archive_month = mysql2date('m', $post->post_date);
        $archive_day = mysql2date('d', $post->post_date);
        // Yearly Archive
        $archive_year_url = preg_replace('#^https?://[^/]+#i', '', get_year_link( $archive_year ) );
        $this->BestwebsiteVarnishPurgeObject( $archive_year_url . $archive_pattern );
        // Monthly Archive
        $archive_month_url = preg_replace('#^https?://[^/]+#i', '', get_month_link( $archive_year, $archive_month ) );
        $this->BestwebsiteVarnishPurgeObject( $archive_month_url . $archive_pattern );
        // Daily Archive
        $archive_day_url = preg_replace('#^https?://[^/]+#i', '', get_day_link( $archive_year, $archive_month, $archive_day ) );
        $this->BestwebsiteVarnishPurgeObject( $archive_day_url . $archive_pattern );
    }

    //wrapper on BestwebsiteVarnishPurgePost for transition_post_status
    function BestwebsiteVarnishPurgePostStatus($old, $new, $post) {
        if ( $old != $new ) {
            if ( $old == 'publish' || $new == 'publish' ) {
                $this->BestwebsiteVarnishPurgePost($post->ID);
            }
        }
    }

    // BestwebsiteVarnishPurgePost - Purges a post object
    function BestwebsiteVarnishPurgePost($post_id, $purge_comments=false) {

        $post = get_post($post_id);
        // We need a post object, so we perform a few checks.
        if ( ! is_object($post) || ! isset($post->post_type) || ! in_array( get_post_type($post), array('post', 'page', 'attachment') ) ) {
            return;
        }

        //$Bestwebsite_url = get_permalink($post->ID);
        // Here we do not use ``get_permalink()`` to get the post object's permalink,
        // because this function generates a permalink only for published posts.
        // So, for example, there is a problem when a post transitions from
        // status 'publish' to status 'draft', because ``get_permalink`` would
        // return a URL of the form, ``?p=123``, which does not exist in the cache.
        // For this reason, the following workaround is used:
        //   http://wordpress.stackexchange.com/a/42988/14743
        // It creates a clone of the post object and pretends it's published and
        // then it generates the permalink for it.
        if (in_array($post->post_status, array('draft', 'pending', 'auto-draft'))) {
            $my_post = clone $post;
            $my_post->post_status = 'published';
            $my_post->post_name = sanitize_title($my_post->post_name ? $my_post->post_name : $my_post->post_title, $my_post->ID);
            $Bestwebsite_url = get_permalink($my_post);
        } else {
            $Bestwebsite_url = get_permalink($post->ID);
        }

        $Bestwebsite_url = preg_replace( '#^https?://[^/]+#i', '', $Bestwebsite_url );

        // Purge post comments feed and comment pages, if requested, before
        // adding multipage support.
        if ( $purge_comments === true ) {
            // Post comments feed
            $this->BestwebsiteVarnishPurgeObject( $Bestwebsite_url . 'feed/(?:(atom|rdf)/)?$' );
            // For paged comments
            if ( intval(get_option('page_comments', 0)) == 1 ) {
                if ( get_option($this->Bestwebsite_update_commentnavi_optname) == 1 ) {
                    $this->BestwebsiteVarnishPurgeObject( $Bestwebsite_url . 'comment-page-[\d]+/(?:#comments)?$' );
                }
            }
        }

        // Add support for multipage content for posts and pages
        if ( in_array( get_post_type($post), array('post', 'page') ) ) {
            $Bestwebsite_url .= '([\d]+/)?$';
        }
        // Purge object permalink
        $this->BestwebsiteVarnishPurgeObject($Bestwebsite_url);

        // For attachments, also purge the parent post, if it is published.
        if ( get_post_type($post) == 'attachment' ) {
            if ( $post->post_parent > 0 ) {
                $parent_post = get_post( $post->post_parent );
                if ( $parent_post->post_status == 'publish' ) {
                    // If the parent post is published, then purge its permalink
                    $Bestwebsite_url = preg_replace( '#^https?://[^/]+#i', '', get_permalink($parent_post->ID) );
                    $this->BestwebsiteVarnishPurgeObject( $Bestwebsite_url );
                }
            }
        }
    }

    // wrapper on BestwebsiteVarnishPurgePostComments for comment status changes
    function BestwebsiteVarnishPurgePostCommentsStatus($comment_id, $new_comment_status) {
        $this->BestwebsiteVarnishPurgePostComments($comment_id);
    }

    // BestwebsiteVarnishPurgePostComments - Purge all comments pages from a post
    function BestwebsiteVarnishPurgePostComments($comment_id) {
        $comment = get_comment($comment_id);
        $post = get_post( $comment->comment_post_ID );

        // Comments feed
        $this->BestwebsiteVarnishPurgeObject( '/comments/feed/(?:(atom|rdf)/)?$' );

        // Purge post page, post comments feed and post comments pages
        $this->BestwebsiteVarnishPurgePost($post, $purge_comments=true);

        // Popup comments
        // See:
        // - http://codex.wordpress.org/Function_Reference/comments_popup_link
        // - http://codex.wordpress.org/Template_Tags/comments_popup_script
        $this->BestwebsiteVarnishPurgeObject( '/.*comments_popup=' . $post->ID . '.*' );

    }

function BestwebsiteVarnishPostID() {
    global $posts, $comment_post_ID, $post_ID;

    if ($post_ID) {
        return $post_ID;
    } elseif ($comment_post_ID) {
        return $comment_post_ID;
    } elseif (is_single() || is_page() && count($posts)) {
        return $posts[0]->ID;
    } elseif (isset($_REQUEST['p'])) {
        return (integer) $_REQUEST['p'];
    }

    return 0;
}

  function BestwebsiteVarnishAdminMenu() {
    if (!defined('VARNISH_HIDE_ADMINMENU')) {
      add_options_page(__('Bestwebsite-varnish Configuration','Bestwebsite-varnish'), 'Bestwebsite-varnish', 'publish_posts', 'BestwebsiteVarnish', array($this, 'BestwebsiteVarnishAdmin'));
    }
  }

  function BestwebsiteVarnishAdminBarLinks($admin_bar){
    $admin_bar->add_menu( array(
      'id'    => 'Bestwebsite-varnish',
      'title' => __('Varnish','Bestwebsite-varnish'),
      'href' => admin_url('admin.php?page=BestwebsiteVarnish')
    ));
    $admin_bar->add_menu( array(
      'id'    => 'clear-all-cache',
      'parent' => 'Bestwebsite-varnish',
      'title' => 'Purge All Cache',
      'href'  => wp_nonce_url(admin_url('admin.php?page=BestwebsiteVarnish&amp;BestwebsiteVarnish_clear_blog_cache&amp;noheader=true'), 'Bestwebsite-varnish')
    ));
    $admin_bar->add_menu( array(
      'id'    => 'clear-single-cache',
      'parent' => 'Bestwebsite-varnish',
      'title' => 'Purge This Page',
      'href'  => wp_nonce_url(admin_url('admin.php?page=BestwebsiteVarnish&amp;BestwebsiteVarnish_clear_post&amp;noheader=true&amp;post_id=' . $this->BestwebsiteVarnishPostID() ), 'Bestwebsite-varnish')
    ));
  }



  // BestwebsiteVarnishAdmin - Draw the administration interface.
  function BestwebsiteVarnishAdmin() {
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
       if (current_user_can('manage_options')) {

          $nonce = $_REQUEST['_wpnonce'];

          if (isset($_GET['BestwebsiteVarnish_clear_blog_cache']) && wp_verify_nonce( $nonce, 'Bestwebsite-varnish' )) {
            $this->BestwebsiteVarnishPurgeAll();
            header('Location: '.admin_url('admin.php?page=BestwebsiteVarnish'));
          }

          if (isset($_GET['BestwebsiteVarnish_clear_post']) && wp_verify_nonce( $nonce, 'Bestwebsite-varnish' )) {
            $this->BestwebsiteVarnishPurgePost($_GET['post_id']);
            header('Location: '.admin_url('admin.php?page=BestwebsiteVarnish'));
          }
       }
    }elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
       if (current_user_can('manage_options')) {
          if (isset($_POST['BestwebsiteVarnish_admin'])) {
             cleanSubmittedData('BestwebsiteVarnish_port', '/[^0-9]/');
             cleanSubmittedData('BestwebsiteVarnish_addr', '/[^0-9.]/');
             if (!empty($_POST["$this->Bestwebsite_addr_optname"])) {
                $Bestwebsite_addr_optval = $_POST["$this->Bestwebsite_addr_optname"];
                update_option($this->Bestwebsite_addr_optname, $Bestwebsite_addr_optval);
             }

             if (!empty($_POST["$this->Bestwebsite_port_optname"])) {
                $Bestwebsite_port_optval = $_POST["$this->Bestwebsite_port_optname"];
                update_option($this->Bestwebsite_port_optname, $Bestwebsite_port_optval);
             }

             if (!empty($_POST["$this->Bestwebsite_secret_optname"])) {
                $Bestwebsite_secret_optval = $_POST["$this->Bestwebsite_secret_optname"];
                update_option($this->Bestwebsite_secret_optname, $Bestwebsite_secret_optval);
             }

             if (!empty($_POST["$this->Bestwebsite_timeout_optname"])) {
                $Bestwebsite_timeout_optval = $_POST["$this->Bestwebsite_timeout_optname"];
                update_option($this->Bestwebsite_timeout_optname, $Bestwebsite_timeout_optval);
             }

             if (!empty($_POST["$this->Bestwebsite_update_pagenavi_optname"])) {
                update_option($this->Bestwebsite_update_pagenavi_optname, 1);
             } else {
                update_option($this->Bestwebsite_update_pagenavi_optname, 0);
             }

             if (!empty($_POST["$this->Bestwebsite_update_commentnavi_optname"])) {
                update_option($this->Bestwebsite_update_commentnavi_optname, 1);
             } else {
                update_option($this->Bestwebsite_update_commentnavi_optname, 0);
             }

             if (!empty($_POST["$this->Bestwebsite_use_adminport_optname"])) {
                update_option($this->Bestwebsite_use_adminport_optname, 1);
             } else {
                update_option($this->Bestwebsite_use_adminport_optname, 0);
             }

             if (!empty($_POST["$this->Bestwebsite_vversion_optname"])) {
                $Bestwebsite_vversion_optval = $_POST["$this->Bestwebsite_vversion_optname"];
                update_option($this->Bestwebsite_vversion_optname, $Bestwebsite_vversion_optval);
             }
          }

          if (isset($_POST['BestwebsiteVarnish_purge_url_submit'])) {
              $this->BestwebsiteVarnishPurgeURL($_POST["$this->BestwebsiteVarnish_purge_url_optname"]);
          }

          if (isset($_POST['BestwebsiteVarnish_clear_blog_cache']))
             $this->BestwebsiteVarnishPurgeAll();

          ?><div class="updated"><p><?php echo __('Settings Saved!','Bestwebsite-varnish' ); ?></p></div><?php
       } else {
          ?><div class="updated"><p><?php echo __('You do not have the privileges.','Bestwebsite-varnish' ); ?></p></div><?php
       }
    }

         $Bestwebsite_timeout_optval = get_option($this->Bestwebsite_timeout_optname);
         $Bestwebsite_update_pagenavi_optval = get_option($this->Bestwebsite_update_pagenavi_optname);
         $Bestwebsite_update_commentnavi_optval = get_option($this->Bestwebsite_update_commentnavi_optname);
         $Bestwebsite_use_adminport_optval = get_option($this->Bestwebsite_use_adminport_optname);
         $Bestwebsite_vversion_optval = get_option($this->Bestwebsite_vversion_optname);
    ?>
    <div class="wrap">
      <script type="text/javascript" src="<?php echo plugins_url('Bestwebsite-varnish.js', __FILE__ ); ?>"></script>
      <h2><?php echo __("Bestwebsite Varnish Administration",'Bestwebsite-varnish'); ?></h2>
      <h3><?php echo __("IP address and port configuration",'Bestwebsite-varnish'); ?></h3>
      <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <?php
          // Can't be edited - already defined in wp-config.php
          global $varnish_servers;
          global $varnish_version;
          if (is_array($varnish_servers)) {
             echo "<p>" . __("These values can't be edited since there's a global configuration located in <em>wp-config.php</em>. If you want to change these settings, please update the file or contact the administrator.",'Bestwebsite-varnish') . "</p>\n";
             // Also, if defined, show the varnish servers configured (VARNISH_SHOWCFG)
             if (defined('VARNISH_SHOWCFG')) {
                echo "<h3>" . __("Current configuration:",'Bestwebsite-varnish') . "</h3>\n";
                echo "<ul>";
                if ( isset($varnish_version) && $varnish_version )
                   echo "<li>" . __("Version: ",'Bestwebsite-varnish') . $varnish_version . "</li>";
                foreach ($varnish_servers as $server) {
                   @list ($host, $port, $secret) = explode(':', $server);
                   echo "<li>" . __("Server: ",'Bestwebsite-varnish') . $host . "<br/>" . __("Port: ",'Bestwebsite-varnish') . $port . "</li>";
                }
                echo "</ul>";
             }
          } else {
          // If not defined in wp-config.php, use individual configuration.
    ?>
       <!-- <table class="form-table" id="form-table" width=""> -->
       <table class="form-table" id="form-table">
        <tr valign="top">
            <th scope="row"><?php echo __("Varnish Administration IP Address",'Bestwebsite-varnish'); ?></th>
            <th scope="row"><?php echo __("Varnish Administration Port",'Bestwebsite-varnish'); ?></th>
            <th scope="row"><?php echo __("Varnish Secret",'Bestwebsite-varnish'); ?></th>
        </tr>
        <script>
        <?php
          $addrs = get_option($this->Bestwebsite_addr_optname);
          $ports = get_option($this->Bestwebsite_port_optname);
          $secrets = get_option($this->Bestwebsite_secret_optname);
          //echo "rowCount = $i\n";
          for ($i = 0; $i < count ($addrs); $i++) {
             // let's center the row creation in one spot, in javascript
             echo "addRow('form-table', $i, '$addrs[$i]', $ports[$i], '$secrets[$i]');\n";
        } ?>
        </script>
	</table>

      <br/>

      <table>
        <tr>
          <td colspan="3"><input type="button" class="" name="BestwebsiteVarnish_admin" value="+" onclick="addRow ('form-table', rowCount)" /> <?php echo __("Add one more server",'Bestwebsite-varnish'); ?></td>
        </tr>
      </table>
      <?php
         }
      ?>
      <p><?php echo __("Timeout",'Bestwebsite-varnish'); ?>: <input class="small-text" type="text" name="BestwebsiteVarnish_timeout" value="<?php echo $Bestwebsite_timeout_optval; ?>" /> <?php echo __("seconds",'Bestwebsite-varnish'); ?></p>

      <p><input type="checkbox" name="BestwebsiteVarnish_use_adminport" value="1" <?php if ($Bestwebsite_use_adminport_optval == 1) echo 'checked '?>/> <?php echo __("Use admin port instead of PURGE method.",'Bestwebsite-varnish'); ?></p>

      <p><input type="checkbox" name="BestwebsiteVarnish_update_pagenavi" value="1" <?php if ($Bestwebsite_update_pagenavi_optval == 1) echo 'checked '?>/> <?php echo __("Also purge all page navigation (experimental, use carefully, it will include a bit more load on varnish servers.)",'Bestwebsite-varnish'); ?></p>

      <p><input type="checkbox" name="BestwebsiteVarnish_update_commentnavi" value="1" <?php if ($Bestwebsite_update_commentnavi_optval == 1) echo 'checked '?>/> <?php echo __("Also purge all comment navigation (experimental, use carefully, it will include a bit more load on varnish servers.)",'Bestwebsite-varnish'); ?></p>

      <p><?php echo __('Varnish Version', 'Bestwebsite-varnish'); ?>: <select name="BestwebsiteVarnish_vversion"><option value="2" <?php if ($Bestwebsite_vversion_optval == 2) echo 'selected '?>/> 2 </option><option value="3" <?php if ($Bestwebsite_vversion_optval == 3) echo 'selected '?>/> 3 </option></select></p>

      <p class="submit"><input type="submit" class="button-primary" name="BestwebsiteVarnish_admin" value="<?php echo __("Save Changes",'Bestwebsite-varnish'); ?>" /></p>

      <p>
        <?php echo __('Purge a URL', 'Bestwebsite-varnish'); ?>:<input class="text" type="text" name="BestwebsiteVarnish_purge_url" value="<?php echo get_bloginfo('url'), '/'; ?>" />
        <input type="submit" class="button-primary" name="BestwebsiteVarnish_purge_url_submit" value="<?php echo __("Purge",'Bestwebsite-varnish'); ?>" />
      </p>

      <p class="submit"><input type="submit" class="button-primary" name="BestwebsiteVarnish_clear_blog_cache" value="<?php echo __("Purge All Blog Cache",'Bestwebsite-varnish'); ?>" /> <?php echo __("Use only if necessary, and carefully as this will include a bit more load on varnish servers.",'Bestwebsite-varnish'); ?></p>
      </form>
    </div>
  <?php
  }

  // BestwebsiteVarnishPurgeObject - Takes a location as an argument and purges this object
  // from the varnish cache.
  // IOK 2015-12-21 changed to delay this to shutdown time.
   function BestwebsiteVarnishPurgeObject($Bestwebsite_url) {
    $this->Bestwebsite_purgeactions[$Bestwebsite_url]=$Bestwebsite_url;
  }
  function BestwebsiteVarnishPurgeOnExit() {
   $purgeurls = array_keys($this->Bestwebsite_purgeactions);
   foreach($purgeurls as $Bestwebsite_url) {
    $this->BestwebsiteVarnishActuallyPurgeObject($Bestwebsite_url);
   }
  }
  function BestwebsiteVarnishActuallyPurgeObject($Bestwebsite_url) {
    global $varnish_servers;

    // added this hook to enable other plugins do something when cache is purged
    do_action( 'BestwebsiteVarnishPurgeObject', $Bestwebsite_url );

    if (is_array($varnish_servers)) {
       foreach ($varnish_servers as $server) {
          list ($host, $port, $secret) = explode(':', $server);
          $Bestwebsite_purgeaddr[] = $host;
          $Bestwebsite_purgeport[] = $port;
          $Bestwebsite_secret[] = $secret;
       }
    } else {
       $Bestwebsite_purgeaddr = get_option($this->Bestwebsite_addr_optname);
       $Bestwebsite_purgeport = get_option($this->Bestwebsite_port_optname);
       $Bestwebsite_secret = get_option($this->Bestwebsite_secret_optname);
    }

    $Bestwebsite_timeout = get_option($this->Bestwebsite_timeout_optname);
    $Bestwebsite_use_adminport = get_option($this->Bestwebsite_use_adminport_optname);
    global $varnish_version;
    if ( isset($varnish_version) && in_array($varnish_version, array(2,3)) )
       $Bestwebsite_vversion_optval = $varnish_version;
    else
       $Bestwebsite_vversion_optval = get_option($this->Bestwebsite_vversion_optname);

    // check for domain mapping plugin by donncha
    if (function_exists('domain_mapping_siteurl')) {
        $Bestwebsite_wpurl = domain_mapping_siteurl('NA');
    } else {
        $Bestwebsite_wpurl = get_bloginfo('url');
    }
    $Bestwebsite_replace_wpurl = '/^https?:\/\/([^\/]+)(.*)/i';
    $Bestwebsite_host = preg_replace($Bestwebsite_replace_wpurl, "$1", $Bestwebsite_wpurl);
    $Bestwebsite_blogaddr = preg_replace($Bestwebsite_replace_wpurl, "$2", $Bestwebsite_wpurl);
    $Bestwebsite_url = $Bestwebsite_blogaddr . $Bestwebsite_url;

    // allow custom purge functions and stop if they return false
    if (function_exists($this->Bestwebsite_custom_purge_obj_f)) {
        $f = $this->Bestwebsite_custom_purge_obj_f;
        if (!$f($Bestwebsite_url, $Bestwebsite_host))
            return;
    }

    for ($i = 0; $i < count ($Bestwebsite_purgeaddr); $i++) {
      $varnish_sock = fsockopen($Bestwebsite_purgeaddr[$i], $Bestwebsite_purgeport[$i], $errno, $errstr, $Bestwebsite_timeout);
      if (!$varnish_sock) {
        error_log("Bestwebsite-varnish error: $errstr ($errno) on server $Bestwebsite_purgeaddr[$i]:$Bestwebsite_purgeport[$i]");
        continue;
      }

      if($Bestwebsite_use_adminport) {
        $buf = fread($varnish_sock, 1024);
        if(preg_match('/(\w+)\s+Authentication required./', $buf, $matches)) {
          # get the secret
          $secret = $Bestwebsite_secret[$i];
          fwrite($varnish_sock, "auth " . $this->WPAuth($matches[1], $secret) . "\n");
	  $buf = fread($varnish_sock, 1024);
          if(!preg_match('/^200/', $buf)) {
            error_log("Bestwebsite-varnish error: authentication failed using admin port on server $Bestwebsite_purgeaddr[$i]:$Bestwebsite_purgeport[$i]");
	    fclose($varnish_sock);
	    continue;
	  }
        }
        if ($Bestwebsite_vversion_optval == 3) {
            $out = "ban req.url ~ ^$Bestwebsite_url$ && req.http.host == $Bestwebsite_host\n";
          } else {
            $out = "purge req.url ~ ^$Bestwebsite_url && req.http.host == $Bestwebsite_host\n";
          }
      } else {
        $out = "BAN $Bestwebsite_url HTTP/1.0\r\n";
        $out .= "Host: $Bestwebsite_host\r\n";
        $out .= "User-Agent: WordPress-Varnish plugin\r\n";
        $out .= "Connection: Close\r\n\r\n";
      }
      fwrite($varnish_sock, $out);
      fclose($varnish_sock);
    }
  }

  function WPAuth($challenge, $secret) {
    $ctx = hash_init('sha256');
    hash_update($ctx, $challenge);
    hash_update($ctx, "\n");
    hash_update($ctx, $secret . "\n");
    hash_update($ctx, $challenge);
    hash_update($ctx, "\n");
    $sha256 = hash_final($ctx);

    return $sha256;
  }
}

$BestwebsiteVarnish = new BestwebsiteVarnish();

// Helper functions
function cleanSubmittedData($varname, $regexp) {
// FIXME: should do this in the admin console js, not here   
// normally I hate cleaning data and would rather validate before submit
// but, this fixes the problem in the cleanest method for now
  foreach ($_POST[$varname] as $key=>$value) {
    $_POST[$varname][$key] = preg_replace($regexp,'',$value);
  }
}
?>
