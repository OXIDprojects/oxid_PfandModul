<?php

/**
 *
 * Version:    1.0
 * Author:     Pasquale Pari | Vendingtechnik
 * Author URL: http://www.vendingtechnik.com
 * Originallösungsansatz aus der Oxid Community (http://forum.oxid-esales.com/showthread.php?t=528&page=3)
 * License:    GNU GPL 3.0
 *             !! it is forbidden to resell this Software !!
 */

/**
 * Metadata version
 */
$sMetadataVersion = '1.1';


$aModule = array (
    'id'           => 'vtec_pfand',
    'title'        => 'Pfandberechnung für den Oxid Shop, ab CE 4.8.6',
    'description'  => 'Fügt in der Artikelverwaltung das Feld Pfand hinzu, enthält dieses Feld einen Wert wird der Wert als Pfandartikel dem Warenkorb hinzugef&uuml;gt.<br \> Originalcode und L&ouml;sungsansatz aus der Oxid <a href="http://forum.oxid-esales.com/showthread.php?t=528&page=3" target="_blank">Communitiy (Klick)</a>',
    'thumbnail'    => 'pfand.jpg',
    'version'      => '1.0',
    'author'       => 'Pasquale Pari',
    'url'          => 'http://www.vendingtechnik.com <br \> http://www.getraenkekiste.ch',
    'email'        => 'pasquale.pari@vendingtechnik.com',
    'extend'       => array (
        'oxarticle'     => 'vtec_pfand/vtec_pfand',
        'oxbasketitem'  => 'vtec_pfand/vtec_oxbasketitem',
        'oxbasket'      => 'vtec_pfand/vtec_oxbasket',
        ),
        
     'settings'     => array
    (
        array(
                'group'     => 'main',
                'name'      => 'vtec_pfand_mwst',
                'type'      => 'str',
                'value'     => '0',
              ),
     ),            
    
    'blocks' => array(
        array('template'     => 'article_main.tpl',
              'block'        => 'admin_article_main_form',         
              'file'         => '/views/blocks/vtec_pfand.tpl'
              ),
        array('template'     => 'page/details/inc/productmain.tpl',
              'block'        => 'details_productmain_priceperunit',
              'file'         => '/views/blocks/vtec_detail_pfandanzeige',
             ),      
        ),
);
