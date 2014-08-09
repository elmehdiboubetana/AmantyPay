<?php

	$m2tsecretkey = 'votre clé';

	if($_POST["coderep"] == 0) 
	{
		if($_POST["type"] == "RSS") 
		{
			$clair = $_POST["idpartner"].$_POST["numaudit"].$_POST["numcmd"].$_POST["mntcmd"].$_POST["url"].$_POST["numtrx"];
			if(md5($clair.$m2tsecretkey) != $_POST["mac"])
			{
				$code = 1;
				$msg = 'Error mac';
				echo $code.';'.$msg.';'.md5($clair.$code.$m2tsecretkey).';';
				return;
			}
			if($_POST["mode"] == 'OFF') 
			{
				$msg="Numéro de Commande :  ".$_POST['numcmd'].'    '.
				     "Montant de Commande :  ".$_POST['mntcmd'].'   ';
				mail("votre mail","Commande effectuée (tashilate)",$msg);
			}
			else if ($_POST["mode"] == 'ON')  
			{
				$msg="Numéro de Commande :  ".$_POST['numcmd'].'    '.
				     "Montant de Commande :  ".$_POST['mntcmd'].'   ';
				mail("votre mail","Commande effectuée (en ligne)",$msg);
			}
			$code = 0;
			$msg = 'Payement Aprouved';
			$var='Payement Aprouved'.
			'Numéro de transaction : '.$_POST['numtrx'].' '.
			'Code Commande :'.$_POST['codecmd'].'  '.
			'Montant de Commande : '.$_POST['mntcmd'].' '.
			'Commande Bien enregistrer';
			$mac = md5($code.$clair.$m2tsecretkey);
			echo $code.';'.$msg.';'.$mac.';';
			return;
		}
		else  
		{
            require_once('coupon.php');
		}

	}
	else if ($_POST["coderep"] == 1)  
	{
		if($_POST["type"] == "RSS")   
		{
            $clair = $_POST['idpartner'].$_POST['numaudit'].$_POST['numcmd'].$_POST['mntcmd'].$_POST['url'].$_POST['codecmd'];
			
			if(md5($clair.$m2tsecretkey) != $_POST['mac'])
			{
				$code = 1;
				$msg = 'Error mac';
				echo $code.';'.$msg.';'.md5($clair.$code.$m2tsecretkey).';';
				return;
			}
            $var='Type de commande : Amanty Tashilate   '.
			'Numéro de Commande : '.$_POST['numcmd'].'  '.
			'Code Commande Tashilate :'.$_POST['codecmd'].'  '.
			'Montant de Commande : '.$_POST['mntcmd'].'   '.
			'Commande Bien enregistrer';
			mail("","confirmation enregistrement de Commande",$var);
			$code = 0;
			$msg  = 'confirmation enregistrement Command';
			$mac = md5($code.$clair.$m2tsecretkey);
			echo $code.';'.$msg.';'.$mac.';';
			return;
		}
		else 
		{
			require_once('reservation.php');
		}

	}
	else 
	{
		require_once('error.php');
	}

?>

