$(function() {
	/*********** スレッド一覧ページ用 ***********/
	$("#delete_btn").click(function(){
		if(!confirm("選択したスレッドを削除します。")){
			return false;
		}
	});

	/*********** 投稿内容一覧ページ用 ***********/
	//返信クリック時のフォーム表示、非表示制御
	$("li[class=list] a[class=formopen]").click(function(e){
		e.preventDefault();

		var open_id = $(this).attr("id").replace("formopen-","");
		$("#reply-form"+open_id).toggle("normal");
	});
	
	//削除クリック時のAjax通信
	$("li[class=list] a[class=post-delete]").click(function (e) {
		e.preventDefault();
		
		//スレッドのID
		var sured_id  = $("#sured_id").val();
		//削除する投稿データ
		var delete_id = $(this).attr("id").replace("delete-","");
		//表示制御用ID
		var list_id   = $("input[name=postid-"+delete_id+"]").val();

		$.ajax({
			url: 'ajax.php',
			type: 'post',
			dataType: 'json',
			data: {
				sured_id : sured_id,
				delete_id: delete_id,
				type     : 'delete',
			}
		})
		// ・ステータスコードは正常で、dataTypeで定義したようにパース出来たとき
		.done(function (response) {
			//ul以下の投稿一覧を一度全て削除
			$("#post-list").empty();
			//ul以下に取得したデータを入れ込む
			$("#post-list").html(response.data);
		})
		///通信に失敗,またはエラーが返って来た場合
		.fail(function (response) {
			console.log(message);
			//エラーメッセージを取得
			var message = $.parseJSON(response.responseText);
			//削除しようとしたメッセージにポップアップでエラーメッセージを表示する
			$("#list-"+list_id).showBalloon({
				contents : message.data,
				position : "left",
				tipSize: 10,
				showDuration: 500,
				hideDuration: 500,
				maxLifetime: 5000,
				css: {
					border: 'solid 4px #5baec0',
					padding: '0 5px',
					fontSize: '10px',
					fontWeight: 'bold',
					lineHeight: '3',
					backgroundColor: '#666',
					color: '#fff',
				}
			});
		});
		
		//ajax通信後jqueryが動作しなくなるため再読み込み
		$.getScript("js/common.js");
	});
	
	//最下部スクロール時自動読み込み
	$(window).bottom({proximity: 0.01});
	
	//ajax通信 データ取得時のoffset用
	var page = 1;
	
	$(window).bind('bottom', function() {
		var obj = $(this);
		if (!obj.data('loading')) {
			obj.data('loading', true);
			
			//連続自動読み込みを防ぐため、少しだけスクロール位置をあげる
			//SetTimeOut内でスクロール位置をあげるのは
			//タイミング的に次の処理が走ってしまうため遅い
			var position = $(window).scrollTop() - 35;
			$("html,body").animate({
				scrollTop : position
			});
			// ローディング画像を表示
			$('#loadimg').html('<img src="img/loading.gif" />');
			
			setTimeout(function() {
				$('#loadimg').html('');
				
				//スレッドのID
				var sured_id = $("#sured_id").val();
				//ajax通信にて次の10件を取得
				$.ajax({
					url     : 'ajax.php',
					type    : 'post',
					dataType: 'json',
					data: {
						sured_id : sured_id,
						page     : page,
						type     : 'page',
					}
				})
				//ステータスコードは正常で、dataTypeで定義したようにパース出来たとき
				.done(function (response) {
					//投稿内容の最後にに取得したデータを追加する
					$("#post-list").append(response.data);
					page++;
				})
				//通信に失敗,またはエラーが返って来た場合
				.fail(function (response) {
					//エラーメッセージを取得
					var message = $.parseJSON(response.responseText);
					console.log(response.responseText);
				});
				//ajax通信後jqueryが動作しなくなるため再読み込み
				$.getScript("js/common.js");
				
				obj.data('loading', false);
			}, 1500);
		}
	});
});