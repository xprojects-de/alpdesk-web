
$('div.automationhistorychart').each(function () {

  $(this).html('');

  var trace = {
    x: $(this).attr('data-x').split('||'),
    y: $(this).attr('data-y').split('||'),
    mode: 'lines+markers',
    marker: {
      color: 'rgb(139, 195, 74)',
      size: 5,
      line: {
        color: 'rgb(139, 195, 74)',
        width: 1
      }
    }
  };

  var data = [trace];

  var layout = {
    margin: {
      l: 50,
      r: 30,
      b: 75,
      t: 25,
      pad: 2
    }
  };

  var config = {responsive: true}

  Plotly.newPlot(document.getElementById($(this).attr('id')), data, layout, config);

});


