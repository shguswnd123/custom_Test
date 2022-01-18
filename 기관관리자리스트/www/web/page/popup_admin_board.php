<div id="AdminListBoard" class="popup _data">
    <button class="bnt_close" onclick="popupClose('AdminListBoard');">닫기</button>

    <h1>기관 관리자</h1>

    <div class="popup_container">
        <ul class="tabs" style="width:400px;float:left">
            <li class="active" data-type="get" data-url="/web/page/popup_admin_board_notice.php">기관 관리자</li>
        </ul>
        <div class="tab_container _board">
        </div>


        <div class="btn_set cb ac pt30">
            <button class="white" onclick="popupClose('AdminListBoard');">닫기</button>
        </div>
    </div>


</div>

<script type="text/javascript">
    (function(){

        var popup_ajax_state='rest';
        /*
        * 팝업 내 ajax 통신을 담당,
        * 통신 후 ,  .tab_container 영역에 결과를 삽입해준다.
        * **/
        function popup_ajax(opts){
            if(! opts.url) return; //url 정보없으면 아무것도 처리하지 않음.

            //현재 ajax 통신중인지 검사
            if(popup_ajax_state=='working') {
                alert('처리중입니다');
                return;
            }//end if
            //현재 ajax 통신중인지 검사 끝

            var defaults={
                dataType: 'html'
                ,html_elem : "#AdminListBoard .tab_container"
            };//end defaults

            var opts = $.extend(defaults,opts); //옵션 merge적용 
          
            opts = $.extend(opts,{
                success: function(response, textStatus, jqXHR){
                    popup_ajax_state='rest'; //ajax 상태 변경

                    var msg = jqXHR.getResponseHeader('X-RESULT_MSG');//결과메세지
                    if(msg){ //넘겨받은 메세지가 있으면 얼럿처리
                        msg=decodeURIComponent(msg);// urldecode
                        dialogMessageOpen('오류', msg);
                        return;
                    }//end if

                    if(opts.dataType=='html'){//데이터 타입이 html 인경우
                        $(opts.html_elem).html(response);
                    }//end if

                    //popup_ajax 이벤트처리
                    $(opts.html_elem).find('.popup_ajax')
                        .off('click.popup_ajax')//중복등록을막기위한 제거
                        .on('click.popup_ajax', function(){
                            var ajax_opt={
                                type: $(this).data('type')
                                , url: $(this).data('url')
                            };//end opt

                            if($(this).data('html_elem')){
                                ajax_opt.html_elem=$(this).data('html_elem');
                            }//end if

                            popup_ajax(ajax_opt);
                        });//end submit
                    //popup_ajax 이벤트처리 끝

                    //ajax submit 이벤트처리
                    $(opts.html_elem).find('.ajax_submit')
                        .off('submit.ajax_submit')//중복등록을막기위한 제거
                        .on('submit.ajax_submit', function(){
                            var ajax_opt={
                                type: $(this).prop('method') //폼의 메소드로 설정
                                , url: $(this).prop('action') //폼의 액션으로 url설정
                                ,data: $(this).serializeArray() //폼의 요소들을 배열로 만들어서 , data 에 등록
                            };//end opt

                            if($(this).data('html_elem')){
                                ajax_opt.html_elem=$(this).data('html_elem');
                            }//end if

                            popup_ajax(ajax_opt);

                            return false;
                        });//end submit
                    //ajax submit 이벤트처리 끝
                }//end success
                ,error: function(jqXHR, textStatus, errorThrown){
                    popup_ajax_state='rest'; //ajax 상태 변경

                    if(jqXHR.status==404){
                        dialogMessageOpen('오류', '페이지를 찾을 수 없습니다');
                    }else{
                        dialogMessageOpen('오류', "통신에 실패하였습니다. 다시 시도해주십시오(status : "+jqXHR.status+", "+jqXHR.statusText+")");
                    }//end if
                }//end error
                ,complete: function(jqXHR, textStatus){
                }//end complete
                ,beforeSend: function(){
                    popup_ajax_state='working'; //ajax 상태 변경
                }//end beforeSend
            });//필수항목들 적용

            $.ajax(opts);//ajax 실행
        }//end func


        $('#AdminListBoard').get(0).notice={//공지사항 관리객체
            goto_url: function(url){ // 공지사항 - 주어진 url로 이동
                popup_ajax({
                    type: 'get'
                    , url: url
                });
            }//end method
        };//end notice
      

        $('#AdminListBoard .tabs li').on('click', function(event) { // 탭 버튼 이벤트
            if($(this).data('type')=='popup'){ //팝업창띄우는 경우
                event.stopImmediatePropagation();// 등록된 다음 이벤트를 실행하지 않기 위함.
                return;
            }//end if
            popup_ajax({
                type: $(this).data('type')
                , url: $(this).data('url')
            });//end popup_ajax
        });//end click
    })();//end anomy func

</script>
