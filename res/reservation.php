
<html>
<head>
  <meta content="text/html; charset=utf-8" http-equiv="content-type">
	<title>Page de reservation</title>
</head>
<body>
   <h2 style="text-align: center;"> Merci !</h2>
   <div style="text-align: center; font-size: 20px;">                        
   Notez les informations ci-dessous relatives à votre commande :
   Numéro de Commande : <span style="color:red;"> <?php echo $_POST['numcmd']; ?> </span>
   Montant TTC : <span style="color:red;"> <?php  echo $_POST['mntcmd']; ?> Dhs </span>
   (Prévoir 2,25 Dhs pour les frais dès réception de l'encaissement)
   Pour procéder au règlement de votre commande, merci de vous rendre chez votre ES 
   Tasshilat le plus proche et souscrire au service Amanty. 
   Cliquez <a href="http://www.m2t.ma/index.php?option=com_m2t&lang=fr" target="_blank"> ici </a> pour visualiser la liste du réseau Tasshilat: 
   </div>
  <br>
  <img src="capture.png" alt="tashilate" style="margin-right: 0px; padding-left: 35%; width: 350px;" />
  <br>
<div style="text-align: center; font-size: 20px;"> 
Dès réception du règlement chez l’ES Tasshilat, notre partenaire vous transmettra un email de confirmation avec vos coupons à imprimer. 
<br>
<p style="color:red;">Attention : vous n'avez que 48h pour valider votre commande.</p>
  <div>
    <br>
    
  </div>
	</body>
</html>