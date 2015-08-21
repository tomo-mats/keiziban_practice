<?php
class Post
{
	private $mysqli = null;	//DB接続用オブジェクト格納

	function __construct($mysqli)
	{
		$this->mysqli = $mysqli;
	}

	/**
	 * スレッドの投稿内容一覧を取得する
	 */
	public function getPostList($sured_id, $limit = 10, $offset = 0){
		$post_list = array();

		$sql  = 'SELECT p.id,p.title,p.body,p.username,p.reply_id,p.created FROM post p ';
		$sql .= 'INNER JOIN sured s ON s.id = p.sured_id ';
		$sql .= 'WHERE s.id= ? ';
		$sql .= 'AND p.del = 0 ORDER BY p.created asc ';
		$sql .= 'LIMIT ? ';
		$sql .= 'OFFSET ? ';
		var_dump($sql);
		$stmt = $this->mysqli->prepare($sql);
		if($stmt){
			$stmt->bind_param("iii", $sured_id, $limit, $offset);
			$stmt->execute();

			// 連想配列を取得
			$stmt->bind_result($id, $title, $body, $username, $reply_id, $created);
			$cnt = 0;
			while ($stmt->fetch()) {
				$post_list[$cnt]['id']       = $id;
				$post_list[$cnt]['title']    = $title;
				$post_list[$cnt]['body']     = $body;
				$post_list[$cnt]['username'] = $username;
				$post_list[$cnt]['reply_id'] = $reply_id;
				$post_list[$cnt]['created']  = $created;
				$cnt++;
			}
			// 結果セットを閉じる
			$stmt->close();
		}
		return $post_list;
	}

	/**
	 * スレッドの投稿内容の件数を取得する
	 */
	public function getPostCount($sured_id){
		$post_count = 0;

		$sql  = 'SELECT p.id FROM post p ';
		$sql .= 'INNER JOIN sured s ON s.id = p.sured_id ';
		$sql .= 'WHERE s.id= ? ';
		$sql .= 'AND p.del = 0 ';

		$stmt = $this->mysqli->prepare($sql);
		if($stmt){
			$stmt->bind_param("i", $sured_id);
			$stmt->execute();

			$stmt->store_result();
			$post_count = $stmt->num_rows;
			$stmt->close();
		}
		return $post_count;
	}

	/**
	 * 新規のスレッドを追加する
	 */
	public function addPost($username,$body,$sured_id,$reply_id = 0){
		$sql = "INSERT INTO post (body,username,sured_id,reply_id,del,created,updated) VALUES (?,?,?,?,0,NOW(),NOW())";
		$stmt = $this->mysqli->prepare($sql);
		$stmt->bind_param('ssii',$body,$username,$sured_id,$reply_id);
		$is_insert = $stmt->execute();
		$stmt->close();

		return $is_insert;
	}

	/**
	 * 指定の投稿を削除する(論理削除)
	 */
	public function deletePost($post_id){
		$sql = "UPDATE post SET del = 1 WHERE id = ?";
		$stmt = $this->mysqli->prepare($sql);
		$stmt->bind_param('i',$post_id);
		$is_update = $stmt->execute();
		$stmt->close();

		return $is_update;
	}

	/**
	 * 存在する投稿IDかチェックする
	 * return boolean
	 */
	public function checkPostIdExist($post_id){
		$is_exsist = false;

		$sql = 'SELECT count(id) FROM post WHERE id= ?';
		$stmt = $this->mysqli->prepare($sql);
		if($stmt){
			$stmt->bind_param("i", $post_id);
			$stmt->execute();

			$stmt->store_result();
			if ($stmt->num_rows > 0){
				$is_exsist = true;
			}
			$stmt->close();
		}
		return $is_exsist;
	}

	/**
	 * 投稿内容を一件取得する
	 */
	public function getPostContent($sured_id, $reply_id){
		$post_content = array();

		$sql  = 'SELECT p.id,p.title,p.body,p.username,p.sured_id,p.reply_id,p.created FROM post p ';
		$sql .= 'INNER JOIN sured s ON s.id = p.sured_id ';
		$sql .= 'WHERE s.id= ? ';
		$sql .= 'AND p.id = ? ';

		$stmt = $this->mysqli->prepare($sql);
		if($stmt){
			$stmt->bind_param("ii", $sured_id, $reply_id);
			$stmt->execute();

			// 連想配列を取得
			$stmt->bind_result($id, $title, $body, $username, $sured_id, $reply_id, $created);
			$cnt = 0;
			while ($stmt->fetch()) {
				$post_content[$cnt]['id']       = $id;
				$post_content[$cnt]['title']    = $title;
				$post_content[$cnt]['body']     = $body;
				$post_content[$cnt]['username'] = $username;
				$post_content[$cnt]['reply_id'] = $reply_id;
				$post_content[$cnt]['created']  = $created;
				$cnt++;
			}
			// 結果セットを閉じる
			$stmt->close();
		}
		return $post_content;
	}

}