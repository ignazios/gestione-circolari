<?php
/**
 * Gestione Circolari - Funzioni Gestione Gruppi
 * 
 * @package Gestione Circolari
 * @author Scimone Ignazio
 * @copyright 2011-2014
 * @ver 2.7.3
 */
 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
  die('You are not allowed to call this page directly.'); 
}

//Gestione Gruppi Utenti
add_action( 'init', 'Crea_tassonomia_GruppoUtenti');
add_filter('manage_users_sortable_columns', 'gruppi_user_sortable_columns' );
add_filter('request', 'gruppi_user_column_orderby' );
add_action('manage_users_custom_column', 'gruppi_add_custom_user_columns', 15, 3);
add_filter('manage_users_columns', 'gruppi_add_user_columns', 15, 1);
add_action( 'show_user_profile', 'visualizza_gruppo_utenti' );
add_action( 'edit_user_profile', 'visualizza_gruppo_utenti' );
add_action( 'personal_options_update', 'memorizza_gruppo_utenti' );
add_action( 'edit_user_profile_update', 'memorizza_gruppo_utenti' );

function memorizza_gruppo_utenti( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) ){
		return false;
	}
	$GruppiArray=array();
	foreach($_POST['Gruppo'] as $Gruppo){
		$GruppiArray[]=$Gruppo;
	}
	if (isset($GruppiArray)){
		update_user_meta( $user_id, 'gruppo', $GruppiArray);
	}
}
function sort_terms_hierarchicaly(Array &$cats, Array &$into, $parentId = 0)
{
    foreach ($cats as $i => $cat) {
        if ($cat->parent == $parentId) {
            $into[$cat->term_id] = $cat;
            unset($cats[$i]);
        }
    }

    foreach ($into as $topCat) {
        $topCat->children = array();
        sort_terms_hierarchicaly($cats, $topCat->children, $topCat->term_id);
    }
}
function displayLivelloGruppo($gruppi,$GruppiUtente,$Livello){
	foreach($gruppi as $K=>$gruppo){
		if (in_array($K, $GruppiUtente)){
			$Selezionato= "checked";
		}else{
			$Selezionato="";
		}	

		echo '<p style="margin-bottom:10px;">'
		. '<input type="checkbox" name="Gruppo['.$K.']" value="'.$K.'" '.$Selezionato.' style="margin-left:'.($Livello*20).'px;">'.$gruppo->name
		.'</p>';
		if (count($gruppo->children)>0){
			displayLivelloGruppo($gruppo->children,$GruppiUtente,$Livello+1);
		}
	}
}
function visualizza_gruppo_utenti( $user ) { 
 $gruppiutenti=get_terms('gruppiutenti', array('hide_empty' => false));
 $gruppi = array();
 sort_terms_hierarchicaly($gruppiutenti, $gruppi);
//  echo "<pre>";print_r($gruppi);echo "</pre>";
 $GruppiUtente=get_the_author_meta( 'gruppo', $user->ID );
  if(!is_array( $GruppiUtente )){
	 $GruppiUtente=array($GruppiUtente);
 }
?>
	<h3>Informazioni aggiuntive</h3>
	<table class="form-table">
		<tr>
			<th><label for="gruppo">Gruppi Utente</label></th>
			<td>
<?php	     if (current_user_can('create_users'))
     	{
				displayLivelloGruppo($gruppi,$GruppiUtente,0);
			?>
				<span class="description">Per favore seleziona il gruppo di appartenenza dell'utente.</span>
<?php
		}else
			foreach($gruppi as $K=>$gruppo){
				if (in_array($K, $GruppiUtente)) 
					echo $gruppo."\n";
			}
?>
			</td>
		</tr>
	</table>
<?php }

function gruppi_add_user_columns( $defaults ) {
 	$defaults['gruppo'] = "Gruppo/i";
     return $defaults;
}
function gruppi_add_custom_user_columns($value, $column_name, $id) {
      if( $column_name == 'gruppo' ) {	
      	$IDGruppo=array();
	  	if(($IDG=get_the_author_meta( 'gruppo', $id ))==NULL)
	  		$IDGruppo[]=-1;
	  	else
	  		$IDGruppo[]=$IDG;
//	  	echo "U=".$id." - g=".get_the_author_meta( 'gruppo', $id )."<br />";
		$gruppiutenti=get_terms('gruppiutenti', array('hide_empty' => FALSE,'include'=>$IDGruppo));
		$GruppiUtente="";
		foreach($gruppiutenti as $gruppo){
			$GruppiUtente.=$gruppo->name.", ";
		}
		return substr($GruppiUtente,0,-2);
      }
 }

function gruppi_user_sortable_columns( $columns ) {
	$columns['gruppo'] = 'Gruppo';
	return $columns;
}

function gruppi_user_column_orderby( $vars ) {
 if ( isset( $vars['orderby'] ) && 'gruppo' == $vars['orderby'] ) {
 			$vars = array_merge( $vars, array(
			'meta_key' => 'gruppo',
			'orderby' => 'meta_value',
			'order'     => 'asc'
		) );
	}
	return $vars;
}
/**
* Tassonomia personalizzata Gruppi Utenti
* 
*/
function Crea_tassonomia_GruppoUtenti() 
{
	 register_taxonomy(
		'gruppiutenti',
		'circolari',
		array(
			'public' => true,
			'show_ui' => true,
			'show_admin_column' => false,
			'hierarchical' => true,
			'labels' => array(
				'name' => __( 'Destinatari' ),
				'singular_name' => __( 'Destinatario' ),
				'menu_name' => __( 'Gruppi Utenti' ),
				'search_items' => __( 'Cerca Gruppo' ),
				'popular_items' => __( 'Gruppo più Popolare' ),
				'all_items' => __( 'Tutti i Gruppi' ),
				'edit_item' => __( 'Modifica Gruppo' ),
				'update_item' => __( 'Aggiorna Gruppo' ),
				'add_new_item' => __( 'Aggiungi nuovo Gruppo' ),
				'new_item_name' => __( 'Nome nuovo Gruppo' ),
				'separate_items_with_commas' => __( 'Separa i Destinatari con virgole. Se la circolare è pubblica non indicare nulla' ),
				'add_or_remove_items' => __( 'Aggiungi o rimuovi Destinatari' ),
				'choose_from_most_used' => __( 'Seleziona tra i Destinatari più popolari' ),
			),
			'rewrite' => array(
				'with_front' => true,
				'slug' => 'gruppo/utente' 
			),
			'capabilities' => array(
				'manage_terms' => 'edit_users', // Using 'edit_users' cap to keep this simple.
				'edit_terms'   => 'edit_users',
				'delete_terms' => 'edit_users',
				'assign_terms' => 'read',
			)
		)
	);

}

?>