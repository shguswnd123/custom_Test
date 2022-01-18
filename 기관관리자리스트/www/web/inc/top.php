<?php 
  $groupArray = explode(",", str_replace('\'','',getGroupList_mpower($_SESSION['lol_ids81'])));

  //var_dump($groupArray);
  $bSearch = false;
  for($i=0; $i < count($groupArray); $i++){
      if (true === in_array(trim($groupArray[$i]), $wotSearchGroupArray)){
          $bSearch = true;
          break;
      }
  }

?><header>
	<img src="./img/logo.gif" title="G드라이브" alt="G드라이브" onClick="location.href='/web/main.php';" style="cursor:pointer;"/>
	<div class="header_right">
        <!-- 노현중_기관관리자list_테스트진행중_220106 -->
        <div class="notice" onClick="openAdminList();" style="cursor:pointer;">기관관리자 리스트</div>
		<span class="bar">|</span>
		<div class="notice" onClick="openNotice();" style="cursor:pointer;">게시판</div>
		<span class="bar">|</span>
		<button class="setting" id="btnPreference" onClick="OnPreference();fnVWconfig();" style="color: white; font-weight: bold;">개인설정</button>
		<?if($bSearch){ ?>
		<span class="bar">|</span>
        <button class="notice" id="btnPreference" onClick="fnSearchAI();" style="color: white; font-weight: bold;">검색</button>
        <?}?>
		<span class="bar">|</span>
        <div class="notice" onClick="openUserinfo();" style="cursor:pointer;">사용자 정보 수정</div>
        <span class="bar">|</span>
        <?php
        if($_SESSION['lol_idsUserName'] == 'GDRIVE관리자'){
            ?>
                <div class="notice" onClick="openservey();" style="cursor:pointer;">--</div>
            <?
        }
        ?>
		<div class="user"><?=$_SESSION['lol_idsUserName']?> 님 <? if($_SESSION['lol_managerBtn'] >= 16){ ?><button id="btnAdmin" onClick="window.open('../admin/login/log_check.php?menu='+$(this).attr('data-menu_id'));" >관리자</button><?}?></div>
		<button class="power" onclick="OnLogOut();">전원</button>
	</div>
    
</header>
<Script type="text/javascript">
    function openAdminList(){
        popup('AdminListBoard');
        $('#AdminListBoard .tabs li').filter(':first').click(); //첫번째 탭 선택
    }
    function openNotice() {
        popup('dialogBoard');
        $('#dialogBoard .tabs li').filter(':first').click(); //첫번째 탭 선택
        /*
        var $bpopup=$('#dialogBoard').bPopup({
            closeClass: 'bpopup_close' //닫기 버튼 클래스
            ,scrollBar: false //오버레이 배경에 스크롤 표시안함
        });
        */
    }//end func
     function openUserinfo() {
        window.open('/web/page/popup_Userinfo.php','', 'width=550, height=800, scrollbars=yes;resizeable=yes;')
     }
     function openUser() {
	   window.open('/web/page/test_log_n.php','', 'width=800, height=800, scrollbars=yes;resizeable=yes;')
     }
     function openservey() {
        window.open('/web/survey/popup_survey.php','만족도조사 팝업', 'width=650, height=780, scrollbars=no;')
     }
</Script>
