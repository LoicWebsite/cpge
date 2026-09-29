<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<?php
	// la date de modification force le rechargement du style après une mise à jour
	$versionStyle = @filemtime(__DIR__ . '/../css/concours.css');
?>
<link rel="stylesheet" href="css/concours.css<?php echo $versionStyle ? '?v=' . $versionStyle : ''; ?>">
 <!-- <script src="https://kit.fontawesome.com/75ff54ab34.js" crossorigin="anonymous"></script>  -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=ID-GOOGLE"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'ID-GOOGLE');
</script>