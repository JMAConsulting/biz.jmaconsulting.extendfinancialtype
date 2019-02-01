<div id="chapter_code" style="margin-right:60px;float:right">{$form.chapter_code.html}</div>

{literal}
<script type="text/javascript">
CRM.$( function($) {
  $( document ).ajaxComplete(function( event, xhr, settings ) {
    $('#chapter_code').insertAfter($('#financial_type_id'));
  });
  $('#chapter_code').insertAfter($('#financial_type_id'));

})
</script>
{/literal}