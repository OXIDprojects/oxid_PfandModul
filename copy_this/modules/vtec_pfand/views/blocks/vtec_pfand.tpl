[{$smarty.block.parent}]

<tr>
      <td class="edittext">
       [{ oxmultilang ident="VTEC_PFAND_ADMIN" }] ([{ $oActCur->sign }])
      </td>
      <td class="edittext">
        <input type="text" class="editinput" size="8" maxlength="[{$edit->oxarticles__vtecpfand->fldmax_length}]" name="editval[oxarticles__vtecpfand]" value="[{$edit->oxarticles__vtecpfand->value}]" [{ $readonly }]>
        [{ oxinputhelp ident="VTEC_PFAND_HELP" }]
      </td>     
</tr>
