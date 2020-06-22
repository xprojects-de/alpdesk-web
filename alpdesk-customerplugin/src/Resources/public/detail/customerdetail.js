
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

if ($('#alpdeskcustomerplugin_detail').length > 0) {
  if ($('#backButton').length > 0) {
    $("#backButton").click(function () {
      sendEventNotification({
        subtarget: 'list'
      });
    });
  }
  if ($('#newProjectButton').length > 0) {
    $("#newProjectButton").click(function () {
      var id = $(this).attr('data-id');
      sendEventNotification({
        id: id,
        type: 'new',
        subtarget: 'detail'
      });
    });
  }
  $("button.editProjectButton").each(function () {
    $(this).click(function () {
      var id = $(this).attr('data-id');
      sendEventNotification({
        id: id,
        type: 'edit',
        subtarget: 'detail'
      });
    });
  });
  $("button.deleteProjectButton").each(function () {
    $(this).click(function () {
      if (confirm("Really delete?")) {
        var id = $(this).attr('data-id');
        sendEventNotification({
          id: id,
          type: 'delete',
          subtarget: 'detail'
        });
      }
    });
  });
  if ($('#saveButton').length > 0) {
    $('#saveButton').click(function () {
      var id = $(this).attr('data-id');
      sendEventNotification({
        id: id,
        type: 'editsave',
        subtarget: 'detail',
        title: $('#title').val(),
        domain: $('#domain').val(),
        ftp: $('#ftp').val(),
        datenbank: $('#datenbank').val(),
        beschreibung: $('#beschreibung').val()
      });
    });
  }
  if ($('#createButton').length > 0) {
    $('#createButton').click(function () {
      var id = $(this).attr('data-id');
      sendEventNotification({
        id: id,
        type: 'createsave',
        subtarget: 'detail',
        title: $('#title').val(),
        domain: $('#domain').val(),
        ftp: $('#ftp').val(),
        datenbank: $('#datenbank').val(),
        beschreibung: $('#beschreibung').val()
      });
    });
  }
}