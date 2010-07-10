<?php
include("config.php");
require_once('core/dbsimple/Generic.php'); // including simple conecting for DB
require_once('core/functions.php');

$rDB = DbSimple_Generic::connect($db_config["rdb_type"]."://".$db_config["rdb_username"].":".$db_config["rdb_pass"]."@".$db_config["rdb_hostname"].":".$db_config["rdb_port"]."/".$db_config["rdb_db"]);
$cDB = DbSimple_Generic::connect($db_config["cdb_type"]."://".$db_config["cdb_username"].":".$db_config["cdb_pass"]."@".$db_config["cdb_hostname"].":".$db_config["cdb_port"]."/".$db_config["cdb_db"]);
$wDB = DbSimple_Generic::connect($db_config["wdb_type"]."://".$db_config["wdb_username"].":".$db_config["wdb_pass"]."@".$db_config["wdb_hostname"].":".$db_config["wdb_port"]."/".$db_config["wdb_db"]);

$rDB->setErrorHandler('databaseErrorHandler');
$cDB->setErrorHandler('databaseErrorHandler');
$wDB->setErrorHandler('databaseErrorHandler');

$rDB->query("SET NAMES ".$db_config['db_encoding']);
$cDB->query("SET NAMES ".$db_config['db_encoding']);
$wDB->query("SET NAMES ".$db_config['db_encoding']);

if(isset($_GET['status'])) {
	$img = ImageCreateTrueColor(250, 80);
	$online = onlineChars(); // ������ �� ������
	$count  = countChars();	 // ������ �� ���������� �����
	$server = $rDB->selectRow("SELECT name,address,port FROM `realmlist` ");
	$uptime = uptimeServer();// ������ �� ������� - �� ������������ ���� ���
	$statusWorld = testSocketRealm($server['address'],$server['port']);
	
	$bg_alfa = ImageColorAllocateAlpha($img, 255, 255, 255, 70); // ��� �����
	if($statusWorld) { // ������
		$bg_color = ImageColorAllocate($img, 0, 255, 0); // ���� ��������� ���� �����
		$bg_colorAlpha = ImageColorAllocateAlpha($img, 0, 255, 0, 80); // ���� ��������� ������
		list($dest_r, $dest_g, $dest_b) = getHexColors($color_on); // ���� ����
		$text_color_2 = $text_color['title_online'];
	}elseif(!$statusWorld && $online[0]>0){	// �������/����
		$bg_color = ImageColorAllocate($img, 255, 255, 255); // ���� ��������� ���� �����
		$bg_colorAlpha = ImageColorAllocateAlpha($img, 255, 255, 255, 80); // ���� ��������� ������
		//list($dest_r, $dest_g, $dest_b) = getHexColors($color_crash); // ���� ����
		$text_color_2 = $text_color['title_crash'];
	}else{	// �������/shutdown
		$bg_color = ImageColorAllocate($img, 255, 0, 0); // ���� ��������� ���� �����
		$bg_colorAlpha = ImageColorAllocateAlpha($img, 255, 0, 0, 80); // ���� ��������� ������
		list($dest_r, $dest_g, $dest_b) = getHexColors($color_off); // ���� ����
		$text_color_2 = $text_color['title_offline'];
	}

	$base_r = 0; $base_g = 0; $base_b = 0;
	for($i=0; $i<ImageSY($img); $i++) {
		$r = min($base_r + (($dest_r) / ImageSY($img) * $i) ,255);
		$g = min($base_g + (($dest_g) / ImageSY($img) * $i) ,255);
		$b = min($base_b + (($dest_b) / ImageSY($img) * $i) ,255);
		$c = ImageColorAllocate($img, $r, $g, $b);
		ImageLine($img, 0, $i, ImageSX($img), $i, $c);
		ImageColorDeallocate($img, $c);
	}

	/* top left */
	$offset_1 = 5;
	// ($img, ������_�����_x, ������_������_y, ������_������_x, ������_�����_y, ���� ����)
	//ImageFilledRectangle($img, $offset_1, $offset_1, ImageSX($img)-50*$offset_1-2, ImageSY($img)-8*$offset_1-2, $bg_alfa); // ��������� ������������ �����
	//ImageRectangle($img, $offset_1, $offset_1, ImageSX($img)-50*$offset_1-2, ImageSY($img)-8*$offset_1-2, $bg_colorAlpha); // ������ ����������� �����
	/* foot left */
	$offset_2 = 5;
	// ($img, ������_�����_x, ������_������_y, ������_������_x, ������_�����_y, ���� ����)
	ImageFilledRectangle($img, $offset_2, 5*$offset_2+2, ImageSX($img)-18*$offset_2, ImageSY($img)-$offset_2, $bg_alfa); // ��������� ������������ �����
	ImageRectangle($img, $offset_2, 5*$offset_2+2, ImageSX($img)-18*$offset_2, ImageSY($img)-$offset_2, $bg_colorAlpha); // ������ ����������� �����

	ImageFilledRectangle($img, 33*$offset_1, $offset_1, ImageSX($img)-$offset_1, ImageSY($img)-$offset_1, $bg_alfa); // ��������� ������������ �����
	ImageRectangle($img, 33*$offset_1, $offset_1, ImageSX($img)-$offset_1, ImageSY($img)-$offset_1, $bg_colorAlpha); // ������ ����������� �����

	ImageRectangle($img, 0, 0, ImageSX($img), ImageSY($img), $bg_color); // ��������� ���� ��������

/////////////////////////////////////
	$text_color_1 = $text_color['in_block']; // ���� ���� �������� � ������ - ����� ����� ������ �� ��������� ������

	// �������� �� ���� ������
	list($text_r, $text_g, $text_b) = getHexColors($text_color_1);
	list($s_text_r, $s_text_g, $s_text_b) = getHexColors($text_color_2);
	$textcolor = ImageColorAllocate($img, $text_r, $text_g, $text_b);
	$secondaryTextColor = ImageColorAllocate($img, $s_text_r, $s_text_g, $s_text_b);

	/* ������ ����� */
	ImageTTFText($img, 12, 0, 9, 22, $secondaryTextColor, $mainFont, $server['name']);
	ImageTTFText($img, 8, 0, 9, 40, $textcolor, $mainFont, win2uni("������: ".$count[1]." (".$online[1]." ������)"));
	ImageTTFText($img, 8, 0, 9, 54, $textcolor, $mainFont, win2uni("����: ".$count[2]." (".$online[2]." ������)"));
	ImageTTFText($img, 8, 0, 9, 68, $textcolor, $mainFont, win2uni("�����: ".$count[0]." (".$online[0]." ������)"));

	ImageTTFText($img, 8, 0, 190, 18, $textcolor, $mainFont, win2uni("�����"));
	ImageTTFText($img, 8, 0, 170, 30, $textcolor, $mainFont, win2uni("������: ".$rates['quest']));
	ImageTTFText($img, 8, 0, 170, 42, $textcolor, $mainFont, win2uni("�����: ".$rates['honor']));
	ImageTTFText($img, 8, 0, 170, 54, $textcolor, $mainFont, win2uni("����: ".$rates['npc']));
	ImageTTFText($img, 8, 0, 170, 66, $textcolor, $mainFont, win2uni("����: ".$rates['drop'])); 

	header("Content-disposition: inline; filename=\"status.png\"");
	header("content-type: image/png");
	header("cache-control: max-age=86400");
	ImagePNG($img);
}

?>