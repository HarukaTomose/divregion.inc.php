<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: enddivregion.inc.php,v 1.11 2026.Sep.17. Haruka.Tomose
//
// Ver 1.11
// divregionとの組み合わせ用の「終了タグ作成用メソッド」を作成。
// これにより「閉じすぎ/開きすぎ」のチェックを破綻なく行えるようにする
// 各ページの最後に１つ「#enddivregion(clear)」とお守り記載してください。
// #divregion と enddivregion の個数整合性をみて、
// つじつまが合う数の <div>を追加します。
// 文脈は水単純に数合わせ＆警告表示するだけなので、
// ページレイアウトの整合は利用者が実施してください。

function plugin_enddivregion_convert()
{
	
	if (!function_exists('plugin_divregion_getendtag')) {
		//バカ除け。divregion を書かずにいきなりenddivregionを書くケース。
		// この場合、クローズしなくてよい。
		return "";	
	}
	
	$num = func_num_args();
	$args = func_get_args();

	if ($num == 0){
		// 実際に出力するタグは #divregion 側で判断する。
		// (過剰にクローズする場合にそれを戻さないようにする)
		return plugin_divregion_getendtag();
	}else{
		return plugin_divregion_getendtag($args[0]);
		
	}
}

?>
