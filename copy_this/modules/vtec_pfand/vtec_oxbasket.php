<?php
class  vtec_oxbasket extends vtec_oxbasket_parent
{
    /**
     * Iterates through basket contents and adds bundles to items + adds
     * global basket bundles
     *
     * @return null
     */
    protected function _addBundles()
    {
        $aBundles = array();
        // iterating through articles and binding bundles
        foreach ( $this->_aBasketContents as $key => $oBasketItem ) {
            try {
                // adding discount type bundles
                if ( !$oBasketItem->isDiscountArticle() && !$oBasketItem->isBundle() ) {
                    $aBundles = $this->_getItemBundles( $oBasketItem, $aBundles );
                } else {
                    continue;
                }

                    // adding item type bundles
                    $aArtBundles = $this->_getArticleBundles( $oBasketItem );

                    //Pfand Artikel als Bundle in den Warenkorb legen
                    if(!empty($oBasketItem->getArticle()->oxarticles__vtecpfand->value))
                    {
                        $pAId = $this->PfandArtikelID($oBasketItem->getArticle()->oxarticles__vtecpfand->value);
                        $oBundleItem = $this->addToBasket( $pAId, $oBasketItem->getAmount(), null, null, false, true );

                        //Pfandartikel Link auf Artikellink setzen damit man nicht auf die Pfandartikel Seite kommt
                        if($oBasketItem->getLink() && $oBundleItem) {
                            $oBundleItem->setLink($oBasketItem->getLink());
                        }
                    }
                    
                    // adding bundles to basket
                    $this->_addBundlesToBasket( $aArtBundles );
            } catch ( oxNoArticleException $oEx ) {
                $this->removeItem( $key );
                oxRegistry::get("oxUtilsView")->addErrorToDisplay( $oEx );
            } catch( oxArticleInputException $oEx ) {
                $this->removeItem( $key );
                oxRegistry::get("oxUtilsView")->addErrorToDisplay( $oEx );
            }
        }

        // adding global basket bundles
        $aBundles = $this->_getBasketBundles( $aBundles );

        // adding all bundles to basket
        if ( $aBundles ) {
            $this->_addBundlesToBasket( $aBundles );
        }
    }

    /**
     * Vergibt eine ArtikelID für den Pfandartikel und schreibt den Pfandpreis in die DB
     */
    protected function PfandArtikelID($price)
    {
        $oxLang = oxLang::getInstance();
        $title = $oxLang->translateString( 'VTEC_PFAND', 0);
        $vtec_mwst = oxConfig::getInstance()->getConfigParam('vtec_pfand_mwst');
        $sSelect = "SELECT oxid FROM oxarticles WHERE oxtitle = '" . $title . "' AND oxprice = '" . $price . "' LIMIT 1";

        $qResult = oxDb::getDb(ADODB_FETCH_ASSOC)->getOne($sSelect);
        if($qResult==false || $qResult==null) {
            $oArticle = oxNew("oxarticle");
            $aLangs= $oxLang->getLanguageIds();
            $oArticle->assign( array( 'oxarticles__active' => 1,
                                      'oxpicsgenerated'  => 0,
                                      'oxarticles__oxprice' => $price,
                                      'oxarticles__oxissearch' => 0,
                                      'oxarticles__oxpic1' => 'pfand.jpg',
                                      'oxarticles__oxvat' => $vtec_mwst,
                              ));
            $oArticle->save();

            //foreach ($aLangs as $iLang){
            for($i=0; $i<count($aLangs); $i++) {
                $oArticle->setLanguage( $i );
                $oArticle->assign(array(
                    "oxarticles__oxtitle" => $oxLang->translateString( 'VTEC_PFAND', $i),
                ));
                $oArticle->save();
            }  
            $qResult = $oArticle->oxarticles__oxid->value;
        }

        return $qResult;
    }
}
?>