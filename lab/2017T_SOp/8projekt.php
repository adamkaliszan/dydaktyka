<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
</head>
<?php
include_once '../../class.geshi.php';
//include_once '../../geshi/functions.geshi.php';
?>
<body>
<h1>Zadanie projektowe</h1>
<h2>Wymagania</h2>
<ul>
 <li>Obsługa gniazd sieciowych</li>
 <li>Obsługa wątków lub odpowiednie posługiwanie się funkcją select</li>
 <li>Znajomość zasad działania protokołu TCP</li>
</ul>
<h2>1 Wprowadzenie</h2>
<p>
Celem zajęć jest napisanie programów, które wzajemnie komunikują się i umożliwiają prowadzenie internetowych rozgrywek w najprostszą grę, np. kółko i krzyżyk. Sama gra nie jest tematem zadania tylko umiejętność napisania aplikacji działających w systemie rozproszonym.
</p>
<p>
System składać się będzie w 3 programów:
<ol>
 <li>Serwera</li>
 <li>Klienta dla gracza</li>
 <li>Bota</li>
</ol>
</p>
<p>
Serwer wykonuje następujące czynności:
<ul>
 <li>Pośredniczy pomiędzy graczami (klientami), graczem i botem lub botami.</li>
 <li>Przyjmuje graczy do gry, odbiera o nich informację o nicku gracza, który dołączył do gry.</li>
 <li>Analizuje każdy ruch i określa stan gry. W przypadku rozstrzygnięcia do obu stron wysyła odpowiednią wiadomość. Jeśli ruch jest niepoprawny to wysyła odpowiednią wiadomość do gracza lub bota.</li>
 <li>Określa, który gracz wykonuje jako pierwszy ruch.</li>
 <li>Pośredniczy w wymianie wiadomości tekstowych pomiędzy graczami.</li>
</ul>
</p>
<p>
Klient wykonuje następujące czynności
<ul>
 <li>Wczytuje ruchy od gracza i wysyła na serwer</li>
 <li>Wyświetla na ekrane ruchy gracza i przeciwnika (musi je odebrać z serwera)</li>
 <li>Umożliwia prowadzenie rozmowy tekstowej z drugim graczem</li>
</ul>
</p>
<p>
Bot wykonuje następujące czynności
<ul>
 <li>Sam wykonuje ruchy i przesyła na serwer</li>
</ul>
</p>
<h2>Zadanie 1</h2>
<p>
Dokonaj podziału grupy na 3 podzespoły. Każdy podzespół będzie pisał jeden z modułów.
</p>
<h2>Zadanie 2</h2>
<p>
Zaproponuj format wiadomości jakie będą wymieniane pomiędzy programami
</ul>
 <li>Przystąp do gry</li>
 <li>Potwierdzenie/odrzucenie odebranej wiadomości</li>
 <li>Wiadomość tekstowa</li>
 <li>Ruch</li>
 <li>Poddaj grę</li>
 <li>Zmiana stanu gry (koniec/poddanie)</li>
</ul>
Zalecanym formatem jest TLV (type length value). Można zastosować inne rozwiązanie, np. xml.
</p>

</p>
<h1>Podpowiedzi</h1>
<h2>Kod gry</h2>
<p>
Plik z zaimplementowaną grą: <a href=rozw2017t/gra.c>gra.c</a>
</p>
<p>
<?php
$source = file_get_contents(dirname(__FILE__).'/rozw2017t/gra.c');
$language = 'C++';
$geshi = new GeSHi($source, $language);
echo $geshi->parseCode();
?>
</p>
<h2>Przesyłanie wiadomości w formacie TLV</h2>
<p>
Wysłanie wiadomości wymaga wygenerowanie odpowiedniej tablicy znaków, a następnie wysyłanie jej za pomocą funkcji send. Każdy bajt w takiej wiadomości ma znaczenie. Podobnie w przypadku odczytu nalezy odpowiednio interpretować każdy ze znaków. MOdyfikacja funkcji do zapisu pociąga za sobą konieczność modyfikacji funkcji do odczytu. Całość można jednak uprościć stosując konstrowersyjne brudne rzutowanie.
</p>
<h3>Brudne rzutowanie</h3>
<p>
W celu implementacji protokołu z wiadomościami tylu TLV można zdefiniować struktury. Każda struktura odpowiada innej widomości.
Pola wewnątz struktury ułożone są w tej samej kolejności co poszczególne dane w protokole TVL. Zastosowanie struktury ułatwia wypełnianie wiadomości odpowiednimi informacjami. Co więcej plik nagłówkowy, w którym zdefiniowana jest taka struktura może być współdzielony pomiędzy wszystkimi modułami projektu.
</p>
<p>
Wiadomość można zapisywać do tablicy danych (bufora) za pośrednictwem wskaźnika do struktury z wiadomością. Wskaźnik ten należy ustawić na początek bufora. W ten sposób dokonywane jest brudne rzutowanie. Rzutujemy wskaźnik do struktury na tablicę bajtów, zatem na inny typ danych.
</p>
<h3>Uniwersalna struktura dla każdego typu wiadomości</h3>
<p>
Każda wiadomość ma taki sam nagłówek: Typ wiadomości, długość danych. W zależności od typu wiadomości różni się sama organizacja danych. Zatem dane można zapisać jako unię. Unia zawiera dalej struktury. Każda struktura reprezentuje dane dla określonego typu wiadomości. Na podstawie typu wiadomości można jednoznacznie określić do którego pola uni (struktury w niej zawartej) należy odwoływać się.
</p>
<h3>Problemy i kontrowersje</h3>
<ol>
 <li>Rezultatem błędu programisty może być odwoływanie się do złego typu z dannymi</li>
 <li>Problem endianów pozostaje nierozwiązany. W przypadku typów zapisanych na więcej niż 1 bajcie powinna być wywoływany funkcje typu htons, ntohs, htonl, ntohl. Zatem przed zapisem 16 (32) bitwego pola dane do zapisu powinny być konwertowane za pomocą funkcji htons(htonl). Analogicznie w przypadku odczytu dane należy przekonwertować przy pomocy funkcji ntohs(ntohl).</li>  
</ol>
</p>





</body>
