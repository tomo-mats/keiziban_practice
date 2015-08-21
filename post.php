<?php
require_once('class/connect_database.php');
require_once('class/sured.php');
require_once('class/post.php');

$post = $_POST;
$get  = $_GET;

//URLパラメータ異常の確認
if (!isset($get['sured'])) {
	header("Location: ./sured.php");
}

//SQL操作用クラスを生成
$con_db_class = new Connection_database();
$mysqli = $con_db_class->getConnection();
//スレッド一覧関連のデータを操作するスレッドクラスを生成
$sured_class = new Sured($mysqli,$get['sured']);
//スレッド内の投稿内容関連のデータを操作するポストクラスを生成
$post_class = new Post($mysqli);

//スレッドIDの存在確認
$is_exsist_sured = $sured_class->checkSuredIdExist($get['sured']);
if (!$is_exsist_sured) {
	header("Location: ./sured.php");
}

//スレッドIDの取得
$sured_id   = $sured_class->getSuredId();
//スレッドタイトルの取得
$sured_name = $sured_class->getSuredName();

//スレッドの内容一覧を取得
$post_list = $post_class->getPostList($sured_id);
//スレッドの投稿全件数を取得
$post_count = $post_class->getPostCount($sured_id);

//投稿が押されたら
if (isset($post['post'])){
	//必須入力チェック
	//文字数チェック

	try{
		$mysqli->begin_transaction();
		//データベースに保存する
		$is_insert = $post_class->addPost($post['username'],$post['body'],$sured_id,0);
		if (!$is_insert) {
			throw new Exception("投稿に失敗しました。");
		}
		$mysqli->commit();
	}catch(Exception $e){
		echo '$e->getMessage() : ', $e->getMessage();
		$mysqli->rollback();
	}
	//ページを再読み込み　※投稿内容の連続登録防止
	header("Location: " . $_SERVER['REQUEST_URI']);
}

//返信が押されたら
if (isset($post['reply'])){
	//必須入力チェック
	//文字数チェック

	try{
		$mysqli->begin_transaction();
		//データベースに保存する
		$is_insert = $post_class->addPost($post['reply_username'],$post['reply_body'],$sured_id,$post['reply_id']);
		if (!$is_insert) {
			throw new Exception("投稿に失敗しました。");
		}
		$mysqli->commit();
	}catch(Exception $e){
		echo '$e->getMessage() : ', $e->getMessage();
		$mysqli->rollback();
	}
	//ページを再読み込み　※投稿内容の連続登録防止
	header("Location: " . $_SERVER['REQUEST_URI']);
}

?>
<?php
//ヘッダーhtml の読み込み
require_once('view/header.php');
?>

<div id="main">
	<div class="container">
		<div class="post-form">
			<h2>スレッド投稿</h2>
			<form action="" method="post">
				<dl>
					<dt>投稿者名：</dt>
					<dd><input type="text" name="username" value=""></dd>
				</dl>
				<dl>
					<dt>本文：</dt>
					<dd><textarea name="body"></textarea></dd>
				</dl>
				<input type="submit" name="post" value="投稿する" id="post">
			</form>
		</div>
		<div class="post-contents">
			<h2>スレッドタイトル：<?php echo $sured_name; ?></h2>
			<h3>投稿件数 / <?php echo $post_count; ?>件</h3>
			<ul id="post-list">
				<?php
				if (count($post_list) > 0):
					$cnt = 0;
					foreach ($post_list as $post):
						$cnt++;
				?>
					<li class="list" id="list-<?php echo $cnt; ?>">
						<dl>
							<dt><?php echo $post['id']; ?> : [<?php echo $post['created']; ?>] : <?php echo $post['username']; ?></dt>
							<?php if ($post['reply_id'] != 0){ ?>
							<dd>
								<a href="<?php echo 'reply.php?sured='.$sured_id.'&reply='.$post['reply_id'] ?>"><?php echo '>>'.$post['reply_id']; ?></a>
							</dd>
							<?php } ?>
							<dd>
								<?php echo nl2br($post['body']); ?>
							</dd>
							<dd class="reply-form" id="reply-form<?php echo $cnt; ?>" style="display: none;">
								<p>┗ID<b><?php echo $post['id']; ?></b>に返信します</p>
								<form action="" method="post">
									<dl>
										<dt>返信者名：</dt>
										<dd><input type="text" name="reply_username" value=""></dd>
									</dl>
									<dl>
										<dt>返信内容：</dt>
										<dd><textarea name="reply_body"></textarea></dd>
									</dl>
									<input type="submit" value="返信" name="reply">
									<input type="hidden" name="reply_id" value="<?php echo $post['id']; ?>">
								</form>
							</dd>
							<dd>
								<a href="#" class="formopen" id="formopen-<?php echo $cnt; ?>">返信</a> /
								<a href="#" class="post-edit">編集</a> /
								<a href="#" class="post-delete" id="delete-<?php echo $post['id']; ?>">削除</a>
								<input type="hidden" name="postid-<?php echo $post['id']; ?>" value="<?php echo $cnt; ?>">
							</dd>
						</dl>
					</li>
				<?php
					endforeach;
				else:
					echo '<p>投稿内容がありません。</p>';
				endif;
				?>
			</ul>
			<div id="loadimg"></div>
		</div>
		<input type="hidden" name="sured_id" value="<?php echo $sured_id; ?>" id="sured_id">
	</div>
</div>
<?php
//フッターhtml の読み込み
require_once('view/footer.php');
