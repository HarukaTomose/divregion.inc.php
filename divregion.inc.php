<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: divregion.inc.php,v 1.3 2021.Sep.
//
// H.Tomose
// region.inc.php �򻲹ͤ˺�����
// Table��ȤäƤ��� Region �򥢥�󥸤���
// <div> �Ǽ¸����롣
// 
// �񼰤�����css ��������뤳�ȡ�ɬ�פʤ�Τϰʲ���
//div.divregion{ ɸ��ǤΥإå���
//div.divregion_contents{ ɸ��Ǥ���ʸ��ʬ
// div.divregion_h1{ h1������Υإå���
//div.divregion_h2{ h2������Υإå���
//
//----
// Ver1.1 �Ǥϡ��������������ĥ���ޤ�����
// ��h1,h2 �ʳ��Υ�����������Ǥ���褦�ˡ�
//   divregion_xxx,divregion_h1_xxx �����������Ƥ����ơ�
//   �嵭xxx ��ʬ��ʸ�������Ǥ���褦�ˤ��ޤ�����
// ��body ��ʬ��ʸ�������طʿ������Ǥ���褦�ˤ��ޤ�����
//----
// Ver1.2 �Ǥϡ��֤ޤȤ�Ƴ���/�Ĥ���פ���ο����ץ����򥵥ݡ��ȡ�
// ����group : �֤ޤȤ�Ƴ���/�Ĥ���ץܥ��������
// ����groupend : �ޤȤ����ν�ü�Ȥʤ�Ԥλ���
// ���ͤ� GamersWiki(https://jpngamerswiki.com)�� ac�ץ饰����򻲹ͤˤ��Ƥ��ޤ���
//----
// Ver1.3�Ǥϡ��ޥ���饤��������б���
// #divregion(�ޤ���ߥ����ȥ�){{
// ��ʸ
// }}
// �������Ȥ��������򥵥ݡ��Ȥ��ޤ������ξ�硢#enddivregion ����Ϥ��ʤ��Ǥ���������



function plugin_divregion_convert()
{
	static $builder = 0;
	if( $builder==0 ) $builder = new DivRegionPluginHTMLBuilder();

	// static ��������Ƥ��ޤä��Τǣ����ܸƤФ줿�Ȥ������ξ��󤬻ĤäƤ����Ѥ�ư��ˤʤ�Τǽ������
	$builder->setDefaultSettings();

	$lastparam="";

	// ���������ꤵ��Ƥ���褦�ʤΤǲ���
	if (func_num_args() >= 1){
		$args = func_get_args();

		// �ޥ���饤�����==��ʸ������ˤʤäƤ����ǽ���Υ����å���
		$lastparam = array_pop($args);
		$tgtcontent = str_replace(array("\r\n","\r","\n"), "\n", $lastparam);
		$tgtcontent = explode("\n",$tgtcontent);

		if( count($tgtcontent)>1 ){
			// ���Ԥ��ʤ���硢���줬��ʸ���ä˲��⤻�����ѥ�᡼���Ȥ����ݻ����Ƥ�����
		}else{
			// ���Ԥ������硢��ʸ�ϥѥ�᡼�����ʤΤǥץ饰������Ǥ�̵�뤹�롣
			//array_push($args,$lastparam);
			array_push($args,$lastparam);
			$lastparam="";
		}
	}

	if (func_num_args() >= 1){
//		$args = func_get_args();

		$builder->setDescription( array_shift($args) );
		foreach( $args as $value ){
			// opened �����ꤵ�줿����ɽ���ϳ��������֤�����
			if( preg_match("/^open/i", $value) ){
				$builder->setOpened();
			// closed �����ꤵ�줿����ɽ�����Ĥ������֤����ꡣ
			}elseif( preg_match("/^close/i", $value) ){
				$builder->setClosed();
			// h1 �����ꤵ�줿�顢�٤��̤�ؤä�
			}elseif( preg_match("/^h1/i", $value) ){
				$builder->setH1();
			// h2 �����ꤵ�줿�顢��������С��ؤä�
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
	// �ȣԣͣ��ֵ�
	return $builder->build($lastparam);
} 


// ���饹�κ������http://php.s3.to/man/language.oop.object-comparison-php4.html
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

	//�� build�᥽�åɤ�Ƥ������򥫥���Ȥ��롣
	//�� ����ϡ����Υץ饰������������JavaScript��ǥ�ˡ������ѿ�̾�����ʤ��ѿ�̾�ˤ��������뤿��˻Ȥ��ޤ�
	var $callcount;

	function DivRegionPluginHTMLBuilder() {
		$this->callcount = 0;
		$this->setDefaultSettings();
	}
	function setDefaultSettings(){
		$this->description = "...";
		$this->headchar = "��";

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
	// convert_html()��Ȥäơ����פ���ʬ�˥֥�󥱥åȥ͡����Ȥ���褦�˲��ɡ�
	function setDescription($description){
		$this->description = convert_html($description);
		// convert_html��Ȥ��� <p>�����ǰϤޤ�Ƥ��ޤ���Mozzila����ɽ���������Τ�<p>������ä���
		$this->description = preg_replace( "/^<p>/i", "", $this->description);
		$this->description = preg_replace( "/<\/p>$/i", "", $this->description);
	}
	function setGroup(){ $this->isgroup = true; }
	function setGroupEnd(){ $this->isgroupend = true; }


	function build($contents){
		$html = array();
		if( $this->callcount == 0 ) {
			//�ǽ�θƤӽФ��ΤȤ��Τߡ�������ץȤ�����
			array_push( $html, $this->buildScripts() );
		}
		$this->callcount++;
		// �ʹߡ��ȣԣ̺ͣ�������
		array_push( $html, $this->buildSummaryHtml() );
		array_push( $html, $this->buildContentHtml() );

		if( strcmp($contents,"") !=0 ){
			array_push( $html, convert_html($contents) );
			array_push( $html, "</div>" );
		}

		return join($html);
	}

	// �� 1�٤Τ߸ƤФ�륹����ץ��ѡ�
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

	// �� �إå���ʬ��ɽ�����ơ����ģ��Ĥ�div��ޤࡣ
	function buildSummaryHtml(){

		$summarystyle = ($this->isopened) ? 
			$this->headerstyle."display:none;" : 
			$this->headerstyle."display:block;";
		$summarystyle2 = ($this->isopened) ? 
			$this->headerstyle."display:block;":
			$this->headerstyle."display:none;" ;

		$retstr = <<<EOD
<div class='$this->divclass' id='drgn_summary$this->callcount' data-mode='contents' style="$summarystyle" onclick='divregion_opentgt($this->callcount)'>��$this->description
</div>
<div class='$this->divclass' id='drgn_summaryV$this->callcount' style="$summarystyle2" onclick='divregion_closetgt($this->callcount)'>��$this->description
</div>
EOD;

		if ($this->isgroup ){

		$retstr = <<<EOD
<div class='$this->groupclass' id='drgn_summary$this->callcount' style="display:block;" onclick='divregion_groupact($this->callcount,0)' data-mode='group'>
<span class='$this->groupclass'>[$this->description]��ޤȤ�Ƴ���</span>
</div>
<div class='$this->groupclass' id='drgn_summaryV$this->callcount' style="display:none;" onclick='divregion_groupact($this->callcount,1)'>
<span class='$this->groupclass'>[$this->description]��ޤȤ���Ĥ���</span>
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

	// �� Ÿ��ɽ�����Ƥ���Ȥ���ɽ��������ʬ��������</div>���Ĥ������� endregion ¦�ˤ��롣
	function buildContentHtml(){
		// �����������롼�׷ϻ���Ǥϲ���ɽ�����ʤ���
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
