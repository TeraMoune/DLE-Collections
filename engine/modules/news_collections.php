<?php
if( !defined('DATALIFEENGINE') ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

$collections_id = intval($_GET['id']);
if( isset( $_GET['action'] ) and $_GET['action'] == "favorites" ) {
	if( $config['allow_alt_url'] ) $fav_u = "/favorites";
	else $fav_u = "&action=favorites";
	$is_fav = 1;
} else {
	$fav_u = "";
	$is_fav = 0;
}


if ($cstart) {
	$cstart = $cstart - 1;
	$cstart = $cstart * $config['collection_number'];
}	

if( $collections_id ) {

	$config['collection_news_number'] = $config['collection_news_number'] ? $config['collection_news_number'] : 10;
	$collections = $db->super_query("SELECT * FROM `".PREFIX."_news_collections` WHERE id = '{$collections_id}'");
	$seo_title = $collections['metatitle'] ? $collections['metatitle'] : $collections['name'];
	$seo_tags = $collections['keywords'];
	$seo_descr = strip_tags($collections['descr']);
	$collections['news_ids'] = explode(',',$collections['news_ids']);
	if ( $is_logged and ( $user_group[$member_id['user_group']]['allow_edit'] and !$user_group[$member_id['user_group']]['allow_all_edit'] ) ) $config['allow_cache'] = false;
	if ( isset($_SESSION['dle_no_cache']) AND $_SESSION['dle_no_cache'] ) $config['allow_cache'] = false;
	if ( $cstart ) $cache_id = ($cstart / $config['collection_news_number']) + 1;
	else $cache_id = 1;
				
	$config['max_cache_pages'] = intval($config['max_cache_pages']);
	if( $config['max_cache_pages'] < 3 ) $config['max_cache_pages'] = 3;

	if ( $config['allow_cache'] AND $cache_id <= $config['max_cache_pages'] ) {
		$active = dle_cache( "collections_news", $cache_id, true );
		$short_news_cache = true;
	} else {
		$active = false;
		$short_news_cache = false;
	}	
	
	if ( $active ) {

		$tpl->result['content'] .= $active;
		$active = null;
		$news_found = true;
			
	} else {
		
		$news_sort_by = ($config['collections_news_sort']) ? $config['collections_news_sort'] : $config['news_sort'];
		$news_direction_by = ($config['collections_news_msort']) ? $config['collections_news_msort'] : $config['news_msort'];		
		
		$sql_select = "SELECT p.id, p.autor, p.date, p.short_story, CHAR_LENGTH(p.full_story) as full_story, p.xfields, p.title, p.category, p.alt_name, p.comm_num, p.allow_comm, p.fixed, p.tags, e.news_read, e.allow_rate, e.rating, e.vote_num, e.votes, e.view_edit, e.editdate, e.editor, e.reason FROM " . PREFIX . "_post p LEFT JOIN " . PREFIX . "_post_extras e ON (p.id=e.news_id) WHERE id regexp '[[:<:]](" . implode('|', $collections['news_ids']) . ")[[:>:]]' AND approve=1 ORDER BY " . $news_sort_by . " " . $news_direction_by . " LIMIT " . $cstart . "," . $config['collection_news_number'];
		$sql_count = "SELECT COUNT(*) as count FROM " . PREFIX . "_post WHERE id regexp '[[:<:]](" . implode('|', $collections['news_ids']) . ")[[:>:]]' AND approve=1";
		$allow_active_news = true;
		$view_template = "collections";
		$config['news_number'] = $config['collection_news_number'];
		include_once (DLEPlugins::Check(ENGINE_DIR . '/modules/show.short.php'));
			
		if ($config['files_allow']) if (strpos ( $tpl->result['content'], "[attachment=" ) !== false) {
			$tpl->result['content'] = show_attach ( $tpl->result['content'], $attachments );
		}
				
		if ($news_found AND $cache_id <= $config['max_cache_pages'] ) create_cache ( "collections_news", $tpl->result['content'], $cache_id . $cache_prefix, true );

	}
	
} else {
	
	if( $is_fav ) {
		
		$fav_t = explode(',', $member_id['favorites_collections']);
		$fav = "id regexp '[[:<:]](" . implode('|', $fav_t) . ")[[:>:]]'";
		
	} else $fav = "1";		

	
	$config['collection_number'] = $config['collection_number'] ? $config['collection_number'] : 10;
	
	$count_all = $db->super_query( "SELECT COUNT(*) as count FROM " . PREFIX . "_news_collections WHERE {$fav}" );
	$count_all = $count_all['count'];	
	$news_count = $cstart;
	$db->query( "SELECT * FROM " . PREFIX . "_news_collections WHERE {$fav} ORDER BY date DESC LIMIT " . $cstart . "," . $config['collection_number']);
	
	$collections_found = false;

	$tpl->load_template( 'collections_item.tpl' );

	while ( $row = $db->get_row() ) {
	
		$collections_found = true;
		$row['date'] = strtotime( $row['date'] );
		$row['create_date'] = strtotime( $row['create_date'] );
		
		if( $is_logged ) {
			
			$fav_arr = explode( ',', $member_id['favorites_collections'] );
			
			if( !in_array( $row['id'], $fav_arr ) ) {

				$tpl->set( '{favorites}', "<a id=\"fav-id-" . $row['id'] . "\" class=\"collections_fav\" href=\"#\" onclick=\"doFavorites_collections('" . $row['id'] . "', 'plus', 0); return false;\" title=\"" . $lang['news_addfav'] . "\"><svg class=\"icon icon-fav\"><use xlink:href=\"#icon-fav\"></use></svg></a>" );
				$tpl->set( '[add-favorites]', "<a id=\"fav-id-" . $row['id'] . "\" class=\"collections_fav\" onclick=\"doFavorites_collections('" . $row['id'] . "', 'plus', 0); return false;\" href=\"#\">" );
				$tpl->set( '[/add-favorites]', "</a>" );
				$tpl->set_block( "'\\[del-favorites\\](.*?)\\[/del-favorites\\]'si", "" );
			} else { 

				$tpl->set( '{favorites}', "<a id=\"fav-id-" . $row['id'] . "\" class=\"collections_fav\" href=\"#\" onclick=\"doFavorites_collections('" . $row['id'] . "', 'minus', 0); return false;\" title=\"" . $lang['news_minfav'] . "\"><svg class=\"icon icon-star\"><use xlink:href=\"#icon-star\"></use></svg></a>" );
				$tpl->set( '[del-favorites]', "<a id=\"fav-id-" . $row['id'] . "\" class=\"collections_fav\" onclick=\"doFavorites_collections('" . $row['id'] . "', 'minus', 0); return false;\" href=\"#\">" );
				$tpl->set( '[/del-favorites]', "</a>" );
				$tpl->set_block( "'\\[add-favorites\\](.*?)\\[/add-favorites\\]'si", "" );
			}
		
		} else {
			$tpl->set( '{favorites}', "" );
			$tpl->set_block( "'\\[add-favorites\\](.*?)\\[/add-favorites\\]'si", "" );
			$tpl->set_block( "'\\[del-favorites\\](.*?)\\[/del-favorites\\]'si", "" );
		}		
		
		
		if( $row['cover'] AND file_exists( ROOT_DIR . '/uploads/posts/' . $row['cover'] ) ) {

			$tpl->set( '{cover}', $config['http_home_url'] . 'uploads/posts/' . $row['cover'] );
		
		} else $tpl->set( '{cover}', '{THEME}/dleimages/no_image.jpg'  );
	
		if( $config['allow_alt_url'] ) $url = $config['http_home_url'] . 'collections/' . $row['id'] . '-' . $row['alt_url'];
		else $url = $config['http_home_url'] . '?do=collections&id=' . $row['id'];
	
		$tpl->set( '{title}', str_replace("&amp;amp;", "&amp;", htmlspecialchars( $row['name'], ENT_QUOTES, $config['charset'] ) ) );
		
		if ( preg_match( "#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
			$count= intval($matches[1]);
			$row['name'] = strip_tags( $row['name'] );

			if( $count AND dle_strlen( $row['name'], $config['charset'] ) > $count ) {
		
				$row['name'] = dle_substr( $row['name'], 0, $count, $config['charset'] );
					
				if( ($temp_dmax = dle_strrpos( $row['name'], ' ', $config['charset'] )) ) $row['name'] = dle_substr( $row['name'], 0, $temp_dmax, $config['charset'] );
				
			}
			$tpl->set( $matches[0], str_replace("&amp;amp;", "&amp;", htmlspecialchars( $row['name'], ENT_QUOTES, $config['charset'] ) ) );
		
		}
		
		if( date( 'Ymd', $row['date'] ) == date( 'Ymd', $_TIME ) ) {
			
			$tpl->set( '{date}', $lang['time_heute'] . langdate( ", H:i", $row['date'] ) );
		
		} elseif( date( 'Ymd', $row['date'] ) == date( 'Ymd', ($_TIME - 86400) ) ) {
			
			$tpl->set( '{date}', $lang['time_gestern'] . langdate( ", H:i", $row['date'] ) );
		
		} else {
			
			$tpl->set( '{date}', langdate( ( $config['collection_timestamp_active'] ? $config['collection_timestamp_active'] : $config['timestamp_active'] ), $row['date'] ) );
		
		}

		if( date( 'Ymd', $row['create_date'] ) == date( 'Ymd', $_TIME ) ) {
			
			$tpl->set( '{create_date}', $lang['time_heute'] . langdate( ", H:i", $row['create_date'] ) );
		
		} elseif( date( 'Ymd', $row['create_date'] ) == date( 'Ymd', ($_TIME - 86400) ) ) {
			
			$tpl->set( '{create_date}', $lang['time_gestern'] . langdate( ", H:i", $row['create_date'] ) );
		
		} else {
			
			$tpl->set( '{create_date}', langdate( ( $config['collection_timestamp_active'] ? $config['collection_timestamp_active'] : $config['timestamp_active'] ), $row['create_date'] ) );
		
		}

		$news_date = $row['date'];
		$tpl->copy_template = preg_replace_callback ( "#\{date=(.+?)\}#i", "formdate", $tpl->copy_template );
		$news_date = $row['create_date'];
		$tpl->copy_template = preg_replace_callback ( "#\{create_date=(.+?)\}#i", "formdate", $tpl->copy_template );		
		
		if( $row['descr'] ) {
			
			$row['descr'] = stripslashes($row['descr']);
			$tpl->set( '{descr}', $row['descr'] );
			
			if ( preg_match( "#\\{descr limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches ) ) {
				$count= intval($matches[1]);
				
				$row['descr'] = preg_replace( "#<!--dle_spoiler(.+?)<!--spoiler_text-->#is", "", $row['descr'] );
				$row['descr'] = preg_replace( "#<!--spoiler_text_end-->(.+?)<!--/dle_spoiler-->#is", "", $row['descr'] );	
				$row['descr'] = preg_replace( "'\[attachment=(.*?)\]'si", "", $row['descr'] );
				$row['descr'] = preg_replace ( "#\[hide(.*?)\](.+?)\[/hide\]#is", "", $row['descr'] );
					
				$row['descr'] = str_replace( "><", "> <", $row['descr'] );
				$row['descr'] = strip_tags( $row['descr'], "<br>" );
				$row['descr'] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $row['descr'] ) ) ) ));
				$row['descr'] = preg_replace('/\s+/u', ' ', $row['descr']);

				if( $count AND dle_strlen( $row['descr'], $config['charset'] ) > $count ) {
						
					$row['descr'] = dle_substr( $row['descr'], 0, $count, $config['charset'] );
						
					if( ($temp_dmax = dle_strrpos( $row['descr'], ' ', $config['charset'] )) ) $row['descr'] = dle_substr( $row['descr'], 0, $temp_dmax, $config['charset'] );
					
				}

				$tpl->set( $matches[0], $row['descr'] );

			}
			
			$tpl->set( '[not-descr]', '' );
			$tpl->set( '[/not-descr]', '' );
		
		} else $tpl->set_block( "'\\[not-descr\\](.*?)\\[/not-descr\\]'si", "" );
		
		$tpl->set( '{num_elem}', $row['num_elem'] );
		$tpl->set( '{url}', $url );

		$tpl->compile( 'content' );	
	
		$news_count++;
	}

	$db->free();

	if( $collections_found ) {
		
		$tpl->load_template( 'navigation.tpl' );
		
		//----------------------------------
		// Previous link
		//----------------------------------
		

		$no_prev = false;
		$no_next = false;
		
		if( isset( $cstart ) and $cstart != "" and $cstart > 0 ) {
			$prev = $cstart / $config['collection_number'];

			if( $config['allow_alt_url'] ) {

				if ($prev == 1) $prev_page = "/collections{$fav_u}";
				else $prev_page = "/collections{$fav_u}/page/" . $prev;

				$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<a href=\"" . $prev_page . "\">\\1</a>" );

			} else {

				if ($prev == 1) $prev_page = $config['http_home_url']."?do=collections{$fav_u}";
				else $prev_page = $PHP_SELF . "?do=collections{$fav_u}&cstart=" . $prev;

				$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<a href=\"" . $prev_page . "\">\\1</a>" );
			}
		
		} else {
			$tpl->set_block( "'\[prev-link\](.*?)\[/prev-link\]'si", "<span>\\1</span>" );
			$no_prev = TRUE;
		}
		
		//----------------------------------
		// Pages
		//----------------------------------
		if( $config['collection_number'] ) {

			$pages = "";
			
			if( $count_all > $config['collection_number'] ) {
				
				$enpages_count = @ceil( $count_all / $config['collection_number'] );
				
				$cstart = ($cstart / $config['collection_number']) + 1;

				if( $enpages_count <= 10 ) {
					
					for($j = 1; $j <= $enpages_count; $j ++) {
						
						if( $j != $cstart ) {
							
							if( $config['allow_alt_url'] ) {

								if ($j == 1) $pages .= "<a href=\"/collections{$fav_u}\">$j</a> ";
								else $pages .= "<a href=\"/collections{$fav_u}/page/" . $j . "\">$j</a> ";

							} else {

								if ($j == 1) $pages .= "<a href=\"{$config['http_home_url']}?do=collections{$fav_u}\">$j</a> ";
								else $pages .= "<a href=\"$PHP_SELF?do=collections{$fav_u}&cstart=$j\">$j</a> ";

							}
						
						} else $pages .= "<span>$j</span> ";
					
					}
				
				} else {
					
					$start = 1;
					$end = 10;
					$nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
					
					if( $cstart > 0 ) {
						
						if( $cstart > 6 ) {
							
							$start = $cstart - 4;
							$end = $start + 8;
							
							if( $end >= $enpages_count-1 ) {
								$start = $enpages_count - 9;
								$end = $enpages_count - 1;
							}
						
						}
					
					}
					
					if( $end >= $enpages_count-1 ) $nav_prefix = ""; else $nav_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> ";
					
					if( $start >= 2 ) {

						if( $start >= 3 ) $before_prefix = "<span class=\"nav_ext\">{$lang['nav_trennen']}</span> "; else $before_prefix = "";

						if( $config['allow_alt_url'] ) $pages .= "<a href=\"/collections{$fav_u}\">1</a> ".$before_prefix;
						else $pages .= "<a href=\"{$config['http_home_url']}?do=collections{$fav_u}\">1</a> ".$before_prefix;
					
					} 
					
					for($j = $start; $j <= $end; $j ++) {
						
						if( $j != $cstart ) {

							if( $config['allow_alt_url'] ) {

								if ($j == 1) $pages .= "<a href=\"/collections{$fav_u}\">$j</a> ";
								else $pages .= "<a href=\"/collections{$fav_u}/page/" . $j . "\">$j</a> ";

							} else {

								if ($j == 1) $pages .= "<a href=\"{$config['http_home_url']}?do=collections{$fav_u}\">$j</a> ";	
								else $pages .= "<a href=\"$PHP_SELF?do=collections{$fav_u}&cstart=$j\">$j</a> ";

							}
						
						} else {
							
							$pages .= "<span>$j</span> ";
						}
					
					}
					
					if( $cstart != $enpages_count ) {
						
						if( $config['allow_alt_url'] ) {
							
							$pages .= $nav_prefix . "<a href=\"/collections{$fav_u}/page/{$enpages_count}\">{$enpages_count}</a>";
							
						} else {
							
							$pages .= $nav_prefix . "<a href=\"$PHP_SELF?do=collections{$fav_u}&cstart={$enpages_count}\">{$enpages_count}</a>";
							
						}
					
					} else
						$pages .= "<span>{$enpages_count}</span> ";
				
				}
			
			}
			$tpl->set( '{pages}', $pages );
		}
		
		//----------------------------------
		// Next link
		//----------------------------------
		if( $config['collection_number'] AND $config['collection_number'] < $count_all AND $news_count < $count_all ) {
			$next_page = $news_count / $config['collection_number'] + 1;
			
			if( $config['allow_alt_url'] ) {
				
				$next = "/collections{$fav_u}/page/" . $next_page;
				$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<a href=\"" . $next . "\">\\1</a>" );
				
			} else {
				
				$next = $PHP_SELF . "?do=collections{$fav_u}&cstart=" . $next_page;
				$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<a href=\"" . $next . "\">\\1</a>" );
			}
		
		} else {
			$tpl->set_block( "'\[next-link\](.*?)\[/next-link\]'si", "<span>\\1</span>" );
			$no_next = TRUE;
		}
		
		if( !$no_prev OR !$no_next ) {
			$tpl->compile( 'navi' );
	
			$tpl->result['content'] .= $tpl->result['navi'];	
			
		}
		
		$tpl->clear();
	}	
	
	if( !$collections_found ) {

		$tpl->load_template( 'info.tpl' );
		$tpl->set( '{error}', $lang['mod_list_f'] );
		$tpl->set( '{title}', $lang['all_info'] );
		$tpl->compile( 'content' );
		$tpl->clear();

	} else {
		
	$ajax .= <<<HTML
<script>

function doFavorites_collections( fav_id, event, alert ){
	ShowLoading('');

	$.get(dle_root + "engine/ajax/controller.php?mod=collections_favorites", { fav_id: fav_id, action: event, skin: dle_skin, alert: alert, user_hash:dle_login_hash }, function(data){
		HideLoading('');
		if( alert ) {
			DLEalert(data, dle_info);
		} else {
			$("#fav-id-" + fav_id).html(data);
		}
	});

	return false;
}
</script>
HTML;
		
	}
	

}
?>
