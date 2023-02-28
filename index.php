<?
include $_SERVER['DOCUMENT_ROOT']."/shkim/db.php";
?>
<!doctype html>
<head>
	<meta charset="UTF-8">
	<title>강의자료 게시판</title>
	<link rel="stylesheet" type="text/css" href="./css/style.css" />
	<link rel="stylesheet" type="text/css" href="./css/jquery-ui.css" />
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" ></script>
	<script type="text/javascript" src="/shkim/js/common.js"></script>
	<script>
	$(function() {
		//	[ 페이지 내 게시글이 1개 이상일 경우 드래그 드롭 순서변경 가능 ] ============
		let dragidx2 = $("#maxdragidx").val();
		if(dragidx2 > 2){
			$("#sortable").sortable({
				placeholder: "highlight", // 드래그 중인 아이템이 놓일 자리를 표시할 스타일을 지정
				revert: true,
				//	[ 드래그가 끝난 후 실행되는 코드 ] ============
				update: function(event, ui){
					//	[ 페이지 내 가장 큰 dragidx ] ============
					let dragidx = $("#maxdragidx").val();
					//	[ 드래그가 끝난 후 dragidx desc 정렬 ] ============
					$("#sortable tr").each(function(){
						$(this).children("#num").text(dragidx);
						dragidx = dragidx-1;
					});
					//	[ Ajax로 보낼 데이터 변수 정의 ] ============
					let mode = "idxRow";
					let myArray1 = [];	// idx 고유한 값
					let myArray2 = [];	// 드래그로 변경된 dragidx 값

					//	[ Ajax로 보낼 데이터 추출 ] ============
					$("#sortable tr td").each(function(){
						let this_id = $(this).attr("data_idx");
						if(this_id){
							let this_id2 =$("[data_idx='"+this_id+"']").text();

							myArray1.push(this_id.replace('num_', ''));
							myArray2.push(this_id2);
						}
					});
					let page = "<?=$page?>";
					$.ajax({
						type:"POST",
						url:"./page/board/process.php",
						data:{
							mode: mode,
							myArray1: myArray1,
							myArray2: myArray2,
						},
						success: function(data){
							alert('index가 새롭게 정렬되었습니다.');
							location.href = "http://192.168.3.8/shkim/index.php?page="+page;
						},
						error: function(status){
							alert("순서 변경 실패");
							console.log(status);
						},
					});
				}
			});
		//	[ 페이지 내 게시글이 1일 경우 순서변경 불가능 ] ============
		}else{}
	});
	</script>
</head>
<body>
	<div id="board_area"> 
		<h1><a href="http://192.168.3.8/shkim/index.php">강의자료 게시판</a></h1>
		<h4>JAVA강의 업로드 게시판입니다.</h4>
		<!-- 검색 -->
		<div id="search_box">
			<form action="/shkim/page/board/search_result.php" method="get">
				<select class="s_search" name="catgo">
					<option value="title">제목</option>
					<option value="name">글쓴이</option>
					<option value="content">내용</option>
				</select>
				<input class="search" type="text" name="search" size="40" required="required" /> <button class="btn_search">검색</button>
			</form>
		</div>
		<div id="write_btn">
			<a href="/shkim/page/board/write.php"><button class="btn">글쓰기</button></a>
		</div>
		<table class="list-table">
			<thead>
			<tr id='top'>
				<th width="70">번호</th>
				<th width="500">제목</th>
				<th width="120">글쓴이</th>
				<th width="100">작성일</th>
				<th width="100">조회수</th>
                <th width="100">idx / 순번</th>
			</tr>
			</thead>
			<tbody id="sortable">
<?
			if(isset($_GET['page'])){
				$page = $_GET['page'];
			}else{
				$page = 1;
			}
			$sql = mq("select * from board");
			$row_num = mysqli_num_rows($sql); //게시판 총 레코드 수
			$list = 5; //한 페이지에 보여줄 개수
			$block_ct = 5; //블록당 보여줄 페이지 개수

			$block_num = ceil($page/$block_ct); // 현재 페이지 블록 구하기
			$block_start = (($block_num - 1) * $block_ct) + 1; // 블록의 시작번호
			$block_end = $block_start + $block_ct - 1; //블록 마지막 번호

			$total_page = ceil($row_num / $list); // 페이징한 페이지 수 구하기
			if($block_end > $total_page) $block_end = $total_page; //만약 블록의 마지막 번호가 페이지수보다 많다면 마지박번호는 페이지 수
			$total_block = ceil($total_page/$block_ct); //블럭 총 개수
			$start_num = ($page-1) * $list; //시작번호 (page-1)에서 $list를 곱한다.
			
			if($page==1){
				$i = $row_num;
			}else if($page==2){
				$i = $row_num - $list;
			}else if($page==3){
				$i = $row_num - ($list*2);
			}            
			// board테이블에서 idx를 기준으로 내림차순해서 10개까지 표시
			$sql2 = mq("select * from board order by dragidx desc limit $start_num, $list"); // 드래그로 바뀐 index순서로 적용하려면 order by dragidx

			//	[ while 반복문 시작 ] ============
			while($board = $sql2->fetch_array()){
				//	[ board테이블 필드 변수 정의 ] ============
				$title      = $board["title"];
				$dragidx    = $board["dragidx"]; 
                if($maxdragidx < $dragidx){
                    $maxdragidx = $dragidx;
                }
				$idx        = $board["idx"];
				$name       = $board["name"]; 
				$regdate    = $board["regdate"]; 
				$hit        = $board["hit"]; 
				$thumbup    = $board["thumbup"];
				if(strlen($title)>30){ 
					//title이 30을 넘어서면 ...표시
					$title=str_replace($board["title"],mb_substr($board["title"],0,30,"utf-8")."...",$board["title"]);
				}
				//댓글 수 카운트
				$sql3 = mq("select * from reply where con_num='".$board['idx']."'"); //reply테이블에서 con_num이 board의 idx와 같은 것을 선택
				$rep_count = mysqli_num_rows($sql3); //num_rows로 정수형태로 출력

?>
			<tr>
				<td width="70" id="num" data_idx="num_<?=$idx?>">
				<?=$i?>
				</td>
				<td width="500">
<?
				$lockimg = "<img src='/shkim/img/lock.png' alt='lock' title='lock' with='20' height='20' />";
				$boardtime = mb_substr($regdate, 0, 10); //$boardtime변수에 board['regdate']값에서 날짜값만 넣음(시간 제외)
				$timenow = date("Y-m-d"); //$timenow변수에 현재 시간 Y-M-D를 넣음

				if($boardtime==$timenow){
					$img = "<img src='/shkim/img/new.png' alt='new' title='new' />";
				}else{
					$img ="";
				}

				if($board['lock_post']=="1"){ 
?>
				<a href='/shkim/page/board/ck_read.php?idx=<?=$idx?>'><?php echo $title, $lockimg;
				} else{ 
?>
				<a href='/shkim/page/board/read.php?idx=<?=$idx ?>'><?=$title?><span class="re_ct">[<?=$rep_count ?>]<?=$img ?></span></a></td>
				<td width="120"><?=$name?></td>
				<td width="100"><?=$regdate?></td>
				<td width="100"><?=$hit?></td>
                <td width="100"><? echo "{$idx} / {$dragidx}" ?></td>
<?
				}
?>
			</tr>
<?
			$i--;
			}
			//	[ while 반복문 끝 ] ============
?>
			</tbody>
		</table>

		<!---페이징 넘버 --->
		<div id="page_num" align="center">
			<ul>
<?
			if($page <= 1){ //만약 page가 1보다 크거나 같다면
				echo "<li class='fo_re'>처음</li>"; //처음이라는 글자에 빨간색 표시 
			}else{
				echo "<li><a href='?page=1'>처음</a></li>"; //알니라면 처음글자에 1번페이지로 갈 수있게 링크
			}
			if($page <= 1){ //만약 page가 1보다 크거나 같다면 빈값

			}else{
				$pre = $page-1; //pre변수에 page-1을 해준다 만약 현재 페이지가 3인데 이전버튼을 누르면 2번페이지로 갈 수 있게 함
				echo "<li><a href='?page=$pre'>이전</a></li>"; //이전글자에 pre변수를 링크한다. 이러면 이전버튼을 누를때마다 현재 페이지에서 -1하게 된다.
			}
			for($i=$block_start; $i<=$block_end; $i++){ 
				//for문 반복문을 사용하여, 초기값을 블록의 시작번호를 조건으로 블록시작번호가 마지박블록보다 작거나 같을 때까지 $i를 반복시킨다
				if($page == $i){ //만약 page가 $i와 같다면 
					echo "<li class='fo_re' style='padding: 5px 10px 5px 10px;'>$i</li>"; //현재 페이지에 해당하는 번호에 굵은 빨간색을 적용한다
				}else{
					echo "<li><a href='?page=$i'>$i</a></li>"; //아니라면 $i
				}
			}
			if($block_num >= $total_block){ //만약 현재 블록이 블록 총개수보다 크거나 같다면 빈 값
			}else{
				$next = $page + 1; //next변수에 page + 1을 해준다.
				echo "<li><a href='?page=$next'>다음</a></li>"; //다음글자에 next변수를 링크한다. 현재 4페이지에 있다면 +1하여 5페이지로 이동하게 된다.
			}
			if($page >= $total_page){ //만약 page가 페이지수보다 크거나 같다면
				echo "<li class='fo_re'>마지막</li>"; //마지막 글자에 긁은 빨간색을 적용한다.
			}else{
				echo "<li><a href='?page=$total_page'>마지막</a></li>"; //아니라면 마지막글자에 total_page를 링크한다.
			}
?>
			</ul>
			<!-- jQuery 에서 사용하기 위한 값 -->
            <input type="hidden" id='maxdragidx' value="<?=$maxdragidx?>">
		</div>
	</div>
</body>
</html>