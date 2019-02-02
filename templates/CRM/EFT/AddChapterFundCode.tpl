<div id="chapter_code">{$form.chapter_code.html} {$form.fund_code.html}</div>

{literal}
<script type="text/javascript">
CRM.$( function($) {
  $( document ).ajaxComplete(function( event, xhr, settings ) {
    $('#chapter_code').insertAfter($('#financial_type_id'));
  });
  $('#chapter_code').insertAfter($('#financial_type_id'));


  $('#chapter_code').on('change', function (e) {
    var chapter = e.target.value;
    if ($("#fund_code option[value='" + chapter + "']").length > 0) {
      $('#fund_code').val(chapter);
    }
  });
})
</script>
{/literal}