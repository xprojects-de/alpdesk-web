
function sendEventNotification(params) {
  var event = new CustomEvent("alpdesk", {
    detail: {
      type: 'route',
      target: 'customer',
      params: params
    }
  });
  document.dispatchEvent(event);
}

if ($('#alpdeskcustomerplugin_list').length > 0) {

  if ($('#backButton').length > 0) {
    $("#backButton").click(function () {
      sendEventNotification({
        subtarget: 'list'
      });
    });
  }

  $("button.customerdetailbutton").each(function () {
    $(this).click(function () {
      var id = $(this).attr('data-id');
      sendEventNotification({
        id: id,
        subtarget: 'detail'
      });
    });
  });

  if ($('#newCustomerButton').length > 0) {
    $("#newCustomerButton").click(function () {
      sendEventNotification({
        type: 'newCustomer',
        subtarget: 'list'
      });
    });
  }

  if ($('#createButton').length > 0) {
    $('#createButton').click(function () {
      sendEventNotification({
        type: 'createCustomer',
        subtarget: 'list',
        firma: $('#firma').val(),
        name: $('#name').val(),
        email: $('#email').val(),
        telefon: $('#telefon').val(),
        strasse: $('#strasse').val(),
        ort: $('#ort').val()
      });
    });
  }

  $("button.deleteCustomerButton").each(function () {
    $(this).click(function () {
      if (confirm("Really delete?")) {
        var id = $(this).attr('data-id');
        sendEventNotification({
          id: id,
          subtarget: 'list',
          type: 'deleteCustomer'
        });
      }
    });
  });

  $("button.editCustomerButton").each(function () {
    $(this).click(function () {
      var id = $(this).attr('data-id');
      sendEventNotification({
        id: id,
        type: 'editCustomer',
        subtarget: 'list'
      });
    });
  });

  if ($('#saveCustomerButton').length > 0) {
    $('#saveCustomerButton').click(function () {
      var id = $(this).attr('data-id');
      sendEventNotification({
        id: id,
        type: 'editsaveCustomer',
        subtarget: 'list',
        firma: $('#firma').val(),
        name: $('#name').val(),
        email: $('#email').val(),
        telefon: $('#telefon').val(),
        strasse: $('#strasse').val(),
        ort: $('#ort').val()
      });
    });
  }

}
