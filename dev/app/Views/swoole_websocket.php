<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swoole Websocket</title>

</head>
<body>
    <input type="text" id='SendText'>
    <br>
    <button onclick="send()">send</button>
    <script>
        var wsServer = 'ws://localhost:8080/OpenSwooleWebsocket/socket';
        var websocket = new WebSocket(wsServer);
        websocket.onopen = function (evt) {
            console.log("Successfully connected to the WebSocket service.");
        };

        websocket.onclose = function (evt) {
            console.log("Close Connection.");
        };

        websocket.onmessage = function (evt) {
            console.log('Server Data: ' + evt.data);
        };

        websocket.onerror = function (evt, e) {
            console.log('Error: ' + evt.data);
        };

        function send (){
            websocket.send(document.getElementById('SendText').value);
        }
    </script>
</body>
</html>