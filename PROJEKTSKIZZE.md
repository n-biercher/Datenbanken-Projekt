# Projektskizze

## 1. Ziel des Projekts

Im Projekt soll eine Web-Anwendung auf Basis von `PHP` und `MySQL/MariaDB` entstehen, mit der Radrennen angemeldet, durchgeführt und ausgewertet werden können. Der Schwerpunkt liegt nicht auf Design, sondern auf sauberer serverseitiger Umsetzung, korrekter Datenhaltung und sinnvoller Nutzung von Datenbankfunktionen.

Die Anwendung soll drei fachliche Bereiche abdecken:

1. Erfassung und Verwaltung von Teams, Fahrern, Trainings und Rennanmeldungen
2. Auswertung von Trainingsdaten für Teamchefs
3. gemeinsame Datenbasis für externe Anwendungen zur Prämienberechnung

## 2. Verbindliche Rahmenbedingungen laut Professor

- Umsetzung mit `PHP` sowie `MySQL` oder `MariaDB`
- Fokus auf Datenbankzugriffe und serverseitige Logik
- ein einfaches link-basiertes Menü ist ausreichend
- GUI und Benutzerführung gehen nicht in die Bewertung ein
- kein `AJAX`
- kein JavaScript für die Kernfunktionalität
- keine Frameworks
- Fehlermeldungen dürfen schlicht in HTML/PHP ausgegeben werden
- Schutz vor `SQL-Injection` und `Cross-Site-Scripting` ist verpflichtend
- Datenbankoperationen müssen konsistent umgesetzt werden
- `.inc.php` nur für tatsächlich inkludierte Dateien verwenden
- reine PHP-Seiten ohne fachlichen Inhalt, die nur weiterleiten, sollen vermieden werden

## 3. Benutzerrollen

### Teamchef

- registriert sich mit eindeutigem Login-Namen und Kennwort
- legt ein Team an
- meldet sich an und pflegt die Daten seines Teams
- verwaltet Fahrer seines Teams
- erfasst Trainings
- meldet Fahrer zu Rennen an
- kann Anmeldungen eines Rennens in ein anderes Rennen kopieren
- wertet die Trainingsdaten seines Teams aus

### Rennveranstalter

- registriert sich mit eindeutigem Namen und Kennwort
- meldet sich an
- legt Rennen an
- erfasst Rennergebnisse

### Externe Desktop-Anwendungen

- verwenden dieselbe Datenbank
- greifen auf Login-Daten und Renninformationen zu
- dienen der Prämienberechnung
- werden nicht als Teil dieser Web-Anwendung entwickelt

## 4. Pflichtfunktionen der Web-Anwendung

### 4.1 Startseite

Die Startseite soll mindestens diese Einstiege bieten:

- neues Team anlegen
- Teamchef-Login
- Veranstalter-Registrierung
- Veranstalter-Login

### 4.2 Teamanlage und Teamchef

Beim Anlegen eines Teams werden mindestens erfasst:

- Teamname
- Name und Vorname des Teamchefs
- eindeutiger Login-Name des Teamchefs
- Kennwort

Zusätzlich gilt:

- jeder Teamname ist eindeutig
- jeder Teamname kann nur einmal genutzt werden
- Teamchefs sollen sich später erneut anmelden und Teamdaten pflegen können

### 4.3 Fahrerverwaltung

Teamchefs müssen Fahrer:

- anlegen
- ändern
- löschen

Ein Fahrer besitzt mindestens:

- teamspezifische Mitarbeiter-ID
- Vorname
- Nachname
- Adresse
- Telefonnummer

Für die Erfassungslogik gilt:

- die Seite für Neues Anlegen und Ändern soll im Aufbau gleich sein
- beim Ändern darf die Mitarbeiter-ID nicht verändert werden

### 4.4 Trainingserfassung

Teamchefs sollen für Fahrer Trainings erfassen können. Zu speichern sind mindestens:

- Datum
- Kilometer
- Trainingsziel

Die Standard-Trainingsziele sind:

- Ausdauer
- Sprintkraft
- Steigungen

Wichtig:

- pro Fahrer wird an einem Tag nicht mehr als ein Training durchgeführt
- weitere Trainingsziele sollen in späteren Ausbaustufen möglich sein

### 4.5 Rennverwaltung durch Veranstalter

Nach erfolgreicher Anmeldung kann ein Veranstalter Rennen anlegen. Ein Rennen besitzt mindestens:

- vom System vergebene Renn-ID
- Datum
- Startort
- Kilometer
- Höhenmeter
- maximale Steigung in Prozent

### 4.6 Rennanmeldung

Ein Teamchef soll Fahrer zu ausgewählten Rennen anmelden können.

Der Ablauf laut Aufgabenstellung:

1. Teamchef wählt ein vorhandenes oder zukünftiges Rennen aus.
2. Teamchef gibt an, wie viele Fahrer angemeldet werden sollen.
3. Es erscheint eine Tabelle mit genau dieser Anzahl an Eingabezeilen.
4. In jeder Zeile wird ein Fahrer über eine Combobox ausgewählt.
5. Beim Speichern werden alle eingetragenen Fahrer zum Rennen angemeldet.
6. Jeder Fahrer erhält eine Startnummer.
7. Die Startnummern werden pro Rennen aufsteigend ab `1` vergeben.

Zusätzlich:

- Anmeldungen eines Rennens sollen für ein neues Rennen kopiert werden können
- eine nachträgliche Änderung von Rennanmeldungen ist nicht erforderlich

### 4.7 Ergebniserfassung

Ein Veranstalter kann nach dem Rennen Ergebnisse erfassen.

Dafür wird eine Tabelle erzeugt mit:

- allen Fahrern des Rennens
- sortiert nach Startnummer

Für jeden Fahrer werden erfasst:

- Platzierung
- Fahrzeit

Die Ergebniserfassung ist laut Aufgabenstellung:

- ein einmaliger Vorgang
- danach nicht veränderbar

### 4.8 Auswertungsbereich

Teamchefs sollen einen Auswertungsbereich für Trainingsdaten erhalten.

Filtermöglichkeiten:

- ein konkretes Trainingsziel
- oder `Alle Ziele`
- optional ein Zeitraum

Anzuzeigen sind für jeden Monat im gewählten Zeitraum die Kennzahlen der Trainingskilometer je Fahrer:

- Summe
- Durchschnitt
- Minimum
- Maximum
- Median
- Standardabweichung

## 5. Technische Pflichtbestandteile

Die Aufgabenstellung verlangt ausdrücklich Kapselung mit separaten Klassen und Funktionen.

Mindestens enthalten sein sollen:

- eine PHP-Funktion, die prüft, ob ein Team bei der Neuregistrierung bereits existiert
- eine PHP-Funktion, die ein neues Team in die Datenbank einträgt
- eine PHP-Funktion zum Speichern oder Ändern der Daten eines Fahrers
- eine PHP-Auswertungsklasse für Fahrerkennzahlen

Die Auswertungsklasse soll mindestens:

- für einen Fahrer, optional ein Trainingsziel und einen Zeitraum arbeiten
- die Werte intern speichern
- Getter- und Setter-Methoden besitzen
- für jeden Monat Summe, Durchschnitt, Minimum, Maximum, Median und Standardabweichung bereitstellen
- die Standardabweichung in einer eigenen Methode berechnen
- eine Zugriffsmethode für die berechneten Monatswerte bereitstellen

## 6. Datenbankpflichten

Laut Aufgabenstellung müssen zusätzlich Datenbankmechanismen eingesetzt werden:

- mindestens eine Stored Procedure
- mindestens ein Trigger
- die Startnummernvergabe soll über einen Trigger umgesetzt werden

Damit ist klar:

- nicht die gesamte Logik gehört in PHP
- ein Teil der fachlichen Verarbeitung soll bewusst in der Datenbank liegen

## 7. Verbindliches Datenbankschema

Die Datei [schema.sql](/Users/nicolasbiercher/Documents/Hochschule/Datenbanken Projekt/database/schema.sql) beschreibt nicht nur den aktuellen Stand, sondern den verbindlichen Soll-Zustand der Tabellenstruktur für dieses Projekt. Die Projektskizze übernimmt dieses Schema daher inhaltlich und schlägt keine abweichende Tabellenstruktur vor.

Verbindlich vorhandene Tabellen:

- `Teamchef`
- `Veranstalter`
- `Trainingsziele`
- `Team`
- `Fahrer`
- `Rennen`
- `teilnehmen`
- `Training`

## 8. Abgleich zwischen Anforderungen und verbindlichem Schema

### Durch das Schema bereits festgelegt

- `Teamchef` und `Veranstalter` sind getrennt modelliert
- `Team` verweist auf `Teamchef`
- `Fahrer` verweist auf `Team`
- `Rennen` verweist auf `Veranstalter`
- `teilnehmen` bildet die Zuordnung Fahrer zu Rennen ab
- `Trainingsziele` erlaubt erweiterbare Trainingsziele
- `Training` bildet fachlich ab:
  pro Fahrer und Tag genau ein Training

### Fachlich noch offen, aber nicht als Tabellenänderung zu verstehen

- Startnummernvergabe ist im Schema als Feld vorbereitet und soll zusätzlich per Trigger umgesetzt werden
- für die einmalige und danach unveränderliche Ergebniserfassung braucht es ein klares technisches Konzept in SQL und/oder PHP
- die verpflichtende Stored Procedure ist fachlich noch festzulegen
- die genaue Prämienlogik ist noch offen
- die Monatsauswertung mit Median und Standardabweichung muss sauber zwischen SQL und PHP aufgeteilt werden

## 9. Sinnvolle fachliche Kandidaten für Stored Procedure und Trigger

### Trigger

Pflichtkandidat:

- automatische Vergabe der Startnummer beim Einfügen einer Rennanmeldung

### Stored Procedure

Sinnvolle Kandidaten:

- Anmeldung mehrerer Fahrer zu einem Rennen
- Kopieren vorhandener Rennanmeldungen in ein neues Rennen
- geschütztes Speichern einer vollständigen Ergebnisliste

## 10. Priorisierte Umsetzung

### Kurzfristig

1. Startseite und Rollen-Einstiege sauber abbilden
2. Registrierung und Login für Teamchef und Veranstalter umsetzen
3. Team- und Fahrerverwaltung aufbauen

### Danach

1. Trainingserfassung implementieren
2. Rennanlage und Rennanmeldung umsetzen
3. Trigger für Startnummernvergabe ergänzen

### Anschließend

1. Ergebniserfassung absichern
2. Auswertungsbereich mit Kennzahlen umsetzen
3. Stored Procedure fachlich sauber integrieren

## 11. Offene Punkte für die weitere Arbeit

- Soll ein Teamchef fachlich genau ein Team besitzen oder mehrere Teams besitzen dürfen?
- Wie wird technisch sichergestellt, dass Rennergebnisse nach Erfassung nicht mehr geändert werden?
- Welche fachliche Operation eignet sich am besten für die verpflichtende Stored Procedure?
- Wie sollen Teamprämie und Veranstalterprämie genau berechnet werden?
- Welche Teile der Auswertung werden direkt in SQL vorbereitet und welche in PHP berechnet?

## 12. Arbeitsregel für dieses Projekt

Für die weitere Zusammenarbeit gilt:

- `database/schema.sql` ist die verbindliche Vorgabe für die Tabellenstruktur
- Anforderungen des Professors haben Vorrang vor meinen Interpretationen
- die Projektskizze muss das Schema übernehmen, nicht umdeuten
- offene Punkte betreffen Logik, Absicherung und Umsetzung, nicht eigenmächtige Änderungen der Tabellenstruktur

## 13. Aktueller Implementierungsstand

Der aktuelle Code setzt bereits einen ersten Teil der Anwendung um.

### Bereits umgesetzt

- einfacher Frontcontroller in `public/index.php`
- Routing nicht mehr direkt in HTML und Geschäftslogik vermischt, sondern über Controller getrennt
- gemeinsame Authentifizierung für `Teamchef` und `Veranstalter`
- beim Erstellen eines Teams wird der `Teamchef` direkt mit angelegt
- Registrierung für `Veranstalter`
- Login mit Passwort-Hash-Prüfung
- Session-basierte Anmeldung
- CSRF-Schutz für Formulare
- einfaches Dashboard nach erfolgreichem Login

### Aktuelle Projektstruktur

- `public/index.php`
  Einstiegspunkt der Anwendung
- `src/config/bootstrap.php`
  Laden der Grundfunktionen, Sessions und Hilfsfunktionen
- `src/config/database.php`
  Aufbau der PDO-Datenbankverbindung
- `src/controllers/AppController.php`
  zentrales Routing auf Controller-Ebene
- `src/controllers/HomeController.php`
  Startseite
- `src/controllers/AuthController.php`
  Registrierung, Login, Logout
- `src/controllers/DashboardController.php`
  geschützter Bereich nach Login
- `src/models/AuthService.php`
  Datenbanklogik für Registrierung und Login
- `src/views/layout/main.php`
  gemeinsames HTML-Grundlayout
- `src/views/home/index.php`
  Startseite
- `src/views/auth/form.php`
  Auth-Formular
- `src/views/dashboard/index.php`
  Dashboard

### Noch nicht umgesetzt

- Teamdaten ändern
- Fahrerverwaltung
- Trainingserfassung
- Rennverwaltung
- Rennanmeldung
- Ergebniserfassung
- Auswertungsbereich
- Trigger und Stored Procedure

### Regel für weitere Änderungen

Wenn neue Funktionen gebaut werden, soll die Projektskizze anschließend immer mit aktualisiert werden, damit fachlicher Stand, Code-Struktur und bereits umgesetzte Phasen synchron bleiben.
