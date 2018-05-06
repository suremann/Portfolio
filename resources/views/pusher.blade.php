<!DOCTYPE html>
<head>
  <title>Pusher Test</title>
  <script src="https://js.pusher.com/4.1/pusher.min.js"></script>
  <script>

    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('97e0bc3b7c60a3b97196', {
      cluster: 'us2',
      encrypted: true
    });

    var channel = pusher.subscribe('coin.BCH');
    channel.bind('PriceUpdate', function(data) {
      console.log("!!EVENT RESPONSE: " + data.message);
    });
  </script>
</head>