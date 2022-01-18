<?php
/*
 * 공지사항 리스트 - 검색페이지
 *
 * 메소드
 *      get
 *
 * 요청 파라메터
 *      type : 검색타입
 *          - 'title'  : 제목
 *          - 'user_name' : 게시자
 *      keyword : 검색어
 *      order : 정렬방법
 *
 * 응답
 *      html
 *
 */
require_once('popup_board_init.php');
login_check();
// $has_system=($_SESSION['lol_user_right'] & 64) > 0;
$org_id = $_SESSION['lol_orgid'];


//파라메터
$type= $AC->getGet('type');
$keyword= $AC->getGet('keyword');

//파라메터 끝

//마스터DB 커넥션
$masterDbConn = new MpowerDB2;
$masterDbConn->connectWithIndex(0); //마스터 디비접속
//마스터DB 커넥션 끝

$selector=new Selector($masterDbConn);//셀렉터 생성
$page=$AC->getGet('page', 1);//페이지 정보(1,2....인지)

$selector->set_paging($page, 'page_callback', 5);//페이지 설정

$selector->set_cols("
    OI.ORG_NAME,
    GI.GROUP_NAME,
    UI.USER_ID,
    UI.USER_NAME,
    UI.EMAIL
");


$selector->set_from("
    USER_INFO UI, USER_GROUP_RELATE_INFO UGRI, ORG_INFO OI, GROUP_INFO GI
");

$selector->add_where("
UI.USER_ID = UGRI.TARGET_ID 
AND OI.ORG_ID = UI.ORG_ID
AND GI.GROUP_ID = UGRI.GROUP_ID
AND  UI.USER_RIGHT_CODE >= '17' AND UI.STATUS ='1' 
"
);
$selector->search('UI.ORG_ID LIKE :ORG_ID', ':ORG_ID', $org_id, '%str%');

$selector->set_group("UI.USER_ID");





//검색조건
$user_id='';
$user_name='';
switch($type){
    case "user_id":
        $user_id=$keyword;
        break;
    case "user_name":
        $user_name=$keyword;
        break;
}
//end switch
$selector->search('UI.USER_ID LIKE :USER_ID', ':USER_ID', $user_id, '%str%');
$selector->search('UI.USER_NAME LIKE :USER_NAME', ':USER_NAME', $user_name, '%str%');

//검색조건 끝

//정렬
 $order=$AC->getGet('order', 'group.desc');
 list($order_col, $order_type) = explode('.', $order); // 칼럼명과 타입 분리


$selector->add_order('group.asc','GI.GROUP_NAME ASC, GI.GROUP_NAME DESC');
$selector->add_order('group.desc','GI.GROUP_NAME DESC, GI.GROUP_NAME DESC');

$selector->add_order('admin_id.asc','UI.USER_ID ASC, UI.USER_ID DESC');
$selector->add_order('admin_id.desc','UI.USER_ID DESC, UI.USER_ID DESC');

$selector->add_order('admin_name.asc','UI.USER_NAME ASC, UI.USER_NAME DESC');
$selector->add_order('admin_name.desc','UI.USER_NAME DESC, UI.USER_NAME DESC');

$selector->add_order('mail.asc','UI.EMAIL ASC, UI.EMAIL DESC');
$selector->add_order('mail.desc','UI.EMAIL DESC, UI.EMAIL DESC');

$selector->set_order($order);
//정렬 끝

$rows=$selector->select();//검색실행
if($rows===false){
    error_response('조회에 실패하였습니다');
}//end if
$paginate_html=$selector->get_paginate_html();//페이지네이트 html 개수

?>
<style type="text/css">
    #AdminListBoard .board_head li{cursor: pointer}
    /* #AdminListBoard .board_body a{cursor: pointer} */
</style>

<div class="notice_list">
    <div class="board_search">


        <div class="fr">
            <form method="get" action="/web/page/popup_admin_board_notice.php" class="frm_search ajax_submit">
                <input type="hidden" name="order" />
                <input type="hidden" name="type" />

                <div class="over_layer select fl mr10" >
                    <button type="button" style="width: 80px;">게시자</button>
                    <ul>
                        <li data-value="user_id">아이디</li>
                        <li data-value="user_name">이름</li>
                    </ul>
                </div>

                <input type="text" name="keyword" onfocus="this.placeholder=''; return true" class="fl" value="<?=$keyword?>">
                <button type="button" onclick="$(this).closest('form').submit();" class="btn_search fl">검색</button>
            </form>
        </div>
    </div>

    <div class="board_head cl">
        <ol>
            <li class="org">기관</li>
            <li class="group" data-order_col="group">부서</li>
            <li class="admin_id" data-order_col="admin_id">담당자 아이디</li>
            <li class="admin_name" data-order_col="admin_name">담당자 이름</li>
            <li class="mail ac" data-order_col="mail">이메일</li>
        </ol>
    </div>

    

    <div class="admin_body" style="height: 203px;">
        <?php foreach($rows as $row){ //리스트 출력 ?>
            <div class="admin_list cl">
                <ol>
                <li class="org"><?=$row['ORG_NAME']?></li>
                    <li class="group"><?=$row['GROUP_NAME']?></li>
                    <li class="admin_id"><?=$row['USER_ID']?>&nbsp;<!--이름없으면 li가 width 값을 제대로 표현못해서 그냥 하나 넣어둠.--></li>
                    <li class="admin_name"><?=$row['USER_NAME']?></li>
                    <li class="mail"><?=$row['EMAIL']?></li>
                </ol>
            </div>
        <?php }//end foreach ?>

    </div>

    <div class="pag"> <!---2017.03.13 style 삭제 -->
        <?=$paginate_html?>
        <!-- 페이지버튼 ui가 view 단인데 g드라이브는 adminpopup클래스에 있음 v10에서도 동일한지 참고... -->
    </div>

</div>

<!-- get_url를 타고가면 $_SERVER['PHP_SELF'] 값을 받아 보통 기본으로 php경로의파일정보를 리턴 -->
<script type="text/javascript">
    function page_callback(page){
        $('#AdminListBoard').get(0).notice.goto_url('<?=$AC->get_url('', 'page');?>page='+page);
    }//end func

    (function(){
        var inputs=$('#AdminListBoard .frm_search input[type="text"]');
        if(inputs.length>1){// text 박스가 2개 이상일때만
            inputs.on('keydown', function(e){//텍스트박스 엔터 서브밋 처리
                if(e.keyCode==13){
                    $(this).closest('form').submit();// 자신이 등록된 폼의 서브밋 실행
                }//end if
            });//end keydown
        }//end if

        // 정렬버튼

        $('#AdminListBoard .board_head li').on('click', function(event){// 정렬버튼 이벤트
            var order_col = $(this).data('order_col');
            if(order_col!=undefined) {
            var order_type = $(this).data('order_type')=='desc'?'asc':'desc' ;
            var order=order_col+'.'+order_type;
            $('#AdminListBoard').get(0).notice.goto_url('<?=$AC->get_url('', 'order');?>order='+order);
        }
        });//end click
        
        $('#AdminListBoard .frm_search input[name="order"]').val('<?=$order?>');//폼에 현재 정렬방법 저장
       
        //현재 선택된 정렬 클래스설정
        var $btn = $('<button type="button" >정렬</button>');
        $btn.addClass('<?=$order_type=='asc'?'range_active_up':'range_active'?>');
        $('#AdminListBoard .board_head li[data-order_col="<?=$order_col?>"]')
            .css('fontWeight', 'bold')
            .data('order_type', '<?=$order_type?>')
            .append($btn);
        //현재 선택된 정렬 클래스설정 끝
        // 정렬버튼  끝
        


    })();//end anomy func
</script>




<script type="text/javascript">
    // //검색타입 셀렉트박스 처리
    $('#AdminListBoard .select button').click(function () {
        $selW = $(this).width() + 37;

        if($(this).next().is(':visible')) {
            $(this).next().slideUp(SLIDE_SPEED);
        }
        //dd 의 display 속성이 block 이 아니라면
        else {
            $(this).next().slideDown(SLIDE_SPEED);
            $(this).next().css({'min-width':$selW+'px'});

        }
    } );

    $('#AdminListBoard .select ul').on('click', 'li', function(){ //셀렉트 옵션 선택
        $('#AdminListBoard .frm_search input[name="type"]').val($(this).data('value')); //히든값설정
        $(this).closest('.select').find('button').text($(this).text()); //버튼명칭교체
        $(this).closest('ul').hide();
    });//end click

    (function(){
        //초기 검색값 선택
        var $li = $( '#AdminListBoard .select ul li[data-value="<?=$type?>"]');
        if($li.length==0) $li=$( '#AdminListBoard .select ul li:first');//검색타입 선택된거없으면 첫번재 옵션선택
        $li.click();
        //초기 검색값 선택 끝
    })();
    //end anomy func

    // //검색타입 셀렉트박스 처리 끝
</script>
