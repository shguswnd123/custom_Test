<?php 

require_once(__DIR__."/../common/mpower.session.start.php");

// skchoi. 2017.03.14. mpower.session.start.php 로 대체 
// session_start();

if($_SESSION['lol_ids81'] == ""){header('Location: ../../index.php');exit;}
?>

<!-- css & js & 상단 영역 -->
<? include "./inc/header.php";?>
<?

require_once($_SERVER['DOCUMENT_ROOT']."/web/inc/container_common.php");

//----------------------아래 한줄 추가-------------------------------
require_once($_SERVER['DOCUMENT_ROOT']."/admin/common/class.AdminPopup.php");	//트리 팝업 클래스 로드
require_once($_SERVER['DOCUMENT_ROOT']."/admin/common/class.Policy.php");	//트리 팝업 클래스 로드
require_once($_SERVER['DOCUMENT_ROOT']."/common/common.function.php");

$ua = getBrowser();
$browser =  $ua['name'];

$powerdb_con = new MpowerDB2;
$powerdb_con->connectWithIndex($_SESSION['lol_db_idx']);

/*
$bWOT = false;
if (true === in_array($_SESSION['lol_ids81'], $wotUserArray)){
  $bWOT = true;
}
*/

$groupArray = explode(",", str_replace('\'','',getGroupList_mpower($_SESSION['lol_ids81'])));

$bWOT = true;
$bWotSearch = true;
if (in_array($_SESSION['lol_ids81'], $wotUserArray)){
    $bWOT = true;
}else{
    for($i=0; $i < count($groupArray); $i++){
        if (in_array(trim($groupArray[$i]), $wotGroupArray)){
            $bWOT = true;
            break;
        }
    }
}
for($i=0; $i < count($groupArray); $i++){
    if (in_array(trim($groupArray[$i]), $wotSearchGroupArray)){
        $bWotSearch = true;
        break;
    }
}

$bUNR = checkUserNonReqular($_SESSION['lol_ids81']);

//휴지통 보관주기 정책 데이터 가져오기
$Policy = new Policy($powerdb_con);
$data = $Policy->getSystemPolicy(); //여기 데이터 가져오는건 동기화 테이블이므로 아무디비나 붙어도 됨
$recycle_interval = '';
$recycle_interval = $data[4]['GCFG_INT_VAL'];
if($recycle_interval=='0') $recycle_interval = '영구보관';
else if($data[4]['GCFG_OPT']=='1') $recycle_interval .= '개월';
else if($data[4]['GCFG_OPT']=='2') $recycle_interval .= '주';
else if($data[4]['GCFG_OPT']=='3') $recycle_interval .= '일';

//----------------------위 디비컨넥션을 가지고 아래 한줄 추가-------------------------------
$AdminPopup = new AdminPopup($powerdb_con);

//----------------------아래의 여러 줄 중에 필요한것 골라서 사용-------------------------------
$option = Array(
    'id'=>'ShareUserSearch'    //한 화면에 여러개 뿌리기 위해 각 고유한 아이디 값을 지정함
    , 'title'=>'공유 대상자 찾기'    //title
    , 'sub_text'=>'공유 대상자를 선택한 후 등록 버튼을 누르세요.'    //내부 설명
    , 'select_type'=>'multi' //single, multi
    , 'target_type'=>'user'    //group(조직공유함), user(유저), user_only(유저), club, club_only (온나라문서함), (협업문서함)
    , 'target_mix'=> true    //허성 추가  조직 + 유저 선택
    , 'group_status'=>'1'
//  , 'parent_group_id'=>'1740195'  //선택된 그룹 아이디 - 이 값이 있으면 이 하위값만 조회됨
	, 'parent_group_id'=> $_SESSION['lol_idsOrg']  //선택된 그룹 아이디 - 이 값이 있으면 이 하위값만 조회됨
//	, 'parent_group_id'=>'0000000'
	, 'is_include_myself'=>true	//parent_group_id를 포함하여 트리 표현여부 : 기본값 true
    , 'not_include_user_id'=>array($_SESSION['lol_ids81'])
    , 'user_return_column' => Array('이름', '기관', '부서ID')	//위 user_column에 사용되는 name의 종류와 동일하다. 콜백 받고 싶은것만 나열하면 됨
//	, 'group_type'=>array('T', 'O', 'G')	//필요한 group_type만 선별하여 적용. 없으면 모든 값 다 조회
//	, 'group_type'=>array('O')
//	, 'group_type'=>array('O')
//	, 'group_type'=>array('G')
//	, 'group_type'=>array('T')
//	, 'club_type'=>array('100', '110')	//club_info.club_type : 값이 없으면 모두 뿌리기
//	, 'club_type'=>array('200', '210')	//club_info.club_type : 값이 없으면 모두 뿌리기
//	, 'parent_group_name'=>'관리자 메뉴'  //트리 최상단에 뿌려줄 텍스트 - 없으면 '정부' 기본값
//	, 'node_type'=>'O'	//group_tree.node_type값 설정하지 않으면 모두 뿌려주고 설정하면 값에 해당되는 데이터만 뿌림
//	, 'group_depth'=>'2'	//값을 넣으면 group_tree.depth값을 연동하여 조회
//    , 'user_column' => Array(
//        Array('name'=>'이름', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'아이디', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'기관', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'부서', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'직급', 'width'=>'20%', 'order_option'=>'DESC')
//    )
//    , 'user_column' => Array(
//        Array('name'=>'구분', 'width'=>'30%', 'order_option'=>'ASC')
//        , Array('name'=>'문서함', 'width'=>'', 'order_option'=>'')
////        , Array('name'=>'할당 용량', 'width'=>'30%', 'order_option'=>'')
//    )//'이름', '부서', '직급', '할당 용량', '사용 용량'중 필요한것만 나열하여 사용하면 됨 - width 합은 90%로, 10%는 체크박스자동할당
//	, 'user_return_column' => Array('이름', '할당 용량')	//위 user_column에 사용되는 name의 종류와 동일하다. 콜백 받고 싶은것만 나열하면 됨
//	, 'user_return_column' => Array('문서함')	//위 user_column에 사용되는 name의 종류와 동일하다. 콜백 받고 싶은것만 나열하면 됨
    , 'is_popup'=>true  //true(팝업호출), false(그냥 호출)
    , 'is_ico'=>true    //true(폴더아이콘사용), false(아이콘사용안함)
    , 'is_checkbox'=>true    //true(checkbox 아이콘사용), false(checkbox 사용안함)
	, 'debug_display'=>'none'	//디버그 위해 사용 개발자를 제외한 사용자는 이 값 넣을 필요 없음
);
$tree_html1 = $AdminPopup->getSearchPopup($option);   //조직 공유함 찾기(싱글선택)


$option = Array(
    'id'=>'ShareGroupSearch'    //한 화면에 여러개 뿌리기 위해 각 고유한 아이디 값을 지정함
    , 'title'=>'공유 대상자 찾기'    //title
    , 'sub_text'=>'공유 대상자를 선택한 후 등록 버튼을 누르세요.'    //내부 설명
    , 'select_type'=>'single' //single, multi
    , 'target_type'=>'group'    //group(조직공유함), user(유저), user_only(유저), club, club_only (온나라문서함), (협업문서함)
    , 'parent_group_id'=> $_SESSION['lol_idsGroup']  //선택된 그룹 아이디 - 이 값이 있으면 이 하위값만 조회됨
    , 'is_include_myself'=>true	//parent_group_id를 포함하여 트리 표현여부 : 기본값 true
    , 'is_popup'=>true  //true(팝업호출), false(그냥 호출)
    , 'is_ico'=>true    //true(폴더아이콘사용), false(아이콘사용안함)
    , 'is_checkbox'=>true    //true(checkbox 아이콘사용), false(checkbox 사용안함)
    , 'debug_display'=>'none'	//디버그 위해 사용 개발자를 제외한 사용자는 이 값 넣을 필요 없음
    , 'not_include_user_id'=>array($_SESSION['lol_ids81'])
    , 'group_return_column' => Array('이름', '기관')	//위 user_column에 사용되는 name의 종류와 동일하다. 콜백 받고 싶은것만 나열하면 됨
//    , 'user_column' => Array(
//        Array('name'=>'이름', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'아이디', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'기관', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'부서', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'직급', 'width'=>'20%', 'order_option'=>'DESC')
//    )
);
$tree_html2 = $AdminPopup->getSearchPopup($option);   //조직 공유함 찾기(싱글선택)
//echo $tree_html2;exit;

$option = Array(
    'id'=>'ShareApUserSearch'    //한 화면에 여러개 뿌리기 위해 각 고유한 아이디 값을 지정함
    , 'title'=>'공유 승인자 찾기'    //title
    , 'sub_text'=>'공유 승인자를 선택한 후 등록 버튼을 누르세요.'    //내부 설명
    , 'select_type'=>'single' //single, multi
    , 'target_type'=>'user'    //group(조직공유함), user(유저), user_only(유저), club, club_only (온나라문서함), (협업문서함)
    , 'parent_group_id'=> $_SESSION['lol_idsGroup']  //선택된 그룹 아이디 - 이 값이 있으면 이 하위값만 조회됨
    , 'is_include_myself'=>true	//parent_group_id를 포함하여 트리 표현여부 : 기본값 true
    , 'is_popup'=>true  //true(팝업호출), false(그냥 호출)
    , 'is_ico'=>true    //true(폴더아이콘사용), false(아이콘사용안함)
    , 'is_checkbox'=>true    //true(checkbox 아이콘사용), false(checkbox 사용안함)
    , 'debug_display'=>'none'	//디버그 위해 사용 개발자를 제외한 사용자는 이 값 넣을 필요 없음
    , 'not_include_user_id'=>array($_SESSION['lol_ids81'])
//    , 'user_column' => Array(
//        Array('name'=>'이름', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'아이디', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'기관', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'부서', 'width'=>'20%', 'order_option'=>'')
//        , Array('name'=>'직급', 'width'=>'20%', 'order_option'=>'DESC')
//    )
);
$tree_html5 .= $AdminPopup->getSearchPopup($option);   //조직 공유함 찾기(싱글선택)



// ===========================================================================================================================================
// 상세 검색 관련
// ===========================================================================================================================================

//require_once(__DIR__ . "/../../common/common.function.php");
//require_once(__DIR__ . "/../../common/class.mpowerdb.php");
if($_SESSION['lol_ids81'])
{
	$powerdb_con = new MpowerDB2();
	foreach($DB_LIST_INFO as $idx => $DB_INFO)
	{
		$powerdb_con->connectWithIndex($idx);
		$query		= "SELECT NODE_ID FROM SHARE_NODE_INFO WHERE OWNER_ID=? AND NODE_TYPE=?";
		$pstmt		= $powerdb_con->prepare($query);
		$paramIndex = 1;
		$powerdb_con->bindParam($pstmt, $paramIndex++, $_SESSION['lol_ids81'], PDO::PARAM_STR);
		$powerdb_con->bindParam($pstmt, $paramIndex++, 1, PDO::PARAM_INT);
		$powerdb_con->execute($pstmt, true);
		$rs			= $powerdb_con->fetch($pstmt);

		if($rs)
		{
            $searchDefaultDBidx		= $idx;
			$searchDefaultNodeId	= $rs["NODE_ID"];
			$searchDefaultUserId	= $_SESSION['lol_ids81'];
			break;	
		}
		else
		{
            $searchDefaultDBidx	= 0;
			$searchDefaultNodeId	= "";
			$searchDefaultUserId	= "";
		}
	}
}
else
{
    $searchDefaultDBidx = 0;
	$searchDefaultNodeId = "";
	$searchDefaultUserId = "";
}

//echo "<br>searchDefaultDBidx : ". $searchDefaultDBidx;
//echo "<Br>searchDefaultNodeId : ". $searchDefaultNodeId;
//echo "<br>searchDefaultUserId : ". $searchDefaultUserId;
// ===========================================================================================================================================
// 20201123 ~ 20201204 설문 조사 팝업
$s_date = date("YmdHis");
if($s_date > "20201123000000" && $s_date < "20201204999999"){
		$survey_user = $_SESSION['lol_ids81'];
		$masterDbConn = new MpowerDB2;
		$masterDbConn->connectWithIndex(0);
		$query		= "SELECT USER_ID FROM survey_score WHERE USER_ID = ?";
		$pstmt		= $masterDbConn->prepare($query);
		$paramIndex = 1;
		$masterDbConn->bindParam($pstmt, $paramIndex++, $survey_user, PDO::PARAM_STR);
		$masterDbConn->execute($pstmt, true);
		$survey_check = $masterDbConn->fetch($pstmt);
		
		if($survey_check['USER_ID'] == ''){
			echo ("<script language=javascript> window.open('/web/survey/popup_survey.php','만족도조사 팝업', 'width=650, height=780, scrollbars=no;');</script>");
		}
	
}
// ===========================================================================================================================================
?>

<script>
    <?
    /*
     * header.php에서의 자바스크립트 전역변수 선언은 DB연결 전이므로 DB를 활용할 수 없다.
     * 이곳에서 DB를 활용한 자바스크립트 전역변수를 저장토록 한다.
     */
    ?>
    var recycle_interval = '<?=$recycle_interval?>';
   // var m_bWOT   ='<?=$bWOT?>'; 20201214 변경
    var m_bWOT   ='1';
    
    var m_bUNR = '<?=$bUNR?>';
    
    var __USE_WEB_LIST_ROW = <?=$__USE_WEB_LIST_ROW?>;
    
</script>
<!-- css & js & 상단 영역 -->
<body id="ContentBody" oncontextmenu="return false">
<div style="position:absolute;height0px;width:0px;">
    <input type="text" style="left:-1000px;"/>
    <input type="password" style="left:-1000px;"/>
</div>
<div id="overlay"></div>
<article>
	<!-- css & js & 상단 영역 -->
	<? include "./inc/top.php";?>
	<!-- css & js & 상단 영역 -->

	<section id="section01">
	<!-- 좌측 LNB 영역 -->
	<? include "./inc/navi.php";?>
	<!-- 좌측 LNB 영역 -->
    <div id='content_bar' style="position:absolute;left:247px;width:3px;height:100%;z-index:30;cursor:w-resize;"></div>
	<!-- 컨텐츠 영역-->
	<main id="main01">

		<button class="navi_back"></button>

		<div class="search_box">
			<!--<button class="ico_search" onClick='fnSearchSubmit(1,0);'>아이콘</button>
			<input type="text" name="inputSearchDetail" autocomplete="" id="inputSearchDetail" onfocus="this.placeholder=''; enableSelection();return true" onkeydown="fnKeySarch(event);" placeholder='검색어' value="" onblur="disableSelection();">
			<button class="btn_search fl" onclick="fnBtnDetailSearch()">상세검색</button>-->
			<h1 class="fl" id="NowPath">내 문서함</h1>
            <span id="spanComment" style="padding-left:10px;color:#a3a3a3;"></span>
		</div>

		<? //include "./inc/inc_Search.php";?>
<style>
    .over_layer li {width:100%; line-height:21px; color:#555555; padding:0 10px; white-space:nowrap;cursor:pointer;}
    .over_layer ul li.disabled {cursor:default !important;}
    .slideBarMove {background:#c1c1c1;  opacity: 0.7;}
</style>

		<div class="btn_group">
			
			<div class="over_layer select fl" id="topUploadGroupBtn">
				<button id="select_button_menu_1" class="btn_up fl strong">올리기</button>
				<ul id="select_button_view_1" class="select_button_view" name="select_button_view" style="display: none;">
					<li id='li_fileupload' onClick="FileUpload();">파일 올리기</li>
					<li id='li_fileupload2' onClick="FileUpload2();">파일/폴더 올리기</li>
				</ul>
			</div>
			<div class="over_layer select fl" id="topDownloadGroupBtn">
				<button id="select_button_menu_2" class="btn_down fl strong">내려받기</button>
				<ul id="select_button_view_2" class="select_button_view" name="select_button_view" style="display: none;">
					<li style="padding:0px 10px" id="li_filedownload">파일 내려받기</li>
					<li style="padding:0px 10px" id="li_filedownload2">파일/폴더 내려받기</li>
				</ul>
			</div>
			<div class="over_layer select fl" id="topNewFileGroupBtn">
				<button id="select_button_menu_5" class="strong">새 문서</button>
				<ul id="select_button_view_5" class="select_button_view" name="select_button_view" style="display: none;">
  				<li style="padding:0px 10px" id="li_newfile_hwp" onclick="OnNewFile('hwp');"><span class="ico_file hwp" style="margin:0 0 0px 0px !important">h</span> 한글</li>
  				<li style="padding:0px 10px" id="li_newfile_xls" onclick="OnNewFile('xls');"><span class="ico_file xls" style="margin:0 0 0px 0px !important">x</span> 엑셀</li>
					<li style="padding:0px 10px" id="li_newfile_ppt" onclick="OnNewFile('ppt');"><span class="ico_file ppt" style="margin:0 0 0px 0px !important">p</span> 파워포인트</li>
					<li style="padding:0px 10px" id="li_newfile_doc" onclick="OnNewFile('doc');"><span class="ico_file doc" style="margin:0 0 0px 0px !important">d</span> 워드</li>
				</ul>
			</div>
			<button class="btn_newfolder fl" id="topNewFolderBtn" onClick="OnNewFolder();">새폴더</button>
			<div id="topTrashBtn" style="display:none;">
				<button class="btn_newfolder fl" onClick="OnRecyleBinDeleteAll();">휴지통 비우기</button>
				<button class="btn_newfolder fl" onClick="OnRestore();">선택항목 복원</button>
			</div>	
			<div class="over_layer select fl" id="topEditGroupBtn">
				<button id="select_button_menu_3">편집</button>
				<ul id="select_button_view_3" class="select_button_view" name="select_button_view">
					<li id="li_cut">잘라내기</li>
					<li id="li_copy">복사</li>
					<li id="li_paste">붙여넣기</li>
					<!--  li class="disabled">붙여넣기</li-->
					<li id="li_move">이동</li>
					<li id="li_doc_edit" class="bt5">웹오피스 편집</li>
					<li id="li_doc_read">웹오피스 읽기</li>
					<li id="li_rename" class="bt5">이름바꾸기</li>
					<li id="li_set_password">비밀번호 설정</li>
					<li class="bt5" id="li_preview">미리보기</li>
					<li class="bt5" id="li_detail">속성</li>
				</ul>
			</div>
			<button class="btn_del fl" id="topDeleteBtn" onClick="javascript:OnDelete();">삭제</button>
			<div class="over_layer select fl" id="topShareGroupBtn">
				<button class="btn_share strong" id="select_button_menu_4">공유</button>
				<ul id="select_button_view_4" class="select_button_view" name="select_button_view">
					<li id="topBtnShare">폴더 공유</li>
					<li id="topBtnLink">URL 공유</a></li>
				</ul>
			</div>
			<button id="topUrlBtn" class="fl" style="display:none;" onclick="OnLink();">URL 공유</button>
			<button id="topTbBtn" class="fl" style="display:none;" onclick="OnTb();">인수인계</button>
			<button id="topCpBtn" class="fl" style="display:none;" onclick="OnClubRequest();">협업 신청</button>
			<button id="topRefreshBtn" class="refresh fl" onclick="OnRefresh();">새로고침</button>
			<!--  button onclick="folderTreeDialogOpen('test', false);">폴더선택</button-->
			<?if(is_array($__USE_WEB_LIST_ROW)){?>
			<div class="over_layer select fr">
				<button class="btn_list strong" id="select_button_menu_8">리스트</button>
				<ul id="select_button_view_8" class="select_button_view" style="left: -56px; top: 28px;">
					<li style="padding: 5px 5px 5px 5px; height: 40px;min-width: 130px;">
						<div class="fl pt5">스타일</div>
						<div class="fr">
							<? if($_COOKIE['web_page_style'] == "tile"){?>
    						<button id="btnStyleList" class="btn_view _list fl" onclick="fncChangeListStyle('list');">리스트 보기</button>
    						<button id="btnStyleTail" class="btn_view _big_view fl" onclick="fncChangeListStyle('tile');">크게보기</button>
							<?}else{?>
    						<button id="btnStyleList" class="btn_view _list_view fl" onclick="fncChangeListStyle('list');">리스트 보기</button>
    						<button id="btnStyleTail" class="btn_view _big fl" onclick="fncChangeListStyle('tile');">크게보기</button>
    						<?}?>
    					</div>
					</li>
					<li style="padding: 5px 5px 5px 5px; height: 40px;min-width: 130px;">
						<div class="fl pt5">목록 개수</div>
						<div class="fr">
    						<select id="selectRowChange" onchange="fncWebRowChange()" style="width:60px; padding-left:5px;" >
    							<?
    							foreach ($__USE_WEB_LIST_ROW as $val){
    							    if($val == 'all'){
    							        $val = 10000000;
    							        $text = '전체';
    							    }else{
    							        $text = $val;
    							    }
    							    
    							    if($_COOKIE['web_page_row'] == $val){
    							        echo "<option value='".$val."' selected>".$text."</option>";
    							    }else{
			                                        echo "<option value='".$val."'>".$text."</option>";
    							    }
    							}
    							?>
    						</select>
						</div>
					</li>
				</ul>
			</div>
			<?}else{?>
			<div class="fr">
				<button id="btnStyleList" class="btn_view _list_view fl" onclick="OnStyleChange('list');">리스트 보기</button>
				<button id="btnStyleTail" class="btn_view _big fl" onclick="OnStyleChange('tile');">크게보기</button>
			</div>
			<?}?>
		</div>
		<div class="table">
            <!-- 정렬 레이어 -->
            <div class="over_layer sort fl" id="sortLayer"><!-- 올린 날짜순 -->
                <ul id="ul_big_order" style="display:none;">
                    <li onclick="fnOrder(0)"><a href="#">중요표시</a></li>
                    <li onclick="fnOrder(1)"><a href="#" >이름</a></li>
                    <li onclick="fnOrder(2)"><a href="#" >크기</a></li>
                    <li onclick="fnOrder(3)"><a href="#" >수정한 날짜</a></li>
                    <li onclick="fnOrder(4)" class="on"><a href="#" >올린 날짜</a></li>
                </ul>
            </div>
            <!-- 정렬 레이어 -->
			<div id="list_head" class="list_head">
				<ol>
					<li class="check_small fl"><input type="checkbox" id="chkAll"><label for="chkAll">체크박스</label></li>
					<li class="mark fl"><button class="mark_on m6a" onclick='fnOrder(0)'>중요 표시</button></li>
					<li class="type fl">종류</li>
					<li class="name fl ti10">이름<button id='btn_order1' class='range' onclick='fnOrder(1)'></button></li>
					<li class="size">크기<button id='btn_order2' class='range' onclick='fnOrder(2)'></button></li>
					
					<li class="modified_date">수정한 날짜<button id='btn_order3' class='range' onclick='fnOrder(3)'></button></li>
					<li class="uploaded">올린 날짜<button id='btn_order4' class='range' onclick='fnOrder(4)'></button></li>

				</ol>
			</div>

			<div id="div_ListBody" class="table_list _scroll_auto"><!-- 스크롤 -->
			</div>

			<div class="pag" id="divPaging"></div>
		
	</main>
	<!-- 컨텐츠 영역-->
	</section>
    <div id="treeDialog" class="popup _data" style='width:350px;display:none;'></div>
<!-- footer 영역 -->
<? include "./inc/footer.php";?>
<!-- footer 영역 -->


<!-- Dialog 영역 -->
<!-- 팝업 -->
<div style="visibility:hidden;">
	<?=$tree_html1?><?//위에서 처리된 html을 화면에 뿌려줌.?>
	<?=$tree_html2?>
	<?=$tree_html5?><?//위에서 처리된 html을 화면에 뿌려줌.?>
	<? include "./page/popup_basic.php";?>
	<? include "./page/popup_basic2.php";?>
	<? include "./page/popup_basic_q.php";?>
	<? include "./page/popup_basic_q2.php";?>
	<? include "./page/popup_share.php";?>
	<? include "./page/popup_link.php";?>
	<? include "./page/popup_manage_sharing.php";?>
	<? include "./page/popup_manage_url.php";?>
	<? include "./page/popup_preferences.php";?>
	<? include "./page/popup_load.php";?>
	<? include "./page/popup_load_failure.php";?>
	<? include "./page/popup_duplicate_upload.php";?>
	<? include "./page/popup_duplicate_op.php";?>
	<? include "./page/progressBar.php";?>
	<? include "./page/popup_property.php";?>
	<? include "./page/popup_preview.php";?>
	<? include "./page/popup_taking_over.php";?>
	<? include "./page/popup_collaborative_folder.php";?>
	<? include "./page/popup_password.php";?>
	<? include "./page/popup_manage_password.php";?>
	<? include "./page/popup_password_input.php";?>
	<? include "./page/popup_admin_board.php";?>
	<? include "./page/popup_board.php";?>
    <? include "./page/popup_group_manage.php";?>
    <? include "./page/popup_onnara_manage.php";?>
    <? include "./page/popup_club_manage.php";?>
    <? include "./page/popup_request_cancel.php";?>
    <? include "./page/popup_notice_alarm.php";?>
    <? include "./page/popup_club_list.php";?>
    <? include "./page/popup_club_join.php";?>
    <? include "./page/popup_member_message.php";?>
    <? include "./page/popup_ai_search.php";?>
</div>
<div id="div_download"></div>
<span id="span_loading" style="cursor:wait;display:none;width:100%;height:100%;position:absolute;top:0;left:0;background-color:#ffffff;opacity:0.0;filter:alpha(opacity=80);z-index:30000;display:none;"></span>
<button id="btn_clipboard" data-clipboard-action="copy" data-clipboard-target="#txt_clipboard"></button>
<div style="height:0px;width:0px;position:absolute;"><input style="left:-1000px" type="text" id="txt_clipboard" value=""></div>
<!-- 팝업 -->
<!-- Dialog 영역 -->

</article>
</body>
</html>
