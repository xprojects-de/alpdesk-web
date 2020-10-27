
$('div.automationhistorychart').each(function () {
  Plotly.newPlot(document.getElementById($(this).attr('id')), [{
      x: [1, 2, 3, 4, 5],
      y: [1, 2, 4, 8, 16]}], {
    margin: {t: 0}
  });
});


