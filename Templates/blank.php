<!DOCTYPE html>
<html>
    <head>
		<title><?php echo config::get_title() ;?></title>
		<meta charset="utf-8" />
        <link rel="stylesheet" href="/blank.css" />
        		<?php echo config::get_css() ;?>
        <script src="/jquery1.8.2.js"></script>
		<?php echo config::get_script() ;?>

		<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
		<meta name="viewport" content="width=device-width" />
        <title>GROUPE-COGEP - </title>
    </head>

    <body>

		<?= config::get_content(); ?>

    </body>
</html>