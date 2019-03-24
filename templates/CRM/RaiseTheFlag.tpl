<div id="chapter_code_section" class="crm-section">
  <div class="label">{$form.chapter_code.label}</div>
  <div class="content">{$form.chapter_code.html}</div>
</div>

{literal}
<script type="text/javascript">
CRM.$(function($) {
  // Chapter Code
  $('#chapter_code_section').insertAfter($('#editrow-email-Primary'));
});
</script>
{/literal}