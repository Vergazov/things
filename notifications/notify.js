var request = new ActiveXObject("Msxml2.XMLHTTP.3.0");
var url = "http://localhost:82/things/public/notify.php";
request.open("GET", url);
request.send(null);
WScript.Sleep(500); // чтобы скрипт не завершился, прежде чем запрос уйдет в сеть