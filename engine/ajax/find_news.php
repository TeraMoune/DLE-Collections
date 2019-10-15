<?php
if(!defined('DATALIFEENGINE')) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

if( $_REQUEST['user_hash'] == "" OR $_REQUEST['user_hash'] != $dle_login_hash ) {
	die( "error" );
}

$type = isset($_REQUEST['type']) ? true : false;

if( !$type ) {

if( preg_match( "/[\||\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_GET['term'] ) ) $term = "";
else $term = $db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_GET['term'] ) ) ), ENT_QUOTES, $config['charset'] ) );

if( $term == "" ) die("[]");

$buffer = "[]";
$news = array ();

$db->query("SELECT id, title FROM " . PREFIX . "_post WHERE approve=1 AND title LIKE '%{$term}%' ORDER by date DESC LIMIT 10");

while($row = $db->get_row()){
	
	$news[] = array("label"=>"{$row['title']}", "value"=>"{$row['id']}");

}

if (count($news)) {

	die(json_encode($news));
	
}

} else {

if( preg_match( "/[\||\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_REQUEST['name'] ) ) $name = "";
else $name = $db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_REQUEST['name'] ) ) ), ENT_QUOTES, $config['charset'] ) );

$tags = rawurldecode( $_REQUEST['tags'] );
$tags = htmlspecialchars ( strip_tags ( stripslashes ( trim ( $tags ) ) ), ENT_COMPAT, $config['charset'] );
$tags = @$db->safesql ( $tags );

if( preg_match( "/[\||\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_REQUEST['xfields'] ) ) $xfields = "";
else $xfields = $db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_REQUEST['xfields'] ) ) ), ENT_QUOTES, $config['charset'] ) );

if( preg_match( "/[\||\<|\>|\"|\!|\?|\$|\@|\/|\\\|\&\~\*\+]/", $_REQUEST['description'] ) ) $description = "";
else $description = $db->safesql( htmlspecialchars( strip_tags( stripslashes( trim( $_REQUEST['description'] ) ) ), ENT_QUOTES, $config['charset'] ) );

$where = array();
if( $name ) $where[] = "title LIKE '%{$name}%'";
if( $tags ) {
	
	$tags = explode(', ', $tags);
	
	if ( count($tags) ) {

        $ttmpArray = array();				   
			   
		foreach( $tags as $tmp_tag ) {
					   
			$ttmpArray[] = "`tags` like '%".$tmp_tag."%'";
					
		}
			    
		$tag = implode ( " AND ", $ttmpArray );
		
		$where[] = $tag;		
	}

}
if( $xfields ) $where[] = "xfields LIKE '%{$xfields}%'";
if( $description ) $where[] = "(short_story LIKE '%{$description}%' OR full_story LIKE '%{$description}%')";

if( count($where) ) $where_line = implode(' AND ', $where);

$query = $db->super_query("SELECT id, title FROM " . PREFIX . "_post WHERE approve=1 AND {$where_line} ORDER by date DESC", true);
	
foreach( $query as $row ){
		
	$insert_news_ids[] = '{ value: "'.$row['id'].'", label: "'.$row['title'].'" }';

}
			  
$news_ids_input = implode(',', $insert_news_ids);
echo "[".$news_ids_input."]";
die();
}
?>
