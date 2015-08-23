<?php

require_once('class/connect_database.php');
require_once('class/sured.php');
require_once('class/post.php');

$get  = $_GET;

//URLパラメータ異常の確認
if (!isset($get['sured'])) {
	header("Location: ./sured.php");
}
if (!isset($get['reply'])) {
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
$is_exsist_reply = $post_class->checkPostIdExist((int)$get['reply']);
if (!$is_exsist_reply) {
	header("Location: ./sured.php");
}

//スレッドIDの取得
$sured_id   = $sured_class->getSuredId();
//スレッドタイトルの取得
$sured_name = $sured_class->getSuredName();

//返信元の投稿内容を取得
$post_content = $post_class->getPostContent($sured_id, (int)$get['reply']);
?>
<?php
//ヘッダーhtml の読み込み
require_once('view/header.php');
?>
<div id="main">
	<div class="container">
		<div class="post-contents">
			<h2>スレッドタイトル：<?php echo $sured_name; ?></h2>
			<ul id="post-list">
				<?php
				if (count($post_content) > 0):
					$cnt = 0;
					foreach ($post_content as $post):
						$cnt++;
				?>
					<li class="list" id="list-<?php echo $cnt; ?>">
						<dl>
							<dt><?php echo $post['id']; ?> : [<?php echo $post['created']; ?>] : <?php echo $post['username']; ?></dt>
							<?php if ($post['reply_id'] != 0){ ?>
							<dd>
								<a href="<?php echo 'post.php?sured='.$sured_id.'&reply='.$post['reply_id'] ?>"><?php echo '>>'.$post['reply_id']; ?></a>
							</dd>
							<?php } ?>
							<dd>
								<?php echo nl2br($post['body']); ?>
							</dd>
							<dd>
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
		</div>
		<input type="hidden" name="sured_id" value="<?php echo $sured_id; ?>" id="sured_id">
	</div>
</div>
<?php
//フッターhtml の読み込み
require_once('view/footer.php');
