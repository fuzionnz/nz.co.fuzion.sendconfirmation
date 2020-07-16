<div id="send_online_receipt-div-label">{$form.send_online_receipt.label}</div>

<div id="send_online_receipt-div-html">{$form.send_online_receipt.html} <br/>
<span class='description'>{ts}Uses contribution.sendconfirmation to send online receipts.{/ts}</span>
</div>
{literal}
<script>
CRM.$(function($) {
  $('#is_email_receipt, #note').closest('tr').after('<tr id="send_online_receipt-tr"><td id="send_online_receipt_label"></td><td id="send_online_receipt_element"></td></tr>');
  $("#send_online_receipt-div-label").appendTo("#send_online_receipt_label");
  $("#send_online_receipt-div-html").appendTo("#send_online_receipt_element");
});
</script>
{/literal}