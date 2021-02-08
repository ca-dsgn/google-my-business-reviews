# Google My Business Reviews via API laden
PHP based script to load latest reviews with Google's My Business API

## Anleitung zur Nutzung der Google My Business API

1. Google Konto erstellen (falls nicht bereits vorhanden)
2. Ein Google Projekt als Google Developer erstellen unter diesem Link: https://console.developers.google.com/
3. Zugriff auf die My Business API beantragen - dazu Anleitung unter diesem Link folgen: https://developers.google.com/my-business/content/prereqs#request-access
4. Nach erfolgreicher Beantragung: My Business API im erstellten Google Projekt aktivieren: https://console.developers.google.com/apis/library
5. Im Projekt-Dashboard (https://console.developers.google.com/apis/dashboard) unter dem Menüpunkt (links) Anmeldedaten auswählen und dort auf "Anmeldedaten erstellen" klicken (oben).
6. Dort OAuth-Client-ID auswählen und im Folgeschritt als Anwendungstyp "Webanwendung" auswählen.
7. Nach dem Erstellen der OAuth-Client-ID in der Listenansicht auf das Download-Icon klicken. Dieses lädt die Client-Secret JSON-Datei.
8. Die Client-Secret JSON-Datei umbenennen zu "client_secret.json" und in den Ordner "private" (Der Ordner muss erstellt werden) im Projekt verschieben.
9. In der Datei reviews.php muss die redirectURL (In Zeile 20) so angepasst werden, dass diese auf das Script zeigt (absolute URL). 
10. Anschließend kann die Datei reviews.php im Browser geöffnet werden.

Nach dem Öffnen wird das Script die Anfrage zum Google OAuth-Prozess weiterleiten und nach einem Google Konto fragen. Dieses Google Konto sollte in My Business das Unternehmen zugeordnet haben. Anschließend muss einmal zugestimmt werden, dass der Anwendung der Zugriff auf das Google Konto über die My Business API gewährt wird. Durch den AccessType "Offline" wird nach dem der Zugriff erteilt wurde auch ein RefreshToken in den Credentials zurückgeliefert, so dass das Token jederzeit automatisch erneuert werden kann, ohne dass nochmals eine Weiterleitung zu Google erfolgen muss. Das passiert über die Zeilen 39-43. Die credentials werden ebenfalls im private Ordner unter dem Dateinamen credentials.json gespeichert. Der private Ordner sollte vor äußerem Zugriff (kein Aufruf über URL) geschützt sein.