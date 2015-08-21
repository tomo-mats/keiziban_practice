<?php
require_once('class/connect_database.php');
require_once('class/sured.php');

//SQL操作用クラスを生成
$con_db_class = new Connection_database();
$mysqli = $con_db_class->getConnection();
//スレッド一覧関連のデータを操作するスレッドクラスを生成
$sured_class = new Sured($mysqli);

$post = $_POST;
$get  = $_GET;

//スレッド一覧を取得
$sured_list = $sured_class->getSuredList();
$stmt_sured_list = null;

//スレッドの新規作成が押されたら
if (isset($post['create'])) {
	//必須入力チェック
	//文字数チェック
	//同じスレッド名が存在しないかチェック

	try{
		$mysqli->begin_transaction();
		//データベースに保存する
		$is_insert = $sured_class->addSured($post['new_sured']);
		if (!$is_insert) {
			throw new Exception("スレッドの追加に失敗しました。");
		}
		$mysqli->commit();
	}catch(Exception $e){
		echo '$e->getMessage() : ', $e->getMessage();
		$mysqli->rollback();
	}
	//ページを再読み込み　※スレッドの連続登録防止
	header("Location: " . $_SERVER['SCRIPT_NAME']);
}
//スレッドの削除が押されたら
if (isset($post['delete'])) {
	//チェックされた値があるか
	if (count($post['sured']) > 0) {
		//数値の整合性チェック

		try{
			//選択されたスレッドのレコードの削除フラグ有効にする
			$is_update = $sured_class->deleteSured($post['sured']);
			if (!$is_update) {
				throw new Exception("スレッドの削除に失敗しました。");
			}
		}catch(Exception $e){

		}
		header("Location: " . $_SERVER['SCRIPT_NAME']);
	}
}
$mysqli->close();
?>
<?php
//ヘッダーhtml の読み込み
require_once('view/header.php');
?>
<div id="main">
	<div class="container">
		<div class="sured-form">
			<h2>スレッド作成</h2>
			<form action="" method="post">
				<dl>
					<dt>スレッド名：</dt>
					<dd><input type="text" name="new_sured" value=""></dd>
				</dl>
				<input type="submit" name="create" value="新規作成">
			</form>
		</div>
		<div class="sured">
			<form action="" method="post">
				<h2>スレッド一覧</h2>
				<p>※スレッドを削除する場合はチェックボックスにチェックを入れて削除を実行</p>
				<ul>
				<?php
					if (count($sured_list) > 0) {
						foreach ($sured_list as $sured) {
							echo '<li>';
							echo '<input type="checkbox" name="sured[]" value="'.$sured['id'].'">';
							echo '<a href="/keiziban/post.php?sured='.$sured['id'].'">'.$sured['name'].'</a>';
							echo '</li>';
						}
					}else{
						echo '<p>スレッドがありません。</p>';
					}
				?>
				</ul>
				<div class="btn-area">
					<input type="submit" name="delete" value="削除" id="delete_btn">
				</div>
			</form>
		</div>
	</div>
</div>
<?php
//フッターhtml の読み込み
require_once('view/footer.php');
