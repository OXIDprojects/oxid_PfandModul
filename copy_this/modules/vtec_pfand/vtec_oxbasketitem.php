<?php
class  vtec_oxbasketitem extends vtec_oxbasketitem_parent
{
    /**
     * Gibt Artikelpfand zurück
     */
    public function Pfand()
    {
        return $oArticle->oxarticles__vtecpfand->value;
    }

    /**
     * Gibt formatierten Pfandwert zurück
     */
    public function getFormatPfand()
    {
        if(empty($this->oxarticles__vtecpfand->value)) {
            return;
        }
        $pfand = oxRegistry::getLang()->formatCurrency( $this->oxarticles__vtecpfand->value );
        return $pfand;
    }

    /**
     * Gibt Total des Pfandes zurück
     */
    public function TotalPfand()
    {
        $oArticle = $this->getArticle( true );
        if(empty($oArticle->oxarticles__vtecpfand->value)) {
            return;
        }
        return ($oArticle->oxarticles__vtpfand->value * $this->getAmount());
    }

    /**
     * Gibt formatiertes Pfandtotal zurück
     */
    public function FormTotalPfand()
    {
        $oArticle = $this->getArticle( true );
        if(empty($oArticle->oxarticles__vtecpfand->value)) {
            return;
        }
        $pfand = oxRegistry::getLang()->formatCurrency( $oArticle->oxarticles__vtecpfand->value * $this->getAmount());
        return $pfand;
    }

    public function setLink($artLink)
    {
        $this->_sLink = $artLink;
        return $this->getSession()->processUrl( $this->_sLink );
    }
}
?>