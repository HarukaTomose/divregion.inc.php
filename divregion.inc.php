<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: divregion.inc.php,v 1.3 2021.Sep.
//
// H.Tomose
// region.inc.php を参考に作成。
// Tableを使っていた Region をアレンジし、
// <div> で実現する。
// 
// 書式は別途css で定義すること。必要なものは以下：
//div.divregion{ 標準でのヘッダ行
//div.divregion_contents{ 標準での本文部分
// div.divregion_h1{ h1指定時のヘッダ行
//div.divregion_h2{ h2指定時のヘッダ行
//
//----
// Ver1.1 では、スタイル指定を拡張しました。
// ・h1,h2 以外のスタイルを指定できるように。
//   divregion_xxx,divregion_h1_xxx を事前定義しておいて、
//   上記xxx 部分を文字列指定できるようにしました。
// ・body 部分の文字色・背景色を指定できるようにしました。
//----
// Ver1.2 では、「まとめて開く/閉じる」ための新オプションをサポート。
// 　　group : 「まとめて開く/閉じる」ボタンの設置
// 　　groupend : まとめ操作の終端となる行の指定
// 仕様は GamersWiki(https://jpngamerswiki.com)の acプラグインを参考にしています。
//----
// Ver1.3では、マルチライン引数に対応。
// #divregion(折り畳みタイトル){{
// 本文
// }}
// ・・・という形式をサポートします。この場合、#enddivregion 指定はしないでください。



function plugin_divregion_convert()
{
	static $builder = 0;
	if( $builder==0 ) $builder = new DivRegionPluginHTMLBuilder();

	// static で宣言してしまったので２回目呼ばれたとき、前の情報が残っていて変な動作になるので初期化。
	$builder->setDefaultSettings();

	$lastparam="";

	// 引数が指定されているようなので解析
	if (func_num_args() >= 1){
		$args = func_get_args();

		// マルチライン引数==本文も引数になっている可能性のチェック。
		$lastparam = array_pop($args);
		$tgtcontent = str_replace(array("\r\n","\r","\n"), "\n", $lastparam);
		$tgtcontent = explode("\n",$tgtcontent);

		if( count($tgtcontent)>1 ){
			// 改行がない場合、それが本文。特に何もせず、パラメータとして保持しておく。
		}else{
			// 改行がある場合、本文はパラメータ外なのでプラグイン内では無視する。
			//array_push($args,$lastparam);
			array_push($args,$lastparam);
			$lastparam="";
		}
	}

	if (func_num_args() >= 1){
//		$args = func_get_args();

		$builder->setDescription( array_shift($args) );
		foreach( $args as $value ){
			// opened が指定されたら初期表示は開いた状態に設定
			if( preg_match("/^open/i", $value) ){
				$builder->setOpened();
			// closed が指定されたら初期表示は閉じた状態に設定。
			}elseif( preg_match("/^close/i", $value) ){
				$builder->setClosed();
			// h1 が指定されたら、べたぬりへっど
			}elseif( preg_match("/^h1/i", $value) ){
				$builder->setH1();
			// h2 が指定されたら、アンダーバーへっど
			}elseif( preg_match("/^h2/i", $value) ){
				$builder->setH2();
			}elseif( preg_match("/^hstyle:([0-9a-zA-Z]*)/i", $value,$match) ){
				$builder->setHCSS($match[1]);
			}elseif( preg_match("/^cstyle:([0-9a-zA-Z]*)/i", $value,$match) ){
				$builder->setCCSS($match[1]);

			}elseif( preg_match("/^gstyle:([0-9a-zA-Z]*)/i", $value,$match) ){
				$builder->setGCSS($match[1]);

			}elseif( preg_match("/^color:(#[0-9a-fA-F]*)/i", $value,$match) ){
				$builder->AddCSS( $value);
			}elseif( preg_match("/^background-color:(#[0-9a-fA-F]*)/i", $value,$match) ){
				$builder->AddCSS( 'background-color:'.$match[1]);
			}elseif( preg_match("/^content-color:(#[0-9a-fA-F]*)/i", $value,$match) ){
				$builder->AddBodyCSS( 'color:'.$match[1]);
			}elseif( preg_match("/^content-bgcolor:(#[0-9a-fA-F]*)/i", $value,$match) ){
				$builder->AddBodyCSS( 'background-color:'.$match[1]);
			}elseif( preg_match("/^groupend/i", $value) ){
				$builder->setGroupEnd();
			}elseif( preg_match("/^group/i", $value) ){
				$builder->setGroup();
			}


		}
	}
	// ＨＴＭＬ返却
	return $builder->build($lastparam);
} 


// クラスの作り方⇒http://php.s3.to/man/language.oop.object-comparison-php4.html
class DivRegionPluginHTMLBuilder
{
	var $description;
	var $headchar;
	var $isopened;
	var $isgroup;
	var $isgroupend;
	var $scriptVarName;

	var $borderstyle;
	var $headerstyle;
	var $groupstyle;

	var $divclass;
	var $contentclass;
	var $groupclass;

	//↓ buildメソッドを呼んだ回数をカウントする。
	//↓ これは、このプラグインが生成するJavaScript内でユニークな変数名（被らない変数名）を生成するために使います
	var $callcount;

	function DivRegionPluginHTMLBuilder() {
		$this->callcount = 0;
		$this->setDefaultSettings();
	}
	function setDefaultSettings(){
		$this->description = "...";
		$this->headchar = "▼";

		$this->isopened = false;
		$this->isgroup = false;
		$this->isgroupend = false;

		$this->headerstyle = 'cursor:pointer;'; 
		$this->borderstyle = ''; 
		$this->groupstyle = ''; 

		$this->divclass = 'divregion';
		$this->contentclass = 'divregion_contents';
		$this->groupclass = 'divregion_group';
	}
	function setClosed(){ $this->isopened = false; }
	function setOpened(){ $this->isopened = true; }
	function setH1(){ $this->divclass = 'divregion_h1'; }
	function setH2(){ $this->divclass = 'divregion_h2'; }
	function setHCSS($foo){ $this->divclass = 'divregion_'.$foo; }
	function setCCSS($foo){ $this->contentclass = 'divregion_contents_'.$foo; }
	function setGCSS($foo){ $this->groupclass = 'divregion_group_'.$foo; }

	function AddCSS($foo){ $this->headerstyle .= $foo.';'; }
	function AddBodyCSS($foo){ $this->borderstyle .= $foo.';'; }
	// convert_html()を使って、概要の部分にブランケットネームを使えるように改良。
	function setDescription($description){
		$this->description = convert_html($description);
		// convert_htmlを使うと <p>タグで囲まれてしまう。Mozzilaだと表示がずれるので<p>タグを消す。
		$this->description = preg_replace( "/^<p>/i", "", $this->description);
		$this->description = preg_replace( "/<\/p>$/i", "", $this->description);
	}
	function setGroup(){ $this->isgroup = true; }
	function setGroupEnd(){ $this->isgroupend = true; }


	function build($contents){
		$html = array();
		if( $this->callcount == 0 ) {
			//最初の呼び出しのときのみ、スクリプトを挿入
			array_push( $html, $this->buildScripts() );
		}
		$this->callcount++;
		// 以降、ＨＴＭＬ作成処理
		array_push( $html, $this->buildSummaryHtml() );
		array_push( $html, $this->buildContentHtml() );

		if( strcmp($contents,"") !=0 ){
			array_push( $html, convert_html($contents) );
			array_push( $html, "</div>" );
		}

		return join($html);
	}

	// ■ 1度のみ呼ばれるスクリプト用。
	function buildScripts(){
		return <<<EOD
<script>
function divregion_opentgt(id){
	n=id;
	if(document.getElementById('drgn_summary'+n)!=null){
		document.getElementById('drgn_content'+n).style.display='block';
		document.getElementById('drgn_summaryV'+n).style.display='block';
		document.getElementById('drgn_summary'+n).style.display='none';
	} 
}

function divregion_closetgt(id){
	n=id;
	if(document.getElementById('drgn_summary'+n)!=null){
		document.getElementById('drgn_content'+n).style.display='none';
		document.getElementById('drgn_summaryV'+n).style.display='none';
		document.getElementById('drgn_summary'+n).style.display='block';
	} 
}

function divregion_groupact(id,sw){

	if(sw==0){
		document.getElementById('drgn_summaryV'+id).style.display='block';
		document.getElementById('drgn_summary'+id).style.display='none';

	}else if(sw==1){
		document.getElementById('drgn_summaryV'+id).style.display='none';
		document.getElementById('drgn_summary'+id).style.display='block';

	}

	n=id+1;
	do{
		tgt='drgn_summary'+n;
		if(document.getElementById('drgn_summary'+n)==null){
			n= 0;		
		}else if(document.getElementById('drgn_summary'+n).dataset.mode=='contents'){
			if(sw==0) divregion_opentgt(n);
			else divregion_closetgt(n);
			n++;
		}else{
			n= 0;
		} 
	} while( n!= 0 );


}

</script>
EOD;

	}

	// ■ ヘッダ部分の表示内容。開閉２つのdivを含む。
	function buildSummaryHtml(){

		$summarystyle = ($this->isopened) ? 
			$this->headerstyle."display:none;" : 
			$this->headerstyle."display:block;";
		$summarystyle2 = ($this->isopened) ? 
			$this->headerstyle."display:block;":
			$this->headerstyle."display:none;" ;

		$retstr = <<<EOD
<div class='$this->divclass' id='drgn_summary$this->callcount' data-mode='contents' style="$summarystyle" onclick='divregion_opentgt($this->callcount)'>▼$this->description
</div>
<div class='$this->divclass' id='drgn_summaryV$this->callcount' style="$summarystyle2" onclick='divregion_closetgt($this->callcount)'>▲$this->description
</div>
EOD;

		if ($this->isgroup ){

		$retstr = <<<EOD
<div class='$this->groupclass' id='drgn_summary$this->callcount' style="display:block;" onclick='divregion_groupact($this->callcount,0)' data-mode='group'>
<span class='$this->groupclass'>[$this->description]をまとめて開く</span>
</div>
<div class='$this->groupclass' id='drgn_summaryV$this->callcount' style="display:none;" onclick='divregion_groupact($this->callcount,1)'>
<span class='$this->groupclass'>[$this->description]をまとめて閉じる</span>
</div>
EOD;
		}

		if ($this->isgroupend ){

		$retstr = <<<EOD
<div class='$this->divclass' id='drgn_summary$this->callcount' style='display:none' data-mode='groupend'></div>

EOD;
		}
		return $retstr;

	}

	// ■ 展開表示しているときの表示内容部分。ここの</div>の閉じタグは endregion 側にある。
	function buildContentHtml(){
		// ただし、グループ系指定では何も表示しない。
		if ($this->isgroup ) return "";
		if ($this->isgroupend ) return "";

		$contentstyle = ($this->isopened) ? 
			$this->borderstyle."display:block;" : 
			$this->borderstyle."display:none;";

		$retstr = <<<EOD
<div class='$this->contentclass' id='drgn_content$this->callcount' style="$contentstyle">
EOD;

		return $retstr;
	}
//valign='top' 

}// end class RegionPluginHTMLBuilder

?>
