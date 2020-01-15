<div id="paymentsTab" style="display:none">
<table class="selector row-highlight" id="newPaymentTable">
  <tbody>
    <tr>
      <th>{ts}Amount{/ts}</th>
      <th>{ts}Type{/ts}</th>
      <th>{ts}Payment Method{/ts}</th>
      <th>{ts}Received{/ts}</th>
      <th>{ts}Transaction ID{/ts}</th>
      <th>{ts}Chapter Code{/ts}</th>
      <th>{ts}Fund Code{/ts}</th>
      <th>{ts}Status{/ts}</th>
      <th></th>
    </tr>
    {foreach from=$payments item=payment}
      <tr class="{cycle values="odd-row,even-row"}">
        <td>{$payment.total_amount|crmMoney:$payment.currency}</td>
        <td>{$payment.financial_type}</td>
        <td>{$payment.payment_instrument}{if $payment.check_number} (#{$payment.check_number}){/if}</td>
        <td>{$payment.receive_date|crmDate}</td>
        <td>{$payment.trxn_id}</td>
        <td>{$payment.chapter_code}</td>
        <td>{$payment.fund_code}</td>
        <td>{$payment.status}</td>
        <td>{$payment.action}</td>
      </tr>
    {/foreach}
  </tbody>
</table>
</div>
{literal}
<script type="text/javascript">
  CRM.$(function ($) {
    // the class .payment-details_group only exists in the edit mode so otherwise look for .crm-info-panel
    if ($('.payment-details_group').length) {
      var paymentsTable = $('.payment-details_group').find('table');
    } else {
      var paymentsTable = $('.crm-info-panel').find('table');
    }
    $(paymentsTable).replaceWith('<table class="selector row-highlight">' + $('#newPaymentTable').html() + '</table>');
  });
</script>
{/literal}
