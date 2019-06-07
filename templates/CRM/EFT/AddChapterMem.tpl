{if $isBackOffice}
<table>
<tr id="chaptertd_code_section">
  <td class="label">{$form.chapter_code.label}</td> <td class="content">{$form.chapter_code.html}</td>
</tr>
<tr id="fundtd_code_section">
  <td class="label">{$form.fund_code.label}</td> <td class="content">{$form.fund_code.html}</td>
</tr>
</table>
{literal}
<script type="text/javascript">
CRM.$( function($) {
  $('#fundtd_code_section').insertAfter($('.crm-membership-form-block-membership_type_id'));
  $('#chaptertd_code_section').insertAfter($('.crm-membership-form-block-membership_type_id'));
  $('#chapter_code').on('change', function (e) {
    var chapter = e.target.value;
    if ($("#fund_code option[value='" + chapter + "']").length > 0) {
      $('#fund_code').val(chapter);
    }
  });
});
</script>
{/literal}
{else}
<div id="chapter_code_section"><div class="label">{$form.chapter_code.label}</div> <div class="content">{$form.chapter_code.html}</div></div>
{literal}
<script type="text/javascript">
CRM.$( function($) {
  $('#chapter_code_section').insertAfter($('#membership'));
});
</script>
{/literal}
{/if}