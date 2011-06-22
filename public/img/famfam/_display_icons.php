<html>
<head>
<title>Famfam Icons</title>
</head>
<body>
<form style="width:100%; text-align: center;">
	<input type="text" name="search" value="<?php if(isset($_GET['search'])) echo $_GET['search'];?>">
	<input type="submit" name="submit" value="search"/>
</form>
<?php

$glob = isset($_GET['search']) ? glob('*' . $_GET['search'] . '*.png') : glob('*.png');

foreach($glob as $img) {
	echo '<div style="float:left;margin:2px;width:180px;padding:2px;border:1px solid #666;font: 10px arial, helvetica, clean, sans-serif;">';
	echo "<img src='$img' />";
	echo '&nbsp;' . $img;
	echo '</div>';
}
?>
</body>
</html>
