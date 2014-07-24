[{$smarty.block.parent}]


[{if $oDetailsProduct->oxarticles__vtecpfand->value}]
      <span id="productPriceUnit">[{oxmultilang ident="VTEC_PFAND_DETAIL_FIRST" suffix="COLON"}] [{$currency->sign}] [{$oDetailsProduct->getFormatPfand()}] [{oxmultilang ident="VTEC_PFAND_DETAIL_LAST"}]</span> 
[{/if}]       
      
    <!-- [{oxprice price=$oDetailsProduct->getTPrice() currency=$currency}] -->
    <!-- [{$oDetailsProduct->oxarticles__vtecpfand->value}] -->