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
                        $oBundleItem = $this->addToBasket( $pAId, $oBasketItem->getAmount(), null, null, false, true,null,$key ); //$key hinzugefügt

                        //Pfandartikel Link auf Artikellink setzen damit man nicht auf die Pfandartikel Seite kommt
                        if($oBasketItem->getLink() && $oBundleItem) {
                            $oBundleItem->setLink($oBasketItem->getLink());
                            $oBundleItem->setTitle($oBundleItem->getTitle() . " " . $oBasketItem->getTitle() );       //Neu 
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
    
    /**
     * Adds user item to basket. Returns oxBasketItem object if adding succeeded
     *
     * @param string $sProductID        id of product
     * @param double $dAmount           product amount
     * @param mixed  $aSel              product select lists (default null)
     * @param mixed  $aPersParam        product persistent parameters (default null)
     * @param bool   $blOverride        marker to accumulate passed amount or renew (default false)
     * @param bool   $blBundle          marker if product is bundle or not (default false)
     * @param mixed  $sOldBasketItemId  id if old basket item if to change it
     * @param string $VtecPfandParentKey  Schlüssel des Pfand-Vaters
     *
     * @throws oxOutOfStockException oxArticleInputException, oxNoArticleException
     *
     * @return object
     */
    public function addToBasket( $sProductID, $dAmount, $aSel = null, $aPersParam = null, $blOverride = false, $blBundle = false, $sOldBasketItemId = null, $VtecPfandParentKey = null )
    {
        // enabled ?
        if ( !$this->isEnabled() )
            return null;

        // basket exclude
        if ( $this->getConfig()->getConfigParam( 'blBasketExcludeEnabled' ) ) {
            if ( !$this->canAddProductToBasket( $sProductID ) ) {
                $this->setCatChangeWarningState( true );
                return null;
            } else {
                $this->setCatChangeWarningState( false );
            }
        }
        // Pfandkey
        if(!empty($VtecPfandParentKey)) {
            $sItemId = $this->getItemKey( $sProductID.$VtecPfandParentKey, $aSel, $aPersParam, $blBundle );
        } else {
            $sItemId = $this->getItemKey( $sProductID, $aSel, $aPersParam, $blBundle );
        }
       // Ende       
        if ( $sOldBasketItemId && ( strcmp( $sOldBasketItemId, $sItemId ) != 0 ) ) {
            if ( isset( $this->_aBasketContents[$sItemId] ) ) {
                // we are merging, so params will just go to the new key
                unset( $this->_aBasketContents[$sOldBasketItemId] );
                // do not override stock
                $blOverride = false;
            } else {
                // value is null - means isset will fail and real values will be filled
                $this->_changeBasketItemKey( $sOldBasketItemId, $sItemId );
            }
        }

        // after some checks item must be removed from basket
        $blRemoveItem = false;

        // initialling exception storage
        $oEx = null;

        if ( isset( $this->_aBasketContents[$sItemId] ) ) {

            //updating existing
            try {
                // setting stock check status
                $this->_aBasketContents[$sItemId]->setStockCheckStatus( $this->getStockCheckMode() );
                //validate amount
                //possibly throws exception
                $this->_aBasketContents[$sItemId]->setAmount( $dAmount, $blOverride, $sItemId );
            } catch( oxOutOfStockException $oEx ) {
                // rethrow later
            }

        } else {
            //inserting new
            $oBasketItem = oxNew( 'oxbasketitem' );
            try {
                $oBasketItem->setStockCheckStatus( $this->getStockCheckMode() );
                $oBasketItem->init( $sProductID, $dAmount, $aSel, $aPersParam, $blBundle );
            } catch( oxNoArticleException $oEx ) {
                // in this case that the article does not exist remove the item from the basket by setting its amount to 0
                //$oBasketItem->dAmount = 0;
                $blRemoveItem = true;

            } catch( oxOutOfStockException $oEx ) {
                // rethrow later
            } catch ( oxArticleInputException $oEx ) {
                // rethrow later
                $blRemoveItem = true;
            }
           // Pfandkey
            if(empty($VtecPfandParentKey)) {
                $this->_aBasketContents[$sItemId] = $oBasketItem;
            } else {
                $this->_aBasketContents = $this->array_insert_after($this->_aBasketContents, $VtecPfandParentKey, Array( $sItemId => $oBasketItem));
            }
         }
        // Ende
        //in case amount is 0 removing item
        if ( $this->_aBasketContents[$sItemId]->getAmount() == 0 || $blRemoveItem ) {
            $this->removeItem( $sItemId );
        } elseif ( $blBundle ) {
            //marking bundles
            $this->_aBasketContents[$sItemId]->setBundle( true );
        }

        //calling update method
        $this->onUpdate();

        if ( $oEx ) {
            throw $oEx;
        }

        // notifying that new basket item was added
        if (!$blBundle) {
            $this->_addedNewItem( $sProductID, $dAmount, $aSel, $aPersParam, $blOverride, $blBundle, $sOldBasketItemId );
        }

        // returning basket item object
        return $this->_aBasketContents[$sItemId];
    }

    function array_insert_after($array, $key, $new)
    {
        $keys = array_keys($array);
        $pos = (int) array_search($key, $keys);
        return array_merge(
            array_slice($array, 0, $pos+1),
            $new,
            array_slice($array, $pos+1)
        );
    }      
}
?>