<?php
class Sured
{
	private $mysqli = null;	//DB接続用オブジェクト格納

	protected $sured = array(
		'id'   => '',
		'name' => '',
	);	//スレッド名

	function __construct($mysqli, $sured_id = null)
	{
		$this->mysqli = $mysqli;

		//インスタンス生成時にidが渡された場合、idのスレッドデータをセットする
		if(!is_null($sured_id)){
			$this->sured = $this->getSuredData($sured_id);
		}
	}

	public function getSuredId() {
		return $this->sured['id'];
	}

	public function getSuredName() {
		return $this->sured['name'];
	}

	/**
	 * スレッド名を取得する
	 * return boolean
	 */
	public function getSuredData($id){

		$sured_list = array();
		$sql = 'SELECT id,name FROM sured WHERE id= ? LIMIT 1';
		$stmt = $this->mysqli->prepare($sql);
		if($stmt){
			$stmt->bind_param("i", $id);
			$stmt->execute();

			// 連想配列を取得
			$stmt->bind_result($id, $name);
			while ($stmt->fetch()) {
				$sured_list['id']   = $id;
				$sured_list['name'] = $name;
			}
			// 結果セットを閉じる
			$stmt->close();
		}
		return $sured_list;
	}

	/**
	 * スレッド一覧を取得する
	 */
	public function getSuredList(){
		$sured_list = array();
		$sql = 'SELECT id,name FROM sured WHERE del = 0 ORDER BY created asc ';
		$stmt = $this->mysqli->query($sql);
		if ($stmt) {
			// 連想配列を取得
			$cnt = 0;
			while ($row = $stmt->fetch_assoc()) {
				$sured_list[$cnt]['id']   = $row['id'];
				$sured_list[$cnt]['name'] = $row['name'];
				$cnt++;
			}
			// 結果セットを閉じる
			$stmt->close();
		}
		return $sured_list;
	}

	/**
	 * 新規のスレッドを追加する
	 */
	public function addSured($name){
		$sql = "INSERT INTO sured (name,del,created,updated) VALUES (?,0,NOW(),NOW())";
		$stmt = $this->mysqli->prepare($sql);
		$stmt->bind_param('s',$name);
		$is_insert = $stmt->execute();
		$stmt->close();
		return $is_insert;
	}

	/**
	 * 指定のスレッドを削除する(論理削除)
	 */
	public function deleteSured($sured_id_ary){
		//IN句用のバインドパラメータを作成
		$placeholder_param = implode(',', array_fill(0, count($sured_id_ary), '?'));
		$sql = "UPDATE sured SET del = 1 WHERE id IN (".$placeholder_param.")";

		$stmt = $this->mysqli->prepare($sql);
		$stmtParams = array(str_repeat('d', count($sured_id_ary) ) );
		foreach ($sured_id_ary as $k=>$v){
			$stmtParams[] = &$sured_id_ary[$k];
		}
		call_user_func_array(array($stmt, 'bind_param'), $stmtParams);
		$is_update = $stmt->execute();
		$stmt->close();

		return $is_update;
	}

	/**
	 * 存在するスレッドIDかチェックする
	 * return boolean
	 */
	public function checkSuredIdExist($id){
		$is_exsist = false;

		$sql = 'SELECT id FROM sured WHERE id= ?';
		$stmt = $this->mysqli->prepare($sql);
		if($stmt){
			$stmt->bind_param("i", $id);
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
	 * すでに存在するスレッド名かチェックする
	 * return boolean
	 */
	public function checkSuredNameExsist($id){
		$is_exsist = false;

		$sql = 'SELECT name FROM sured WHERE id= ?';
		$stmt = $this->mysqli->prepare($sql);
		if($stmt){
			$stmt->bind_param("i", $id);
			$stmt->execute();

			$stmt->store_result();
			if (count($stmt->num_rows) > 0){
				$is_exsist = true;
			}
		}
		return $is_exsist;
	}

}