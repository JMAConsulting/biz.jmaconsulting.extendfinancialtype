<div id="chapter_code_trxn_section"><br/>{$form.chapter_code_trxn.label} {$form.chapter_code_trxn.html} <br/> <br/>{$form.fund_code_trxn.label}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$form.fund_code_trxn.html}</div>

{literal}
<script type="text/javascript">
CRM.$( function($) {
  $( document ).ajaxComplete(function( event, xhr, settings ) {
    if ($('#payment_instrument_id').length) {
      $('#chapter_code_trxn_section').show();
      $('#chapter_code_trxn_section').insertAfter($('#payment_instrument_id'));
    }
    else {
      $('#chapter_code_trxn_section').hide();
    }
  });
  if ($('#payment_instrument_id').length) {
    $('#chapter_code_trxn_section').show();
    $('#chapter_code_trxn_section').insertAfter($('#payment_instrument_id'));
  }
  else {
    $('#chapter_code_trxn_section').hide();
  }


  $('#chapter_code_trxn').on('change', function (e) {
    var chapter = e.target.value;
    if ($("#fund_code_trxn option[value='" + chapter + "']").length > 0) {
      $('#fund_code_trxn').val(chapter);
    }
  });
});
</script>
{/literal}
