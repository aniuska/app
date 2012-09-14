<?
/**
 * @var $title String a title of a page
 * @var $html String content of a page
 */
?>
<h1 id=pageName><?= $title; ?></h1>
<section id=wkPage>
	<div id=mw-content-text>
		<?= $html; ?>
	</div>
</section>
<div id=wkMdlWrp>
	<div id=wkMdlTB>
		<div id=wkMdlTlBar></div>
		<div id=wkMdlClo class=clsIco></div>
	</div>
	<div id=wkMdlCnt></div>
	<div id=wkMdlFtr></div>
</div>