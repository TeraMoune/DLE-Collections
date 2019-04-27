<?PHP
if( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

	$action = $action ? $action : "settings";
	$id = intval( $_REQUEST['id'] );

	$menu_active_settings = " class=\"active\"";
	$menu_active_list = "";
	
	if( $action == "list") {
  
	$lang['tabs_gr_all'] = $lang['header_tm_1'];
	$menu_active_list = " class=\"active\"";
	$menu_active_settings = "";
	$add_template = "<div style=\"display:inline-block;\">
     <a href=\"#\" data-toggle=\"modal\" data-target=\"#addCollections\"><i class=\"fa fa-plus-circle\"></i> Создание новой подборки</a>
	</div>";
  
	}

    function saveFile($path, $filename) {

		$filename = totranslit( $filename );

        if(!@move_uploaded_file($_FILES['qqfile']['tmp_name'], $path.$filename)){
            return false;
        }

        return $filename;
    }	
	
	function check_filename ( $filename ) {
		global $config;
		
		if( $filename != "" ) {

			$filename = str_replace( "\\", "/", $filename );
			$filename = preg_replace( '#[.]+#i', '.', $filename );
			$filename = str_replace( "/", "", $filename );
			$filename = str_ireplace( "php", "", $filename );

			$filename_arr = explode( ".", $filename );
			
			if(count($filename_arr) < 2) {
				return false;
			}
			
			$type = totranslit( end( $filename_arr ) );
			
			if(!$type) return false;
			
			$curr_key = key( $filename_arr );
			unset( $filename_arr[$curr_key] );
 
			$filename = totranslit( implode( "_", $filename_arr ) );
			
			if( !$filename ) {
				$filename = time() + rand( 1, 100 );
			}
			
			$filename = $filename . "." . $type;

		} else return false;

		$filename = preg_replace( '#[.]+#i', '.', $filename );

		if( stripos ( $filename, ".php" ) !== false ) return false;
		if( stripos ( $filename, ".phtm" ) !== false ) return false;
		if( stripos ( $filename, ".shtm" ) !== false ) return false;
		if( stripos ( $filename, ".htaccess" ) !== false ) return false;
		if( stripos ( $filename, ".cgi" ) !== false ) return false;
		if( stripos ( $filename, ".htm" ) !== false ) return false;
		if( stripos ( $filename, ".ini" ) !== false ) return false;

		if( stripos ( $filename, "." ) === 0 ) return false;
		if( stripos ( $filename, "." ) === false ) return false;
		
		if( dle_strlen( $filename, $config['charset'] ) > 170 ) {
			return false;
		}

		return $filename;

	}
	
    function getFileName() {

		$path_parts = @pathinfo($_FILES['qqfile']['name']);

        return $path_parts['basename'];

    }
    function getFileSize() {
        return $_FILES['qqfile']['size'];
    }

    function getErrorCode() {

		$error_code = $_FILES['qqfile']['error'];

		if ($error_code !== UPLOAD_ERR_OK) {

		    switch ($error_code) { 
		        case UPLOAD_ERR_INI_SIZE: 
		            $error_code = 'PHP Error: The uploaded file exceeds the upload_max_filesize directive in php.ini'; break;
		        case UPLOAD_ERR_FORM_SIZE: 
		            $error_code = 'PHP Error: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; break;
		        case UPLOAD_ERR_PARTIAL: 
		            $error_code = 'PHP Error: The uploaded file was only partially uploaded'; break;
		        case UPLOAD_ERR_NO_FILE: 
		            $error_code = 'PHP Error: No file was uploaded'; break;
		        case UPLOAD_ERR_NO_TMP_DIR: 
		            $error_code = 'PHP Error: Missing a PHP temporary folder'; break;
		        case UPLOAD_ERR_CANT_WRITE: 
		            $error_code = 'PHP Error: Failed to write file to disk'; break;
		        case UPLOAD_ERR_EXTENSION: 
		            $error_code = 'PHP Error: File upload stopped by extension'; break;
		        default: 
		            $error_code = 'Unknown upload error';  break;
		    } 

		} else return false;

        return $error_code;
    }	
	
	function showRow($title = "", $description = "", $field = "", $class = "") {
		echo "<tr>
        <td class=\"col-xs-6 col-sm-6 col-md-7\"><h6 class=\"media-heading text-semibold\">{$title}</h6><span class=\"text-muted text-size-small hidden-xs\">{$description}</span></td>
        <td class=\"col-xs-6 col-sm-6 col-md-5\">{$field}</td>
        </tr>";
	}
	
	function makeCheckBox($name, $selected) {

		$selected = $selected ? "checked" : "";
	
		return "<input class=\"switch\" type=\"checkbox\" name=\"{$name}\" value=\"1\" {$selected}>";

	}

	function makeDropDown($options, $name, $selected) {
		$output = "<select class=\"uniform\" style=\"opacity:0;\" name=\"$name\">\r\n";
		foreach ( $options as $value => $description ) {
			$output .= "<option value=\"$value\"";
			if( $selected == $value ) {
				$output .= " selected ";
			}
			$output .= ">$description</option>\n";
		}
		$output .= "</select>";
		return $output;
	}

if( $action == "settings" ) {
	
	if( $_REQUEST['is'] == "save" ) {
		
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die( "Hacking attempt!" );
	
	}

	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '48', 'Изменение настроек подборок')" );
	
	$save_con = $_POST['save_con'];
    $save_con['collection_news_number'] = intval($save_con['collection_news_number']);	
    $save_con['collection_number'] = intval($save_con['collection_number']);	
    $save_con['collection_disable_index'] = intval($save_con['collection_disable_index']);	
    
	$params = array();
	$find = array();
	$replace = array();
	
	$find[] = "'\r'";
	$replace[] = "";
	$find[] = "'\n'";
	$replace[] = "";
	$save_con = $save_con + $config;
	
	$handler = fopen( ENGINE_DIR . '/data/config.php', "w" );
	fwrite( $handler, "<?PHP \n\n//System Configurations\n\n\$config = array (\n\n" );
	foreach ( $save_con as $name => $value ) {
		
		if( $name == "collection_separator" ) {
			$value = str_replace( '&amp;', '&', $value );
		}
		
		$value = preg_replace( $find, $replace, $value );
		$value = str_replace( "$", "&#036;", $value );
		$value = str_replace( "{", "&#123;", $value );
		$value = str_replace( "}", "&#125;", $value );
		$value = str_replace( chr(0), "", $value );
		$value = str_replace( chr(92), "", $value );
		$value = str_ireplace( "decode", "dec&#111;de", $value );
		
		$name = preg_replace( $find, $replace, $name );
		$name = str_replace( "$", "&#036;", $name );
		$name = str_replace( "{", "&#123;", $name );
		$name = str_replace( "}", "&#125;", $name );
		$name = str_replace( chr(0), "", $name );
		$name = str_replace( chr(92), "", $name );
		$name = str_replace( '(', "", $name );
		$name = str_replace( ')', "", $name );
		$name = str_ireplace( "decode", "dec&#111;de", $name );
		
		fwrite( $handler, "'{$name}' => '{$value}',\n\n" );

	}
	fwrite( $handler, ");\n\n?>" );
	fclose( $handler );

	clear_cache();
	
	if (function_exists('opcache_reset')) {
		opcache_reset();
	}
	
	msg( "info", $lang['opt_sysok'], $lang['opt_sysok_1'], "?mod=news_collections" );		
	} else {

	echoheader( $lang['media_gallery_settings'], $lang['media_gallery_desc'] );

	echo <<<HTML
<form action="" method="post">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<input type="hidden" name="mod" value="news_collections">
<input type="hidden" name="action" value="settings">
<input type="hidden" name="is" value="save">
<div class="navbar navbar-default navbar-component navbar-xs" style="z-index: inherit;">
  <div class="navbar-collapse collapse" id="navbar-filter">
    <ul class="nav navbar-nav">
      <li{$menu_active_settings}><a href="?mod=news_collections">{$lang['skin_option']}</a></li>
      <li{$menu_active_list}><a href="?mod=news_collections&action=list">Подборки</a></li>
    </ul>
  </div>
</div>

<div id="general" class="panel panel-flat">
<div class="panel-body">Основное</div>
  <div class="table-responsive">

    <table class="table table-normal">
HTML;

	showRow( "Количество новостей на страницу подборки", "По умолчанию использует параметр количества новостей", "<input autocomplete=\"off\" class=\"form-control\" type=\"text\" name=\"save_con[collection_news_number]\" value=\"" . ( $config['collection_news_number'] ? $config['collection_news_number'] : $config['news_number']). "\" style=\"width:100%;max-width:100px;float:right\">" );
	showRow( "Количество подборок на страницу", "По умолчанию использует параметр количества новостей", "<input autocomplete=\"off\" class=\"form-control\" type=\"text\" name=\"save_con[collection_number]\" value=\"" . ( $config['collection_number'] ? $config['collection_number'] : $config['news_number'] ). "\" style=\"width:100%;max-width:100px;float:right\">" );
	showRow( "Разделитель подборок на странице новости", "По умолчанию - запятая", "<input autocomplete=\"off\" class=\"form-control\" type=\"text\" name=\"save_con[collection_separator]\" value=\"" . ( $config['collection_separator'] ? $config['collection_separator'] : ',' ) . "\" style=\"width:100%;max-width:50px;float:right\">" );
	showRow( $lang['opt_sys_msort'], $lang['opt_sys_msortd']."<br>По умолчанию: {$config['news_msort']}", "<div style=\"float:right\">" . makeDropDown( array ("DESC" => $lang['opt_sys_mminus'], "ASC" => $lang['opt_sys_mplus'] ), "save_con[collections_news_msort]", ( $config['collections_news_msort'] ? $config['collections_news_msort'] : $config['news_msort'] ) ) . "</div>" );
	showRow( $lang['opt_sys_sort'], $lang['opt_sys_sortd']."<br>По умолчанию: {$config['news_sort']}", "<div style=\"float:right\">" . makeDropDown( array ("date" => $lang['opt_sys_sdate'], "rating" => $lang['opt_sys_srate'], "news_read" => $lang['opt_sys_sview'], "title" => $lang['opt_sys_salph'] ), "save_con[collections_news_sort]", ( $config['collections_news_sort'] ? $config['collections_news_sort'] : $config['news_sort'] ) ) . "</div>" );
	showRow( $lang['opt_sys_an'], "<a onclick=\"javascript:Help('date'); return false;\" href=\"#\">$lang[opt_sys_and]</a>", "<div style=\"float:right\"><input  type=\"text\" class=\"form-control\" style=\"max-width:150px; text-align: center;\" name=\"save_con[collection_timestamp_active]\" value=\"".( $config['collection_timestamp_active'] ? $config['collection_timestamp_active'] : $config['timestamp_active'] )."\"></div>" );
	
    echo <<<HTML
	</table>
	</div>
</div>
<div id="general" class="panel panel-flat">
  <div class="panel-body">SEO</div>
  <div class="table-responsive">

    <table class="table table-normal">
HTML;


	showRow( "Запретить индексацию подборок", "Опция позволит исключить страницы подборок из поисковых запросов", "<div style=\"float:right\">" . makeCheckBox( "save_con[collection_disable_index]", "{$config['collection_disable_index']}" ) . "</div>" );
	showRow( "Мета тег TITLE", "Введите мета тег TITLE для страницы всех подборок", "<input autocomplete=\"off\" class=\"form-control\" type=\"text\" name=\"save_con[collection_title]\" value=\"{$config['collection_title']}\" style=\"width:100%;max-width:350px;float:right\">" );
	showRow( "Мета тег DESCRIPTION", "Введите мета тег DESCRIPTION для страницы всех подборок", "<input autocomplete=\"off\" class=\"form-control\" type=\"text\" name=\"save_con[collection_description]\" value=\"{$config['collection_description']}\" style=\"width:100%;max-width:350px;float:right\">" );
	showRow( "Мета тег KEYWORDS", "Введите мета тег KEYWORDS для страницы всех подборок", "<input autocomplete=\"off\" class=\"form-control\" type=\"text\" name=\"save_con[collection_keywords]\" value=\"{$config['collection_keywords']}\" style=\"width:100%;max-width:350px;float:right\">" );
	showRow( "Хлебные крошки", "Введите хлебные крошки для страницы всех подборок<br>Так же это название будет использовано в полной новости при использовании тегов<br>{collections} и {collections-link}", "<input autocomplete=\"off\" class=\"form-control\" type=\"text\" name=\"save_con[collection_speedbar]\" value=\"{$config['collection_speedbar']}\" style=\"width:100%;max-width:350px;float:right\">" );

	echo "</table></div></div><center><button type=\"submit\" class=\"btn bg-teal btn-raised position-left legitRipple\"><i class=\"fa fa-floppy-o position-left\"></i>Сохранить</button></center></form>";

    echo <<<HTML
	<script type="text/javascript">
	$(function(){
		$('.categoryselect').chosen({allow_single_deselect:true, no_results_text: '{$lang['addnews_cat_fault']}'});
	});
	</script> 
HTML;

	echofooter();
	
	}
	
} else if( $action == "list" ) {
	
	if( $_REQUEST['is'] == "delete" ) {

	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		die('sess_error');
	
	}
	
	$id = intval($_POST['id']);
	
	if( !$id ) die('error');
	
	$row = $db->super_query( "SELECT name, news_ids, cover FROM " . PREFIX . "_news_collections WHERE id = '{$id}'");
	
	if( $row ) {
		
		if( $row['news_ids'] ) {
		
		$news_ids = explode(',' ,$row['news_ids']);
		
		foreach( $news_ids as $val ) {
			
			$row = $db->super_query("SELECT collections FROM ".PREFIX."_post WHERE id = '{$val}'");
			
			$list = explode( ",", $row['collections'] );
			$i = 0;
	
			foreach ( $list as $cid ) {

				if( $cid == $id ) unset( $list[$i] );
				$i ++;

			}
	
			if( count( $list ) ) $new_collections = implode( ",", $list );
			else $new_collections = "";
			
			$db->query("UPDATE ".PREFIX."_post SET collections = '{$new_collections}' WHERE id = '{$val}'");

		}
		
		}

		if( $row['cover'] ) {
			
			$url_image = explode( "/", $row['cover'] );
		
			$folder_prefix = $url_image[0] . "/";
			$image = $url_image[1];
			$image = totranslit($image);
	
			@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . $image );	
			
		}
		
		$db->query( "DELETE FROM " . PREFIX . "_news_collections WHERE id = '{$id}'" );
		@unlink( ENGINE_DIR . '/cache/system/collections.php' );
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '48', 'Удаление подборки: ".$db->safesql($row['name'])."-{$id}')" );
		die('ok');
		
	} else die('error');
	
	} elseif( $_REQUEST['is'] == "save" ) {
		
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		msg( "error", array('javascript:history.go(-1)' => "Добавление новой подборки на сайт", '' => $lang['addnews_error'] ), $lang['sess_error'], "javascript:history.go(-1)" );
	
	}
	
	@header('X-XSS-Protection: 0;');

	include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
	
	$parse = new ParseFilter();	
	
	$name = $parse->process(  trim( strip_tags ($_POST['name']) ) );
	$news_ids = $_POST['news_ids'] ? $parse->process( $_POST['news_ids'] ) : '';

	if( $news_ids ) {
		
		$news_ids = explode(', ', $news_ids);
		$num_elem = count($news_ids);
		$news_ids = implode(',',$news_ids);
		
	} else 	$num_elem = 0;	
	
	if ( $config['allow_admin_wysiwyg'] ) $parse->allow_code = false;
	
	$descr = $parse->process( $_POST['short_story'] );
	
	if( $config['allow_admin_wysiwyg'] OR $allow_br != '1' ) $descr = $db->safesql( $parse->BB_Parse( $descr ) );
	else $descr = $db->safesql( $parse->BB_Parse( $descr, false ) );

	if( $parse->not_allowed_text ) {
		msg( "error", array('javascript:history.go(-1)' => "Добавление новой подборки на сайт", '' => $lang['addnews_error'] ), "Ваше описание или имя содержит недопустимый текст.", "javascript:history.go(-1)" );
	}
	
	$alt_url = trim($_POST['alt_url']);
	$poster = trim($_POST['poster']);
	
	if( $poster ) {

		$url_image = explode( "/", $poster );
				
		if( count( $url_image ) == 2 ) {
					
			$folder_prefix = $url_image[0] . "/";
			$image = $url_image[1];
				
		} else {
					
			$folder_prefix = "";
			$image = $url_image[0];
			
		}

		$image = totranslit($image);
		$filename_arr = explode('.', $image);
		$type = end($filename_arr);
		$poster = $folder_prefix . $image;
	} else $poster = '';
	
	
	if(!$alt_url) $alt_url = totranslit( stripslashes( $name ), true, false );
	else $alt_url = totranslit( stripslashes( $alt_url ), true, false );
	
	if( dle_strlen( $alt_url, $config['charset'] ) > 190 ) {
		$alt_url = dle_substr( $alt_url, 0, 190, $config['charset'] );
	}
	
	$name = $db->safesql( $name );
	$alt_url = $db->safesql( $alt_url );
	
	$metatags = create_metatags( $descr );
	
	$added_time = time();
	$thistime = date( "Y-m-d H:i:s", $added_time );
	
	if( !$name ) {
		msg( "error", array('javascript:history.go(-1)' => "Добавление новой подборки на сайт", '' => $lang['addnews_error'] ), "Имя является обязательным при создании подборки.", "javascript:history.go(-1)" );
		
	}

	if( dle_strlen( $name, $config['charset'] ) > 255 ) {
		msg( "error", array('javascript:history.go(-1)' => "Добавление новой подборки на сайт", '' => $lang['addnews_error'] ), "Слишком длинное имя.", "javascript:history.go(-1)" );
	}
		
	$db->query( "INSERT INTO " . PREFIX . "_news_collections (user_name, name, alt_url, descr, date, create_date, cover, news_ids, num_elem, keywords, metatitle) values ('".$db->safesql($member_id['name'])."', '{$name}', '{$alt_url}', '{$descr}', '{$thistime}', '{$thistime}', '{$poster}', '{$news_ids}', '{$num_elem}', '{$metatags['keywords']}', '{$metatags['title']}')" );

	$id = $db->insert_id();
	
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '48', 'Добавление новой подборки: {$name}-{$id}')" );	
	
	if( $news_ids ) {
		
		$news_ids = explode(',' ,$news_ids);
		
		foreach( $news_ids as $val ) {
			
			$val = intval($val);
			
			$row = $db->super_query("SELECT collections FROM ".PREFIX."_post WHERE id = '{$val}'");
			
			if( $row['collections'] ) {
				
				$row['collections'] = explode(',', $row['collections']);
				$row['collections'][] = $id;
				$row['collections'] = array_unique($row['collections']);
				$new_collections = implode(',', $row['collections']);
				
			} else $new_collections = $id;
			
			$db->query("UPDATE ".PREFIX."_post SET collections = '{$new_collections}' WHERE id = '{$val}'");

		}
		
		$count_news = " | Количество новостей: " . $num_elem;
		
	} else $count_news = "";
	
	if( $poster ) {

		if( file_exists( ROOT_DIR . "/uploads/posts/" . date( "Y-m" )."/uploaded_cover_0.". $type ) ) {
		
			$cover_name = date( "Y-m" ) . "/" . totranslit($name) . '_' . $id.'.'.$type;
			rename(ROOT_DIR . "/uploads/posts/" . date( "Y-m" )."/uploaded_cover_0.". $type, ROOT_DIR . "/uploads/posts/" . $cover_name);
			$db->query("UPDATE ".PREFIX."_news_collections SET cover = '{$cover_name}' WHERE id = '{$id}'");
		}
		
	}

	
	@unlink( ENGINE_DIR . '/cache/system/collections.php' );
	msg( "success", "Подборка добавлена", "Подборка" . " \"" . stripslashes( stripslashes( $name ) ) . "\" " . $lang['addnews_ok_2'] . $count_news, array('?mod=news_collections&action=list' => 'Назад к списку' ) );

	}
	
	$collections = $db->super_query( "SELECT * FROM " . PREFIX . "_news_collections WHERE 1 ORDER BY id ASC", true);
	
	$user_group[$member_id['user_group']]['allow_image_upload'] = false;
	$user_group[$member_id['user_group']]['allow_file_upload'] = false;
	
	if( $config['allow_admin_wysiwyg'] == 1 ) {
		$js_array[] = "engine/skins/codemirror/js/code.js";
		$js_array[] = "engine/editor/jscripts/froala/editor.js";
		$js_array[] = "engine/editor/jscripts/froala/languages/{$lang['wysiwyg_language']}.js";
		$css_array[] = "engine/editor/jscripts/froala/css/editor.css";
	}

	if( $config['allow_admin_wysiwyg'] == 2 ) {
		$js_array[] = "engine/editor/jscripts/tiny_mce/tinymce.min.js";
	}
	
	if( !$config['allow_admin_wysiwyg'] ) {
		$js_array[] = "engine/classes/js/typograf.min.js";
	}
	
	$js_array[] = "engine/classes/uploads/html5/fileuploader.js?v=24";
	
	echoheader( $lang['media_gallery_settings'], $lang['media_gallery_desc'] );

	if ( !$user_group[$member_id['user_group']]['allow_html'] ) $config['allow_admin_wysiwyg'] = 0;	

	if( $config['allow_admin_wysiwyg'] == "2" ) $save = "tinyMCE.triggerSave();"; else $save = "";
	
	echo <<<HTML
<style>
.tokenfield.form-control {
	
	max-width: 100%;
    display: block;	
	
}

.tokenfield .token > .close {
	
	top: 35%;
	
}
</style>	
<div class="navbar navbar-default navbar-component navbar-xs" style="z-index: inherit;">
  <div class="navbar-collapse collapse" id="navbar-filter">
    <ul class="nav navbar-nav">
      <li{$menu_active_settings}><a href="?mod=news_collections">{$lang['skin_option']}</a></li>
      <li{$menu_active_list}><a href="?mod=news_collections&action=list">Подборки</a></li>
    </ul>
  </div>
</div> 
<div id="general" class="panel panel-flat">
  <div class="panel-heading">{$add_template}</div>

<form action="" method="post" onsubmit="if(checkxf()=='fail') return false;" id="collections" name="addcollections">
<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
<input type="hidden" name="mod" value="news_collections">
<input type="hidden" name="action" value="list">
<input type="hidden" name="is" value="save"> 
<div class="modal fade" id="addCollections" tabindex="-1" role="dialog" aria-labelledby="newtemplatesLabel">
  <div class="modal-dialog modal-lg" role="document" style="width:900px;">
    <div class="modal-content">
      <div class="modal-header ui-dialog-titlebar">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <span class="ui-dialog-title" id="newcatsLabel">Добавление новой подборки</span>
      </div>
      <div class="modal-body">
    
    <div class="form-group">
      <div class="row">
        <div class="col-sm-6">
          <label>Название</label>
          <input name="name" id="name" type="text" class="form-control" maxlength="190" autocomplete="off">
        </div>
        <div class="col-sm-6">
          <label>ЧПУ URL подборки</label>
		  <input name="alt_url" type="text" class="form-control" maxlength="190" autocomplete="off" placeholder="Если оставить пустым будет использовано Название">
        </div>
		<div class="col-sm-12">
          <label>Новости</label>
		  <input id="news_ids" name="news_ids" type="text" class="form-control" autocomplete="off">
        </div>
        <div class="col-sm-6">
          <label>Мета тег TITLE</label>
          <input name="meta_title" type="text" class="form-control" maxlength="190" autocomplete="off" placeholder="Если оставить пустым будет использовано Название">
        </div>
        <div class="col-sm-6">
          <label>Мета тег KEYWORDS</label>
		  <input name="keywords" type="text" class="form-control" maxlength="190" autocomplete="off">
        </div>		
        <div class="col-sm-12" style="margin-top:10px;">
          <label>Описание</label>
HTML;

	if( $config['allow_admin_wysiwyg'] ) {
		
		$mod = 'collections';
		include (DLEPlugins::Check(ENGINE_DIR . '/editor/shortnews.php'));
	
	} else {

		$bb_editor = true;
		include (DLEPlugins::Check(ENGINE_DIR . '/inc/include/inserttag.php'));
		echo "<div class=\"editor-panel\"><div class=\"shadow-depth1\">{$bb_code}<textarea class=\"editor\" style=\"width:100%;height:300px;\" onfocus=\"setFieldName(this.name)\" name=\"short_story\" id=\"short_story\"></textarea></div></div>";
	}
	
echo <<<HTML
        </div>
        <div class="col-sm-6">
        <label>Постер</label>
		<div id="uploaded_poster"></div>
		<div id="uploads_poster"></div>
		<input type="hidden" name="poster" id="poster" value="" />
        </div>	
      </div>
    </div>
      </div>
      <div class="modal-footer" style="margin-top:-20px;">
      <button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$lang['user_save']}</button>
        <button type="button" class="btn bg-slate-600 btn-sm btn-raised" data-dismiss="modal">{$lang['p_cancel']}</button>
      </div>
    </div>
  </div>
</div></form>   
  <div class="table-responsive">

    <table class="table table-normal">
	  <thead>
      <tr>
        <th>#</th>
        <th>Название подборки</th>
        <th>Количество новостей</th>
        <th>Создана</th>
        <th>Обновлена</th>
        <th>Меню</th>
      </tr>
      </thead>
	  <tbody>
HTML;



	foreach( $collections as $val ) {
		
		$val['create_date'] = strtotime( $val['create_date'] );
		$val['date'] = strtotime( $val['date'] );
		$addedtime = date( "d.m.Y", $val['create_date'] );
		$updatetime = date( "d.m.Y", $val['date'] );
		
		$menu_link = <<<HTML
        <div class="btn-group">
          <a href="#" class="dropdown-toggle nocolor" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-bars"></i><span class="caret"></span></a>
          <ul class="dropdown-menu text-left dropdown-menu-right">
            <li><a class="editlink" href="?mod=news_collections&action=edit&id={$val['id']}"><i class="fa fa-pencil-square-o position-left"></i>{$lang['word_ledit']}</a></li>
			<li class="divider"></li>
            <li><a uid="{$val['id']}" class="dellink" href="#"><i class="fa fa-trash-o position-left text-danger"></i>{$lang['word_ldel']}</a></li>
          </ul>
        </div>
HTML;

	if( $config['allow_alt_url'] ) $url = $config['http_home_url'] . 'collections/' . $val['id'] . '-' . $val['alt_url'];
	else $url = $config['http_home_url'] . '?do=collections&id=' . $val['id'];
		
		echo "<tr>
        <td><a href=\"{$url}\" target\"_blank\">{$val['id']}</a></td>
        <td id=\"content_{$val['id']}\">{$val['name']}</td>
        <td>{$val['num_elem']}</td>
        <td>{$addedtime}</td>
        <td>{$updatetime}</td>
        <td>{$menu_link}</td>
        </tr>";

	}

	echo "</tbody></table></div></div><center><button type=\"submit\" class=\"btn bg-teal btn-raised position-left legitRipple\"><i class=\"fa fa-floppy-o position-left\"></i>Сохранить</button></center>";

	echo <<<HTML
<script>
function checkxf ()	{

		var status = '';

		{$save}

		if(document.addcollections.name.value == ''){

			Growl.error({
				title: '{$lang['p_info']}',
				text: 'Название является обязательным'
			});

			status = 'fail';

		}

		return status;

};
	
  jQuery(function($){
	  
	$('#news_ids').tokenfield({
	  autocomplete: {
	    source: 'engine/ajax/controller.php?mod=find_news&user_hash={$dle_login_hash}',
		minLength: 3,
	    delay: 500
	  },
	  createTokensOnBlur:true
	});

	$('.dellink').click(function(){
		
		name = $('#content_'+$(this).attr('uid')).text();
		id = $(this).attr('uid');

	    DLEconfirm( 'Вы уверены, что хотите удалить <b>&laquo;'+name+'&raquo;</b> из подборок ?', '{$lang['p_confirm']}', function () {

		$.post('/admin.php?mod=news_collections&action=list', {id: id, is:'delete', user_hash:'{$dle_login_hash}'}, function(data) {
			
        if(data == 'ok'){
          $('#content_'+ id).parent().remove();
        }
		
		});		

		} );

		return false;
	});	 

	new qq.FileUploader({
		element: document.getElementById('uploads_poster'),
		action: 'admin.php?mod=news_collections&action=upload_poster',
		maxConnections: 1,
		multiple: false,
		allowdrop: false,
		encoding: 'multipart',
        sizeLimit: 0,
		allowedExtensions: ['jpg', 'jpeg', 'png'],
	    params: {"user_hash" : "{$dle_login_hash}", "id" : "0"},
        template: '<div class="qq-uploader">' + 
                '<div id="uploadedfile_poster" class="clrfix"></div>' +
                '<div class="qq-upload-button btn btn-green bg-teal btn-sm btn-raised" style="width: auto;">Загрузить изображение</div>' +
                '<ul class="qq-upload-list" style="display:none;"></ul>' + 
             '</div>',
		onSubmit: function(id, fileName) {

					$('<div id="uploadfile-'+id+'" class="file-box"><span class="qq-upload-file-status">Загрузка файла:</span><span class="qq-upload-file">&nbsp;'+fileName+'</span>&nbsp;<span class="qq-status"><span class="qq-upload-spinner"></span><span class="qq-upload-size"></span></span><div class="progress "><div class="progress-bar progress-blue" style="width: 0%"><span>0%</span></div></div></div>').appendTo('#uploaded_poster');

        },
		onProgress: function(id, fileName, loaded, total){
					$('#uploadfile-'+id+' .qq-upload-size').text(DLEformatSize(loaded)+' из '+DLEformatSize(total));
					var proc = Math.round(loaded / total * 100);
					$('#uploadfile-'+id+' .progress-bar').css( "width", proc + '%' );
					$('#uploadfile-'+id+' .qq-upload-spinner').css( "display", "inline-block");

		},
		onComplete: function(id, fileName, response){

						if ( response.success ) {
							var returnbox = response.returnbox;
							var returnval = response.link;

							returnbox = returnbox.replace(/&lt;/g, "<");
							returnbox = returnbox.replace(/&gt;/g, ">");
							returnbox = returnbox.replace(/&amp;/g, "&");

							$('#uploadfile-'+id+' .qq-status').html('успешно завершена');
							$('#uploadedfile_poster').html( returnbox );
							$('#poster').val(returnval);
							
							setTimeout(function() {
								$('#uploadfile-'+id).fadeOut('slow', function() { $(this).remove(); });
							}, 1000);

						} else {
							$('#uploadfile-'+id+' .qq-status').html('завершилось ошибкой');

							if( response.error ) $('#uploadfile-'+id+' .qq-status').append( '<br /><span class="text-danger">' + response.error + '</span>' );

							setTimeout(function() {
								$('#uploadfile-'+id).fadeOut('slow');
							}, 4000);
						}
		},
        messages: {
            typeError: "Файл {file} имеет неверное расширение. Только {extensions} разрешены к загрузке.",
            sizeError: "Файл {file} слишком большого размера, максимально допустимый размер файлов: {sizeLimit}.",
            emptyError: "Файл {file} пустой, выберите файлы повторно."
        },
		debug: false
    });	
	
  });
</script>  
HTML;
	
	echofooter();
} else if( $action == "edit" ) {
	
	if( $_REQUEST['is'] == "save" ) {
		
	if( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		msg( "error", array('javascript:history.go(-1)' => "Изменение подборки", '' => $lang['addnews_error'] ), $lang['sess_error'], "javascript:history.go(-1)" );
	
	}
	
	$id = intval( $_GET['id'] );
	
	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_news_collections WHERE id = '{$id}'" );
	
	if( !$row  ) {
		
		msg( "error", array('javascript:history.go(-1)' => "Изменение подборки", '' => $lang['addnews_error'] ), "Подборки не найдено.", "javascript:history.go(-1)" );
			
	}
	
	if( !is_dir( ENGINE_DIR . "/cache/system/collections_title/" ) ) {
			
		@mkdir( ENGINE_DIR . "/cache/system/collections_title/", 0777 );
		@chmod( ENGINE_DIR . "/cache/system/collections_title/", 0777 );

	}	
	
	@header('X-XSS-Protection: 0;');

	include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));
	
	$parse = new ParseFilter();	
	
	$name = $parse->process(  trim( strip_tags ($_POST['name']) ) );
	$news_ids = $_POST['news_ids'] ? $parse->process( $_POST['news_ids'] ) : '';
	
	if( $news_ids ) {
		
		$news_ids = explode(', ', $news_ids);
	
		$num_elem = count($news_ids);
		$news_ids = implode(',',$news_ids);
		
		
		if( $news_ids != $row['news_ids'] ) {
			
			$news_ids_t = explode(',', $news_ids);
			$post = $db->super_query( "SELECT id, title FROM " . PREFIX . "_post WHERE id regexp '[[:<:]](" . implode('|', $news_ids_t) . ")[[:>:]]'", true );
			$title_p = array();
			foreach( $post as $val ){
				
				$title_p[$val['id']] = $val['title'];
				
			}
			set_vars ( "collections_title_" . $row['id'], $title_p, "/cache/system/collections_title/" );
		}
		
	} else $num_elem = 0;
	
	if ( $config['allow_admin_wysiwyg'] ) $parse->allow_code = false;
	
	$descr = $parse->process( $_POST['short_story'] );
	
	if( $config['allow_admin_wysiwyg'] OR $allow_br != '1' ) $descr = $db->safesql( $parse->BB_Parse( $descr ) );
	else $descr = $db->safesql( $parse->BB_Parse( $descr, false ) );

	if( $parse->not_allowed_text ) {
		msg( "error", array('javascript:history.go(-1)' => "Добавление новой подборки на сайт", '' => $lang['addnews_error'] ), "Ваше описание или имя содержит недопустимый текст.", "javascript:history.go(-1)" );
	}
	
	$alt_url = trim($_POST['alt_url']);
	$poster = trim($_POST['poster']);
	
	if( $poster ) {

		$url_image = explode( "/", $poster );
				
		if( count( $url_image ) == 2 ) {
					
			$folder_prefix = $url_image[0] . "/";
			$image = $url_image[1];
				
		} else {
					
			$folder_prefix = "";
			$image = $url_image[0];
			
		}

		$image = totranslit($image);	
		$poster = $folder_prefix . $image;
	} else $poster = '';
	
	if(!$alt_url) $alt_url = totranslit( stripslashes( $name ), true, false );
	else $alt_url = totranslit( stripslashes( $alt_url ), true, false );
	
	if( dle_strlen( $alt_url, $config['charset'] ) > 190 ) {
		$alt_url = dle_substr( $alt_url, 0, 190, $config['charset'] );
	}
	
	$name = $db->safesql( $name );
	$alt_url = $db->safesql( $alt_url );
	
	$metatags = create_metatags( $descr );
	
	$added_time = time();
	$thistime = date( "Y-m-d H:i:s", $added_time );
	
	if( !$name ) {
		msg( "error", array('javascript:history.go(-1)' => "Сохранение подборки", '' => $lang['addnews_error'] ), "Имя является обязательным при сохранении подборки.", "javascript:history.go(-1)" );
		
	}

	if( dle_strlen( $name, $config['charset'] ) > 190 ) {
		msg( "error", array('javascript:history.go(-1)' => "Сохранение подборки", '' => $lang['addnews_error'] ), "Слишком длинное имя.", "javascript:history.go(-1)" );
	}
	
	$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$_TIME}', '{$_IP}', '48', 'Изменение подборки: {$name}-{$id}')" );
		
	$db->query("UPDATE ".PREFIX."_news_collections SET name = '{$name}', alt_url = '{$alt_url}', descr = '{$descr}', news_ids = '{$news_ids}', num_elem = '{$num_elem}', metatitle = '{$metatags['title']}', keywords = '{$metatags['keywords']}', cover = '{$poster}', date = '{$thistime}' WHERE id = '{$id}'");	



	if( $news_ids != $row['news_ids'] ) {
		
		$news_ids = explode(',' ,$news_ids);
		
		$add_collections = array_diff ($news_ids, explode(',',$row['news_ids']));
		$remove_collections = $row['news_ids'] ? array_diff (explode(',',$row['news_ids']), $news_ids) : 0;
	
		if( count($add_collections) ) {
			
		foreach( $add_collections as $val ) {
			
			$val = intval($val);
			
			$row2 = $db->super_query("SELECT collections FROM ".PREFIX."_post WHERE id = '{$val}'");
			
			if( $row2['collections'] ) {
				
				$row2['collections'] = explode(',', $row2['collections']);
				$row2['collections'][] = $row['id'];
				$row2['collections'] = array_unique($row2['collections']);
				$new_collections = implode(',', $row2['collections']);
				
			} else $new_collections = $row['id'];
			
			$db->query("UPDATE ".PREFIX."_post SET collections = '{$new_collections}' WHERE id = '{$val}'");

		}			
			
		}
		
		if( is_array($remove_collections) AND count($remove_collections) ) {
	
		foreach( $remove_collections as $val ) {
			
			$val = intval($val);
			
			$row2 = $db->super_query("SELECT collections FROM ".PREFIX."_post WHERE id = '{$val}'");
			
			$list = explode( ",", $row2['collections'] );
			$i = 0;
	
			foreach ( $list as $cid ) {

				if( $cid == $id ) unset( $list[$i] );
				$i ++;

			}
	
			if( count( $list ) ) $new_collections = implode( ",", $list );
			else $new_collections = "";
			
			$db->query("UPDATE ".PREFIX."_post SET collections = '{$new_collections}' WHERE id = '{$val}'");

		}			

		}	
		
	}
	@unlink( ENGINE_DIR . '/cache/system/collections.php' );
	msg( "success", "Подборка изменена", "Подборка" . " \"" . stripslashes( stripslashes( $name ) ) . "\" " . "изменена ", array('?mod=news_collections&action=list' => 'Назад к списку' ) );

	}

	$_SESSION['admin_referrer'] = "?mod=news_collections&amp;action=list";	
	
	include_once (DLEPlugins::Check(ENGINE_DIR . '/classes/parse.class.php'));

	$parse = new ParseFilter();
	
	$id = intval( $_GET['id'] );

	$row = $db->super_query( "SELECT * FROM " . PREFIX . "_news_collections WHERE id = '$id'" );

	$found = FALSE;

	if( $id == $row['id'] ) $found = TRUE;
	if( !$found ) {
		msg( "error", $lang['cat_error'], "- Не найдена подборка -" );
	}
	
	$row['name'] = $parse->decodeBBCodes( $row['name'], false );
	$row['descr'] = $parse->decodeBBCodes( $row['descr'], false );
	$row['keywords'] = $parse->decodeBBCodes( $row['keywords'], false );
	$row['metatitle'] = stripslashes( $row['metatitle'] );
	
	$insert_news_ids = array();
	
	$tmp_news_ids = get_vars ( "collections_title_" . $row['id'], "/cache/system/collections_title/" );
	
	if(!is_array( $tmp_news_ids )){
		
		if( !is_dir( ENGINE_DIR . "/cache/system/collections_title/" ) ) {
			
			@mkdir( ENGINE_DIR . "/cache/system/collections_title/", 0777 );
			@chmod( ENGINE_DIR . "/cache/system/collections_title/", 0777 );

		}		
		
		$tmp_news_ids = explode(',',$row['news_ids']);
		
		$post = $db->super_query( "SELECT id, title FROM " . PREFIX . "_post WHERE id regexp '[[:<:]](" . implode('|', $tmp_news_ids) . ")[[:>:]]'", true );
		$tmp_news_ids = array();
		foreach( $post as $val ) {
			$tmp_news_ids[$val['id']] = $val['title'];
		}
		set_vars ( "collections_title_" . $row['id'], $tmp_news_ids, "/cache/system/collections_title/" );		
		
	}
	
	foreach( $tmp_news_ids as $key => $val ){
		$val = trim($val);
		
		$insert_news_ids[] = '{ value: "'.$key.'", label: "'.$val.'" }';

	}
			  
	$news_ids_input = implode(',', $insert_news_ids);	
	
	if( $row['cover'] ) {
		$path_parts = pathinfo($row['cover']);

		if( file_exists(ROOT_DIR . "/uploads/posts/" .$path_parts['dirname']."/".$path_parts['basename']) ) {
			$img_url = 	$config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];
		}
				
		$up_image = "<div class=\"uploadedfile\"><div class=\"info\">{$path_parts['basename']}</div><div class=\"uploadimage\"><img style=\"width:auto;height:auto;max-width:100px;max-height:90px;\" src=\"" . $img_url . "\" /></div><div class=\"info\"><a href=\"#\" onclick=\"imagedelete(\\'".$row['cover']."\\');return false;\">Удалить</a></div></div>";
			
	} else $up_image = "";	
	
	if( $config['allow_admin_wysiwyg'] == 1 ) {
		$js_array[] = "engine/skins/codemirror/js/code.js";
		$js_array[] = "engine/editor/jscripts/froala/editor.js";
		$js_array[] = "engine/editor/jscripts/froala/languages/{$lang['wysiwyg_language']}.js";
		$css_array[] = "engine/editor/jscripts/froala/css/editor.css";
	}
	
	if( $config['allow_admin_wysiwyg'] == 2 ) {
		$js_array[] = "engine/editor/jscripts/tiny_mce/tinymce.min.js";
	}
	
	if( !$config['allow_admin_wysiwyg'] ) {
		$js_array[] = "engine/classes/js/typograf.min.js";
	}

	$js_array[] = "engine/classes/uploads/html5/fileuploader.js?v=24";
	
	echoheader( "<i class=\"fa fa-pencil-square-o position-left\"></i><span class=\"text-semibold\">Редактирование подборки</span>", array($_SESSION['admin_referrer'] => $lang['edit_all_title'], '' => "Редактирование подборки" ) );	
	
	if ( !$user_group[$member_id['user_group']]['allow_html'] ) $config['allow_admin_wysiwyg'] = 0;
	
	echo <<<HTML
<style>
.ui-autocomplete{z-index:100 !important;}
.tokenfield.form-control{max-width:100%;}
.editor-panel{max-width:100%;}
</style>
<div class="panel panel-default">
			<form method="post" class="form-horizontal" name="addnews" id="addnews" onsubmit="if(checkxf()=='fail') return false;" action="">
                 <div class="panel-tab-content tab-content">
						<div class="panel-body">

							<div class="form-group">
							  <label class="control-label col-sm-2">Название</label>
							  <div class="col-sm-10">
								<input type="text" class="form-control" name="name" id="name" value="{$row['name']}" autocomplete="off" maxlength="190">
							  </div>
							 </div>

							<div class="form-group">
							  <label class="control-label col-sm-2">ЧПУ URL подборки</label>
							  <div class="col-sm-10">
								<input type="text" class="form-control" name="alt_url" id="alt_url" value="{$row['alt_url']}" autocomplete="off" maxlength="190">
							  </div>
							 </div>

							<div class="form-group">
							  <label class="control-label col-sm-2">Новости</label>
							  <div class="col-sm-10">
								<input id="news_ids" name="news_ids" type="text" class="form-control" autocomplete="off">
							  </div>
							 </div>

							<div class="form-group">
							  <label class="control-label col-sm-2">Мета тег TITLE</label>
							  <div class="col-sm-10">
								<input type="text" class="form-control" name="meta_title" id="meta_title" value="{$row['metatitle']}" autocomplete="off" maxlength="190">
							  </div>
							 </div>

							<div class="form-group">
							  <label class="control-label col-sm-2">Мета тег KEYWORDS</label>
							  <div class="col-sm-10">
								<input type="text" class="form-control" name="keywords" id="keywords" value="{$row['keywords']}" autocomplete="off" maxlength="190">
							  </div>
							 </div>							 
							 
							 <div class="form-group editor-group">
							  <label class="control-label col-md-2">Описание</label>
							  <div class="col-md-12">
HTML;

	if( $config['allow_admin_wysiwyg'] ) {
		
		$mod = 'collections';
		$row['short_story'] = $row['descr'];
		include (DLEPlugins::Check(ENGINE_DIR . '/editor/shortnews.php'));

	} else {

		$bb_editor = true;
		include (DLEPlugins::Check(ENGINE_DIR . '/inc/include/inserttag.php'));
		echo "<div class=\"editor-panel\"><div class=\"shadow-depth1\">{$bb_code}<textarea class=\"editor\" style=\"width:100%;height:300px;\" onfocus=\"setFieldName(this.name)\" name=\"short_story\" id=\"short_story\" >{$row['descr']}</textarea></div></div>";
	}
	
echo <<<HTML
							  </div>
							</div>
							<div class="form-group">
							  <label class="control-label col-sm-2">Постер</label>
							  <div class="col-sm-10">
								<div id="uploaded_poster"></div>
								<div id="uploads_poster"></div>
								<input type="hidden" name="poster" id="poster" value="{$row['cover']}" />
							  </div>
							 </div>								
						</div>
				</div>
				<div class="panel-footer">
					<button type="submit" class="btn bg-teal btn-sm btn-raised position-left"><i class="fa fa-floppy-o position-left"></i>{$lang['news_save']}</button>
					<input type="hidden" name="id" value="$id" />
					<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
					<input type="hidden" name="action" value="edit" />
					<input type="hidden" name="is" value="save" />
					<input type="hidden" name="mod" value="news_collections" />
				</div>				
			</form>
</div>
<script>
function checkxf ()	{

		var status = '';

		{$save}

		if(document.addcollections.name.value == ''){

			Growl.error({
				title: '{$lang['p_info']}',
				text: '{$lang['addnews_alert']}'
			});

			status = 'fail';

		}

		return status;

};

function imagedelete( value ) {
		
		DLEconfirm( 'Вы действительно хотите удалить изображение?', 'Информация', function () {
		
			ShowLoading('');
			
			$.post('admin.php?mod=news_collections&action=upload_poster', { is: 'delete', user_hash: '{$dle_login_hash}', poster:value }, function(data){
	
				HideLoading('');
				
				$('#uploadedfile_poster').html('');
				$('#poster').val('');

			});
			
		} );

		return false;

};

  jQuery(function($){
	  
	$('#news_ids').tokenfield({
	  autocomplete: {
	    source: 'engine/ajax/controller.php?mod=find_news&user_hash={$dle_login_hash}',
		minLength: 3,
		delimiter: ',',
	    delay: 500
	  },
	  createTokensOnBlur:true
	});

	  
	$('#news_ids').tokenfield('setTokens', [{$news_ids_input}]);
	
	new qq.FileUploader({
		element: document.getElementById('uploads_poster'),
		action: 'admin.php?mod=news_collections&action=upload_poster',
		maxConnections: 1,
		multiple: false,
		allowdrop: false,
		encoding: 'multipart',
        sizeLimit: 0,
		allowedExtensions: ['jpg', 'jpeg', 'png'],
	    params: {"user_hash" : "{$dle_login_hash}", "id" : "{$id}"},
        template: '<div class="qq-uploader">' + 
                '<div id="uploadedfile_poster" class="clrfix">{$up_image}</div>' +
                '<div class="qq-upload-button btn btn-green bg-teal btn-sm btn-raised" style="width: auto;">Загрузить изображение</div>' +
                '<ul class="qq-upload-list" style="display:none;"></ul>' + 
             '</div>',
		onSubmit: function(id, fileName) {

					$('<div id="uploadfile-'+id+'" class="file-box"><span class="qq-upload-file-status">Загрузка файла:</span><span class="qq-upload-file">&nbsp;'+fileName+'</span>&nbsp;<span class="qq-status"><span class="qq-upload-spinner"></span><span class="qq-upload-size"></span></span><div class="progress "><div class="progress-bar progress-blue" style="width: 0%"><span>0%</span></div></div></div>').appendTo('#uploaded_poster');

        },
		onProgress: function(id, fileName, loaded, total){
					$('#uploadfile-'+id+' .qq-upload-size').text(DLEformatSize(loaded)+' из '+DLEformatSize(total));
					var proc = Math.round(loaded / total * 100);
					$('#uploadfile-'+id+' .progress-bar').css( "width", proc + '%' );
					$('#uploadfile-'+id+' .qq-upload-spinner').css( "display", "inline-block");

		},
		onComplete: function(id, fileName, response){

						if ( response.success ) {
							var returnbox = response.returnbox;
							var returnval = response.link;

							returnbox = returnbox.replace(/&lt;/g, "<");
							returnbox = returnbox.replace(/&gt;/g, ">");
							returnbox = returnbox.replace(/&amp;/g, "&");

							$('#uploadfile-'+id+' .qq-status').html('успешно завершена');
							$('#uploadedfile_poster').html( returnbox );
							$('#poster').val(returnval);
							
							setTimeout(function() {
								$('#uploadfile-'+id).fadeOut('slow', function() { $(this).remove(); });
							}, 1000);

						} else {
							$('#uploadfile-'+id+' .qq-status').html('завершилось ошибкой');

							if( response.error ) $('#uploadfile-'+id+' .qq-status').append( '<br /><span class="text-danger">' + response.error + '</span>' );

							setTimeout(function() {
								$('#uploadfile-'+id).fadeOut('slow');
							}, 4000);
						}
		},
        messages: {
            typeError: "Файл {file} имеет неверное расширение. Только {extensions} разрешены к загрузке.",
            sizeError: "Файл {file} слишком большого размера, максимально допустимый размер файлов: {sizeLimit}.",
            emptyError: "Файл {file} пустой, выберите файлы повторно."
        },
		debug: false
    });
	
  });
</script> 
HTML;
	
	echofooter();
} else if( $action == "upload_poster" ) {
	
	if( $_REQUEST['user_hash'] == "" OR $_REQUEST['user_hash'] != $dle_login_hash ) {
		
		echo "{\"error\":\"{$lang['sess_error']}\"}";
		die();
	
	}	
	
	if( $_REQUEST['is'] == "delete" ) {
		
		if( isset( $_POST['poster'] ) ) {
			
		$url_image = explode( "/", $_POST['poster'] );
		
		$folder_prefix = $url_image[0] . "/";
		$image = $url_image[1];
		$image = totranslit($image);
	
		@unlink( ROOT_DIR . "/uploads/posts/" . $folder_prefix . $image );	
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$added_time}', '{$_IP}', '37', '{$url_image[1]}')" );
		die();
		} else die('err');
	}
	
	$allowed_extensions = array ("jpg", "png", "jpeg" );
	
	$row = $db->super_query( "SELECT id, name FROM " . PREFIX . "_news_collections WHERE id = '$id'" );
	
	if( $row ) $cover_name = totranslit($row['name']) . '_' . $row['id'];	
	else $cover_name = "uploaded_cover_0";
	
	if( !is_dir( ROOT_DIR . "/uploads/posts/" . date( "Y-m" ) . "/" ) ) {
			
		@mkdir( ROOT_DIR . "/uploads/posts/" . date( "Y-m" ) . "/", 0777 );
		@chmod( ROOT_DIR . "/uploads/posts/" . date( "Y-m" ) . "/", 0777 );
		@mkdir( ROOT_DIR . "/uploads/posts/" . date( "Y-m" ) . "/" . "thumbs", 0777 );
		@chmod( ROOT_DIR . "/uploads/posts/" . date( "Y-m" ) . "/" . "thumbs", 0777 );

	}
	
	if( !is_dir( ROOT_DIR . "/uploads/posts/" . date( "Y-m" ) . "/" ) ) {

		return "{\"error\":\"".$lang['upload_error_0']." /uploads/posts/" . date( "Y-m" ) ."/\"}";
	
	}
	
	$filename = check_filename( getFileName() );
	
	if ( !$filename ) {
		
		return "{\"error\":\"". $lang['upload_error_4'] ."\"}";
		
    }

	$filename_arr = explode( ".", $filename );
	$type = end( $filename_arr );

	if ( !$type ) {
		
		return "{\"error\":\"".$lang['upload_error_4'] ."\"}";
		
    }

	$error_code = getErrorCode();

	if ( $error_code ) {
		
		return "{\"error\":\"". $error_code ."\"}";
		
    }
		
	$size = getFileSize();
		
    if ( !$size ) {
		
        return "{\"error\":\"". $lang['upload_error_5'] ."\"}";
		
    }
	
	$uploaded_filename = saveFile(ROOT_DIR . "/uploads/posts/".date( "Y-m" )."/", $cover_name . '.' . $type);	

	if ( $uploaded_filename ) {

		@chmod( ROOT_DIR . "/uploads/posts/".date( "Y-m" )."/" . $uploaded_filename, 0666 );

		$i_info = @getimagesize(ROOT_DIR . "/uploads/posts/".date( "Y-m" )."/" . $uploaded_filename); 
		
		if( !in_array( $i_info[2], array (1, 2, 3 ) ) )	{
			@unlink( ROOT_DIR . "/uploads/posts/".date( "Y-m" )."/" . $uploaded_filename );
			return "{\"error\":\"". $lang['upload_error_6'] ."\"}";
		}

				
		$db->query( "INSERT INTO " . USERPREFIX . "_admin_logs (name, date, ip, action, extras) values ('".$db->safesql($member_id['name'])."', '{$added_time}', '{$_IP}', '36', 'Загружает файл обложки: {$uploaded_filename}, для колекции ".$db->safesql($row['name'])."-{$row['id']}')" );

		$img_url = $data_url = $config['http_home_url'] . "uploads/posts/" . date( "Y-m" )."/" . $uploaded_filename;

		$link = date( "Y-m" )."/" . $uploaded_filename;
		
		$return_box = "<div class=\"uploadedfile\"><div class=\"info\">{$uploaded_filename}</div><div class=\"uploadimage\"><a class=\"uploadfile\" href=\"{$data_url}\" data-src=\"{$data_url}\" data-type=\"image\"><img style=\"width:auto;height:auto;max-width:100px;max-height:90px;\" src=\"" . $img_url . "\" /></a></div><div class=\"info\">{$i_info[0]}x{$i_info[1]} <a href=\"#\" onclick=\"imagedelete('".$link."');return false;\">Удалить</a></div></div>";


	} else return "{\"error\":\"". $lang['images_uperr_3'] ."\"}";
	
	$return_box = addcslashes($return_box, "\t\n\r\"\\/");
	echo htmlspecialchars("{\"success\":true, \"returnbox\":\"{$return_box}\", \"link\":\"{$link}\"}", ENT_NOQUOTES, $config['charset']);
		
}
?>
