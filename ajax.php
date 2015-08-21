<?php
require_once('class/connect_database.php');
require_once('class/sured.php');
require_once('class/post.php');

// Content-TypeをJSONに指定する
header('Content-Type: application/json');

$post = $_POST;

if(!isset($post['type'])){
	http_response_code(400);
	$data = '処理内容が不明です。';
	echo json_encode(compact('data'));
	exit;
}
if($post['type'] == 'delete'){
	//IDがPOSTに含まれていない場合は、エラーとして処理する
	if(!isset($post['delete_id'])){
		http_response_code(400);
		$data = 'idが存在しません';
		echo json_encode(compact('data'));
		exit;
	}
	if(!isset($post['sured_id'])){
		http_response_code(400);
		$data = 'idが存在しません';
		echo json_encode(compact('data'));
		exit;
	}
}elseif($post['type'] == 'page'){
	if(!isset($post['page'])){
		http_response_code(400);
		$data = 'ページが取得できません';
		echo json_encode(compact('data'));
		exit;
	}
}

//SQL操作用クラスを生成
$con_db_class = new Connection_database();
$mysqli = $con_db_class->getConnection();
//スレッド一覧関連のデータを操作するスレッドクラスを生成
$sured_class = new Sured($mysqli,$post['sured_id']);
//スレッド内の投稿内容関連のデータを操作するポストクラスを生成
$post_class = new Post($mysqli);

$sured_id = $sured_class->getSuredId();
//投稿内容削除時
if($post['type'] == 'delete'){

	//受け取ったIDが数値チェック

	try{
		$mysqli->begin_transaction();
		//データベースから削除する
		$is_delete = $post_class->deletePost($post['delete_id']);

		if(!$is_delete){
			$data = '削除できませんでした。';
			echo json_encode(compact('data'));
			exit;
		}
		$mysqli->commit();
	} catch (Exception $e) {
		$mysqli->rollback();
		$data = 'データベースの更新に失敗しました。';
		echo json_encode(compact('data'));
		exit;
	}

	//スレッドの内容一覧を取得
	$post_list = $post_class->getPostList($sured_id);
	//取得したスレッドの内容一覧を元にHTMLを作成する
	$data = createPostListHtml($post_list);

	echo json_encode(compact('data'));

//次の10件取得時
}elseif($post['type'] == 'page'){
	//pageが数値化チェック

	// 次の投稿内容の10件を取得する
	$limit  = 10;
	$offset = $post['page'] * 10;
	$next_post_list = $post_class->getPostList($sured_id, $limit, $offset);

	//取得したスレッドの内容一覧を元に次の10件のHTMLを作成する
	$data = createPostListHtml($next_post_list, $offset);
var_dump($next_post_list);
	echo json_encode(compact('data'));
}

/**
 * 投稿削除後、表示用のHTMLを新しく作成しなおす
 * @param 配列 $post_list スレッドの内容一覧
 * @return string (整形済みHTML)
 */
function createPostListHtml($post_list, $offset = 0) {

	$html = '';

	if (count($post_list) > 0){
		$cnt = $offset;
		foreach ($post_list as $post){
			$cnt++;
			$html .= '<li class="list" id="list-'.$cnt.'">'.PHP_EOL;
			$html .= '	<dl>'.PHP_EOL;
			$html .= '		<dt>'.$post['id'].' : ['.$post['created'].'] : '.$post['username'].'</dt>'.PHP_EOL;
			$html .= '		<dd>'.nl2br($post['body']).'</dd>'.PHP_EOL;
			$html .= '		<dd class="reply-form" id="reply-form'.$cnt.'" style="display: none;">'.PHP_EOL;
			$html .= '			<p>┗ID<b>'.$post['id'].'</b>に返信します</p>'.PHP_EOL;
			$html .= '			<form action="" method="post">'.PHP_EOL;
			$html .= '				<dl>'.PHP_EOL;
			$html .= '					<dt>返信者名：</dt>'.PHP_EOL;
			$html .= '					<dd><input type="text" name="reply_username" value=""></dd>'.PHP_EOL;
			$html .= '				</dl>'.PHP_EOL;
			$html .= '				<dl>'.PHP_EOL;
			$html .= '					<dt>返信内容：</dt>'.PHP_EOL;
			$html .= '					<dd><textarea name="reply_body"></textarea></dd>'.PHP_EOL;
			$html .= '				</dl>'.PHP_EOL;
			$html .= '			</form>'.PHP_EOL;
			$html .= '		</dd>'.PHP_EOL;
			$html .= '		<dd>'.PHP_EOL;
			$html .= '			<a href="#" class="formopen" id="formopen-'.$cnt.'">返信</a> /'.PHP_EOL;
			$html .= '			<a href="#" class="post-edit">編集</a> /'.PHP_EOL;
			$html .= '			<a href="#" class="post-delete" id="delete-'.$post['id'].'">削除</a>'.PHP_EOL;
			$html .= '			<input type="hidden" name="postid-'.$post['id'].'" value="'.$cnt.'">'.PHP_EOL;
			$html .= '		</dd>'.PHP_EOL;
			$html .= '	</dl>'.PHP_EOL;
			$html .= '</li>'.PHP_EOL;
		}
	}else{
		$html .= '<p>投稿内容がありません。</p>';
	}

	return $html;
}