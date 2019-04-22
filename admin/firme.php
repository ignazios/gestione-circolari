<?php 
/**
 * Gestione Circolari - Funzioni Gestione Firme
 * 
 * @package Gestione Circolari
 * @author Scimone Ignazio
 * @copyright 2011-2014
 * @ver 2.7.3
 */

function circolari_VisualizzaArchivio()
{
global $msg,$TestiRisposte,$Testi;
	$current_user =wp_get_current_user();
	$DataCreazioneUtente=substr(get_userdata($current_user->ID)->user_registered,0,10);
	echo'
		<div class="wrap">
			<i class="fa fa-archive fa-3x" aria-hidden="true"></i> <h2 style="display:inline;margin-left:10px;vertical-align:super;">Archivio Circolari</h2>
		</div>';
	if($msg!="") 
		echo '<div id="message" class="updated"><p>'.$msg.'</p></div>';
	$Posts=GetArchivioCircolari();
	echo '
	<div>
		<table id="TabellaCircolari" class="widefat"  cellspacing="0" width="99%">
			<thead>
				<tr>
					<th style="width:30px;">N°</th>
					<th >Titolo</th>
					<th style="width:60px;"  id="ColOrd" sorted="2">Del</th>
					<th style="width:100px;">Tipo</th>
					<th style="width:130px;">Scadenza</th>
					<th style="width:60px;">Firma</th>
					<th style="width:70px;" >Data Firma</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th style="width:30px;">N°</th>
					<th >Titolo</th>
					<th style="width:60px;">Del</th>
					<th style="width:100px;">Tipo</th>
					<th style="width:130px;">Scadenza</th>
					<th style="width:60px;">Firma</th>
					<th style="width:70px;">Data Firma</th>
				</tr>
			</tfoot>
			<tboby>';
	foreach($Posts as $post){
	//print_r($post);
		if (!(Is_Circolare_Pubblica($post->ID) Or Is_Circolare_per_User($post->ID)))
			continue;
		$sign=get_post_meta($post->ID, "_sign",TRUE);
		$RimuoviFirma="";
		$Campo_Firma="";
		$GGDiff="";
		$BGC="";
		$Scadenza=Get_scadenzaCircolare($post->ID,"DataDB");
//		echo $Scadenza." - ".$sign." - ".Is_Circolare_Firmata($post->ID)." - <br />";
		if($Scadenza>date("Y-m-d") and $sign!="NoFirma" And Is_Circolare_Firmata($post->ID)){
			$Titolo="Rimuovi ".($sign=="Firma"?"Firma":"Espressione");
			$LinkRmFirma=admin_url()."edit.php?post_type=circolari&page=Archivio&op=RemoveFirma&pid=".$post->ID."&circoRmFir=".wp_create_nonce('RmFirmaCircolare');
			$RimuoviFirma='<a href="'.$LinkRmFirma.'"<i class="fa fa-times" aria-hidden="true" title="'.$Titolo.'" style="color:red;"></i></a>';		
		}
		if ($Scadenza>=$DataCreazioneUtente){
			if($sign!="NoFirma"){			
				if ($Scadenza=="9999-12-31")
					$GGDiff=9999;
				else{
					$seconds_diff = strtotime($Scadenza) - strtotime(date("Y-m-d"));
					$GGDiff=floor($seconds_diff/3600/24);				
				}
				if ($GGDiff>0){
					$GGDiff="tra ".$GGDiff." gg";
					$BGC="color: #14D700;";
				}else{
					$GGDiff="da ".(abs($GGDiff)>100?"+100":abs($GGDiff))." gg";
					$BGC="color: red;";
				}	
				if($sign=="Firma"){
					if (Is_Circolare_Firmata($post->ID)){
						 $Campo_Firma="Firmata";
					}else{
						$Campo_Firma="Non Firmata";
					}
				}else{
					$Campo_Firma=$TestiRisposte[get_Circolare_Adesione($post->ID)]->get_Risposta();
				}
			}
		}
//		setup_postdata($post);
//		$dati_firma=get_Firma_Circolare($post->ID);
		echo "
				<tr>
					<td> ".GetNumeroCircolare($post->ID)."</td>
					<td>
					<a href='".get_permalink( $post->ID )."'>
					$post->post_title
					</a>
					</td> 
					<td>".FormatDataItalianoBreve(substr($post->post_date,0,10),TRUE)."</td>
					<td>".Circolari_Tipo::get_TipoCircolare($sign)->get_DescrizioneTipo()."</td>
					<td><spam style='$BGC'>".FormatDataItalianoBreve(Get_scadenzaCircolare( $post->ID,"" ),TRUE)." $GGDiff</spam></td>
					<td>$RimuoviFirma $Campo_Firma</td>
					<td>".FormatDataItalianoBreve(Get_Data_Firma($post->ID),TRUE)."</td>
				</tr>";
	}	
	echo '
				</tbody>
			</table>
		</div>';	
}

function circolari_GestioneFirme()
{
global $msg;
echo'
		<div class="wrap">
			<i class="fa fa-pencil fa-3x" aria-hidden="true"></i> <h2 style="display:inline;margin-left:10px;vertical-align:super;">Circolari da firmare</h2>
		</div>';
if($msg!="") 
	echo '<div id="message" class="updated"><p>'.$msg.'</p></div>';
		VisualizzaTabellaCircolari();		
}
function VisualizzaTabellaCircolari(){
	global $TestiRisposte,$Testi;
	$Posts=GetCircolariDaFirmare("D");
	
	echo '
	<div>
		<table id="TabellaCircolari" class="widefat"  cellspacing="0" width="99%">
			<thead>
				<tr>
					<th style="width:5%;">N°</th>
					<th style="width:60%;">Titolo</th>
					<th style="width:15%;" id="ColOrd" sorted="2">Scadenza</th>
					<th style="width:20%;">Firma</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th style="width:5%;">N°</th>
					<th style="width:60%;">Titolo</th>
					<th style="width:15%;">Scadenza</th>
					<th style="width:20%;">Firma</th>
				</tr>
			</tfoot>
			<tboby>';
//var_dump($Posts);
	$BaseUrl=admin_url()."edit.php";
	foreach($Posts as $post){
		$sign=get_post_meta($post->ID, "_sign",TRUE);
		$Scadenza=Get_scadenzaCircolare($post->ID,"DataDB");
		if ($Scadenza=="9999-12-31")
			$GGDiff=9999;
		else{
			$seconds_diff = strtotime($Scadenza) - strtotime(date("Y-m-d"));
			$GGDiff=floor($seconds_diff/3600/24);

		}
		switch ($GGDiff){
			case ($GGDiff <3):
				$BGC="color: Red;";
				break;
			case ($GGDiff >2 And $GGDiff <7):
				$BGC="color: #FFA500;";
				break;
			case ($GGDiff >6  And $GGDiff <15):
				$BGC="color: #71E600;";
				break;
			default:
				$BGC="color: Blue;";
				break;	
		}
		$TipoCircolare= Circolari_find_Tipo($sign);
		if($sign=="Firma"){
				$Campo_Firma='<a href="'.$BaseUrl.'?post_type=circolari&page=Firma&op=Firma&pid='.$post->ID.'&circoFir='.wp_create_nonce('FirmaCircolare').'">Firma Circolare</a>';
		}elseif ($sign!="NoFirma"){	
				$Campo_Firma=$TipoCircolare->get_Prefisso().': '.$TipoCircolare->get_DescrizioneTipo().'<br />';
				$Campo_Firma.='<form action="'.$BaseUrl.'"  method="get" style="display:inline;">
					<input type="hidden" name="post_type" value="circolari" />
					<input type="hidden" name="page" value="Firma" />
					<input type="hidden" name="op" value="Adesione" />
					<input type="hidden" name="pid" value="'.$post->ID.'" />
					<input type="hidden" name="circoFir" value="'.wp_create_nonce('FirmaCircolare').'" />
					<input type="submit" name="inviaadesione" class="button inviaadesione" id="'.$post->ID.'" value="Firma" rel="'.$post->post_title.'"/>';
				$Risposte=$TipoCircolare->get_Risposte();
				foreach($Risposte as $Risposta){
					$Risp=Circolari_find_Risposta($Risposta);
					$Campo_Firma.='<input type="radio" name="scelta" class="s'.$Risposta.'-'.$post->ID.'" value="'.$Risposta.'"/>'.$Risp->get_Risposta().' '; 
				}
				$Campo_Firma.= ' <input type="hidden" name="to" id="to" value="'.$TipoCircolare->get_TestoElenco().'" />
				</form>';
			}				
			echo "
				<tr>
					<td> ".GetNumeroCircolare($post->ID)."</td>
					<td>
					<a href='".get_permalink( $post->ID )."'>
					$post->post_title
					</a>
					</td>
					<td><spam style='$BGC'>$Scadenza ($GGDiff gg)</spam></td>
					<td>$Campo_Firma</td>
				</tr>";
	}	
	echo '
				</tbody>
			</table>
		</div>';

}