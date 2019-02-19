<div id="chapter_code_section">{$form.chapter_code.html} {$form.fund_code.html}</div>

{literal}
<script type="text/javascript">
CRM.$( function($) {
  $( document ).ajaxComplete(function( event, xhr, settings ) {
    $('#chapter_code_section').insertAfter($('#financial_type_id'));
  });
  $('#chapter_code_section').insertAfter($('#financial_type_id'));


  $('#chapter_code').on('change', function (e) {
    var chapter = e.target.value;
    if ($("#fund_code option[value='" + chapter + "']").length > 0) {
      $('#fund_code').val(chapter);
    }
  });

  $('#financial_type_id').on('change', function (e) {
    var ft = this.options[this.selectedIndex].text;

    fts = [
      "General Donation",
      "Adult Support Program",
      "Autism Awareness Day",
      "Proceeds from Local Fundraising",  
    ];

    switch (ft) {
      case 'Building Brighter Futures Fund':
        $('#chapter_code').val('1000');
        $('#fund_code').val('1000'); // FIXME
      break;

      case 'Eleanor Ritchie Scholarship':
      case 'Jeanette Holden Scholarship':
      case 'Research':
      case 'Hollylyn Towie Scholarship':
        $('#chapter_code').val('1000');
        $('#fund_code').val('1000');
      break;
    }

    if (jQuery.inArray(ft, fts) == -1) {
      $('#chapter_code').val('1000');
      $('#chapter_code').trigger('change');
      $('#chapter_code').attr('readonly', 'true');
    }
    else {
      $('#chapter_code').removeAttr('readonly');
    }
  });
});
</script>
{/literal}
