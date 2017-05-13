<?php
  class nicokaraAPI{

    public $nicokara2_url = "http://www.nicokara.net/";
		public $filters = "(ニコカラ or カラオケ) -カラオケ音源 -北朝鮮 -比較動画 -カラオケ対決 -カラオケが出来るまで -おじさんのカラオケ -カラオケ大好き -カラオケリサイタル -カラオケ店 -暗黒放送 -カラオケソウル -カラオケ回 -カラオケ喫茶 -カラオケ講座 -カラオケまつり -カラオケ祭 -カラオケ祭り -カラオケがあったら -あるあるアカデミー -メイキング -インタビュー -カラオケDAM -とカラオケ -実況 -歌いながら -TIF2016 -長兄デュエット -自称歌い手 -DAM音源 -JOYSOUND音源 -うたスキ -オフ会 -カラオケ配信 -ニコ生 -生主 -カラオケオフ -カラオケボックス -カラオケBOX -カラオケをして -カラオケに -カラオケで -歌ってみた -演奏してみた -踊ってみた -作ってみる -カラオケでうたい -カラオケ会 -ニコニコ技術部 -歌い手 -ランキング -カラオケバトル -カラオケ戦士 -VOCALOID殿堂入り -してみた -PVを作ってみた -WiiカラオケU -カラオケに行く -唄ってみた -うたってみた -softalk -ゆっくり -カラオケ大会 -UTAって -参加者 -行動集 -親フラ -合わせてみた -検索妨害 -混ぜてみた -企画発表 -勝手にPV -嘘字幕シリーズ -重ねてみた -カツヒコ -ぼからまとめ -ニコニコ動画講座 -うたスキ動画 -ニコニコ技術部 -ようつべ技術部";
		public $filters2 = array('sm29396870','sm29961598','sm29772435','sm29768200','sm29395626','sm29338116','sm29343239','sm29124894','sm29075366','so29003987','so29004012','sm20407469','sm23085452','so25876867','sm25904300','sm25767654','sm25447378','sm25447329','sm25447358','sm25437105','sm25436787','sm25414680','sm7594','sm2041140','sm23007380','nm20587183','sm21958722','sm25102267','sm20655942','sm20411404','sm20831656','sm20742013','sm20411382','sm20418011','sm20405110','sm20645731','sm8123825');
  
    function search_json($q,$key,$vocal,$page,$sort2){
			//ページ計算...
			if($page == "" || $page == 1){
				$page=1;
			}
			$page_from = ($page - 1) * 100;
			$page_max = 100;

			if($q!=""){
				$q = $q.' ';
			}else{
				$q=$q;
			}
			
			if($vocal != ""){
				if($vocal == "onvocal"){
					$vocal3='(onvocal or on vocal or オンボ or on　or onvo) -on： -on→ -on⇒ -オンボ： -オンボ→ -オンボ⇒ ';
				}else if($vocal == "offvocal"){
					$vocal3='(offvocal or off vocal or オフボ or off or offvo) -off： -off→ -off⇒ -オフボ： -オフボ→ -オフボ⇒　';
				}
			}else if($vocal == ""){
				$vocal3 = "";
			}
			if($sort2 !== ""){
				$sort = $sort_array[$sort2];
			}else{
				$sort = $sort_array[0];
			}
			if (strpos($sort, "_asc") === FALSE){
				$order = "desc";
			}else{
				$order = "asc";
				$sort = str_replace("_asc","",$sort);
			}
			$url = 'http://api.search.nicovideo.jp/api/';
			$query = $q.$key.$vocal3.$this->filters;
			$data = array(
				"query"=>$query,
				"service"=>array("video"),
				"search"=>array("title","tags"),
				"join"=>array("cmsid","title","description","thumbnail_url","start_time","view_counter","comment_counter","mylist_counter"),
				"filters"=>array("type"=>"range"),
				"from"=>$page_from,
				"size"=>$page_max,
				"sort_by"=>$sort,
				"order"=>$order,
				"issuer"=>"pc",
				"reason"=>"user"
			);
			$options = array(
			  'http' => array(
				'method'  => 'POST',
				'content' => json_encode( $data ),
				'header'=>  "Content-Type: application/json\r\n" .
							"Accept: application/json\r\n"
				)
			);
			$i=1;
			$context  = stream_context_create( $options );
			$result = file_get_contents( $url, false, $context );
			$result = strip_tags($result);
			preg_match_all('/"total":([0-9]+)/i',$result,$total,PREG_PATTERN_ORDER);
			preg_match_all('/"cmsid":"(.*?)"/i',$result, $smid, PREG_PATTERN_ORDER);
			preg_match_all('/"title":"(.*?)"/i',$result,$title,PREG_PATTERN_ORDER);
			preg_match_all('/"description":"(.*?)"/i',$result,$description,PREG_PATTERN_ORDER);
			preg_match_all('/"thumbnail_url":"(.*?)"/i',$result,$thumbnail,PREG_PATTERN_ORDER);
			preg_match_all('/"start_time":"(.*?)"/i',$result,$starttime,PREG_PATTERN_ORDER);
			preg_match_all('/"view_counter":([0-9]+)/i',$result,$viewNum,PREG_PATTERN_ORDER);
			preg_match_all('/"comment_counter":([0-9]+)/i',$result,$commentNum,PREG_PATTERN_ORDER);
			preg_match_all('/"mylist_counter":([0-9]+)/i',$result,$mylistNum,PREG_PATTERN_ORDER);

			$i = 0;

			$totalNum = $total[1][0];
			define('C_PAGE_IN_COUNT', 100); // 1ページ辺りの表示件数
			$TotalPageCount = (int)ceil($totalNum / C_PAGE_IN_COUNT); // 全ページ数
			$videoListArray4[] = array("total_page"=>$TotalPageCount,"total_item"=>$totalNum);
			$videoListArray3[]=array("information"=>$videoListArray4);
			if($totalNum!=0){
				foreach($title[1] as $titleObj){					
					$key = in_array($smid[1][$i], $this->filters2);
					$imageURL = $thumbnail[1][$i];
					if ($key){
						
					}else{
						$videoListArray1[]=array(
							'starttime'=>date('Y/m/d H:i', strtotime($starttime[1][$i])),
							'smid'=>$smid[1][$i],
							'title'=>$titleObj,
							'thumbnail'=>$imageURL,
							'comment'=>$commentNum[1][$i],
							'mylist'=>$mylistNum[1][$i],
							'view'=>$viewNum[1][$i],
							'desc'=>$description[1][$i]
						);
					}
					++$i;
				}
				$videoListArray3[] = array("search"=>$videoListArray1);
				$videoListArray2[] = $videoListArray3;
				header('Content-type: application/json');
				echo json_encode($videoListArray2);
			}else{
				
			}
		}
  }
